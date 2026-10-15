<?php

declare(strict_types=1);

namespace EduSphere\Services;

use EduSphere\Core\Database;
use EduSphere\Core\AuditLogger;
use RuntimeException;

class PaymentReconciliationService
{
    /**
     * Execute Gateway Settlement Reconciliation against Internal Payment Records
     */
    public static function reconcileGatewaySettlement(
        string $startDate,
        string $endDate,
        int $reconciledBy,
        ?array $customGatewayTransactions = null
    ): array {
        // 1. Fetch internal verified portal payments within date range
        $internalPayments = Database::fetchAll(
            "SELECT * FROM portal_payments 
             WHERE DATE(created_at) BETWEEN ? AND ? AND status IN ('verified', 'refunded', 'disputed')
             ORDER BY created_at ASC",
            [$startDate, $endDate]
        );

        // 2. Fetch or receive gateway transactions
        $gatewayData = $customGatewayTransactions;
        if ($gatewayData === null) {
            $gwResponse = PaystackService::fetchSettlements($startDate, $endDate);
            $gatewayData = $gwResponse['data'] ?? [];
        }

        $internalByRef = [];
        $totalInternalGross = 0.0;
        $totalInternalFees = 0.0;
        $totalInternalNet = 0.0;

        foreach ($internalPayments as $ip) {
            $internalByRef[$ip['payment_reference']] = $ip;
            $totalInternalGross += (float)$ip['amount'];
            $totalInternalFees += (float)$ip['gateway_fee'];
            $totalInternalNet += (float)$ip['net_amount'];
        }

        $matchedCount = 0;
        $unmatchedGateway = [];
        $totalGwGross = 0.0;
        $totalGwFees = 0.0;
        $totalGwNet = 0.0;

        foreach ($gatewayData as $gt) {
            $ref = (string)($gt['reference'] ?? '');
            $gwGross = (float)(($gt['amount'] ?? 0) / 100);
            $gwFee = (float)(($gt['gateway_fee'] ?? 0) / 100);
            $gwNet = (float)(($gt['net_amount'] ?? ($gwGross - $gwFee)) / 100);

            $totalGwGross += $gwGross;
            $totalGwFees += $gwFee;
            $totalGwNet += $gwNet;

            if (isset($internalByRef[$ref])) {
                $matchedCount++;
            } else {
                $unmatchedGateway[] = $gt;
            }
        }

        $discrepancy = round(abs($totalGwNet - $totalInternalNet), 2);
        $status = ($discrepancy < 0.05 && empty($unmatchedGateway)) ? 'matched' : 'discrepancy_found';

        $batchId = sprintf('SETTLE-%s-%s', date('Ymd'), bin2hex(random_bytes(3)));

        Database::execute(
            "INSERT INTO gateway_settlement_reconciliations
             (settlement_batch_id, gateway, settlement_date, total_transactions, gross_amount, gateway_fee, net_settled_amount, internal_matched_amount, discrepancy_amount, reconciliation_status, reconciled_by, reconciled_at, notes)
             VALUES (?, 'paystack', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)",
            [
                $batchId,
                $endDate,
                count($gatewayData) ?: count($internalPayments),
                $totalGwGross ?: $totalInternalGross,
                $totalGwFees ?: $totalInternalFees,
                $totalGwNet ?: $totalInternalNet,
                $totalInternalNet,
                $discrepancy,
                $status,
                $reconciledBy,
                "Settlement reconciliation between {$startDate} and {$endDate}. Matched {$matchedCount} records."
            ]
        );
        $reconId = (int)Database::lastInsertId();

        AuditLogger::log('gateway_settlement_reconciled', 'gateway_settlement_reconciliations', $reconId, [
            'batch_id' => $batchId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'discrepancy' => $discrepancy,
            'status' => $status,
            'reconciled_by' => $reconciledBy,
        ]);

        return [
            'reconciliation_id' => $reconId,
            'batch_id' => $batchId,
            'status' => $status,
            'matched_count' => $matchedCount,
            'unmatched_gateway_count' => count($unmatchedGateway),
            'total_gateway_gross' => $totalGwGross ?: $totalInternalGross,
            'total_gateway_fees' => $totalGwFees ?: $totalInternalFees,
            'net_settled_amount' => $totalGwNet ?: $totalInternalNet,
            'internal_matched_amount' => $totalInternalNet,
            'discrepancy_amount' => $discrepancy,
        ];
    }

    /**
     * Mark Discrepancy as Manually Resolved with Audit Notes
     */
    public static function resolveDiscrepancy(int $reconciliationId, string $resolutionNotes, int $resolvedBy): bool
    {
        $recon = Database::fetch("SELECT * FROM gateway_settlement_reconciliations WHERE id = ?", [$reconciliationId]);
        if (!$recon) {
            throw new RuntimeException("Settlement reconciliation record not found.");
        }

        Database::execute(
            "UPDATE gateway_settlement_reconciliations 
             SET reconciliation_status = 'manual_resolved', 
                 notes = CONCAT(IFNULL(notes, ''), '\n[MANUAL RESOLUTION: ', NOW(), '] ', ?),
                 reconciled_by = ?, reconciled_at = NOW()
             WHERE id = ?",
            [$resolutionNotes, $resolvedBy, $reconciliationId]
        );

        AuditLogger::log('settlement_discrepancy_resolved', 'gateway_settlement_reconciliations', $reconciliationId, [
            'resolved_by' => $resolvedBy,
            'notes' => $resolutionNotes,
        ]);

        return true;
    }

    /**
     * Retrieve Historical Gateway Reconciliations
     */
    public static function getReconciliationHistory(int $limit = 20): array
    {
        return Database::fetchAll(
            "SELECT r.*, u.name as reconciled_by_name 
             FROM gateway_settlement_reconciliations r
             LEFT JOIN users u ON u.id = r.reconciled_by
             ORDER BY r.created_at DESC 
             LIMIT ?",
            [$limit]
        );
    }
}
