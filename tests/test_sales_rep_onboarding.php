<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Core\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "========================================================\n";
echo "Testing Live HTTP Sales Rep Registration & Onboarding\n";
echo "========================================================\n";

$baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost/schoolportals', '/');

function curlRequest(string $url, string $method = 'GET', array $postData = [], ?string $cookie = null): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }

    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headerStr = substr($raw ?: '', 0, $headerSize);
    $body = substr($raw ?: '', $headerSize);

    $sessionCookie = '';
    if (preg_match_all('/^Set-Cookie:\s*(PHPSESSID=[^;]+)/mi', $headerStr, $matches)) {
        $sessionCookie = end($matches[1]);
    }

    $location = '';
    if (preg_match('/^Location:\s*(.+)$/mi', $headerStr, $locMatches)) {
        $location = trim($locMatches[1]);
    }

    return [
        'code' => $code,
        'body' => $body,
        'header' => $headerStr,
        'cookie' => $sessionCookie,
        'location' => $location
    ];
}

$testEmail = 'partner_http_' . time() . '@example.com';
$testSchoolName = 'St. Gregory Crest College ' . rand(100, 999);
$testPhone = '080' . rand(10000000, 99999999);

$createdUserId = null;

try {
    // 1. GET /become-a-sales-rep
    echo "1. Testing GET {$baseUrl}/become-a-sales-rep...\n";
    $resp = curlRequest("{$baseUrl}/become-a-sales-rep");
    if ($resp['code'] !== 200) {
        throw new RuntimeException("Expected HTTP 200 for registration form, got: {$resp['code']}");
    }
    if (strpos($resp['body'], 'Sales Partner Registration & School Onboarding') === false ||
        strpos($resp['body'], 'Commercial Sales Partner Agreement') === false) {
        throw new RuntimeException("Registration page body is missing key elements.");
    }
    echo "   [PASS] GET /become-a-sales-rep returned HTTP 200 with complete onboarding interface.\n";

    // Extract CSRF token from page
    preg_match('/name="_csrf"\s+value="([^"]+)"/', $resp['body'], $csrfMatch);
    $csrfToken = $csrfMatch[1] ?? 'test_csrf_token';
    $cookie = $resp['cookie'];

    // 2. POST /become-a-sales-rep
    echo "\n2. Testing POST submission to {$baseUrl}/become-a-sales-rep...\n";
    $postPayload = [
        '_csrf' => $csrfToken,
        'rep_name' => 'Adewale Adeleke',
        'rep_email' => $testEmail,
        'rep_phone' => $testPhone,
        'rep_password' => 'partnerSecurePass1',
        'rep_password_confirm' => 'partnerSecurePass1',
        'territory' => 'Lagos (Island & Lekki)',
        'bank_name' => 'Zenith Bank',
        'account_number' => '1029384756',
        'account_name' => 'Adewale Adeleke',
        'agree_terms' => '1',
        'agree_conduct' => '1',
        'agree_payout' => '1',
        'school_name' => $testSchoolName,
        'school_type' => 'combined',
        'school_state' => 'Lagos',
        'school_address' => '25 Admiralty Way, Lekki Phase 1',
        'contact_person' => 'Mrs. Folashade Coker (Proprietress)',
        'school_phone' => '0802' . rand(1000000, 9999999),
        'school_email' => 'contact@stgregory' . rand(100, 999) . '.example',
        'estimated_students' => 520,
        'current_system' => 'Legacy offline desktop software',
        'notes' => 'Looking to switch for upcoming academic term',
    ];

    $postResp = curlRequest("{$baseUrl}/become-a-sales-rep", 'POST', $postPayload, $cookie);
    echo "   [HTTP status: {$postResp['code']}, Redirect: {$postResp['location']}]\n";

    if ($postResp['code'] !== 302) {
        throw new RuntimeException("Expected HTTP 302 redirect after submission, got: {$postResp['code']}");
    }
    if (strpos($postResp['location'], '/sales') === false) {
        throw new RuntimeException("Expected redirect location to contain /sales, got: {$postResp['location']}");
    }
    echo "   [PASS] Form submission redirected to /sales successfully.\n";

    // Use regenerated authenticated session cookie
    $authCookie = $postResp['cookie'] ?: $cookie;

    // 3. Verify Database Records
    echo "\n3. Inspecting Database Records Integrity...\n";
    $user = Database::fetch("SELECT * FROM users WHERE email = ?", [$testEmail]);
    if (!$user) {
        throw new RuntimeException("User record not found in database.");
    }
    $createdUserId = (int)$user['id'];
    echo "   [PASS] User created with ID {$createdUserId} and role '{$user['role']}'\n";

    $rep = Database::fetch("SELECT * FROM sales_representatives WHERE user_id = ?", [$createdUserId]);
    if (!$rep) {
        throw new RuntimeException("Sales representative record not found.");
    }
    echo "   [PASS] Sales Representative created with Code '{$rep['rep_code']}', Bank '{$rep['bank_name']} ({$rep['account_number']})', Rate: {$rep['commission_rate']}%\n";

    $agreement = Database::fetch("SELECT * FROM sales_partner_agreements WHERE rep_id = ?", [$rep['id']]);
    if (!$agreement) {
        throw new RuntimeException("Sales partner agreement audit row not found.");
    }
    echo "   [PASS] Agreement audit row recorded: Signer '{$agreement['signer_name']}', IP '{$agreement['ip_address']}', Version '{$agreement['agreement_version']}'\n";

    $lead = Database::fetch("SELECT * FROM sales_leads WHERE rep_id = ? AND school_name = ?", [$rep['id'], $testSchoolName]);
    if (!$lead) {
        throw new RuntimeException("Prospective school lead was not found in CRM under this rep.");
    }
    echo "   [PASS] Lead successfully attributed: '{$lead['school_name']}', Est. Pupils: {$lead['estimated_students']}, Source: '{$lead['lead_source']}'\n";

    // 4. Access Sales Dashboard using Session
    echo "\n4. Testing Access to Sales CRM Dashboard with Session Cookie...\n";
    $dashResp = curlRequest("{$baseUrl}/sales", 'GET', [], $authCookie);
    if ($dashResp['code'] !== 200) {
        throw new RuntimeException("Expected HTTP 200 for sales dashboard, got: {$dashResp['code']}");
    }
    if (strpos($dashResp['body'], $rep['rep_code']) === false) {
        throw new RuntimeException("Sales dashboard did not contain representative code '{$rep['rep_code']}'.");
    }
    if (strpos($dashResp['body'], 'Copy Partner Link') === false) {
        throw new RuntimeException("Sales dashboard did not contain Copy Partner Link button.");
    }
    echo "   [PASS] Sales CRM Dashboard loaded HTTP 200 with rep code '{$rep['rep_code']}' and referral tools.\n";

    echo "\n========================================================\n";
    echo "ALL HTTP E2E ONBOARDING & AGREEMENT TESTS PASSED 100%!\n";
    echo "========================================================\n";

} finally {
    if ($createdUserId) {
        Database::execute("DELETE FROM users WHERE id = ?", [$createdUserId]);
        echo "(Test data cleaned up successfully)\n";
    }
}
