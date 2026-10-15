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
            'platform_percentage' => 60.00,
            'school_incentive_percentage' => 25.00,
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
     * Applies configurable billing rules: standard fees, sibling discounts, scholarships, and exemptions.
     */
    public static function generateTermInvoices(int $schoolId, ?int $sessionId = null, ?int $termId = null): array
    {
        // 1. Auto-identify active session and term if not explicitly supplied
        if (!$sessionId) {
            $sess = Database::fetch("SELECT id FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);
            $sessionId = $sess ? (int)$sess['id'] : 1;
        }
        if (!$termId) {
            $t = Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);
            $termId = $t ? (int)$t['id'] : 1;
        }

        // 2. Retrieve approved fee arrangement for this school & term
        $agreement = self::getOrCreateAgreement($schoolId, $sessionId, $termId);

        if ($agreement['status'] !== 'accepted') {
            throw new RuntimeException("Cannot bill students: School has not yet accepted the portal fee agreement for this term.");
        }

        $feePerStudent = (float)$agreement['proposed_fee_per_student'];
        $pricingPolicy = Database::fetch("SELECT * FROM portal_pricing_policies WHERE id = ?", [(int)$agreement['pricing_policy_id']]);

        // 3. Retrieve all currently enrolled students for this tenant (excludes withdrawn/graduated)
        $students = Database::query(
            "SELECT s.id, s.parent_id, s.admission_number, s.first_name, s.last_name, s.created_at
             FROM students s
             WHERE s.school_id = ? AND s.status = 'enrolled'
             ORDER BY s.parent_id ASC, s.id ASC",
            [$schoolId]
        );

        $created = 0;
        $skipped = 0;
        $totalGross = 0.0;
        $totalDiscounts = 0.0;
        $totalNet = 0.0;
        $siblingDiscountsCount = 0;
        $exemptionsCount = 0;
        $dueDate = date('Y-m-d', strtotime('+30 days'));
        $baseCount = (int)(Database::fetch("SELECT COUNT(*) AS c FROM portal_invoices WHERE school_id = ?", [$schoolId])['c'] ?? 0);

        // Track sibling counts per parent for family discount rule
        $parentChildrenCount = [];

        foreach ($students as $stu) {
            $studentId = (int)$stu['id'];
            $parentId = $stu['parent_id'] ? (int)$stu['parent_id'] : null;

            if ($parentId) {
                $parentChildrenCount[$parentId] = ($parentChildrenCount[$parentId] ?? 0) + 1;
                $childIndexInFamily = $parentChildrenCount[$parentId];
            } else {
                $childIndexInFamily = 1;
            }

            $idempHash = hash('sha256', "{$schoolId}:{$sessionId}:{$termId}:{$studentId}");

            // Idempotency check: Never charge twice for the same student and term
            $existing = Database::fetch(
                "SELECT id FROM portal_invoices WHERE idempotency_hash = ? OR (school_id = ? AND session_id = ? AND term_id = ? AND student_id = ?)",
                [$idempHash, $schoolId, $sessionId, $termId, $studentId]
            );

            if ($existing) {
                $skipped++;
                continue;
            }

            // Evaluate configurable billing rules
            $gross = $feePerStudent;
            $discount = 0.0;
            $ruleName = 'Standard full-term fee';
            $eligibilityCategory = 'enrolled';

            // Rule: Sibling & Family Discount (10% discount on 2nd and subsequent siblings of the same parent)
            if ($childIndexInFamily >= 2 && $parentId !== null) {
                $discount = round($gross * 0.10, 2);
                $ruleName = "Sibling family discount (Child #{$childIndexInFamily}, 10%)";
                $eligibilityCategory = 'sibling_discount';
                $siblingDiscountsCount++;
            }

            $amountDue = max(0.0, $gross - $discount);
            $balanceDue = $amountDue;
            $status = ($amountDue <= 0.001) ? 'waived' : 'unpaid';

            if ($status === 'waived') {
                $exemptionsCount++;
            }

            // Generate clean, school-scoped globally unique invoice number
            $baseCount++;
            $invNum = sprintf('PINV-%s-S%d-%04d', date('Y'), $schoolId, $baseCount);

            Database::execute(
                "INSERT INTO portal_invoices
                 (school_id, student_id, parent_id, agreement_id, session_id, term_id, invoice_number, gross_amount, discount_amount, waiver_reason, amount_due, paid_amount, balance_due, status, due_date, idempotency_hash, eligibility_category, adjustment_reason)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0.00, ?, ?, ?, ?, ?, ?)",
                [
                    $schoolId, $studentId, $parentId, (int)$agreement['id'],
                    $sessionId, $termId, $invNum, $gross, $discount,
                    ($discount > 0 ? $ruleName : null),
                    $amountDue, $balanceDue, $status,
                    $dueDate, $idempHash, $eligibilityCategory, $ruleName
                ]
            );
            $createdInvoiceId = (int)Database::lastInsertId();

            // Record itemized breakdown for audit transparency
            Database::execute(
                "INSERT INTO portal_invoice_items 
                 (invoice_id, item_type, description, unit_price, quantity, amount)
                 VALUES (?, 'base_fee', 'Term portal cloud access, CBT & continuous assessment fee', ?, 1, ?)",
                [$createdInvoiceId, $gross, $gross]
            );

            if ($discount > 0) {
                Database::execute(
                    "INSERT INTO portal_invoice_items 
                     (invoice_id, item_type, description, unit_price, quantity, amount)
                     VALUES (?, 'sibling_discount', ?, ?, 1, ?)",
                    [$createdInvoiceId, $ruleName, -$discount, -$discount]
                );
            }

            $created++;
            $totalGross += $gross;
            $totalDiscounts += $discount;
            $totalNet += $amountDue;
        }

        AuditLogger::log('portal_term_invoices_generated', 'school_portal_fee_agreement', (int)$agreement['id'], [
            'school_id' => $schoolId,
            'session_id' => $sessionId,
            'term_id' => $termId,
            'created' => $created,
            'skipped' => $skipped,
            'total_gross' => $totalGross,
            'total_net' => $totalNet,
            'sibling_discounts' => $siblingDiscountsCount,
        ]);

        return [
            'created' => $created,
            'skipped' => $skipped,
            'total_students' => count($students),
            'total_gross_billed' => $totalGross,
            'total_discounts_applied' => $totalDiscounts,
            'total_net_due' => $totalNet,
            'sibling_discounts_count' => $siblingDiscountsCount,
            'exemptions_count' => $exemptionsCount,
            'school_id' => $schoolId,
            'session_id' => $sessionId,
            'term_id' => $termId,
        ];
    }

    /**
     * School-Level Billing Summary Telemetry
     */
    public static function getSchoolBillingSummary(int $schoolId, ?int $sessionId = null, ?int $termId = null): array
    {
        return self::getTermSummary($schoolId, $sessionId, $termId);
    }

    /**
     * Consolidated Multi-Ward Payment Engine:
     * Allocates a parent's lump-sum payment across individual child portal invoices
     */
    public static function payConsolidatedInvoices(
        int $schoolId,
        int $parentId,
        float $totalAmount,
        string $gateway = 'paystack',
        ?string $payerName = null,
        ?string $payerEmail = null,
        ?int $verifiedBy = null
    ): array {
        if ($totalAmount <= 0) {
            throw new RuntimeException("Consolidated payment amount must be greater than zero.");
        }

        // Fetch all unsettled portal invoices for this parent's wards in this school
        $invoices = Database::query(
            "SELECT pi.*, s.first_name, s.last_name 
             FROM portal_invoices pi
             JOIN students s ON s.id = pi.student_id
             WHERE (pi.parent_id = ? OR s.parent_id = ?) 
               AND pi.school_id = ? 
               AND pi.status IN ('unpaid', 'partially_paid') 
               AND pi.balance_due > 0
             ORDER BY pi.id ASC",
            [$parentId, $parentId, $schoolId]
        );

        if (empty($invoices)) {
            throw new RuntimeException("No outstanding portal invoices found for your family account.");
        }

        $remainingToAllocate = $totalAmount;
        $allocations = [];

        foreach ($invoices as $inv) {
            if ($remainingToAllocate <= 0) {
                break;
            }

            $bal = (float)$inv['balance_due'];
            $payForThis = min($remainingToAllocate, $bal);

            $payRef = 'PAY-CONS-' . date('Ymd') . '-' . rand(10000, 99999);
            $res = RevenueSharingService::recordPaymentAndAllocate(
                (int)$inv['id'],
                $payForThis,
                $gateway,
                $payRef,
                $payerName,
                $payerEmail,
                $verifiedBy
            );

            $allocations[] = [
                'invoice_id' => (int)$inv['id'],
                'invoice_number' => $inv['invoice_number'],
                'student_name' => $inv['first_name'] . ' ' . $inv['last_name'],
                'amount_allocated' => $payForThis,
                'payment_reference' => $payRef,
                'status' => $res['invoice_status'],
            ];

            $remainingToAllocate -= $payForThis;
        }

        AuditLogger::log('parent_consolidated_payment', 'parents', $parentId, [
            'school_id' => $schoolId,
            'total_paid' => $totalAmount - $remainingToAllocate,
            'invoices_credited' => count($allocations),
            'allocations' => $allocations,
        ]);

        return [
            'total_allocated' => round($totalAmount - $remainingToAllocate, 2),
            'unallocated_change' => round($remainingToAllocate, 2),
            'allocations' => $allocations,
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

        $payoutCompleted = (float)(Database::fetch(
            "SELECT IFNULL(SUM(requested_amount), 0) AS total FROM school_payout_requests WHERE status = 'paid'"
        )['total'] ?? 0.0);

        $refundsTotal = (float)(Database::fetch(
            "SELECT IFNULL(SUM(amount), 0) AS total FROM portal_payments WHERE status IN ('refunded', 'reversed')"
        )['total'] ?? 0.0);

        $defaultPricing = Database::fetch(
            "SELECT fee_per_student, currency FROM portal_pricing_policies WHERE school_id IS NULL AND status = 'active' ORDER BY id DESC LIMIT 1"
        );
        $currentDefaultFee = (float)($defaultPricing['fee_per_student'] ?? 3000.00);

        return [
            'current_default_fee' => $currentDefaultFee,
            'currency' => $defaultPricing['currency'] ?? 'NGN',
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
            'refunds_and_reversals' => $refundsTotal,
            'net_distributable' => (float)($allocStats['net_distributable'] ?? 0.0),
            'payouts_pending' => $payoutPending,
            'payouts_completed' => $payoutCompleted,
        ];
    }

    /**
     * Term-by-Term Financial Trends aggregation from real DB records
     */
    public static function getTermTrends(): array
    {
        return Database::query(
            "SELECT 
                i.session_id,
                i.term_id,
                IFNULL(s.name, CONCAT('Session #', i.session_id)) AS session_name,
                IFNULL(t.name, CONCAT('Term #', i.term_id)) AS term_name,
                COUNT(i.id) AS total_invoices,
                COUNT(DISTINCT i.student_id) AS students_billed,
                IFNULL(SUM(i.amount_due), 0) AS total_invoiced,
                IFNULL(SUM(i.paid_amount), 0) AS total_collected,
                IFNULL(SUM(i.balance_due), 0) AS total_outstanding,
                SUM(CASE WHEN i.status = 'paid' THEN 1 ELSE 0 END) AS paid_count,
                SUM(CASE WHEN i.status = 'waived' THEN 1 ELSE 0 END) AS waived_count,
                (SELECT IFNULL(SUM(fa.platform_amount), 0) 
                 FROM financial_allocations fa 
                 JOIN portal_invoices pi ON pi.id = fa.invoice_id 
                 WHERE pi.session_id = i.session_id AND pi.term_id = i.term_id AND fa.status = 'allocated') AS platform_revenue,
                (SELECT IFNULL(SUM(fa.school_incentive_amount), 0) 
                 FROM financial_allocations fa 
                 JOIN portal_invoices pi ON pi.id = fa.invoice_id 
                 WHERE pi.session_id = i.session_id AND pi.term_id = i.term_id AND fa.status = 'allocated') AS school_incentives,
                (SELECT IFNULL(SUM(fa.sales_rep_commission_amount), 0) 
                 FROM financial_allocations fa 
                 JOIN portal_invoices pi ON pi.id = fa.invoice_id 
                 WHERE pi.session_id = i.session_id AND pi.term_id = i.term_id AND fa.status = 'allocated') AS sales_commissions
             FROM portal_invoices i
             LEFT JOIN academic_sessions s ON s.id = i.session_id
             LEFT JOIN academic_terms t ON t.id = i.term_id
             GROUP BY i.session_id, i.term_id, s.name, t.name
             ORDER BY i.session_id DESC, i.term_id DESC"
        );
    }

    /**
     * Request clarification or amendment on a proposed fee arrangement
     */
    public static function requestClarification(int $agreementId, int $userId, string $clarification): bool
    {
        $ag = Database::fetch("SELECT * FROM school_portal_fee_agreements WHERE id = ?", [$agreementId]);
        if (!$ag) {
            throw new RuntimeException("Fee agreement record not found.");
        }

        Database::execute(
            "UPDATE school_portal_fee_agreements 
             SET status = 'amendment_requested', clarification_requested = ? 
             WHERE id = ?",
            [$clarification, $agreementId]
        );

        AuditLogger::log('portal_fee_agreement_clarification_requested', 'school_portal_fee_agreement', $agreementId, [
            'school_id' => $ag['school_id'],
            'term_id' => $ag['term_id'],
            'requested_by' => $userId,
            'clarification' => $clarification,
        ]);

        return true;
    }

    /**
     * Apply authorized billing adjustment (scholarship, discount, waiver, mid-term correction)
     */
    public static function applyBillingAdjustment(
        int $invoiceId,
        int $userId,
        string $adjustmentType,
        float $discountAmount,
        string $reason
    ): array {
        $inv = Database::fetch("SELECT * FROM portal_invoices WHERE id = ?", [$invoiceId]);
        if (!$inv) {
            throw new RuntimeException("Invoice not found.");
        }

        $gross = (float)$inv['gross_amount'];
        $paid = (float)$inv['paid_amount'];

        if ($discountAmount < 0 || $discountAmount > $gross) {
            throw new RuntimeException("Discount amount cannot be negative or exceed the gross invoice amount.");
        }

        $newAmountDue = max(0.0, $gross - $discountAmount);
        $newBalanceDue = max(0.0, $newAmountDue - $paid);

        $newStatus = $inv['status'];
        if ($newBalanceDue <= 0.001) {
            $newStatus = ($paid > 0) ? 'paid' : 'waived';
        } elseif ($paid > 0) {
            $newStatus = 'partially_paid';
        } else {
            $newStatus = 'unpaid';
        }

        Database::execute(
            "UPDATE portal_invoices 
             SET discount_amount = ?, waiver_reason = ?, amount_due = ?, balance_due = ?, 
                 status = ?, adjustment_reason = ?, adjusted_by = ?, adjusted_at = NOW(), updated_at = NOW()
             WHERE id = ?",
            [
                $discountAmount,
                $reason,
                $newAmountDue,
                $newBalanceDue,
                $newStatus,
                "{$adjustmentType}: {$reason}",
                $userId,
                $invoiceId
            ]
        );

        AuditLogger::log('portal_invoice_adjustment', 'portal_invoices', $invoiceId, [
            'school_id' => $inv['school_id'],
            'adjustment_type' => $adjustmentType,
            'discount_amount' => $discountAmount,
            'new_amount_due' => $newAmountDue,
            'new_balance_due' => $newBalanceDue,
            'reason' => $reason,
            'adjusted_by' => $userId,
        ]);

        return [
            'invoice_id' => $invoiceId,
            'new_amount_due' => $newAmountDue,
            'new_balance_due' => $newBalanceDue,
            'status' => $newStatus,
        ];
    }

    /**
     * Dispatch payment reminders to parents of unpaid / partially paid invoices
     */
    public static function sendPaymentReminders(int $schoolId, int $termId, int $userId): int
    {
        $unpaidInvoices = Database::query(
            "SELECT id, student_id, parent_id, invoice_number, balance_due 
             FROM portal_invoices 
             WHERE school_id = ? AND term_id = ? AND status IN ('unpaid', 'partially_paid') AND balance_due > 0",
            [$schoolId, $termId]
        );

        $count = count($unpaidInvoices);
        if ($count > 0) {
            $ag = Database::fetch("SELECT id FROM school_portal_fee_agreements WHERE school_id = ? AND term_id = ? LIMIT 1", [$schoolId, $termId]);
            $agId = $ag ? (int)$ag['id'] : 1;

            Database::execute(
                "INSERT INTO portal_billing_notices
                 (school_id, agreement_id, term_id, title, notice_body, notice_type, sent_via, recipient_count, is_published, created_by)
                 VALUES (?, ?, ?, ?, ?, 'payment_reminder', 'portal_announcement', ?, 1, ?)",
                [
                    $schoolId,
                    $agId,
                    $termId,
                    'Payment Reminder: Outstanding Term Portal Fee',
                    "Friendly reminder from school administration: The academic term portal access fee has an outstanding balance. Kindly settle via the online parent portal to ensure uninterrupted access to continuous assessments and results.",
                    $count,
                    $userId
                ]
            );

            AuditLogger::log('portal_payment_reminders_dispatched', 'portal_billing_notices', (int)Database::lastInsertId(), [
                'school_id' => $schoolId,
                'term_id' => $termId,
                'reminders_sent' => $count,
                'dispatched_by' => $userId,
            ]);
        }

        return $count;
    }
}

