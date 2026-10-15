<?php

declare(strict_types=1);

namespace EduSphere\Services;

use EduSphere\Core\Database;
use EduSphere\Core\AuditLogger;
use RuntimeException;

class RevenueSharingService
{
    /**
     * Record a verified payment for a portal invoice and immediately execute revenue allocation
     */
    public static function recordPaymentAndAllocate(
        int $invoiceId,
        float $amount,
        string $gateway = 'paystack',
        ?string $paymentRef = null,
        ?string $payerName = null,
        ?string $payerEmail = null,
        ?int $verifiedBy = null
    ): array {
        if ($amount <= 0) {
            throw new RuntimeException("Payment amount must be greater than zero.");
        }

        $inv = Database::fetch(
            "SELECT i.*, a.revenue_policy_id 
             FROM portal_invoices i
             JOIN school_portal_fee_agreements a ON a.id = i.agreement_id
             WHERE i.id = ?",
            [$invoiceId]
        );

        if (!$inv) {
            throw new RuntimeException("Portal invoice not found.");
        }

        if ($inv['status'] === 'paid') {
            throw new RuntimeException("This invoice has already been fully settled.");
        }

        $balanceDue = (float)$inv['balance_due'];
        if ($amount > $balanceDue + 0.01) {
            throw new RuntimeException("Payment amount (₦" . number_format($amount, 2) . ") exceeds balance due (₦" . number_format($balanceDue, 2) . ").");
        }

        $ref = $paymentRef ?: 'PAY-PTL-' . date('Ymd') . '-' . rand(10000, 99999);

        // Check if payment reference already recorded (Idempotency)
        $existingPay = Database::fetch("SELECT id FROM portal_payments WHERE payment_reference = ?", [$ref]);
        if ($existingPay) {
            throw new RuntimeException("Payment reference '{$ref}' has already been processed.");
        }

        // Calculate gateway fee (e.g., 1.5% capped at ₦2,000 for Paystack)
        $gwFee = 0.00;
        if ($gateway === 'paystack') {
            $gwFee = min(2000.00, round($amount * 0.015, 2));
        }

        $netAmount = max(0.00, $amount - $gwFee);
        $schoolId = (int)$inv['school_id'];
        $studentId = (int)$inv['student_id'];
        $parentId = $inv['parent_id'] ? (int)$inv['parent_id'] : null;

        // 1. Insert Portal Payment
        Database::execute(
            "INSERT INTO portal_payments
             (school_id, invoice_id, student_id, parent_id, payment_reference, gateway, amount, gateway_fee, net_amount, status, payer_name, payer_email, verified_at, verified_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'verified', ?, ?, NOW(), ?)",
            [
                $schoolId, $invoiceId, $studentId, $parentId,
                $ref, $gateway, $amount, $gwFee, $netAmount,
                $payerName, $payerEmail, $verifiedBy
            ]
        );
        $paymentId = (int)Database::lastInsertId();

        // 2. Update Portal Invoice
        $newPaid = (float)$inv['paid_amount'] + $amount;
        $newBal = max(0.00, (float)$inv['amount_due'] - $newPaid);
        $newStatus = ($newBal <= 0.01) ? 'paid' : 'partially_paid';

        Database::execute(
            "UPDATE portal_invoices 
             SET paid_amount = ?, balance_due = ?, status = ?, updated_at = NOW() 
             WHERE id = ?",
            [$newPaid, $newBal, $newStatus, $invoiceId]
        );

        // 3. Retrieve Revenue Sharing Policy
        $revPolicy = Database::fetch(
            "SELECT * FROM revenue_sharing_policies WHERE id = ?",
            [(int)$inv['revenue_policy_id']]
        );

        if (!$revPolicy) {
            $revPolicy = PortalBillingService::getRevenueSharingPolicy($schoolId);
        }

        // 4. Distributable Basis
        $distributable = ($revPolicy['allocation_basis'] === 'net_after_gateway') ? $netAmount : $amount;

        // 5. Check Sales Representative Attribution & Tier
        $lead = Database::fetch(
            "SELECT rep_id FROM sales_leads WHERE created_school_id = ? LIMIT 1",
            [$schoolId]
        );
        $salesRepId = $lead ? (int)$lead['rep_id'] : null;

        // Determine if initial term or renewal
        $termsPaidCount = (int)(Database::fetch(
            "SELECT COUNT(DISTINCT term_id) AS c FROM portal_invoices WHERE school_id = ? AND status = 'paid'",
            [$schoolId]
        )['c'] ?? 0);

        $commTier = ($termsPaidCount <= 3) ? 'initial' : 'recurring';
        $repRate = ($commTier === 'initial') ? (float)$revPolicy['sales_rep_initial_rate'] : (float)$revPolicy['sales_rep_recurring_rate'];

        $repComm = 0.00;
        if ($salesRepId) {
            $repComm = round($distributable * ($repRate / 100), 2);
        }

        $schRate = (float)$revPolicy['school_incentive_percentage'];
        $schIncentive = round($distributable * ($schRate / 100), 2);

        // Platform gets exact remainder to avoid any penny rounding discrepancies
        $platformAmount = round($distributable - ($schIncentive + $repComm), 2);

        // 6. Record Financial Allocation
        Database::execute(
            "INSERT INTO financial_allocations
             (payment_id, school_id, invoice_id, revenue_policy_id, gross_amount, gateway_fee, distributable_amount, platform_amount, school_incentive_amount, sales_rep_id, sales_rep_commission_amount, commission_tier, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'allocated')",
            [
                $paymentId, $schoolId, $invoiceId, (int)$revPolicy['id'],
                $amount, $gwFee, $distributable, $platformAmount,
                $schIncentive, $salesRepId, $repComm, $commTier
            ]
        );
        $allocationId = (int)Database::lastInsertId();

        // 7. Post School Incentive Ledger Entry (Accrual)
        $lastBal = (float)(Database::fetch(
            "SELECT running_balance FROM school_incentive_ledger WHERE school_id = ? ORDER BY id DESC LIMIT 1",
            [$schoolId]
        )['running_balance'] ?? 0.0);

        $newSchBal = $lastBal + $schIncentive;
        Database::execute(
            "INSERT INTO school_incentive_ledger
             (school_id, allocation_id, term_id, entry_type, amount, running_balance, reference, notes)
             VALUES (?, ?, ?, 'accrual', ?, ?, ?, 'Portal fee payment incentive allocation')",
            [$schoolId, $allocationId, (int)$inv['term_id'], $schIncentive, $newSchBal, $ref]
        );

        // 8. Post Sales Commission Ledger Entry
        if ($salesRepId && $repComm > 0) {
            Database::execute(
                "INSERT INTO portal_commission_ledger
                 (rep_id, school_id, allocation_id, term_id, commission_tier, rate_applied, commission_amount, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
                [$salesRepId, $schoolId, $allocationId, (int)$inv['term_id'], $commTier, $repRate, $repComm]
            );
        }

        AuditLogger::log('portal_payment_allocated', 'portal_payment', $paymentId, [
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'distributable' => $distributable,
            'platform' => $platformAmount,
            'school_incentive' => $schIncentive,
            'sales_rep_comm' => $repComm,
            'reference' => $ref,
        ]);

        // Send instant automated payment confirmation notice to parent/guardian
        try {
            BillingCommunicationService::sendPaymentConfirmationNotice($paymentId);
        } catch (\Throwable $e) {
            // Non-blocking notification failure
        }

        return [
            'payment_id' => $paymentId,
            'allocation_id' => $allocationId,
            'reference' => $ref,
            'distributable' => $distributable,
            'platform_share' => $platformAmount,
            'school_incentive' => $schIncentive,
            'rep_commission' => $repComm,
            'invoice_balance' => $newBal,
            'invoice_status' => $newStatus,
        ];
    }

    /**
     * Submit school incentive payout request
     */
    public static function requestSchoolPayout(
        int $schoolId,
        float $requestedAmount,
        string $bankName,
        string $accountNumber,
        string $accountName,
        int $requestedBy
    ): int {
        if ($requestedAmount <= 0) {
            throw new RuntimeException("Payout request amount must be greater than zero.");
        }

        // Check available withdrawable balance (excluding pending requests)
        $summary = PortalBillingService::getTermSummary($schoolId);
        $avail = $summary['withdrawable_balance'] ?? $summary['incentive_balance'];

        if ($requestedAmount > $avail) {
            throw new RuntimeException("Requested amount (₦" . number_format($requestedAmount, 2) . ") exceeds available withdrawable incentive balance (₦" . number_format($avail, 2) . ").");
        }

        $requestCode = 'POUT-SCH-' . date('Ymd') . '-' . rand(1000, 9999);

        Database::execute(
            "INSERT INTO school_payout_requests
             (school_id, request_code, requested_amount, bank_name, account_number, account_name, status, requested_by)
             VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)",
            [$schoolId, $requestCode, $requestedAmount, $bankName, $accountNumber, $accountName, $requestedBy]
        );

        $payoutId = (int)Database::lastInsertId();

        AuditLogger::log('school_payout_requested', 'school_payout_request', $payoutId, [
            'school_id' => $schoolId,
            'amount' => $requestedAmount,
            'request_code' => $requestCode,
        ]);

        return $payoutId;
    }

