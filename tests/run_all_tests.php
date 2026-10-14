<?php

declare(strict_types=1);

$suites = [
    'codebase_health_check.php',
    'deep_oversight_audit.php',
    'test_all_endpoints.php',
    'test_cross_tenant_security.php',
    'test_portal_billing.php',
    'test_notices_and_messaging.php',
    'test_workflows.php',
    'test_new_actions.php'
];

echo "========================================================\n";
echo "EduSphere SaaS &mdash; Master Quality & Verification Suite\n";
echo "========================================================\n\n";

$failedSuites = [];

foreach ($suites as $suite) {
    echo ">>> RUNNING: {$suite}\n";
    $cmd = 'C:\\xampp\\php\\php.exe ' . escapeshellarg(__DIR__ . '/' . $suite);
    passthru($cmd, $exitCode);
    if ($exitCode !== 0) {
        $failedSuites[] = $suite;
    }
    echo "\n--------------------------------------------------------\n\n";
}

if (empty($failedSuites)) {
    echo "🎉 MASTER AUDIT SUCCESS: ALL " . count($suites) . " TEST SUITES PASSED CLEANLY (100% GREEN)!\n";
    exit(0);
} else {
    echo "❌ AUDIT FAILURE: The following test suites encountered errors:\n";
    foreach ($failedSuites as $f) {
        echo "  - {$f}\n";
    }
    exit(1);
}
