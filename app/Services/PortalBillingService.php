<?php

declare(strict_types=1);

namespace EduSphere\Services;

use EduSphere\Core\Database;
use EduSphere\Core\AuditLogger;
use RuntimeException;

class PortalBillingService
{
    /**
     * Get effective pricing policy for a school and term (School specific overrides Global Default)
     */
    public static function getEffectivePricing(int $schoolId, ?int $sessionId = null, ?int $termId = null): array
    {
        // 1. Try school-specific policy
        $schoolPolicy = Database::fetch(
            "SELECT * FROM portal_pricing_policies 
             WHERE school_id = ? AND status = 'active'
             ORDER BY id DESC LIMIT 1",
            [$schoolId]
        );

        if ($schoolPolicy) {
            return $schoolPolicy;
        }

        // 2. Fall back to Global Default policy
        $globalPolicy = Database::fetch(
            "SELECT * FROM portal_pricing_policies 
             WHERE school_id IS NULL AND status = 'active'
             ORDER BY id DESC LIMIT 1"
        );

        if ($globalPolicy) {
            return $globalPolicy;
        }

        // 3. Fallback safe standard
        return [
            'id' => 1,
            'policy_code' => 'DEFAULT-FALLBACK',
            'fee_per_student' => 3000.00,
            'currency' => 'NGN',
            'fee_payer' => 'parent',
            'status' => 'active',
        ];
    }

    /**
     * Get active revenue sharing policy for school
     */
    public static function getRevenueSharingPolicy(int $schoolId): array
    {
        $policy = Database::fetch(
            "SELECT * FROM revenue_sharing_policies 
             WHERE (school_id = ? OR school_id IS NULL) AND is_active = 1
             ORDER BY school_id DESC, id DESC LIMIT 1",
            [$schoolId]
        );

        return $policy ?: [
            'id' => 1,
            'policy_code' => 'DEFAULT-REVSHARE',
            'allocation_basis' => 'net_after_gateway',
            'platform_percentage' => 45.00,
            'school_incentive_percentage' => 40.00,
            'sales_rep_initial_rate' => 15.00,
            'sales_rep_recurring_rate' => 7.50,
            'gateway_fee_bearer' => 'platform',
        ];
    }

    /**
     * Get or create active agreement for school & term
     */
    public static function getOrCreateAgreement(int $schoolId, int $sessionId, int $termId): array
    {
        $agreement = Database::fetch(
            "SELECT a.*, p.policy_code, p.fee_payer, r.policy_code AS rev_policy_code,
                    r.school_incentive_percentage, r.platform_percentage, r.sales_rep_initial_rate
             FROM school_portal_fee_agreements a
             JOIN portal_pricing_policies p ON p.id = a.pricing_policy_id
             JOIN revenue_sharing_policies r ON r.id = a.revenue_policy_id
             WHERE a.school_id = ? AND a.session_id = ? AND a.term_id = ?",
            [$schoolId, $sessionId, $termId]
        );

        if ($agreement) {
            return $agreement;
        }

        $pricing = self::getEffectivePricing($schoolId, $sessionId, $termId);
        $revPolicy = self::getRevenueSharingPolicy($schoolId);

        $eligibleCount = (int)(Database::fetch(
            "SELECT COUNT(*) AS c FROM students WHERE school_id = ? AND status = 'enrolled'",
            [$schoolId]
        )['c'] ?? 0);

        Database::execute(
            "INSERT INTO school_portal_fee_agreements
             (school_id, session_id, term_id, pricing_policy_id, revenue_policy_id, proposed_fee_per_student, estimated_eligible_students, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'proposed')",
            [
                $schoolId, $sessionId, $termId,
                $pricing['id'], $revPolicy['id'],
                $pricing['fee_per_student'], $eligibleCount
            ]
        );

        return self::getOrCreateAgreement($schoolId, $sessionId, $termId);
    }

    /**
     * Accept school portal fee agreement
     */
    public static function acceptAgreement(int $agreementId, int $userId, ?string $notes = null): bool
    {
        $ag = Database::fetch("SELECT * FROM school_portal_fee_agreements WHERE id = ?", [$agreementId]);
        if (!$ag) {
            throw new RuntimeException("Fee agreement record not found.");
        }

        Database::execute(
            "UPDATE school_portal_fee_agreements 
             SET status = 'accepted', accepted_by = ?, accepted_at = NOW(), school_notes = ? 
             WHERE id = ?",
            [$userId, $notes ?? 'Accepted by authorized school administration', $agreementId]
        );

        AuditLogger::log('portal_fee_agreement_accepted', 'school_portal_fee_agreement', $agreementId, [
            'school_id' => $ag['school_id'],
            'term_id' => $ag['term_id'],
            'accepted_by' => $userId,
        ]);

        return true;
    }