    /**
     * Super Admin Approval & Execution of School Incentive Payout
     */
    public static function approveSchoolPayout(int $payoutId, int $approverId, string $disbursementRef): bool
    {
        $po = Database::fetch("SELECT * FROM school_payout_requests WHERE id = ?", [$payoutId]);
        if (!$po || $po['status'] !== 'pending') {
            throw new RuntimeException("Payout request not found or not in pending state.");
        }

        $schoolId = (int)$po['school_id'];
        $amount = (float)$po['requested_amount'];

        Database::execute(
            "UPDATE school_payout_requests 
             SET status = 'paid', approved_by = ?, approved_at = NOW(), paid_at = NOW(), disbursement_reference = ? 
             WHERE id = ?",
            [$approverId, $disbursementRef, $payoutId]
        );

        // Deduct from school incentive ledger (debit)
        $lastBal = (float)(Database::fetch(
            "SELECT running_balance FROM school_incentive_ledger WHERE school_id = ? ORDER BY id DESC LIMIT 1",
            [$schoolId]
        )['running_balance'] ?? 0.0);

        $newBal = max(0.0, $lastBal - $amount);

        Database::execute(
            "INSERT INTO school_incentive_ledger
             (school_id, term_id, entry_type, amount, running_balance, reference, notes)
             VALUES (?, NULL, 'payout', ?, ?, ?, 'Approved bank payout disbursement')",
            [$schoolId, -$amount, $newBal, $disbursementRef]
        );

        AuditLogger::log('school_payout_approved_paid', 'school_payout_request', $payoutId, [
            'school_id' => $schoolId,
            'amount' => $amount,
            'disbursement_ref' => $disbursementRef,
            'approver_id' => $approverId,
        ]);

        return true;
    }

