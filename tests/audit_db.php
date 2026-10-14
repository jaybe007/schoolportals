<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Core\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$tables = [
    'schools', 'tenant_domains', 'users', 'students', 'staff',
    'class_levels', 'class_arms', 'subjects', 'academic_sessions',
    'academic_terms', 'admissions', 'attendance', 'exam_scores',
    'student_term_summaries', 'fee_invoices', 'payment_receipts',
    'library_books', 'inventory_items', 'school_website_pages', 'audit_logs'
];

echo "=== DATABASE TENANT AUDIT ===\n";
foreach ($tables as $t) {
    if ($t === 'schools') {
        $rows = Database::query("SELECT id, name, slug FROM schools");
        echo "schools:\n";
        foreach ($rows as $r) {
            echo "  - ID {$r['id']}: {$r['name']} ({$r['slug']})\n";
        }
        continue;
    }

    $colCheck = Database::query("SHOW COLUMNS FROM `{$t}` LIKE 'school_id'");
    if (empty($colCheck)) {
        continue;
    }

    $counts = Database::query("SELECT school_id, COUNT(*) as cnt FROM `{$t}` GROUP BY school_id");
    echo "{$t}:\n";
    foreach ($counts as $c) {
        $sid = $c['school_id'] === null ? 'NULL (Platform)' : "School #{$c['school_id']}";
        echo "  - {$sid}: {$c['cnt']} records\n";
    }
}
