<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Services\ReportCardPdfService;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

try {
    echo "Testing ReportCardPdfService for student 1 in school 1...\n";
    $dompdf = ReportCardPdfService::generateReportCard(1, 1);
    $output = $dompdf->output();
    echo "SUCCESS: Report card PDF generated! File size: " . strlen($output) . " bytes\n";
    if (strpos($output, '%PDF') === 0) {
        echo "VALID PDF HEADER CONFIRMED (%PDF)\n";
    }
} catch (\Throwable $e) {
    echo "FAILURE: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
