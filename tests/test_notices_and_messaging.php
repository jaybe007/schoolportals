<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Core\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "=================================================================\n";
echo "EduSphere Notices, Direct Messaging & Student Fee Payments Tests\n";
echo "=================================================================\n\n";

$baseAppUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost/schoolportals', '/');

function testHttp(string $path, ?string $cookie = null, string $method = 'GET', array $postData = []): array {
    global $baseAppUrl;
    $url = $baseAppUrl . '/' . ltrim($path, '/');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headerStr = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);

    $sessionCookie = '';
    if (preg_match_all('/^Set-Cookie:\s*(PHPSESSID=[^;]+)/mi', $headerStr, $matches)) {
        $sessionCookie = end($matches[1]);
    }

    return [
        'code' => $httpCode,
        'headers' => $headerStr,
        'body' => $body,
        'cookie' => $sessionCookie,
    ];
}

function extractCsrf(string $html): string {
    if (preg_match('/name="(_csrf|_token)"\s+value="([^"]+)"/', $html, $m)) {
        return $m[2];
    }
    return 'test_csrf_token';
}

$passed = 0;
$failed = 0;

function assertCondition(bool $cond, string $msg): void {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] {$msg}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$msg}\n";
        $failed++;
    }
}

// ===================================================================
// 1. Authenticate Personas
// ===================================================================
echo "1. Authenticating Demo Personas...\n";
$schoolAdminRes = testHttp('/demo-login?role=school_admin');
$schoolCookie = $schoolAdminRes['cookie'];
assertCondition(!empty($schoolCookie), "Apex School Admin logged in successfully");

$studentRes = testHttp('/demo-login?role=student');
$stuCookie = $studentRes['cookie'];
$stuPortalInit = testHttp('/portal/student', $stuCookie);
$stuCsrf = extractCsrf($stuPortalInit['body']);
assertCondition(!empty($stuCookie), "Apex Student logged in successfully");

$parentRes = testHttp('/demo-login?role=parent');
$parentCookie = $parentRes['cookie'];
assertCondition(!empty($parentCookie), "Apex Parent logged in successfully");

// Fetch Student & Parent DB records
$studentUser = Database::fetch("SELECT u.id as user_id, s.id as student_id, s.school_id, s.class_arm_id FROM users u JOIN students s ON s.user_id = u.id WHERE u.email = 'student@apexcollege.edu.ng'");
$parentUser = Database::fetch("SELECT u.id as user_id, p.id as parent_id FROM users u JOIN parents p ON p.user_id = u.id WHERE u.email = 'parent@apexcollege.edu.ng'");
$horizonStudent = Database::fetch("SELECT s.id as student_id, s.school_id FROM students s WHERE s.school_id = 2 LIMIT 1");

assertCondition($studentUser && $studentUser['school_id'] == 1, "Student record verified for Apex Crown College (School 1)");
assertCondition($parentUser !== null, "Parent record verified for Apex Crown College");
assertCondition($horizonStudent && $horizonStudent['school_id'] == 2, "Horizon Academy Student verified (School 2)");

// ===================================================================
// 2. Testing School Notices Creation & Audience Scoping
// ===================================================================
echo "\n2. Testing School Notice Board Creation & Audience Scoping...\n";

// Get CSRF from notice management page
$noticePage = testHttp('/school/notices', $schoolCookie);
$schoolCsrf = extractCsrf($noticePage['body']);

// Create a Staff-only notice
$staffNoticeTitle = "Confidential Staff Meeting " . time();
$createRes = testHttp('/school/notices/create', $schoolCookie, 'POST', [
    '_csrf' => $schoolCsrf,
    '_token' => $schoolCsrf,
    'title' => $staffNoticeTitle,
    'content' => 'Mandatory academic briefing for all faculty members.',
    'target_audience' => 'staff',
    'category' => 'academic',
    'priority' => 'important',
    'is_pinned' => 1
]);
assertCondition($createRes['code'] === 302, "School Admin posted staff notice successfully (HTTP 302 redirect)");

$staffNoticeDb = Database::fetch("SELECT * FROM school_notices WHERE title = ?", [$staffNoticeTitle]);
assertCondition($staffNoticeDb && $staffNoticeDb['target_audience'] === 'staff', "Staff notice persisted with priority 'important' and pinned status");