    /**
     * Reject school portal fee agreement with reason
     */
    public static function rejectAgreement(int $agreementId, int $userId, string $reason): bool
    {
        $ag = Database::fetch("SELECT * FROM school_portal_fee_agreements WHERE id = ?", [$agreementId]);
        if (!$ag) {
            throw new RuntimeException("Fee agreement record not found.");
        }

        Database::execute(
            "UPDATE school_portal_fee_agreements 
             SET status = 'rejected', rejection_reason = ? 
             WHERE id = ?",
            [$reason, $agreementId]
        );

        AuditLogger::log('portal_fee_agreement_rejected', 'school_portal_fee_agreement', $agreementId, [
            'school_id' => $ag['school_id'],
            'term_id' => $ag['term_id'],
            'rejected_by' => $userId,
            'reason' => $reason,
        ]);

        return true;
    }

    /**
     * Term Billing Engine: Generates idempotent portal invoices for all eligible students
     */
    public static function generateTermInvoices(int $schoolId, int $sessionId, int $termId): array
    {
        $agreement = self::getOrCreateAgreement($schoolId, $sessionId, $termId);

        if ($agreement['status'] !== 'accepted') {
            throw new RuntimeException("Cannot bill students: School has not yet accepted the portal fee agreement for this term.");
        }

        $feePerStudent = (float)$agreement['proposed_fee_per_student'];

        // Retrieve all currently enrolled students for this tenant
        $students = Database::query(
            "SELECT s.id, s.parent_id, s.admission_number, s.first_name, s.last_name
             FROM students s
             WHERE s.school_id = ? AND s.status = 'enrolled'
             ORDER BY s.id ASC",
            [$schoolId]
        );

        $created = 0;
        $skipped = 0;
        $dueDate = date('Y-m-d', strtotime('+30 days'));
        $baseCount = (int)(Database::fetch("SELECT COUNT(*) AS c FROM portal_invoices WHERE school_id = ?", [$schoolId])['c'] ?? 0);

        foreach ($students as $stu) {
            $studentId = (int)$stu['id'];
            $idempHash = hash('sha256', "{$schoolId}:{$sessionId}:{$termId}:{$studentId}");

            // Check if already billed (Idempotency)
            $existing = Database::fetch(
                "SELECT id FROM portal_invoices WHERE idempotency_hash = ? OR (school_id = ? AND session_id = ? AND term_id = ? AND student_id = ?)",
                [$idempHash, $schoolId, $sessionId, $termId, $studentId]
            );

            if ($existing) {
                $skipped++;
                continue;
            }

            // Generate clean, school-scoped globally unique invoice number
            $baseCount++;
            $invNum = sprintf('PINV-%s-S%d-%04d', date('Y'), $schoolId, $baseCount);

            Database::execute(
                "INSERT INTO portal_invoices
                 (school_id, student_id, parent_id, agreement_id, session_id, term_id, invoice_number, gross_amount, discount_amount, amount_due, paid_amount, balance_due, status, due_date, idempotency_hash)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0.00, ?, 0.00, ?, 'unpaid', ?, ?)",
                [
                    $schoolId, $studentId, $stu['parent_id'], (int)$agreement['id'],
                    $sessionId, $termId, $invNum, $feePerStudent, $feePerStudent, $feePerStudent,
                    $dueDate, $idempHash
                ]
            );

            $created++;
        }

        AuditLogger::log('portal_term_invoices_generated', 'school_portal_fee_agreement', (int)$agreement['id'], [
            'school_id' => $schoolId,
            'session_id' => $sessionId,
            'term_id' => $termId,
            'created' => $created,
            'skipped' => $skipped,
        ]);