    /**
     * Process Refund or Transaction Reversal with symmetrical ledger debit adjustments
     */
    public static function processRefund(int $paymentId, string $reason, int $authorizedBy): bool
    {
        $pay = Database::fetch("SELECT * FROM portal_payments WHERE id = ?", [$paymentId]);
        if (!$pay || $pay['status'] !== 'verified') {
            throw new RuntimeException("Payment not found or cannot be refunded.");
        }

        $alloc = Database::fetch("SELECT * FROM financial_allocations WHERE payment_id = ?", [$paymentId]);
        if (!$alloc) {
            throw new RuntimeException("Allocation record not found.");
        }

        $schoolId = (int)$pay['school_id'];
        $invoiceId = (int)$pay['invoice_id'];
        $amount = (float)$pay['amount'];

        // 1. Mark Payment Refunded
        Database::execute("UPDATE portal_payments SET status = 'refunded', notes = ? WHERE id = ?", [$reason, $paymentId]);

        // 2. Mark Allocation Reversed
        Database::execute(
            "UPDATE financial_allocations SET status = 'reversed', reversal_reason = ? WHERE id = ?",
            [$reason, (int)$alloc['id']]
        );

        // 3. Restore Invoice Balance
        Database::execute(
            "UPDATE portal_invoices 
             SET paid_amount = GREATEST(0.00, paid_amount - ?), 
                 balance_due = LEAST(amount_due, balance_due + ?),
                 status = 'unpaid', updated_at = NOW() 
             WHERE id = ?",
            [$amount, $amount, $invoiceId]
        );

        // 4. Reverse School Incentive
        $schInc = (float)$alloc['school_incentive_amount'];
        if ($schInc > 0) {
            $lastBal = (float)(Database::fetch(
                "SELECT running_balance FROM school_incentive_ledger WHERE school_id = ? ORDER BY id DESC LIMIT 1",
                [$schoolId]
            )['running_balance'] ?? 0.0);

            $newBal = max(0.0, $lastBal - $schInc);
            Database::execute(
                "INSERT INTO school_incentive_ledger
                 (school_id, allocation_id, term_id, entry_type, amount, running_balance, reference, notes)
                 VALUES (?, ?, NULL, 'reversal', ?, ?, ?, ?)",
                [$schoolId, (int)$alloc['id'], -$schInc, $newBal, $pay['payment_reference'], "Refund reversal: {$reason}"]
            );
        }

        // 5. Reverse Sales Commission
        if ($alloc['sales_rep_id']) {
            Database::execute(
                "UPDATE portal_commission_ledger SET status = 'reversed' WHERE allocation_id = ?",
                [(int)$alloc['id']]
            );
        }

        AuditLogger::log('portal_payment_refunded', 'portal_payment', $paymentId, [
            'amount' => $amount,
            'reason' => $reason,
            'authorized_by' => $authorizedBy,
        ]);

        return true;
    }
}
