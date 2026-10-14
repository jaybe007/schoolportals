<?php

declare(strict_types=1);

namespace EduSphere\Services;

use EduSphere\Core\Database;
use EduSphere\Core\AuditLogger;
use RuntimeException;

class CommissionEngine
{
    /**
     * Award commission for a verified school subscription payment
     */
    public static function processSubscriptionCommission(int $subscriptionId): ?int
    {
        $sub = Database::fetch(
            "SELECT s.*, sch.name AS school_name 
             FROM subscriptions s 
             JOIN schools sch ON sch.id = s.school_id 
             WHERE s.id = ?",
            [$subscriptionId]
        );

        if (!$sub || $sub['payment_status'] !== 'paid') {
            return null;
        }

        // Check if commission already awarded for this subscription (Idempotency)
        $existing = Database::fetch(
            "SELECT id FROM sales_commissions WHERE subscription_id = ?",
            [$subscriptionId]
        );

        if ($existing) {
            return (int)$existing['id'];
        }

        // Find associated sales representative via sales_leads
        $lead = Database::fetch(
            "SELECT sl.*, sr.id AS rep_id, sr.commission_type, sr.commission_rate, sr.status AS rep_status
             FROM sales_leads sl
             JOIN sales_representatives sr ON sr.id = sl.rep_id
             WHERE sl.created_school_id = ? AND sr.status = 'active'",
            [$sub['school_id']]
        );

        if (!$lead) {
            return null; // No sales rep attributed
        }

        $repId = (int)$lead['rep_id'];
        $transAmount = (float)$sub['amount'];
        $rate = (float)($lead['commission_rate'] ?? 10.00); // 10% default
        $commType = $lead['commission_type'] ?? 'percentage';

        $commAmount = 0.00;
        if ($commType === 'fixed') {
            $commAmount = 50000.00; // Flat 50,000 NGN
        } elseif ($commType === 'tiered') {
            // Tiered: 10% up to 1m, 15% above 1m
            if ($transAmount > 1000000) {
                $commAmount = (1000000 * 0.10) + (($transAmount - 1000000) * 0.15);
            } else {
                $commAmount = $transAmount * 0.10;
            }
        } else {
            // Percentage
            $commAmount = round($transAmount * ($rate / 100), 2);
        }

        Database::execute(
            "INSERT INTO sales_commissions 
             (rep_id, school_id, subscription_id, transaction_amount, commission_rate, commission_amount, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())",
            [$repId, $sub['school_id'], $subscriptionId, $transAmount, $rate, $commAmount]
        );

        $commId = (int)Database::lastInsertId();

        AuditLogger::log('commission_awarded_pending', 'sales_commission', $commId, [
            'rep_id' => $repId,
            'school_id' => $sub['school_id'],
            'amount' => $commAmount,
            'subscription_id' => $subscriptionId,
        ]);

        return $commId;
    }

    /**
     * Authorized manager approval of commission. Reps cannot self-approve.
     */
    public static function approveCommission(int $commissionId, int $approverUserId): bool
    {
        $comm = Database::fetch("SELECT * FROM sales_commissions WHERE id = ?", [$commissionId]);
        if (!$comm) {
            throw new RuntimeException("Commission record not found.");
        }

        // Verify approver is NOT the representative
        $rep = Database::fetch("SELECT * FROM sales_representatives WHERE id = ?", [$comm['rep_id']]);
        if ($rep && (int)$rep['user_id'] === $approverUserId) {
            throw new RuntimeException("Compliance violation: Representatives cannot approve their own commissions.");
        }

        if ($comm['status'] !== 'pending') {
            throw new RuntimeException("Commission is not in pending state.");
        }

        Database::execute(
            "UPDATE sales_commissions 
             SET status = 'approved', approved_by = ?, approved_at = NOW(), updated_at = NOW() 
             WHERE id = ?",
            [$approverUserId, $commissionId]
        );

        AuditLogger::log('commission_approved', 'sales_commission', $commissionId, [
            'approver_user_id' => $approverUserId,
            'commission_amount' => $comm['commission_amount'],
        ]);

        return true;
    }

    /**
     * Payout recording
     */
    public static function markPaid(int $commissionId, string $payoutReference): bool
    {
        $comm = Database::fetch("SELECT * FROM sales_commissions WHERE id = ?", [$commissionId]);
        if (!$comm || $comm['status'] !== 'approved') {
            throw new RuntimeException("Only approved commissions can be marked as paid.");
        }

        Database::execute(
            "UPDATE sales_commissions 
             SET status = 'paid', paid_at = NOW(), payout_reference = ?, updated_at = NOW() 
             WHERE id = ?",
            [$payoutReference, $commissionId]
        );

        AuditLogger::log('commission_payout_recorded', 'sales_commission', $commissionId, [
            'payout_reference' => $payoutReference,
        ]);

        return true;
    }
}
