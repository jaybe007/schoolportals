<?php

declare(strict_types=1);

namespace EduSphere\Services;

use EduSphere\Core\Database;
use EduSphere\Core\AuditLogger;
use RuntimeException;

class RefundManagementService
{
    /**
     * Submit a formal refund request for a verified payment
     */
    public static function requestRefund(
        int $paymentId,
        float $amount,
        string $reason,
        int $requestedBy
    ): int {
        $payment = Database::fetch("SELECT * FROM portal_payments WHERE id = ?", [$paymentId]);
        if (!$payment || $payment['status'] !== 'verified') {
            throw new RuntimeException("Payment record not found or not eligible for refund.");
        }

        if ($amount <= 0 || $amount > (float)$payment['amount'] + 0.01) {
            throw new RuntimeException("Refund amount must be between ₦0.01 and ₦" . number_format((float)$payment['amount'], 2) . ".");
        }

        $existing = Database::fetch(
            "SELECT id FROM portal_refunds WHERE payment_id = ? AND status IN ('requested', 'approved', 'processed')",
            [$paymentId]
        );
        if ($existing) {
            throw new RuntimeException("A refund request has already been logged for this payment transaction.");
        }

        $refundRef = sprintf('RFND-%s-%s', date('Ymd'), bin2hex(random_bytes(3)));

        Database::execute(
            "INSERT INTO portal_refunds
             (payment_id, invoice_id, school_id, refund_reference, amount, reason, status, requested_by)
             VALUES (?, ?, ?, ?, ?, ?, 'requested', ?)",
            [
                $paymentId, (int)$payment['invoice_id'], (int)$payment['school_id'],
                $refundRef, $amount, $reason, $requestedBy
            ]
        );
        $refundId = (int)Database::lastInsertId();

        AuditLogger::log('portal_refund_requested', 'portal_refunds', $refundId, [
            'payment_id' => $paymentId,
            'amount' => $amount,
            'reason' => $reason,
            'requested_by' => $requestedBy,
        ]);

        return $refundId;
    }

    /**
     * Approve and Execute Refund (Platform SuperAdmin or Finance Admin only)
     */
    public static function approveAndProcessRefund(int $refundId, int $approvedBy, ?string $notes = null): bool
    {
        $refund = Database::fetch("SELECT * FROM portal_refunds WHERE id = ?", [$refundId]);
        if (!$refund || $refund['status'] !== 'requested') {
            throw new RuntimeException("Refund request not found or not in pending state.");
        }

        $payment = Database::fetch("SELECT * FROM portal_payments WHERE id = ?", [(int)$refund['payment_id']]);
        if (!$payment) {
            throw new RuntimeException("Underlying payment record not found.");
        }

        // 1. If Paystack gateway payment, trigger gateway refund API
        $gwRefundId = null;
        if ($payment['gateway'] === 'paystack') {
            $gwRes = PaystackService::initiateGatewayRefund(
                $payment['payment_reference'],
                (float)$refund['amount'],
                $refund['reason']
            );
            $gwRefundId = $gwRes['data']['refund_id'] ?? null;
        }

        // 2. Execute accounting reversals via RevenueSharingService
        RevenueSharingService::processRefund((int)$payment['id'], $refund['reason'], $approvedBy);

        // 3. Mark Refund Record as Processed
        Database::execute(
            "UPDATE portal_refunds 
             SET status = 'processed', gateway_refund_id = ?, approved_by = ?, approved_at = NOW(), processed_at = NOW(), notes = ? 
             WHERE id = ?",
            [$gwRefundId, $approvedBy, $notes, $refundId]
        );

        AuditLogger::log('portal_refund_approved_processed', 'portal_refunds', $refundId, [
            'approved_by' => $approvedBy,
            'amount' => (float)$refund['amount'],
            'gateway_refund_id' => $gwRefundId,
        ]);

        return true;
    }

    /**
     * Reject a Refund Request with Reason
     */
    public static function rejectRefund(int $refundId, int $rejectedBy, string $reason): bool
    {
        $refund = Database::fetch("SELECT * FROM portal_refunds WHERE id = ?", [$refundId]);
        if (!$refund || $refund['status'] !== 'requested') {
            throw new RuntimeException("Refund request not found or cannot be rejected.");
        }

        Database::execute(
            "UPDATE portal_refunds 
             SET status = 'rejected', approved_by = ?, approved_at = NOW(), notes = ? 
             WHERE id = ?",
            [$rejectedBy, "Rejected: " . $reason, $refundId]
        );

        AuditLogger::log('portal_refund_rejected', 'portal_refunds', $refundId, [
            'rejected_by' => $rejectedBy,
            'reason' => $reason,
        ]);

        return true;
    }

    /**
     * Retrieve Refund Records with Filtering
     */
    public static function getRefunds(?int $schoolId = null, ?string $status = null, int $limit = 50): array
    {
        $sql = "SELECT r.*, p.payment_reference, p.amount as payment_amount, p.gateway, 
                       i.invoice_number, sch.name as school_name, u_req.name as requested_by_name,
                       u_app.name as approved_by_name
                FROM portal_refunds r
                JOIN portal_payments p ON p.id = r.payment_id
                JOIN portal_invoices i ON i.id = r.invoice_id
                JOIN schools sch ON sch.id = r.school_id
                JOIN users u_req ON u_req.id = r.requested_by
                LEFT JOIN users u_app ON u_app.id = r.approved_by
                WHERE 1=1";
        $params = [];

        if ($schoolId) {
            $sql .= " AND r.school_id = ?";
            $params[] = $schoolId;
        }

        if ($status && $status !== 'all') {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.created_at DESC LIMIT ?";
        $params[] = $limit;

        return Database::fetchAll($sql, $params);
    }
}
