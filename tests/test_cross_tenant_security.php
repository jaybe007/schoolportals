<?php

declare(strict_types=1);

/**
 * EduSphere SaaS - Multi-Tenant Security & Cross-Tenant Data Isolation Test Suite
 * 
 * Tests that two independent demo schools (Apex Crown College vs Horizon International Academy)
 * have complete isolation and that all attempts to access, manipulate, or download
 * records across school boundaries via direct URL manipulation, POST requests,
 * or API routes are strictly blocked and logged to the NDPA audit trail.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Core\Database;
use EduSphere\Core\TenantContext;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "========================================================\n";
echo "EduSphere Multi-Tenant Isolation & Cross-Tenant Security Tests\n";
echo "========================================================\n\n";

$baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost/schoolportals', '/');
$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $title, ?string $details = null): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$title}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$title}\n";
        if ($details) {
            echo "         Details: {$details}\n";
        }
    }
}

function testHttp(string $path, ?string $cookie = null, string $method = 'GET', array $postData = []): array {
    global $baseUrl;
    $url = $baseUrl . '/' . ltrim($path, '/');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }

    if ($method === 'POST') {
        if (!isset($postData['_csrf'])) {
            $postData['_csrf'] = 'test_csrf_token';
        }
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

// -----------------------------------------------------------------
// Test 1: Verify Two Distinct School Tenants Exist in Database
// -----------------------------------------------------------------
echo "1. Checking Independent Demo School Tenants...\n";
$school1 = Database::fetch("SELECT * FROM schools WHERE id = 1");
$school2 = Database::fetch("SELECT * FROM schools WHERE id = 2");

assertTest($school1 !== false && $school2 !== false, "Both Demo School 1 (Apex) and Demo School 2 (Horizon) exist in database");
assertTest($school1['slug'] === 'apex' && $school2['slug'] === 'horizon', "Schools have distinct slugs ('apex' vs 'horizon')");

// Verify students belong strictly to distinct schools
$school1Students = Database::query("SELECT id FROM students WHERE school_id = 1");
$school2Students = Database::query("SELECT id FROM students WHERE school_id = 2");
assertTest(count($school1Students) > 0 && count($school2Students) > 0, "Both schools possess active enrolled students (Apex: " . count($school1Students) . ", Horizon: " . count($school2Students) . ")");

$s1StudentIds = array_column($school1Students, 'id');
$s2StudentIds = array_column($school2Students, 'id');
$intersection = array_intersect($s1StudentIds, $s2StudentIds);
assertTest(empty($intersection), "Student IDs are mutually exclusive across tenants (no overlapping records)");

// -----------------------------------------------------------------
// Test 2: Authenticate Both Tenant Administrators
// -----------------------------------------------------------------
echo "\n2. Authenticating Tenant Users for Both Schools...\n";
$apexLogin = testHttp('/demo-login?email=admin@apexcollege.edu.ng');
$apexCookie = $apexLogin['cookie'];
assertTest($apexLogin['code'] === 302 && !empty($apexCookie), "Apex School Admin logged in successfully");

$horizonLogin = testHttp('/demo-login?email=admin@horizonacademy.sch.ng');
$horizonCookie = $horizonLogin['cookie'];
assertTest($horizonLogin['code'] === 302 && !empty($horizonCookie), "Horizon School Admin logged in successfully");

// -----------------------------------------------------------------
// Test 3: Cross-Tenant Report Card Download Via URL Tampering
// -----------------------------------------------------------------
echo "\n3. Testing Cross-Tenant Report Card Download Guard...\n";
// Apex Admin accesses Apex student (ID: 1) -> Expect HTTP 200 with PDF
$apexOwnPdf = testHttp("/school/students/{$s1StudentIds[0]}/report-card", $apexCookie);
assertTest($apexOwnPdf['code'] === 200 && str_starts_with($apexOwnPdf['body'], '%PDF'), "Apex Admin can download Apex student report card (HTTP 200 PDF)");

// Apex Admin accesses Horizon student (ID: 6) -> Expect HTTP 403 Forbidden
$horizonStudentId = $s2StudentIds[0];
$apexAccessingHorizon = testHttp("/school/students/{$horizonStudentId}/report-card", $apexCookie);
assertTest($apexAccessingHorizon['code'] === 403, "Apex Admin accessing Horizon student report card is BLOCKED with HTTP 403 Forbidden (Got: {$apexAccessingHorizon['code']})");

// Horizon Admin accesses Apex student (ID: 1) -> Expect HTTP 403 Forbidden
$horizonAccessingApex = testHttp("/school/students/{$s1StudentIds[0]}/report-card", $horizonCookie);
assertTest($horizonAccessingApex['code'] === 403, "Horizon Admin accessing Apex student report card is BLOCKED with HTTP 403 Forbidden (Got: {$horizonAccessingApex['code']})");

// Verify NDPA Audit Trail logged the unauthorized attempts
$reportCardAudit = Database::fetch(
    "SELECT * FROM audit_logs WHERE action = 'unauthorized_cross_tenant_report_card_attempt' ORDER BY id DESC LIMIT 1"
);
assertTest($reportCardAudit !== false, "Cross-tenant unauthorized report card attempt recorded in immutable NDPA audit log");

// -----------------------------------------------------------------
// Test 4: Cross-Tenant Attendance Recording Attack
// -----------------------------------------------------------------
echo "\n4. Testing Cross-Tenant Attendance Submission Guard...\n";
// Horizon class arm ID
$horizonArm = Database::fetch("SELECT id FROM class_arms WHERE school_id = 2 LIMIT 1");
$horizonArmId = (int)$horizonArm['id'];

// Apex admin tries to submit attendance on Horizon's class arm
$crossArmAttendance = testHttp(
    "/school/attendance?arm_id={$horizonArmId}&date=2026-10-12",
    $apexCookie,
    'POST',
    ['attendance' => [$horizonStudentId => 'present']]
);
assertTest($crossArmAttendance['code'] === 403, "Apex Admin posting attendance to Horizon Class Arm is BLOCKED with HTTP 403 Forbidden (Got: {$crossArmAttendance['code']})");

// Verify audit log
$attendanceAudit = Database::fetch(
    "SELECT * FROM audit_logs WHERE action = 'unauthorized_cross_tenant_attendance_attempt' ORDER BY id DESC LIMIT 1"
);
assertTest($attendanceAudit !== false, "Cross-tenant unauthorized attendance attempt recorded in NDPA audit log");

// -----------------------------------------------------------------
// Test 5: Cross-Tenant Exam Score Submission Attack
// -----------------------------------------------------------------
echo "\n5. Testing Cross-Tenant Exam Score Submission Guard...\n";
// Horizon subject ID
$horizonSubject = Database::fetch("SELECT id FROM subjects WHERE school_id = 2 LIMIT 1");
$horizonSubjectId = (int)$horizonSubject['id'];

// Apex admin tries to submit exam scores on Horizon's class arm & subject
$crossScoreSubmission = testHttp(
    "/school/exams?arm_id={$horizonArmId}&subject_id={$horizonSubjectId}",
    $apexCookie,
    'POST',
    [
        'action' => 'save_scores',
        'scores' => [
            $horizonStudentId => ['ca1' => 15, 'ca2' => 15, 'exam' => 50]
        ]
    ]
);
assertTest($crossScoreSubmission['code'] === 403, "Apex Admin posting scores to Horizon Class Arm / Subject is BLOCKED with HTTP 403 Forbidden (Got: {$crossScoreSubmission['code']})");

// Verify audit log
$examAudit = Database::fetch(
    "SELECT * FROM audit_logs WHERE action = 'unauthorized_cross_tenant_exam_attempt' ORDER BY id DESC LIMIT 1"
);
assertTest($examAudit !== false, "Cross-tenant unauthorized exam scores attempt recorded in NDPA audit log");

// -----------------------------------------------------------------
// Test 6: Cross-Tenant Fee Invoice Payment Attack
// -----------------------------------------------------------------
echo "\n6. Testing Cross-Tenant Fee Payment Guard...\n";
// Horizon fee invoice ID
$horizonInvoice = Database::fetch("SELECT id FROM fee_invoices WHERE school_id = 2 LIMIT 1");
$horizonInvoiceId = (int)$horizonInvoice['id'];

// Apex admin tries to record payment against Horizon's invoice
$crossPayment = testHttp(
    "/school/finance",
    $apexCookie,
    'POST',
    [
        'action' => 'record_payment',
        'invoice_id' => $horizonInvoiceId,
        'amount_paid' => 50000,
        'payment_method' => 'cash',
        'payment_reference' => 'CROSS_ATTACK_001',
    ]
);
assertTest($crossPayment['code'] === 403, "Apex Admin recording payment against Horizon Invoice is BLOCKED with HTTP 403 Forbidden (Got: {$crossPayment['code']})");

// Verify audit log
$financeAudit = Database::fetch(
    "SELECT * FROM audit_logs WHERE action = 'unauthorized_cross_tenant_finance_attempt' ORDER BY id DESC LIMIT 1"
);
assertTest($financeAudit !== false, "Cross-tenant unauthorized finance attempt recorded in NDPA audit log");

// -----------------------------------------------------------------
// Test 7: Multi-Tenant Host Domain and Slug Isolation
// -----------------------------------------------------------------
echo "\n7. Testing Tenant Host Domain & Slug Isolation...\n";
// Apex public website
$apexSite = testHttp('/site/apex');
assertTest($apexSite['code'] === 200 && str_contains($apexSite['body'], 'Apex Crown College'), "Public site /site/apex renders 'Apex Crown College'");

// Horizon public website
$horizonSite = testHttp('/site/horizon');
assertTest($horizonSite['code'] === 200 && str_contains($horizonSite['body'], 'Horizon International Academy'), "Public site /site/horizon renders 'Horizon International Academy'");

// Custom Domain Resolution
$_SERVER['HTTP_HOST'] = 'apex.edusphere.example';
TenantContext::clear();
TenantContext::resolveSchool();
assertTest(TenantContext::getSchoolId() === 1, "Host 'apex.edusphere.example' strictly resolves School ID 1 (Apex)");

$_SERVER['HTTP_HOST'] = 'horizon.edusphere.example';
TenantContext::clear();
TenantContext::resolveSchool();
assertTest(TenantContext::getSchoolId() === 2, "Host 'horizon.edusphere.example' strictly resolves School ID 2 (Horizon)");

// -----------------------------------------------------------------
// Test 8: Anti-IDOR Parent Portal Billing Guards
// -----------------------------------------------------------------
echo "\n8. Testing Anti-IDOR Parent Portal Billing Guards...\n";
$parentLogin = testHttp('/demo-login?email=parent@apexcollege.edu.ng');
$parentCookie = $parentLogin['cookie'];
assertTest($parentLogin['code'] === 302 && !empty($parentCookie), "Apex Parent logged in successfully");

$horizonInvoice = Database::fetch("SELECT id FROM portal_invoices WHERE school_id = 2 LIMIT 1");
if ($horizonInvoice) {
    $crossPay = testHttp(
        "/portal/parent/pay-portal-fee",
        $parentCookie,
        'POST',
        ['invoice_id' => $horizonInvoice['id'], 'amount' => 1000]
    );
    assertTest(
        $crossPay['code'] === 403,
        "Parent attempting payment on another school's invoice is BLOCKED with HTTP 403 Forbidden"
    );

    $crossDispute = testHttp(
        "/portal/parent/dispute-portal-fee",
        $parentCookie,
        'POST',
        ['invoice_id' => $horizonInvoice['id'], 'dispute_reason' => 'Cross-tenant IDOR attack']
    );
    assertTest(
        $crossDispute['code'] === 302 || $crossDispute['code'] === 403,
        "Parent attempting dispute on another school's invoice is rejected"
    );
}

// =================================================================
echo "\n========================================================\n";
echo "SECURITY TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "========================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