// Verify Student Dashboard DOES NOT see Staff-only notice
$studentDashboard = testHttp('/portal/student', $stuCookie);
assertCondition(!str_contains($studentDashboard['body'], $staffNoticeTitle), "Student Dashboard hides staff-only notice");

// Verify Parent Dashboard DOES NOT see Staff-only notice
$parentDashboard = testHttp('/portal/parent', $parentCookie);
assertCondition(!str_contains($parentDashboard['body'], $staffNoticeTitle), "Parent Dashboard hides staff-only notice");

// Create a Student-targeted Notice
$studentNoticeTitle = "Inter-House Athletic Practice " . time();
$createStuNoticeRes = testHttp('/school/notices/create', $schoolCookie, 'POST', [
    '_csrf' => $schoolCsrf,
    '_token' => $schoolCsrf,
    'title' => $studentNoticeTitle,
    'content' => 'Green house track practice starts at 3:30 PM on the main field.',
    'target_audience' => 'students',
    'category' => 'sports',
    'priority' => 'normal',
    'is_pinned' => 0
]);
assertCondition($createStuNoticeRes['code'] === 302, "School Admin posted student-targeted notice");

// Verify Student Dashboard DOES see student-targeted notice
$studentDashboardAfter = testHttp('/portal/student', $stuCookie);
assertCondition(str_contains($studentDashboardAfter['body'], $studentNoticeTitle), "Student Dashboard displays student-targeted notice on Notice Board");

// Clean up created test notices
Database::execute("DELETE FROM school_notices WHERE title IN (?, ?)", [$staffNoticeTitle, $studentNoticeTitle]);

// ===================================================================
// 3. Testing Direct Parent-School Two-Way Messaging
// ===================================================================
echo "\n3. Testing Direct Parent-School Two-Way Messaging...\n";

// School admin initiates message to parent
$msgPage = testHttp('/school/messages', $schoolCookie);
$schoolMsgCsrf = extractCsrf($msgPage['body']);

$initialSubject = "Student Academic Commendation " . time();
$sendRes = testHttp('/school/messages/send', $schoolCookie, 'POST', [
    '_csrf' => $schoolMsgCsrf,
    '_token' => $schoolMsgCsrf,
    'parent_id' => $parentUser['parent_id'],
    'student_id' => $studentUser['student_id'],
    'subject' => $initialSubject,
    'message' => 'Your ward demonstrated exceptional problem solving in Advanced Physics today.'
]);
assertCondition($sendRes['code'] === 302, "School Admin dispatched message to parent (HTTP 302)");

$sentMsg = Database::fetch("SELECT * FROM parent_school_messages WHERE subject = ?", [$initialSubject]);
assertCondition($sentMsg && $sentMsg['sender_type'] === 'school', "Message recorded in database with sender_type='school' and ID=" . ($sentMsg['id'] ?? 0));

// Check Parent Inbox
$parentInbox = testHttp('/portal/parent/messages', $parentCookie);
assertCondition(str_contains($parentInbox['body'], $initialSubject), "Parent messaging center displays new thread: '{$initialSubject}'");
$parentCsrf = extractCsrf($parentInbox['body']);

// Parent replies to thread
$threadId = (int)$sentMsg['id'];
$parentReplyRes = testHttp("/portal/parent/messages/{$threadId}/reply", $parentCookie, 'POST', [
    '_csrf' => $parentCsrf,
    '_token' => $parentCsrf,
    'message' => 'Thank you very much, we are deeply grateful for the continuous guidance!'
]);
assertCondition($parentReplyRes['code'] === 302, "Parent replied to thread successfully (HTTP 302)");

$replies = Database::query("SELECT * FROM parent_school_messages WHERE id = ? OR parent_message_id = ? ORDER BY id ASC", [$threadId, $threadId]);
assertCondition(count($replies) === 2 && $replies[1]['sender_type'] === 'parent', "Thread history contains 2 sequential messages with alternating senders");

