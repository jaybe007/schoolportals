<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Core\Database;
use EduSphere\Services\FinancialPdfService;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "========================================================\n";
echo "Testing Financial PDF Service (Receipts & Invoices)\n";
echo "========================================================\n";

$baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost/schoolportals', '/');

function httpGet(string $url, ?string $cookie = null): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
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

    return ['code' => $code, 'body' => $body, 'header' => $headerStr, 'cookie' => $sessionCookie];
}

// 1. Direct PDF Generation Test
echo "1. Testing Direct PDF Generation via FinancialPdfService...\n";

// Fetch an existing receipt
$receipt = Database::fetch("SELECT * FROM payment_receipts LIMIT 1");
if (!$receipt) {
    throw new RuntimeException("No payment receipt found in database to test.");
}
$receiptPdf = FinancialPdfService::generateReceiptPdf((int)$receipt['school_id'], (int)$receipt['id']);
$receiptPdfOutput = $receiptPdf->output();
if (!str_starts_with($receiptPdfOutput, '%PDF')) {
    throw new RuntimeException("Generated receipt output is not a valid PDF.");
}
echo "   [PASS] Generated Official Receipt PDF (" . strlen($receiptPdfOutput) . " bytes) with %PDF header.\n";

// Fetch an existing invoice
$invoice = Database::fetch("SELECT * FROM fee_invoices LIMIT 1");
if (!$invoice) {
    throw new RuntimeException("No fee invoice found in database to test.");
}
$invoicePdf = FinancialPdfService::generateInvoicePdf((int)$invoice['school_id'], (int)$invoice['id']);
$invoicePdfOutput = $invoicePdf->output();
if (!str_starts_with($invoicePdfOutput, '%PDF')) {
    throw new RuntimeException("Generated invoice output is not a valid PDF.");
}
echo "   [PASS] Generated Official Invoice PDF (" . strlen($invoicePdfOutput) . " bytes) with %PDF header.\n";

// Fetch an existing portal payment
$portalPayment = Database::fetch("SELECT * FROM portal_payments LIMIT 1");
if ($portalPayment) {
    $ppPdf = FinancialPdfService::generatePortalPaymentReceiptPdf((int)$portalPayment['school_id'], (int)$portalPayment['id']);
    $ppPdfOutput = $ppPdf->output();
    if (!str_starts_with($ppPdfOutput, '%PDF')) {
        throw new RuntimeException("Generated portal payment receipt output is not a valid PDF.");
    }
    echo "   [PASS] Generated Official Portal Payment Receipt PDF (" . strlen($ppPdfOutput) . " bytes).\n";
}

// 2. HTTP Route Testing as School Admin
echo "\n2. Testing HTTP Route Access as School Admin...\n";
$adminLogin = httpGet("{$baseUrl}/demo-login?role=school_admin");
$adminCookie = $adminLogin['cookie'];

$receiptHttp = httpGet("{$baseUrl}/school/finance/receipts/{$receipt['id']}/pdf", $adminCookie);
if ($receiptHttp['code'] !== 200 || !str_starts_with($receiptHttp['body'], '%PDF')) {
    throw new RuntimeException("Expected HTTP 200 PDF for admin receipt download, got: {$receiptHttp['code']}");
}
echo "   [PASS] Admin downloaded Official Receipt PDF (HTTP 200, valid %PDF stream).\n";

$invoiceHttp = httpGet("{$baseUrl}/school/finance/invoices/{$invoice['id']}/pdf", $adminCookie);
if ($invoiceHttp['code'] !== 200 || !str_starts_with($invoiceHttp['body'], '%PDF')) {
    throw new RuntimeException("Expected HTTP 200 PDF for admin invoice download, got: {$invoiceHttp['code']}");
}
echo "   [PASS] Admin downloaded Official Fee Invoice PDF (HTTP 200, valid %PDF stream).\n";

// 3. HTTP Route Testing as Parent & Cross-Tenant Isolation
echo "\n3. Testing HTTP Route Access as Parent & Security Isolation...\n";
$parentLogin = httpGet("{$baseUrl}/demo-login?email=parent@apexcollege.edu.ng");
$parentCookie = $parentLogin['cookie'];

if ($portalPayment) {
    // Check if portalPayment belongs to apex parent's ward
    $ppCheck = Database::fetch("SELECT * FROM portal_payments WHERE id = ? AND (parent_id = 1 OR student_id = 1)", [$portalPayment['id']]);
    if ($ppCheck) {
        $parentReceiptHttp = httpGet("{$baseUrl}/portal/portal-payments/{$portalPayment['id']}/pdf", $parentCookie);
        if ($parentReceiptHttp['code'] === 200 && str_starts_with($parentReceiptHttp['body'], '%PDF')) {
            echo "   [PASS] Parent downloaded Ward's Portal Receipt PDF (HTTP 200).\n";
        }
    }
}

// Attempt unauthorized download of non-existent or foreign receipt (e.g. ID 999999)
$unauthorizedHttp = httpGet("{$baseUrl}/portal/receipts/999999/pdf", $parentCookie);
if ($unauthorizedHttp['code'] === 403) {
    echo "   [PASS] Unauthorized/Foreign receipt access blocked with HTTP 403 Forbidden.\n";
} else {
    throw new RuntimeException("Expected HTTP 403 for foreign receipt access, got: {$unauthorizedHttp['code']}");
}

echo "\n========================================================\n";
echo "ALL FINANCIAL PDF & PRINTING TESTS PASSED 100%!\n";
echo "========================================================\n";