        return [
            'created' => $created,
            'skipped' => $skipped,
            'total_students' => count($students),
        ];
    }

    /**
     * Get School Term Billing Telemetry
     */
    public static function getTermSummary(int $schoolId, ?int $sessionId = null, ?int $termId = null): array
    {
        $where = "school_id = ?";
        $params = [$schoolId];

        if ($sessionId) {
            $where .= " AND session_id = ?";
            $params[] = $sessionId;
        }
        if ($termId) {
            $where .= " AND term_id = ?";
            $params[] = $termId;
        }

        $stats = Database::fetch(
            "SELECT 
                COUNT(*) AS total_invoices,
                IFNULL(SUM(amount_due), 0) AS total_invoiced,
                IFNULL(SUM(paid_amount), 0) AS total_collected,
                IFNULL(SUM(balance_due), 0) AS outstanding_balance,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid_count,
                SUM(CASE WHEN status = 'partially_paid' THEN 1 ELSE 0 END) AS partial_count,
                SUM(CASE WHEN status = 'unpaid' THEN 1 ELSE 0 END) AS unpaid_count,
                SUM(CASE WHEN status = 'waived' THEN 1 ELSE 0 END) AS waived_count
             FROM portal_invoices
             WHERE {$where}",
            $params
        );

        $totInv = (float)($stats['total_invoiced'] ?? 0.0);
        $totCol = (float)($stats['total_collected'] ?? 0.0);
        $rate = $totInv > 0 ? round(($totCol / $totInv) * 100, 1) : 0.0;

        // School incentive accrued and balance
        $incentiveStats = Database::fetch(
            "SELECT 
                IFNULL(SUM(CASE WHEN entry_type = 'accrual' THEN amount ELSE 0 END), 0) AS accrued,
                IFNULL(SUM(CASE WHEN entry_type = 'payout' THEN amount ELSE 0 END), 0) AS paid_out
             FROM school_incentive_ledger
             WHERE school_id = ?",
            [$schoolId]
        );

        $accrued = (float)($incentiveStats['accrued'] ?? 0.0);
        $paidOut = (float)($incentiveStats['paid_out'] ?? 0.0);
        $currentBalance = $accrued - $paidOut;

        $pendingPayouts = (float)(Database::fetch(
            "SELECT IFNULL(SUM(requested_amount), 0) AS p 
             FROM school_payout_requests 
             WHERE school_id = ? AND status = 'pending'",
            [$schoolId]
        )['p'] ?? 0.0);

        $withdrawableBalance = max(0.0, $currentBalance - $pendingPayouts);

        return [
            'total_invoices' => (int)($stats['total_invoices'] ?? 0),
            'total_invoiced' => $totInv,
            'total_collected' => $totCol,
            'outstanding_balance' => (float)($stats['outstanding_balance'] ?? 0.0),
            'collection_rate' => $rate,
            'paid_count' => (int)($stats['paid_count'] ?? 0),
            'partial_count' => (int)($stats['partial_count'] ?? 0),
            'unpaid_count' => (int)($stats['unpaid_count'] ?? 0),
            'waived_count' => (int)($stats['waived_count'] ?? 0),
            'incentive_accrued' => $accrued,
            'incentive_paid_out' => $paidOut,
            'pending_payouts' => $pendingPayouts,
            'incentive_balance' => max(0.0, $currentBalance),
            'withdrawable_balance' => $withdrawableBalance,
        ];
    }

    /**
     * Get Global Platform Telemetry for Super Admin Dashboard
     */
    public static function getPlatformGlobalStats(): array
    {
        $invStats = Database::fetch(
            "SELECT 
                COUNT(*) AS total_invoices,
                IFNULL(SUM(amount_due), 0) AS total_invoiced,
                IFNULL(SUM(paid_amount), 0) AS total_collected,
                IFNULL(SUM(balance_due), 0) AS total_outstanding
             FROM portal_invoices"
        );

        $allocStats = Database::fetch(
            "SELECT 
                IFNULL(SUM(platform_amount), 0) AS platform_revenue,
                IFNULL(SUM(school_incentive_amount), 0) AS school_incentives,
                IFNULL(SUM(sales_rep_commission_amount), 0) AS sales_commissions,
                IFNULL(SUM(gateway_fee), 0) AS gateway_fees,
                IFNULL(SUM(distributable_amount), 0) AS net_distributable
             FROM financial_allocations
             WHERE status = 'allocated'"
        );

        $eligibleStudents = (int)(Database::fetch(
            "SELECT COUNT(*) AS c FROM students WHERE status = 'enrolled'"
        )['c'] ?? 0);

        $billedStudents = (int)(Database::fetch(
            "SELECT COUNT(DISTINCT student_id) AS c FROM portal_invoices"
        )['c'] ?? 0);

        $totInv = (float)($invStats['total_invoiced'] ?? 0.0);
        $totCol = (float)($invStats['total_collected'] ?? 0.0);
        $rate = $totInv > 0 ? round(($totCol / $totInv) * 100, 1) : 0.0;

        $payoutPending = (float)(Database::fetch(
            "SELECT IFNULL(SUM(requested_amount), 0) AS total FROM school_payout_requests WHERE status = 'pending'"
        )['total'] ?? 0.0);

        return [
            'eligible_students' => $eligibleStudents,
            'billed_students' => $billedStudents,
            'total_invoices' => (int)($invStats['total_invoices'] ?? 0),
            'total_invoiced' => $totInv,
            'total_collected' => $totCol,
            'total_outstanding' => (float)($invStats['total_outstanding'] ?? 0.0),
            'collection_rate' => $rate,
            'platform_revenue' => (float)($allocStats['platform_revenue'] ?? 0.0),
            'school_incentives' => (float)($allocStats['school_incentives'] ?? 0.0),
            'sales_commissions' => (float)($allocStats['sales_commissions'] ?? 0.0),
            'gateway_fees' => (float)($allocStats['gateway_fees'] ?? 0.0),
            'net_distributable' => (float)($allocStats['net_distributable'] ?? 0.0),
            'payouts_pending' => $payoutPending,
        ];
    }
}
