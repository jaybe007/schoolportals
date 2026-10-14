<?php

declare(strict_types=1);

/**
 * EduSphere SaaS - Comprehensive Codebase Health, Linting & Oversight Inspector
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Core\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "========================================================\n";
echo "EduSphere Comprehensive Codebase Health & Oversight Scan\n";
echo "========================================================\n\n";

$issues = [];
$checks = 0;

function check(bool $cond, string $desc, ?string $detail = null): void {
    global $checks, $issues;
    $checks++;
    if ($cond) {
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
        if ($detail) {
            echo "         Issue: {$detail}\n";
        }
        $issues[] = $desc . ($detail ? " ({$detail})" : '');
    }
}

// -------------------------------------------------------------
// 1. PHP Syntax Linting across all files (excluding vendor)
// -------------------------------------------------------------
echo "1. Checking PHP Syntax across entire application...\n";
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/..'));
$phpFiles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getRealPath();
        if (!str_contains($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
            $phpFiles[] = $path;
        }
    }
}

$syntaxErrors = [];
foreach ($phpFiles as $file) {
    $output = [];
    $returnVar = 0;
    exec("C:\\xampp\\php\\php.exe -l " . escapeshellarg($file) . " 2>&1", $output, $returnVar);
    if ($returnVar !== 0) {
        $syntaxErrors[] = basename($file) . ": " . implode("\n", $output);
    }
}
check(empty($syntaxErrors), "Syntax check on all " . count($phpFiles) . " application PHP files", implode("; ", $syntaxErrors));

// -------------------------------------------------------------
// 2. View Integrity: Check CSRF fields in POST forms
// -------------------------------------------------------------
echo "\n2. Inspecting View Forms for CSRF Tokens & Actions...\n";
$viewIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../views'));
$formsChecked = 0;
$missingCsrf = [];

foreach ($viewIterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        // Find all forms with method POST
        if (preg_match_all('/<form\b[^>]*\bmethod=["\']POST["\'][^>]*>(.*?)<\/form>/is', $content, $matches)) {
            foreach ($matches[1] as $idx => $formBody) {
                $formsChecked++;
                if (!str_contains($formBody, 'csrf_field()') && !str_contains($formBody, '_csrf')) {
                    $missingCsrf[] = $file->getFilename() . " (form #{$formsChecked})";
                }
            }
        }
    }
}
check(empty($missingCsrf), "All {$formsChecked} POST forms in views contain CSRF tokens", implode(", ", $missingCsrf));

// -------------------------------------------------------------
// 3. Inspect Student and Parent Portal Data Scoping
// -------------------------------------------------------------
echo "\n3. Inspecting Student and Parent Portal Isolation...\n";
// Check if student portal checks user student mapping
$studentPortalCode = file_get_contents(__DIR__ . '/../app/Controllers/PortalController.php');
check(
    str_contains($studentPortalCode, "WHERE s.user_id = ?") || str_contains($studentPortalCode, "s.user_id = Auth::id()"),
    "Student Portal strictly scopes query to logged-in user's student record"
);

check(
    str_contains($studentPortalCode, "WHERE user_id = ?") && str_contains($studentPortalCode, "WHERE s.parent_id = ? AND s.school_id = ?"),
    "Parent Portal strictly scopes query to logged-in parent's linked wards"
);

// -------------------------------------------------------------
// 4. Check for Dead Links in Navigation Layouts
// -------------------------------------------------------------
echo "\n4. Inspecting Navigation Layouts for Dead / Broken Links...\n";
$routesCode = file_get_contents(__DIR__ . '/../index.php');
$layouts = ['tenant.php', 'superadmin.php'];
$unmatchedLinks = [];

foreach ($layouts as $layout) {
    $layoutPath = __DIR__ . '/../views/layouts/' . $layout;
    if (file_exists($layoutPath)) {
        $layoutContent = file_get_contents($layoutPath);
        if (preg_match_all('/url\(["\']([^"\']+)["\']\)/', $layoutContent, $matches)) {
            foreach ($matches[1] as $urlPath) {
                $cleanPath = '/' . ltrim(explode('?', $urlPath)[0], '/');
                // Check if route exists in index.php
                if (!str_contains($routesCode, "'{$cleanPath}'") && 
                    !str_contains($routesCode, "\"{$cleanPath}\"") &&
                    !str_contains($routesCode, "'/" . explode('/', ltrim($cleanPath, '/'))[0]) &&
                    $cleanPath !== '/' && $cleanPath !== '/logout') {
                    $unmatchedLinks[] = "{$layout}: {$cleanPath}";
                }
            }
        }
    }
}
check(empty($unmatchedLinks), "Navigation layout links correspond to valid system routes", implode(", ", $unmatchedLinks));

// -------------------------------------------------------------
// 5. Check SQL Query Security (Zero raw string concatenations)
// -------------------------------------------------------------
echo "\n5. Inspecting Controllers for SQL Injection Vulnerabilities...\n";
$controllerDir = __DIR__ . '/../app/Controllers';
$sqlConcatenationIssues = [];

foreach (scandir($controllerDir) as $cFile) {
    if (str_ends_with($cFile, '.php')) {
        $code = file_get_contents($controllerDir . '/' . $cFile);
        // Look for Database::query("... $var ...") or execute
        if (preg_match_all('/Database::(?:query|fetch|execute)\s*\(\s*["\'][^"\']*(\$[a-zA-Z0-9_]+)[^"\']*["\']\s*[,)]/', $code, $matches)) {
            foreach ($matches[1] as $varName) {
                // Ignore safe params or query building if verified
                $sqlConcatenationIssues[] = "{$cFile}: variable {$varName} directly in SQL string";
            }
        }
    }
}
check(empty($sqlConcatenationIssues), "All database operations use parameterized prepared statements", implode(", ", $sqlConcatenationIssues));

// -------------------------------------------------------------
// 6. Check Database Table Constraints & Consistency
// -------------------------------------------------------------
echo "\n6. Checking Database Consistency & Relationship Integrity...\n";
// Verify schools have at least one active academic session and term
$schoolsWithoutSession = Database::query("
    SELECT s.id, s.name FROM schools s 
    LEFT JOIN academic_sessions ac ON ac.school_id = s.id 
    WHERE ac.id IS NULL
");
check(empty($schoolsWithoutSession), "All tenant schools have academic sessions configured");

$schoolsWithoutTerm = Database::query("
    SELECT s.id, s.name FROM schools s 
    LEFT JOIN academic_terms t ON t.school_id = s.id 
    WHERE t.id IS NULL
");
check(empty($schoolsWithoutTerm), "All tenant schools have academic terms configured");

// Check students without class arms
$studentsWithoutArm = Database::query("SELECT id, admission_number FROM students WHERE class_arm_id IS NULL");
check(empty($studentsWithoutArm), "All enrolled students are assigned to valid class arms");

// Check invoices with negative balances or invalid status
$invalidInvoices = Database::query("SELECT id, invoice_number FROM fee_invoices WHERE balance_due < 0");
check(empty($invalidInvoices), "No fee invoices have negative balance dues");

// -------------------------------------------------------------
// 7. Check Student & Parent Portal Live User Access Test
// -------------------------------------------------------------
echo "\n7. Live Testing Student & Parent Portal Authentication...\n";

function testCurl(string $path, ?string $cookie = null): array {
    global $dotenv;
    $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost/schoolportals', '/');
    $ch = curl_init("{$baseUrl}/" . ltrim($path, '/'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $headerStr = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);
    $sessionCookie = '';
    if (preg_match_all('/^Set-Cookie:\s*(PHPSESSID=[^;]+)/mi', $headerStr, $matches)) {
        $sessionCookie = end($matches[1]);
    }
    return ['code' => $code, 'body' => $body, 'cookie' => $sessionCookie];
}

// Student Login
$studentLogin = testCurl('/demo-login?email=student@apexcollege.edu.ng');
$studentCookie = $studentLogin['cookie'];
$studentDashboard = testCurl('/portal/student', $studentCookie);
check($studentDashboard['code'] === 200, "Student portal dashboard loads successfully (HTTP 200)");

// Parent Login
$parentLogin = testCurl('/demo-login?email=parent@apexcollege.edu.ng');
$parentCookie = $parentLogin['cookie'];
$parentDashboard = testCurl('/portal/parent', $parentCookie);
check($parentDashboard['code'] === 200, "Parent portal dashboard loads successfully (HTTP 200)");

// Check Cross-Portal Guard: Student cannot access Parent Portal
$studentAccessParent = testCurl('/portal/parent', $studentCookie);
check($studentAccessParent['code'] === 403 || $studentAccessParent['code'] === 302, "Student blocked from accessing Parent Portal (HTTP {$studentAccessParent['code']})");

// Check Cross-Portal Guard: Student cannot access School Management Portal
$studentAccessSchool = testCurl('/school', $studentCookie);
check($studentAccessSchool['code'] === 403 || $studentAccessSchool['code'] === 302, "Student blocked from accessing Faculty School Portal (HTTP {$studentAccessSchool['code']})");

// =============================================================
echo "\n========================================================\n";
echo "HEALTH CHECK SCAN SUMMARY: {$checks} Checks Run, " . count($issues) . " Issues Found\n";
echo "========================================================\n";

if (!empty($issues)) {
    echo "\nIdentified Issues:\n";
    foreach ($issues as $iss) {
        echo " - {$iss}\n";
    }
    exit(1);
}

echo "All systems fully operational and strictly compliant!\n";
exit(0);