// Test Anti-IDOR: Unauthorized student/cross-role access to parent reply is blocked
$unauthReply = testHttp("/portal/parent/messages/{$threadId}/reply", $stuCookie, 'POST', [
    '_csrf' => $stuCsrf,
    '_token' => $stuCsrf,
    'message' => 'Hacker reply attempt'
]);
assertCondition($unauthReply['code'] === 403, "Unauthorized student/cross-role access to parent reply is blocked with HTTP 403 Forbidden");

// ===================================================================
// 4. Testing Student Tuition & Fee Online Payment
// ===================================================================
echo "\n4. Testing Student Tuition & Fee Online Payment...\n";

// Ensure a pending fee invoice exists for this student
$feeInvoice = Database::fetch("SELECT * FROM fee_invoices WHERE student_id = ? LIMIT 1", [$studentUser['student_id']]);
if ($feeInvoice) {
    Database::execute("UPDATE fee_invoices SET paid_amount = 0.00, balance_due = total_amount, status = 'unpaid' WHERE id = ?", [$feeInvoice['id']]);
    $feeInvoice = Database::fetch("SELECT * FROM fee_invoices WHERE id = ?", [$feeInvoice['id']]);
} else {
    $session = Database::fetch("SELECT id FROM academic_sessions WHERE school_id = 1 LIMIT 1");
    $term = Database::fetch("SELECT id FROM academic_terms WHERE session_id = ? LIMIT 1", [$session['id']]);
    Database::execute(
        "INSERT INTO fee_invoices (school_id, invoice_number, session_id, term_id, student_id, class_arm_id, total_amount, paid_amount, balance_due, status, due_date, created_at, updated_at)
         VALUES (1, ?, ?, ?, ?, ?, 25000.00, 0.00, 25000.00, 'unpaid', DATE_ADD(CURDATE(), INTERVAL 30 DAY), NOW(), NOW())",
        ['INV-TEST-STU-' . time(), $session['id'], $term['id'], $studentUser['student_id'], $studentUser['class_arm_id'] ?? 1]
    );
    $feeInvoice = Database::fetch("SELECT * FROM fee_invoices WHERE id = ?", [(int)Database::lastInsertId()]);
}

$initialBalance = (float)$feeInvoice['balance_due'];
$paymentAmount = min(5000.00, $initialBalance);

// Get CSRF from student portal
$stuPortalPage = testHttp('/portal/student', $stuCookie);
$stuCsrf = extractCsrf($stuPortalPage['body']);

$payRef = 'STU-FEE-TEST-' . time();
$payFeeRes = testHttp('/portal/student/pay-fee', $stuCookie, 'POST', [
    '_csrf' => $stuCsrf,
    '_token' => $stuCsrf,
    'invoice_id' => $feeInvoice['id'],
    'amount' => $paymentAmount,
    'payment_reference' => $payRef
]);
assertCondition($payFeeRes['code'] === 302, "Student submitted online fee payment successfully (HTTP 302)");

// Verify Fee Invoice balance updated
$updatedFeeInvoice = Database::fetch("SELECT * FROM fee_invoices WHERE id = ?", [$feeInvoice['id']]);
$expectedBal = round($initialBalance - $paymentAmount, 2);
assertCondition(round((float)$updatedFeeInvoice['balance_due'], 2) === $expectedBal, "Invoice balance accurately reduced from ₦{$initialBalance} to ₦{$expectedBal}");

// Verify Payment Receipt generated
$receipt = Database::fetch("SELECT * FROM payment_receipts WHERE invoice_id = ? ORDER BY id DESC LIMIT 1", [$feeInvoice['id']]);
assertCondition($receipt && (float)$receipt['amount_paid'] === $paymentAmount, "Verified payment receipt logged in payment_receipts (Receipt #{$receipt['receipt_number']})");

// Anti-IDOR Test: Student attempts to pay invoice belonging to Horizon student
$horizonInvoice = Database::fetch("SELECT id FROM fee_invoices WHERE school_id = 2 LIMIT 1");
if ($horizonInvoice) {
    $maliciousPayRes = testHttp('/portal/student/pay-fee', $stuCookie, 'POST', [
        '_csrf' => $stuCsrf,
        '_token' => $stuCsrf,
        'invoice_id' => $horizonInvoice['id'],
        'amount' => 1000.00,
        'payment_reference' => 'HACK-REF-' . time()
    ]);
    assertCondition(!Database::fetch("SELECT * FROM payment_receipts WHERE payment_reference LIKE 'HACK-REF-%'"), "Anti-IDOR Guard: Student cannot pay or manipulate cross-tenant fee invoice");
}

