<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Core\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost/schoolportals', '/');
$passed = 0;
$failed = 0;

function assertAction(bool $condition, string $title, ?string $details = null): void {
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

echo "========================================================\n";
echo "Testing Newly Wired Forms & Full CRUD Database Actions\n";
echo "========================================================\n\n";

// Login as Super Admin
$superLogin = testHttp('/demo-login?role=super_admin');
$superCookie = $superLogin['cookie'];

// 1. Create New Subscription Plan
echo "1. Super Admin: Create Subscription Plan...\n";
$planCode = 'plan_test_' . rand(100, 999);
$resPlan = testHttp('/superadmin/subscriptions/create-plan', $superCookie, 'POST', [
    'name' => 'Elite Campus Plan',
    'code' => $planCode,
    'price' => 850000.00,
    'billing_cycle' => 'annual',
    'student_limit' => 1500,
    'staff_limit' => 100,
    'description' => 'Automated test plan creation'
]);
assertAction($resPlan['code'] === 302, "Super Admin created plan -> redirected (HTTP 302)");
$planInDb = Database::fetch("SELECT * FROM subscription_plans WHERE code = ?", [$planCode]);
assertAction($planInDb !== false && (float)$planInDb['price'] === 850000.0, "New plan '{$planCode}' verified in database table");

// 2. Add Custom Domain to School 1
echo "\n2. Super Admin: Add Custom Domain Mapping...\n";
$testDomain = 'portal.apexcollege-' . rand(100, 999) . '.org.ng';
$resDomain = testHttp('/superadmin/schools/1/add-domain', $superCookie, 'POST', [
    'domain' => $testDomain
]);
assertAction($resDomain['code'] === 302, "Super Admin mapped custom domain -> redirected (HTTP 302)");
$domainInDb = Database::fetch("SELECT * FROM tenant_domains WHERE domain = ?", [$testDomain]);
assertAction($domainInDb !== false && (int)$domainInDb['school_id'] === 1, "Custom domain '{$testDomain}' mapped to School ID 1");

// Login as Apex School Admin
$apexLogin = testHttp('/demo-login?email=admin@apexcollege.edu.ng');
$apexCookie = $apexLogin['cookie'];

// 3. Direct Student Enrollment
echo "\n3. School Admin: Enroll New Student...\n";
$stuFirst = 'Kareem';
$stuLast = 'Ogunlesi_' . rand(100, 999);
$resStu = testHttp('/school/students/create', $apexCookie, 'POST', [
    'first_name' => $stuFirst,
    'last_name' => $stuLast,
    'middle_name' => 'Babajide',
    'gender' => 'male',
    'date_of_birth' => '2016-08-10',
    'class_arm_id' => 1,
    'blood_group' => 'O+',
    'genotype' => 'AA',
    'parent_name' => 'Alhaji Ogunlesi',
    'parent_phone' => '08022233344',
    'parent_email' => 'ogunlesi_' . rand(100, 999) . '@example.ng'
]);
assertAction($resStu['code'] === 302, "Enrolled student -> redirected (HTTP 302)");
$stuInDb = Database::fetch("SELECT * FROM students WHERE first_name = ? AND last_name = ? AND school_id = 1", [$stuFirst, $stuLast]);
assertAction($stuInDb !== false && !empty($stuInDb['admission_number']), "Student enrolled with unique admission number '{$stuInDb['admission_number']}'");

// 4. Create Academic Session
echo "\n4. School Admin: Create Academic Session...\n";
$sessName = '2027/2028_' . rand(10, 99);
$resSess = testHttp('/school/academics/create-session', $apexCookie, 'POST', [
    'name' => $sessName,
    'start_date' => '2027-09-01',
    'end_date' => '2028-07-31',
    'is_current' => 0
]);
assertAction($resSess['code'] === 302, "Created session -> redirected (HTTP 302)");
$sessInDb = Database::fetch("SELECT * FROM academic_sessions WHERE name = ? AND school_id = 1", [$sessName]);
assertAction($sessInDb !== false, "Session '{$sessName}' created in database");

// 5. Create Class Arm
echo "\n5. School Admin: Create Class Arm...\n";
$armName = 'Titanium_' . rand(10, 99);
$resArm = testHttp('/school/academics/create-arm', $apexCookie, 'POST', [
    'class_level_id' => 1,
    'name' => $armName,
    'capacity' => 40
]);
assertAction($resArm['code'] === 302, "Created class arm -> redirected (HTTP 302)");
$armInDb = Database::fetch("SELECT * FROM class_arms WHERE name = ? AND school_id = 1", [$armName]);
assertAction($armInDb !== false, "Class Arm '{$armName}' created in database");

// 6. Create Subject
echo "\n6. School Admin: Create Subject...\n";
$subCode = 'ROB_' . rand(10, 99);
$resSub = testHttp('/school/academics/create-subject', $apexCookie, 'POST', [
    'name' => 'Robotics & AI',
    'code' => $subCode,
    'category' => 'sciences'
]);
assertAction($resSub['code'] === 302, "Created subject -> redirected (HTTP 302)");
$subInDb = Database::fetch("SELECT * FROM subjects WHERE code = ? AND school_id = 1", [$subCode]);
assertAction($subInDb !== false, "Subject '{$subCode}' created in database");

// 7. Onboard Staff Member
echo "\n7. School Admin: Onboard Faculty/Staff...\n";
$staffEmail = 'staff_' . rand(100, 999) . '@apexcollege.edu.ng';
$resStaff = testHttp('/school/staff/create', $apexCookie, 'POST', [
    'name' => 'Engr. Daniel Alabi',
    'email' => $staffEmail,
    'phone' => '08099887766',
    'role' => 'teacher',
    'designation' => 'Robotics Instructor',
    'department' => 'Technology',
    'qualification' => 'B.Eng Robotics',
    'salary_base' => 175000.00
]);
assertAction($resStaff['code'] === 302, "Onboarded staff -> redirected (HTTP 302)");
$staffInDb = Database::fetch("SELECT st.*, u.email FROM staff st JOIN users u ON u.id = st.user_id WHERE u.email = ?", [$staffEmail]);
assertAction($staffInDb !== false && !empty($staffInDb['employee_number']), "Staff member onboarded with employee number '{$staffInDb['employee_number']}'");

// 8. Issue Fee Invoice
echo "\n8. School Admin: Issue Student Fee Invoice...\n";
$resInv = testHttp('/school/finance/create-invoice', $apexCookie, 'POST', [
    'student_id' => $stuInDb['id'],
    'fee_category_id' => 1,
    'amount' => 195000.00,
    'due_date' => '2026-11-30'
]);
assertAction($resInv['code'] === 302, "Issued invoice -> redirected (HTTP 302)");
$invInDb = Database::fetch("SELECT * FROM fee_invoices WHERE student_id = ? AND total_amount = 195000.00", [$stuInDb['id']]);
assertAction($invInDb !== false && (float)$invInDb['balance_due'] === 195000.0, "Invoice '{$invInDb['invoice_number']}' created with balance ₦195,000.00");

// =================================================================
echo "\n========================================================\n";
echo "CRUD ACTIONS TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "========================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
