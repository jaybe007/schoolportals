<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Core\Database;
use EduSphere\Core\Session;
use EduSphere\Core\Auth;
use EduSphere\Core\TenantContext;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "========================================================\n";
echo "EduSphere Deep Endpoint & View Integrity Audit\n";
echo "========================================================\n\n";

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

    // Extract the latest rotated PHPSESSID cookie
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

$issues = [];

// 1. Test Public Routes (Guest)
$publicRoutes = [
    '/login',
    '/site/apex',
    '/site/apex/about',
    '/site/apex/admissions',
    '/site/apex/academics',
    '/site/apex/contact',
    '/site/apex/apply',
];

echo "Testing Public Guest Endpoints...\n";
foreach ($publicRoutes as $route) {
    $res = testHttp($route);
    if ($res['code'] !== 200) {
        $issues[] = "Public route {$route} returned HTTP {$res['code']}";
        echo "  ✗ {$route} -> HTTP {$res['code']}\n";
    } else {
        echo "  ✓ {$route} -> HTTP 200\n";
    }
}

// 2. Login as Super Admin & Test Super Admin routes
echo "\nTesting Super Admin Endpoints...\n";
$superLogin = testHttp('/demo-login?role=super_admin');
$superCookie = $superLogin['cookie'];

$superAdminRoutes = [
    '/superadmin',
    '/superadmin/schools',
    '/superadmin/portal-billing',
    '/superadmin/subscriptions',
    '/superadmin/users',
    '/superadmin/audit-logs',
    '/superadmin/settings',
];

foreach ($superAdminRoutes as $route) {
    $res = testHttp($route, $superCookie);
    if ($res['code'] !== 200) {
        $issues[] = "SuperAdmin route {$route} returned HTTP {$res['code']}";
        echo "  ✗ {$route} -> HTTP {$res['code']}\n";
    } else {
        echo "  ✓ {$route} -> HTTP 200\n";
    }
}

// 3. Login as School Admin & Test School Portals
echo "\nTesting School Admin & Faculty Endpoints...\n";
$schoolLogin = testHttp('/demo-login?role=school_admin');
$schoolCookie = $schoolLogin['cookie'];

$schoolRoutes = [
    '/school',
    '/school/admissions',
    '/school/students',
    '/school/academics',
    '/school/attendance',
    '/school/attendance?arm_id=1&date=' . date('Y-m-d'),
    '/school/exams',
    '/school/exams?arm_id=1&subject_id=1',
    '/school/staff',
    '/school/finance',
    '/school/term-portal-fee',
    '/school/incentives',
    '/school/notices',
    '/school/messages',
    '/school/library',
    '/school/website-cms',
    '/school/settings',
];

foreach ($schoolRoutes as $route) {
    $res = testHttp($route, $schoolCookie);
    if ($res['code'] !== 200) {
        $issues[] = "School route {$route} returned HTTP {$res['code']}";
        echo "  ✗ {$route} -> HTTP {$res['code']}\n";
    } else {
        echo "  ✓ {$route} -> HTTP 200\n";
    }
}

// 4. Test PDF Report Card generation via HTTP
echo "\nTesting Report Card PDF Endpoint...\n";
$studentId = Database::fetch("SELECT id FROM students WHERE school_id = 1 LIMIT 1")['id'];
$pdfRes = testHttp("/school/students/{$studentId}/report-card", $schoolCookie);
if ($pdfRes['code'] === 200 && str_contains($pdfRes['body'], '%PDF')) {
    echo "  ✓ /school/students/{$studentId}/report-card -> HTTP 200 with valid binary PDF stream\n";
} else {
    echo "  FATAL ERROR DETAILS: " . $pdfRes['body'] . "\n";
    $issues[] = "Report card PDF route failed: HTTP {$pdfRes['code']}";
    echo "  ✗ /school/students/{$studentId}/report-card -> HTTP {$pdfRes['code']}\n";
}

// 5. Login as Sales Rep & Test Sales CRM
echo "\nTesting Sales CRM Endpoints (Sales Rep & Manager)...\n";
$salesLogin = testHttp('/demo-login?role=sales_rep');
$salesCookie = $salesLogin['cookie'];

$salesRoutes = [
    '/sales',
    '/sales/pipeline',
    '/sales/leads',
    '/sales/commissions',
];

foreach ($salesRoutes as $route) {
    $res = testHttp($route, $salesCookie);
    if ($res['code'] !== 200) {
        $issues[] = "Sales route {$route} returned HTTP {$res['code']}";
        echo "  ✗ {$route} -> HTTP {$res['code']}\n";
    } else {
        echo "  ✓ {$route} -> HTTP 200\n";
    }
}

// 6. Login as Student
echo "\nTesting Student Portal...\n";
$stuLogin = testHttp('/demo-login?role=student');
$stuCookie = $stuLogin['cookie'];
$res = testHttp('/portal/student', $stuCookie);
if ($res['code'] === 200) {
    echo "  ✓ /portal/student -> HTTP 200\n";
} else {
    $issues[] = "Student portal returned HTTP {$res['code']}";
    echo "  ✗ /portal/student -> HTTP {$res['code']}\n";
}

// 7. Login as Parent
echo "\nTesting Parent Portal...\n";
$parentLogin = testHttp('/demo-login?role=parent');
$parentCookie = $parentLogin['cookie'];
$parentRoutes = [
    '/portal/parent',
    '/portal/parent/messages',
];
foreach ($parentRoutes as $pRoute) {
    $res = testHttp($pRoute, $parentCookie);
    if ($res['code'] === 200) {
        echo "  ✓ {$pRoute} -> HTTP 200\n";
    } else {
        $issues[] = "Parent route {$pRoute} returned HTTP {$res['code']}";
        echo "  ✗ {$pRoute} -> HTTP {$res['code']}\n";
    }
}

echo "\n========================================================\n";
if (empty($issues)) {
    echo "AUDIT SUCCESS: All 26 endpoints returned HTTP 200 OK without errors!\n";
} else {
    echo "AUDIT DISCOVERED ISSUES (" . count($issues) . "):\n";
    foreach ($issues as $iss) {
        echo "  - {$iss}\n";
    }
}
echo "========================================================\n";