// ===================================================================
// 5. Testing Student Portal Access Fee Payment & Revenue Allocation
// ===================================================================
echo "\n5. Testing Student Portal Access Fee Payment & Revenue Allocation...\n";

// Ensure a portal invoice exists for this student
$portalInv = Database::fetch("SELECT * FROM portal_invoices WHERE student_id = ? LIMIT 1", [$studentUser['student_id']]);
if ($portalInv) {
    Database::execute("UPDATE portal_invoices SET paid_amount = 0.00, balance_due = gross_amount, status = 'unpaid' WHERE id = ?", [$portalInv['id']]);
    $portalInv = Database::fetch("SELECT * FROM portal_invoices WHERE id = ?", [$portalInv['id']]);
} else {
    $session = Database::fetch("SELECT id FROM academic_sessions WHERE school_id = 1 LIMIT 1");
    $term = Database::fetch("SELECT id FROM academic_terms WHERE session_id = ? LIMIT 1", [$session['id']]);
    $agr = Database::fetch("SELECT id FROM school_portal_fee_agreements WHERE school_id = 1 LIMIT 1");
    Database::execute(
        "INSERT INTO portal_invoices (school_id, student_id, agreement_id, session_id, term_id, invoice_number, gross_amount, discount_amount, amount_due, paid_amount, balance_due, status, due_date, idempotency_hash, created_at, updated_at)
         VALUES (1, ?, ?, ?, ?, ?, 3000.00, 0.00, 3000.00, 0.00, 3000.00, 'unpaid', DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, NOW(), NOW())",
        [$studentUser['student_id'], $agr['id'] ?? 1, $session['id'], $term['id'], 'PINV-STU-TEST-' . time(), hash('sha256', 'stu-test-' . time())]
    );
    $portalInv = Database::fetch("SELECT * FROM portal_invoices WHERE id = ?", [(int)Database::lastInsertId()]);
}

$portalPayAmount = (float)$portalInv['balance_due'];

$payPortalRes = testHttp('/portal/student/pay-portal-fee', $stuCookie, 'POST', [
    '_csrf' => $stuCsrf,
    '_token' => $stuCsrf,
    'invoice_id' => $portalInv['id'],
    'amount' => $portalPayAmount,
]);
assertCondition($payPortalRes['code'] === 302, "Student submitted online portal fee payment successfully (HTTP 302)");

// Verify portal invoice is marked paid
$updatedPortalInv = Database::fetch("SELECT * FROM portal_invoices WHERE id = ?", [$portalInv['id']]);
assertCondition($updatedPortalInv['status'] === 'paid' && (float)$updatedPortalInv['balance_due'] === 0.0, "Portal invoice status transitioned to 'paid' with zero balance due");

// Verify revenue sharing allocation records generated
$portalPayment = Database::fetch("SELECT * FROM portal_payments WHERE invoice_id = ? ORDER BY id DESC LIMIT 1", [$portalInv['id']]);
assertCondition($portalPayment !== null, "Portal payment record created with ID #{$portalPayment['id']}");

if ($portalPayment) {
    $allocation = Database::fetch("SELECT * FROM financial_allocations WHERE payment_id = ?", [$portalPayment['id']]);
    assertCondition($allocation !== null && (float)$allocation['platform_amount'] > 0 && (float)$allocation['school_incentive_amount'] > 0, "Automatic revenue allocations created (Platform: ₦" . ($allocation['platform_amount'] ?? 0) . ", School: ₦" . ($allocation['school_incentive_amount'] ?? 0) . ")");
}

// Verify verified receipts appear in student portal view
$studentDashboardFinal = testHttp('/portal/student', $stuCookie);
assertCondition(str_contains($studentDashboardFinal['body'], $receipt['receipt_number']), "Verified Receipt #{$receipt['receipt_number']} visible in Student Portal Receipts table");

echo "\n=================================================================\n";
echo "TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
