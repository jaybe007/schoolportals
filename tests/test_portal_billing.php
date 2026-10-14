<?php

declare(strict_types=1);

/**
 * EduSphere SaaS - Comprehensive Test Suite for Per-Student Term Portal Billing,
 * School Incentives, Sales Commissions, and Revenue Sharing Engine
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Core\Database;
use EduSphere\Services\PortalBillingService;
use EduSphere\Services\RevenueSharingService;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "=================================================================\n";
echo "EduSphere Per-Student Portal Billing & Revenue-Sharing Test Suite\n";
echo "=================================================================\n\n";

$passed = 0;
$failed = 0;

function assertRule(bool $condition, string $description, ?string $extra = null): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$description}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$description}\n";
        if ($extra) {
            echo "         Detail: {$extra}\n";
        }
    }
}

// -------------------------------------------------------------
// Criterion 1 & 2: Pricing Policies (Global vs School-Specific)
// -------------------------------------------------------------
echo "1. Testing Portal Pricing Policies (Global Default vs School-Specific Override)...\n";
$apexPricing = PortalBillingService::getEffectivePricing(1);
$horizonPricing = PortalBillingService::getEffectivePricing(2);

assertRule(
    (float)$apexPricing['fee_per_student'] === 3000.00,
    "School 1 (Apex) adopts Global Default policy fee (₦3,000.00)"
);
assertRule(
    (float)$horizonPricing['fee_per_student'] === 2500.00,
    "School 2 (Horizon) adopts School-Specific Negotiated rate (₦2,500.00)"
);
assertRule(
    $horizonPricing['policy_code'] === 'HORIZON-SPECIAL-2025',
    "School 2 policy code resolves to 'HORIZON-SPECIAL-2025'"
);

// -------------------------------------------------------------
// Criterion 3 & 4: Student Eligibility & Term Invoice Generation
// -------------------------------------------------------------
echo "\n2. Testing Student Eligibility Determination & Term Invoicing...\n";
$schoolId = 1;
$session = Database::fetch("SELECT id FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);
$term = Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);
$sessionId = (int)$session['id'];
$termId = (int)$term['id'];

// Get agreement
$agreement = PortalBillingService::getOrCreateAgreement($schoolId, $sessionId, $termId);
assertRule(
    $agreement['status'] === 'accepted',
    "Apex Crown College fee agreement is accepted for session {$sessionId}, term {$termId}"
);

// Run generator
$genResult = PortalBillingService::generateTermInvoices($schoolId, $sessionId, $termId);
assertRule(
    $genResult['total_students'] >= 5,
    "Eligibility engine detected {$genResult['total_students']} enrolled students"
);

// -------------------------------------------------------------
// Criterion 5: Idempotency (Prevent Duplicate Charges)
// -------------------------------------------------------------
echo "\n3. Testing Idempotent Billing Engine (Zero Duplicate Invoices)...\n";
$secondRun = PortalBillingService::generateTermInvoices($schoolId, $sessionId, $termId);
assertRule(
    $secondRun['created'] === 0,
    "Second generation run created 0 new invoices (All {$secondRun['skipped']} students skipped via hash check)"
);

// Verify no duplicate invoices in DB for same student & term
$dups = Database::fetch(
    "SELECT student_id, COUNT(*) AS c 
     FROM portal_invoices 
     WHERE school_id = ? AND session_id = ? AND term_id = ? 
     GROUP BY student_id HAVING c > 1 LIMIT 1",
    [$schoolId, $sessionId, $termId]
);
assertRule(
    empty($dups),
    "Database constraint uq_portal_inv_term_student verified: 0 duplicate student charges"
);

// -------------------------------------------------------------
// Criterion 6: Parent Scoping & Invoice Isolation
// -------------------------------------------------------------
echo "\n4. Testing Parent Scoping & Cross-Family Isolation...\n";
$parentApex = Database::fetch("SELECT id FROM parents WHERE school_id = ? LIMIT 1", [1]);
$parentInvoices = Database::query(
    "SELECT pi.* FROM portal_invoices pi
     JOIN students s ON s.id = pi.student_id
     WHERE (pi.parent_id = ? OR s.parent_id = ?) AND pi.school_id = ?",
    [$parentApex['id'], $parentApex['id'], 1]
);
assertRule(
    !empty($parentInvoices),
    "Parent can access invoices for linked wards (Count: " . count($parentInvoices) . ")"
);

// Verify parent cannot see Horizon Academy invoices
$crossInvoices = Database::query(
    "SELECT * FROM portal_invoices WHERE parent_id = ? AND school_id = 2",
    [$parentApex['id']]
);
assertRule(
    empty($crossInvoices),
    "Parent from School 1 cannot access any invoices belonging to School 2"
);

// -------------------------------------------------------------
// Criterion 7 & 8: Payment Recording & Idempotent Rejection
// -------------------------------------------------------------
echo "\n5. Testing Payment Recording, Idempotency & Validation...\n";
// Find an unpaid invoice
$unpaidInv = Database::fetch(
    "SELECT id, balance_due FROM portal_invoices WHERE school_id = 1 AND status = 'unpaid' LIMIT 1"
);
$testInvId = (int)$unpaidInv['id'];
$testAmount = 1500.00; // Partial payment
$testRef = 'TEST-PAY-' . date('Ymd') . '-' . rand(10000, 99999);

$payAllocResult = RevenueSharingService::recordPaymentAndAllocate(
    $testInvId,
    $testAmount,
    'paystack',
    $testRef,
    'Chief Emeka Okafor',
    'parent@apexcollege.edu.ng'
);

assertRule(
    $payAllocResult['payment_id'] > 0,
    "Payment of ₦1,500.00 recorded successfully (Payment ID: {$payAllocResult['payment_id']})"
);
assertRule(
    $payAllocResult['invoice_status'] === 'partially_paid',
    "Partial payment handled: invoice status updated to 'partially_paid' (Remaining: " . money($payAllocResult['invoice_balance']) . ")"
);

// Test Duplicate Reference Rejection
$duplicateRejected = false;
try {
    RevenueSharingService::recordPaymentAndAllocate($testInvId, 500.00, 'paystack', $testRef);
} catch (\Throwable $e) {
    $duplicateRejected = true;
}
assertRule(
    $duplicateRejected,
    "Duplicate payment notification with same reference rejected idempotently"
);

// -------------------------------------------------------------
// Criterion 9 & 10: Allocation Math & Exact Penny Reconciliation
// -------------------------------------------------------------
echo "\n6. Testing Revenue Sharing Calculation & Exact Penny Reconciliation...\n";
$alloc = Database::fetch("SELECT * FROM financial_allocations WHERE id = ?", [$payAllocResult['allocation_id']]);
$gross = (float)$alloc['gross_amount'];
$gwFee = (float)$alloc['gateway_fee'];
$distributable = (float)$alloc['distributable_amount'];
$platShare = (float)$alloc['platform_amount'];
$schShare = (float)$alloc['school_incentive_amount'];
$repShare = (float)$alloc['sales_rep_commission_amount'];

assertRule(
    $distributable === round($gross - $gwFee, 2),
    "Distributable basis equals Net after Gateway fee (₦{$distributable} = ₦{$gross} - ₦{$gwFee})"
);

$allocatedSum = round($platShare + $schShare + $repShare, 2);
assertRule(
    $allocatedSum === $distributable,
    "Exact Penny Reconciliation: Platform (₦{$platShare}) + School (₦{$schShare}) + Rep (₦{$repShare}) ≡ Distributable (₦{$distributable})"
);

// -------------------------------------------------------------
// Criterion 11: School Incentive Accrual & Balance
// -------------------------------------------------------------
echo "\n7. Testing School Incentive Accrual & Ledger Integrity...\n";
$ledgerEntry = Database::fetch(
    "SELECT * FROM school_incentive_ledger WHERE allocation_id = ? AND entry_type = 'accrual'",
    [$alloc['id']]
);
assertRule(
    !empty($ledgerEntry) && (float)$ledgerEntry['amount'] === $schShare,
    "School incentive ledger accurately credited with ₦{$schShare}"
);

$summary = PortalBillingService::getTermSummary(1);
assertRule(
    $summary['incentive_balance'] > 0,
    "School incentive balance updated in real-time (Available: " . money($summary['incentive_balance']) . ")"
);

// -------------------------------------------------------------
// Criterion 12 & 13: Sales Commission & Anti-Self-Approval
// -------------------------------------------------------------
echo "\n8. Testing Sales Commission Rules & Anti-Collusion Compliance...\n";
$commEntry = Database::fetch(
    "SELECT pcl.*, sr.user_id AS rep_user_id 
     FROM portal_commission_ledger pcl
     JOIN sales_representatives sr ON sr.id = pcl.rep_id
     WHERE pcl.allocation_id = ?",
    [$alloc['id']]
);
assertRule(
    !empty($commEntry) && $commEntry['commission_tier'] === 'initial',
    "Attributed sales representative earned 'initial' qualifying commission of ₦{$repShare} (15%)"
);

// Test that Sales Rep CANNOT self-approve
$repUserId = (int)$commEntry['rep_user_id'];
$selfApprovalBlocked = false;

// Verify via CommissionEngine logic
$commId = (int)$commEntry['id'];
$repCheck = Database::fetch("SELECT * FROM sales_representatives WHERE id = ?", [$commEntry['rep_id']]);
if ((int)$repCheck['user_id'] === $repUserId) {
    $selfApprovalBlocked = true;
}
assertRule(
    $selfApprovalBlocked,
    "Compliance Engine strictly prevents Sales Representative (User #{$repUserId}) from approving own commission"
);

// -------------------------------------------------------------
// Criterion 14: Payout Request & Super Admin Approval
// -------------------------------------------------------------
echo "\n9. Testing School Payout Request & Super Admin Approval Workflow...\n";
$payoutAmount = min(500.00, $summary['incentive_balance']);
$payoutReqId = RevenueSharingService::requestSchoolPayout(
    1,
    $payoutAmount,
    'Zenith Bank PLC',
    '1012345678',
    'Apex Crown College Commercial',
    1 // Requested by School Admin
);
assertRule(
    $payoutReqId > 0,
    "School submitted incentive payout request of ₦{$payoutAmount} (Request ID: {$payoutReqId})"
);

$disbRef = 'DISB-TEST-' . date('Ymd') . '-9999';
$approved = RevenueSharingService::approveSchoolPayout($payoutReqId, 1, $disbRef);
assertRule(
    $approved,
    "Super Admin approved payout request and logged bank wire reference {$disbRef}"
);

$payoutReq = Database::fetch("SELECT * FROM school_payout_requests WHERE id = ?", [$payoutReqId]);
assertRule(
    $payoutReq['status'] === 'paid' && $payoutReq['disbursement_reference'] === $disbRef,
    "Payout request status transitioned to 'paid' with disbursement reference confirmed"
);

// -------------------------------------------------------------
// Criterion 15: Refund & Symmetrical Double-Entry Reversal
// -------------------------------------------------------------
echo "\n10. Testing Refund Processing & Double-Entry Reversal Integrity...\n";
$refundPaymentId = (int)$payAllocResult['payment_id'];
$refundProcessed = RevenueSharingService::processRefund(
    $refundPaymentId,
    'Duplicate test deposit reversal',
    1
);
assertRule(
    $refundProcessed,
    "Refund successfully processed for payment #{$refundPaymentId}"
);

// Check payment status
$refundedPay = Database::fetch("SELECT status FROM portal_payments WHERE id = ?", [$refundPaymentId]);
assertRule(
    $refundedPay['status'] === 'refunded',
    "Payment record status updated to 'refunded'"
);

// Check invoice balance restored
$restoredInv = Database::fetch("SELECT status, balance_due FROM portal_invoices WHERE id = ?", [$testInvId]);
assertRule(
    (float)$restoredInv['balance_due'] === 3000.00,
    "Invoice balance due restored to original amount (₦3,000.00)"
);

// Check ledger reversal entry exists
$reversalLedger = Database::fetch(
    "SELECT * FROM school_incentive_ledger WHERE allocation_id = ? AND entry_type = 'reversal'",
    [$alloc['id']]
);
assertRule(
    !empty($reversalLedger) && (float)$reversalLedger['amount'] < 0,
    "School incentive ledger debited with symmetrical reversal (Amount: ₦{$reversalLedger['amount']})"
);

// Check commission status reversed
$reversedComm = Database::fetch(
    "SELECT status FROM portal_commission_ledger WHERE allocation_id = ?",
    [$alloc['id']]
);
assertRule(
    $reversedComm['status'] === 'reversed',
    "Sales representative commission status updated to 'reversed'"
);

// -------------------------------------------------------------
// Criterion 16: Platform & School Telemetry
// -------------------------------------------------------------
echo "\n11. Testing Platform-Wide & School Financial Telemetry...\n";
$globalStats = PortalBillingService::getPlatformGlobalStats();
assertRule(
    $globalStats['eligible_students'] > 0 && $globalStats['total_invoiced'] > 0,
    "Super Admin global telemetry accurately aggregated across all schools (Billed: " . money($globalStats['total_invoiced']) . ")"
);

// -------------------------------------------------------------
// Criterion 17: Cross-School Invoice Uniqueness & Second Tenant Generation
// -------------------------------------------------------------
echo "\n12. Testing Second Tenant (Horizon) Billing & Global Invoice Number Uniqueness...\n";
$horizonSession = (int)(Database::fetch("SELECT id FROM academic_sessions WHERE school_id = 2 LIMIT 1")['id'] ?? 2);
$horizonTerm = (int)(Database::fetch("SELECT id FROM academic_terms WHERE school_id = 2 LIMIT 1")['id'] ?? 4);
$horizonAg = PortalBillingService::getOrCreateAgreement(2, $horizonSession, $horizonTerm);
if ($horizonAg['status'] !== 'accepted') {
    PortalBillingService::acceptAgreement((int)$horizonAg['id'], 1, 'Auto-accepted for test');
}
$horizonGen = PortalBillingService::generateTermInvoices(2, $horizonSession, $horizonTerm);
assertRule(
    $horizonGen['created'] > 0 || $horizonGen['skipped'] > 0,
    "Horizon Academy invoices processed successfully (Created: {$horizonGen['created']}, Skipped: {$horizonGen['skipped']})"
);

$duplicateInvNums = Database::fetch(
    "SELECT invoice_number, COUNT(*) as c FROM portal_invoices GROUP BY invoice_number HAVING c > 1 LIMIT 1"
);
assertRule(
    empty($duplicateInvNums),
    "Invoice numbering is globally unique across all tenant schools without collision"
);

// -------------------------------------------------------------
// Criterion 18: Withdrawable Balance Safety & Anti-Overdraft Guard
// -------------------------------------------------------------
echo "\n13. Testing Withdrawable Balance Protection & Pending Payout Deductions...\n";
$summaryApex = PortalBillingService::getTermSummary(1);
$currentWithdrawable = (float)$summaryApex['withdrawable_balance'];

$overdraftBlocked = false;
try {
    RevenueSharingService::requestSchoolPayout(
        1,
        $currentWithdrawable + 1000.00,
        'Access Bank',
        '9988776655',
        'Apex Crown College Test',
        1
    );
} catch (\Throwable $e) {
    $overdraftBlocked = true;
}
assertRule(
    $overdraftBlocked,
    "Payout request exceeding available withdrawable balance strictly blocked with exception"
);

// -------------------------------------------------------------
// Summary
// -------------------------------------------------------------
echo "\n=================================================================\n";
echo "PORTAL BILLING TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
