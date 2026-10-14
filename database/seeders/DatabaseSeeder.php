<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Core\Database;
use EduSphere\Services\GradingService;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->safeLoad();

echo "=== Seeding EduSphere SaaS Database with Realistic Production-Grade Data ===\n";

try {
    $pdo = Database::getConnection();

    // Disable foreign keys temporarily for clean wipe
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    $tables = [
        'parent_school_messages', 'school_notices',
        'portal_billing_notices', 'portal_commission_ledger', 'school_payout_requests',
        'school_incentive_ledger', 'financial_allocations', 'portal_payments', 'portal_invoices',
        'school_portal_fee_agreements', 'revenue_sharing_policies', 'portal_pricing_policies',
        'audit_logs', 'school_website_pages', 'announcements', 'clinic_records',
        'inventory_items', 'library_loans', 'library_books', 'timetables',
        'sales_commissions', 'sales_activities', 'sales_leads', 'sales_representatives',
        'subscriptions', 'payment_receipts', 'fee_invoices', 'fee_structures',
        'fee_categories', 'student_term_summaries', 'exam_scores', 'grading_scales',
        'assessment_configs', 'staff_attendance', 'attendance', 'admissions',
        'students', 'parents', 'staff', 'subjects', 'class_arms', 'class_levels',
        'academic_terms', 'academic_sessions', 'users', 'tenant_domains',
        'schools', 'subscription_plans', 'platform_settings'
    ];

    foreach ($tables as $tbl) {
        $pdo->exec("TRUNCATE TABLE `{$tbl}`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "1. Inserting Platform Settings & Plans...\n";
    Database::execute("INSERT INTO platform_settings (setting_key, setting_value, setting_group, is_public) VALUES
        ('platform_name', 'EduSphere SaaS', 'general', 1),
        ('platform_tagline', 'Next-Generation Multi-Tenant School Administration Engine', 'general', 1),
        ('support_email', 'support@edusphere.example', 'general', 1),
        ('currency_symbol', '₦', 'finance', 1),
        ('currency_code', 'NGN', 'finance', 1),
        ('enable_paystack', '1', 'finance', 0),
        ('enable_sales_crm', '1', 'sales', 0),
        ('compliance_standard', 'Nigeria Data Protection Act (NDPA) 2023 Compliant', 'security', 1)
    ");

    Database::execute("INSERT INTO subscription_plans (code, name, description, billing_cycle, price, student_limit, staff_limit, features, is_active) VALUES
        ('standard', 'Standard School Plan', 'Comprehensive management for growing primary & secondary schools.', 'annual', 250000.00, 300, 30, '{\"academics\":true,\"attendance\":true,\"exams\":true,\"fees\":true,\"website\":true}', 1),
        ('premium', 'Premium Enterprise Plan', 'Advanced enterprise features including library, clinic, payroll & online admissions.', 'annual', 600000.00, 1000, 80, '{\"academics\":true,\"attendance\":true,\"exams\":true,\"fees\":true,\"website\":true,\"library\":true,\"clinic\":true,\"payroll\":true,\"custom_domain\":true}', 1),
        ('group', 'Apex Multi-Campus Plan', 'Unlimited students and campuses with dedicated priority server infrastructure.', 'annual', 1200000.00, 3000, 200, '{\"all_modules\":true,\"unlimited\":true}', 1)
    ");

    $defaultHash = password_hash('password123', PASSWORD_BCRYPT);

    echo "2. Inserting Platform Super Admin & Sales Team...\n";
    // Super admin
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (NULL, 'Dr. Marcus Adebayo', 'superadmin@edusphere.example', '+2348031234001', ?, 'super_admin', 1)
    ", [$defaultHash]);
    $superAdminId = (int)Database::lastInsertId();

    // Sales Manager
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (NULL, 'Victoria Eke', 'salesmanager@edusphere.example', '+2348031234002', ?, 'sales_manager', 1)
    ", [$defaultHash]);
    $salesManagerId = (int)Database::lastInsertId();

    // Sales Rep
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (NULL, 'Tunde Bakare', 'salesrep@edusphere.example', '+2348031234003', ?, 'sales_rep', 1)
    ", [$defaultHash]);
    $salesRepUserId = (int)Database::lastInsertId();

    Database::execute("INSERT INTO sales_representatives (user_id, rep_code, territory, manager_id, commission_type, commission_rate, monthly_target, status) VALUES
        (?, 'REP-LAGOS-01', 'Lagos & Ogun State Metro', ?, 'percentage', 10.00, 1500000.00, 'active')
    ", [$salesRepUserId, $salesManagerId]);
    $salesRepId = (int)Database::lastInsertId();

    echo "3. Creating Demo Tenant Schools...\n";
    // School 1: Apex Crown College
    Database::execute("INSERT INTO schools (uuid, code, name, slug, motto, school_type, email, phone, address, state, country, current_plan_id, status, subscription_status, subscription_expires_at) VALUES
        ('c3b8a101-1234-4567-89ab-cdef01234567', 'SCH-APEX-001', 'Apex Crown College', 'apex', 'Knowledge, Virtue and Leadership', 'combined', 'admin@apexcollege.edu.ng', '+2348023344551', 'Plot 14, Commercial Avenue, Ikeja GRA, Lagos', 'Lagos', 'Nigeria', 2, 'active', 'active', '2027-10-01')
    ");
    $apexSchoolId = (int)Database::lastInsertId();

    Database::execute("INSERT INTO tenant_domains (school_id, domain, is_primary, is_custom, is_verified, status) VALUES
        (?, 'apex.edusphere.example', 1, 0, 1, 'active'),
        (?, 'apexschools.edu.ng', 0, 1, 1, 'active')
    ", [$apexSchoolId, $apexSchoolId]);

    // School 2: Horizon International Academy
    Database::execute("INSERT INTO schools (uuid, code, name, slug, motto, school_type, email, phone, address, state, country, current_plan_id, status, subscription_status, subscription_expires_at) VALUES
        ('d4c9b202-2345-5678-90bc-def012345678', 'SCH-HORIZON-002', 'Horizon International Academy', 'horizon', 'Nurturing Global Thinkers', 'nursery_primary', 'admin@horizonacademy.sch.ng', '+2348029988772', '12 Diplomatic Drive, Maitama, Abuja', 'FCT', 'Nigeria', 1, 'active', 'active', '2027-08-15')
    ");
    $horizonSchoolId = (int)Database::lastInsertId();

    Database::execute("INSERT INTO tenant_domains (school_id, domain, is_primary, is_custom, is_verified, status) VALUES
        (?, 'horizon.edusphere.example', 1, 0, 1, 'active')
    ", [$horizonSchoolId]);

    echo "4. Seeding School Staff, Roles & Portal Users for Apex Crown College...\n";
    // School Admin
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (?, 'Olawale Sanusi', 'admin@apexcollege.edu.ng', '+2348033221101', ?, 'school_admin', 1)
    ", [$apexSchoolId, $defaultHash]);

    // Principal
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (?, 'Mrs. Folake Folorunsho', 'principal@apexcollege.edu.ng', '+2348033221102', ?, 'principal', 1)
    ", [$apexSchoolId, $defaultHash]);
    $principalUserId = (int)Database::lastInsertId();

    // Registrar
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (?, 'Mr. Godwin Okon', 'registrar@apexcollege.edu.ng', '+2348033221103', ?, 'registrar', 1)
    ", [$apexSchoolId, $defaultHash]);
    $registrarUserId = (int)Database::lastInsertId();

    // Teacher
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (?, 'Mr. Chidi Nwosu', 'teacher@apexcollege.edu.ng', '+2348033221104', ?, 'teacher', 1)
    ", [$apexSchoolId, $defaultHash]);
    $teacherUserId = (int)Database::lastInsertId();

    // Bursar
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (?, 'Mrs. Zainab Aliyu', 'bursar@apexcollege.edu.ng', '+2348033221105', ?, 'bursar', 1)
    ", [$apexSchoolId, $defaultHash]);
    $bursarUserId = (int)Database::lastInsertId();

    // Staff records
    Database::execute("INSERT INTO staff (school_id, user_id, employee_number, designation, department, qualification, date_of_joining, salary_base, status, phone) VALUES
        (?, ?, 'APX-EMP-001', 'Vice Principal & Senior Master', 'Administration', 'M.Ed Educational Management', '2020-09-01', 320000.00, 'active', '+2348033221102'),
        (?, ?, 'APX-EMP-002', 'Academic Registrar', 'Registry', 'B.Sc Public Administration', '2021-01-15', 240000.00, 'active', '+2348033221103'),
        (?, ?, 'APX-EMP-003', 'Senior Mathematics & Physics Tutor', 'Sciences', 'B.Sc Mathematics & Statistics', '2022-03-01', 220000.00, 'active', '+2348033221104'),
        (?, ?, 'APX-EMP-004', 'Chief Bursar & Finance Manager', 'Bursary', 'B.Sc Accounting, ICAN', '2019-11-01', 280000.00, 'active', '+2348033221105')
    ", [
        $apexSchoolId, $principalUserId,
        $apexSchoolId, $registrarUserId,
        $apexSchoolId, $teacherUserId,
        $apexSchoolId, $bursarUserId,
    ]);
    $teacherStaffRecord = Database::fetch("SELECT id FROM staff WHERE user_id = ?", [$teacherUserId]);
    $teacherStaffId = (int)$teacherStaffRecord['id'];

    echo "5. Seeding Academic Structure (Sessions, Terms, Classes, Subjects)...\n";
    Database::execute("INSERT INTO academic_sessions (school_id, name, start_date, end_date, is_current) VALUES
        (?, '2025/2026', '2025-09-08', '2026-07-24', 1)
    ", [$apexSchoolId]);
    $sessionId = (int)Database::lastInsertId();

    Database::execute("INSERT INTO academic_terms (school_id, session_id, name, start_date, end_date, is_current) VALUES
        (?, ?, 'First Term', '2025-09-08', '2025-12-15', 1),
        (?, ?, 'Second Term', '2026-01-12', '2026-04-10', 0),
        (?, ?, 'Third Term', '2026-05-04', '2026-07-24', 0)
    ", [$apexSchoolId, $sessionId, $apexSchoolId, $sessionId, $apexSchoolId, $sessionId]);
    $termId = (int)Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? AND name = 'First Term'", [$apexSchoolId])['id'];

    // Class levels
    Database::execute("INSERT INTO class_levels (school_id, name, section, rank_order) VALUES
        (?, 'JSS 1', 'junior_secondary', 1),
        (?, 'JSS 2', 'junior_secondary', 2),
        (?, 'JSS 3', 'junior_secondary', 3),
        (?, 'SSS 1', 'senior_secondary', 4),
        (?, 'SSS 2', 'senior_secondary', 5),
        (?, 'SSS 3', 'senior_secondary', 6)
    ", [$apexSchoolId, $apexSchoolId, $apexSchoolId, $apexSchoolId, $apexSchoolId, $apexSchoolId]);
    $jss1Id = (int)Database::fetch("SELECT id FROM class_levels WHERE school_id = ? AND name = 'JSS 1'", [$apexSchoolId])['id'];
    $sss2Id = (int)Database::fetch("SELECT id FROM class_levels WHERE school_id = ? AND name = 'SSS 2'", [$apexSchoolId])['id'];

    // Class arms
    Database::execute("INSERT INTO class_arms (school_id, class_level_id, name, capacity, class_teacher_id) VALUES
        (?, ?, 'Gold', 35, ?),
        (?, ?, 'Diamond', 35, NULL),
        (?, ?, 'Science', 30, NULL)
    ", [$apexSchoolId, $jss1Id, $teacherStaffId, $apexSchoolId, $jss1Id, $apexSchoolId, $sss2Id]);
    $jss1GoldId = (int)Database::fetch("SELECT id FROM class_arms WHERE school_id = ? AND name = 'Gold'", [$apexSchoolId])['id'];

    // Subjects
    Database::execute("INSERT INTO subjects (school_id, code, name, category, is_active) VALUES
        (?, 'MTH', 'Mathematics', 'core', 1),
        (?, 'ENG', 'English Language', 'core', 1),
        (?, 'BSC', 'Basic Science', 'core', 1),
        (?, 'CIV', 'Civic Education', 'core', 1),
        (?, 'AGR', 'Agricultural Science', 'vocational', 1),
        (?, 'PHY', 'Physics', 'core', 1)
    ", [$apexSchoolId, $apexSchoolId, $apexSchoolId, $apexSchoolId, $apexSchoolId, $apexSchoolId]);
    $subjects = Database::query("SELECT id, code, name FROM subjects WHERE school_id = ?", [$apexSchoolId]);

    // Grading Scales
    Database::execute("INSERT INTO grading_scales (school_id, grade_name, min_score, max_score, grade_point, remark) VALUES
        (?, 'A1', 75.00, 100.00, 4.00, 'Distinction'),
        (?, 'B2', 70.00, 74.99, 3.50, 'Very Good'),
        (?, 'B3', 65.00, 69.99, 3.00, 'Good'),
        (?, 'C4', 60.00, 64.99, 2.50, 'Credit'),
        (?, 'C5', 55.00, 59.99, 2.00, 'Credit'),
        (?, 'C6', 50.00, 54.99, 1.50, 'Credit'),
        (?, 'D7', 45.00, 49.99, 1.00, 'Pass'),
        (?, 'E8', 40.00, 44.99, 0.50, 'Pass'),
        (?, 'F9', 0.00, 39.99, 0.00, 'Fail')
    ", [
        $apexSchoolId, $apexSchoolId, $apexSchoolId, $apexSchoolId, $apexSchoolId,
        $apexSchoolId, $apexSchoolId, $apexSchoolId, $apexSchoolId
    ]);

    // Assessment config
    Database::execute("INSERT INTO assessment_configs (school_id, session_id, term_id, ca1_weight, ca2_weight, exam_weight, pass_mark, is_locked) VALUES
        (?, ?, ?, 20.00, 20.00, 60.00, 50.00, 0)
    ", [$apexSchoolId, $sessionId, $termId]);

    echo "6. Seeding Parents, Students and Exam Records...\n";
    // Parent User
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (?, 'Chief Emeka Okafor', 'parent@apexcollege.edu.ng', '+2348039900112', ?, 'parent', 1)
    ", [$apexSchoolId, $defaultHash]);
    $parentUserId = (int)Database::lastInsertId();

    Database::execute("INSERT INTO parents (school_id, user_id, title, occupation, address, emergency_phone, relationship_type) VALUES
        (?, ?, 'Chief', 'Senior Logistics Director', '15 Admiralty Way, Lekki Phase 1, Lagos', '+2348039900112', 'father')
    ", [$apexSchoolId, $parentUserId]);
    $parentRecordId = (int)Database::lastInsertId();

    // Student 1 (Portal User)
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (?, 'Chinedu Okafor', 'student@apexcollege.edu.ng', '+2348039900113', ?, 'student', 1)
    ", [$apexSchoolId, $defaultHash]);
    $studentUserId = (int)Database::lastInsertId();

    Database::execute("INSERT INTO students (school_id, user_id, parent_id, class_arm_id, admission_number, first_name, last_name, middle_name, date_of_birth, gender, blood_group, genotype, status, enrolled_at) VALUES
        (?, ?, ?, ?, 'APX/2025/001', 'Chinedu', 'Okafor', 'Chukwuemeka', '2012-05-14', 'male', 'O+', 'AA', 'enrolled', '2025-09-08')
    ", [$apexSchoolId, $studentUserId, $parentRecordId, $jss1GoldId]);
    $chineduStudentId = (int)Database::lastInsertId();

    // Student 2, 3, 4, 5 in JSS 1 Gold for realistic cohort & rankings
    $cohort = [
        ['Aisha', 'Bello', 'Khadijah', 'female', 'APX/2025/002'],
        ['Babatunde', 'Adeyemi', 'Oluwaseun', 'male', 'APX/2025/003'],
        ['Kemi', 'Adeleke', 'Grace', 'female', 'APX/2025/004'],
        ['David', 'Eze', 'Somto', 'male', 'APX/2025/005'],
    ];

    $otherStudentIds = [];
    foreach ($cohort as $stu) {
        Database::execute("INSERT INTO students (school_id, user_id, parent_id, class_arm_id, admission_number, first_name, last_name, middle_name, date_of_birth, gender, status, enrolled_at) VALUES
            (?, NULL, ?, ?, ?, ?, ?, ?, '2012-07-20', ?, 'enrolled', '2025-09-08')
        ", [$apexSchoolId, $parentRecordId, $jss1GoldId, $stu[4], $stu[0], $stu[1], $stu[2], $stu[3]]);
        $otherStudentIds[] = (int)Database::lastInsertId();
    }

    $allJss1Students = array_merge([$chineduStudentId], $otherStudentIds);

    // Seed realistic exam scores for each student across 5 subjects
    echo "7. Seeding Exam Scores & Computing Class Positions...\n";
    $baseScores = [
        $chineduStudentId => [
            'MTH' => [18, 19, 54], // 91 -> A1
            'ENG' => [17, 18, 51], // 86 -> A1
            'BSC' => [19, 18, 52], // 89 -> A1
            'CIV' => [16, 17, 49], // 82 -> A1
            'AGR' => [18, 17, 50], // 85 -> A1
        ],
        $otherStudentIds[0] => [ // Aisha Bello
            'MTH' => [17, 16, 48], // 81
            'ENG' => [19, 19, 56], // 94
            'BSC' => [17, 18, 50], // 85
            'CIV' => [18, 18, 53], // 89
            'AGR' => [17, 17, 49], // 83
        ],
        $otherStudentIds[1] => [ // Babatunde
            'MTH' => [14, 15, 42], // 71
            'ENG' => [15, 14, 45], // 74
            'BSC' => [13, 14, 40], // 67
            'CIV' => [15, 15, 46], // 76
            'AGR' => [14, 15, 43], // 72
        ],
        $otherStudentIds[2] => [ // Kemi
            'MTH' => [13, 12, 38], // 63
            'ENG' => [14, 15, 44], // 73
            'BSC' => [12, 13, 37], // 62
            'CIV' => [14, 14, 41], // 69
            'AGR' => [13, 13, 39], // 65
        ],
        $otherStudentIds[3] => [ // David
            'MTH' => [11, 10, 32], // 53
            'ENG' => [12, 13, 35], // 60
            'BSC' => [10, 11, 30], // 51
            'CIV' => [12, 12, 36], // 60
            'AGR' => [11, 12, 34], // 57
        ],
    ];

    foreach ($baseScores as $sId => $subScores) {
        foreach ($subScores as $subCode => $sc) {
            $subId = (int)Database::fetch("SELECT id FROM subjects WHERE school_id = ? AND code = ?", [$apexSchoolId, $subCode])['id'];
            $tot = (float)($sc[0] + $sc[1] + $sc[2]);
            $gradeData = GradingService::determineGrade($apexSchoolId, $tot);

            Database::execute("INSERT INTO exam_scores 
                (school_id, session_id, term_id, class_arm_id, subject_id, student_id, ca1_score, ca2_score, exam_score, total_score, grade, remark, teacher_id, status, approved_by, approved_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, NOW())
            ", [
                $apexSchoolId, $sessionId, $termId, $jss1GoldId, $subId, $sId,
                $sc[0], $sc[1], $sc[2], $tot, $gradeData['grade'], $gradeData['remark'],
                $teacherStaffId, $principalUserId
            ]);
        }
    }

    // Run position computation
    GradingService::computeClassPositions($apexSchoolId, $sessionId, $termId, $jss1GoldId);

    // Update teacher and principal remarks on Chinedu's summary
    Database::execute("UPDATE student_term_summaries 
        SET teacher_remark = 'Chinedu has maintained outstanding intellectual curiosity and exemplary discipline across all subjects.',
            principal_remark = 'A brilliant academic outing! Keep leading by positive example.',
            is_published = 1, published_at = NOW()
        WHERE school_id = ? AND student_id = ?
    ", [$apexSchoolId, $chineduStudentId]);

    echo "8. Seeding Attendance Records...\n";
    $dates = [
        '2025-10-06', '2025-10-07', '2025-10-08', '2025-10-09', '2025-10-10',
        '2025-10-13', '2025-10-14', '2025-10-15', '2025-10-16', '2025-10-17'
    ];
    foreach ($allJss1Students as $stuId) {
        foreach ($dates as $d) {
            $status = ($stuId === $otherStudentIds[3] && $d === '2025-10-09') ? 'absent' : 'present';
            Database::execute("INSERT INTO attendance 
                (school_id, session_id, term_id, student_id, class_arm_id, attendance_date, status, recorded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ", [$apexSchoolId, $sessionId, $termId, $stuId, $jss1GoldId, $d, $status, $teacherStaffId]);
        }
    }

    echo "9. Seeding Fees, Invoices & Verified Payment Receipts...\n";
    Database::execute("INSERT INTO fee_categories (school_id, name, description) VALUES
        (?, 'Tuition & Academic Instructional Fee', 'Core tuition fees covering coursework and faculty.'),
        (?, 'ICT & STEM Robotics Lab Levy', 'Computer science, robotics and STEM laboratory maintenance.'),
        (?, 'School Development & Facility Levy', 'Annual facility maintenance, power generation and infrastructure.')
    ", [$apexSchoolId, $apexSchoolId, $apexSchoolId]);

    $feeCats = Database::query("SELECT id, name FROM fee_categories WHERE school_id = ?", [$apexSchoolId]);
    $catTuition = (int)$feeCats[0]['id'];
    $catIct = (int)$feeCats[1]['id'];
    $catDev = (int)$feeCats[2]['id'];

    Database::execute("INSERT INTO fee_structures (school_id, session_id, term_id, class_level_id, fee_category_id, amount, is_mandatory) VALUES
        (?, ?, ?, ?, ?, 120000.00, 1),
        (?, ?, ?, ?, ?, 25000.00, 1),
        (?, ?, ?, ?, ?, 15000.00, 1)
    ", [
        $apexSchoolId, $sessionId, $termId, $jss1Id, $catTuition,
        $apexSchoolId, $sessionId, $termId, $jss1Id, $catIct,
        $apexSchoolId, $sessionId, $termId, $jss1Id, $catDev
    ]);

    // Total fee per student = 120,000 + 25,000 + 15,000 = 160,000 NGN
    // Create Invoices for students
    // Chinedu: Fully Paid (160,000)
    Database::execute("INSERT INTO fee_invoices 
        (school_id, invoice_number, session_id, term_id, student_id, class_arm_id, total_amount, discount_amount, paid_amount, balance_due, status, due_date)
        VALUES (?, 'INV-2025-001', ?, ?, ?, ?, 160000.00, 0.00, 160000.00, 0.00, 'paid', '2025-09-30')
    ", [$apexSchoolId, $sessionId, $termId, $chineduStudentId, $jss1GoldId]);
    $inv1Id = (int)Database::lastInsertId();

    Database::execute("INSERT INTO payment_receipts 
        (school_id, receipt_number, invoice_id, student_id, amount_paid, payment_method, payment_reference, transaction_id, payment_date, status, verified_by)
        VALUES (?, 'REC-2025-001', ?, ?, 160000.00, 'paystack', 'PSTK_TXN_9918231', 'TRX_8871625', '2025-09-22', 'verified', ?)
    ", [$apexSchoolId, $inv1Id, $chineduStudentId, $bursarUserId]);

    // Aisha: Partially Paid (100,000 paid, 60,000 balance)
    Database::execute("INSERT INTO fee_invoices 
        (school_id, invoice_number, session_id, term_id, student_id, class_arm_id, total_amount, discount_amount, paid_amount, balance_due, status, due_date)
        VALUES (?, 'INV-2025-002', ?, ?, ?, ?, 160000.00, 0.00, 100000.00, 60000.00, 'partially_paid', '2025-09-30')
    ", [$apexSchoolId, $sessionId, $termId, $otherStudentIds[0], $jss1GoldId]);
    $inv2Id = (int)Database::lastInsertId();

    Database::execute("INSERT INTO payment_receipts 
        (school_id, receipt_number, invoice_id, student_id, amount_paid, payment_method, payment_reference, transaction_id, payment_date, status, verified_by)
        VALUES (?, 'REC-2025-002', ?, ?, 100000.00, 'bank_transfer', 'GTB_TRANSFER_5541', 'TRX_8871626', '2025-09-25', 'verified', ?)
    ", [$apexSchoolId, $inv2Id, $otherStudentIds[0], $bursarUserId]);

    // Babatunde: Unpaid
    Database::execute("INSERT INTO fee_invoices 
        (school_id, invoice_number, session_id, term_id, student_id, class_arm_id, total_amount, discount_amount, paid_amount, balance_due, status, due_date)
        VALUES (?, 'INV-2025-003', ?, ?, ?, ?, 160000.00, 0.00, 0.00, 160000.00, 'unpaid', '2025-09-30')
    ", [$apexSchoolId, $sessionId, $termId, $otherStudentIds[1], $jss1GoldId]);

    echo "10. Seeding Platform Subscriptions & Sales CRM (Pipeline, Leads, Commissions)...\n";
    // Apex School subscription paid
    Database::execute("INSERT INTO subscriptions 
        (school_id, plan_id, invoice_number, amount, billing_cycle, start_date, end_date, payment_status, payment_reference, payment_gateway, verified_by)
        VALUES (?, 2, 'SUB-INV-2025-001', 600000.00, 'annual', '2025-10-01', '2026-10-01', 'paid', 'PSTK_SUB_776152', 'paystack', ?)
    ", [$apexSchoolId, $superAdminId]);
    $sub1Id = (int)Database::lastInsertId();

    // Sales Lead for Apex (Won)
    Database::execute("INSERT INTO sales_leads 
        (rep_id, school_name, contact_person, email, phone, address, state, estimated_students, lead_source, stage, created_school_id)
        VALUES (?, 'Apex Crown College', 'Dr. Olawale Sanusi', 'admin@apexcollege.edu.ng', '+2348033221101', 'Plot 14 Commercial Ave, Ikeja', 'Lagos', 650, 'Outbound Direct', 'won', ?)
    ", [$salesRepId, $apexSchoolId]);
    $leadWonId = (int)Database::lastInsertId();

    // Activities for Apex lead
    Database::execute("INSERT INTO sales_activities (lead_id, rep_id, activity_type, summary, details, scheduled_at, completed_at) VALUES
        (?, ?, 'call', 'Initial discovery call with Chairman', 'Discussed paper-based report cards bottlenecks and financial reconciliation leaks.', NOW() - INTERVAL 30 DAY, NOW() - INTERVAL 30 DAY),
        (?, ?, 'demo', 'On-site platform demonstration', 'Presented multi-tenant portal, result computations and Paystack school fee collection.', NOW() - INTERVAL 20 DAY, NOW() - INTERVAL 20 DAY),
        (?, ?, 'stage_change', 'Contract Executed & Onboarded', 'School enrolled on Premium Plan at ₦600,000/yr.', NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY)
    ", [$leadWonId, $salesRepId, $leadWonId, $salesRepId, $leadWonId, $salesRepId]);

    // Commission for Apex: 10% of ₦600,000 = ₦60,000
    Database::execute("INSERT INTO sales_commissions 
        (rep_id, school_id, subscription_id, transaction_amount, commission_rate, commission_amount, status, approved_by, approved_at)
        VALUES (?, ?, ?, 600000.00, 10.00, 60000.00, 'approved', ?, NOW() - INTERVAL 5 DAY)
    ", [$salesRepId, $apexSchoolId, $sub1Id, $salesManagerId]);

    // Additional active pipeline leads across Kanban stages
    $pipelineLeads = [
        ['Grace High Grammar School', 'Alhaji Rasheed Bello', 'gracehigh@example.com', '+2348021122331', 'Surulere, Lagos', 450, 'negotiation', 'Contract terms under review with school board.'],
        ['Royal Heritage Model College', 'Rev. Father Paul', 'royalheritage@example.com', '+2348032233442', 'Ikorodu, Lagos', 800, 'proposal_sent', 'Custom annual proposal sent for 800 student tier.'],
        ['Kingsway College Victoria Island', 'Mrs. Amanda Lawson', 'kingsway@example.com', '+2348053344553', 'Victoria Island, Lagos', 550, 'demo_scheduled', 'Scheduled live presentation for the academic committee.'],
        ['Anchor International School', 'Chief Anthony Obi', 'anchor@example.com', '+2348074455664', 'Abeokuta, Ogun State', 350, 'qualified', 'Budget verified for 2026 onboarding.'],
        ['Bethel Christian Academy', 'Pastor Samuel David', 'bethel@example.com', '+2348095566775', 'Magodo, Lagos', 280, 'contacted', 'Follow-up call booked for Thursday 10am.'],
        ['New Era Comprehensive High', 'Mr. Festus Ade', 'newera@example.com', '+2348016677886', 'Otta, Ogun State', 400, 'new_lead', 'Lead generated from Lagos State Private School Exhibition.']
    ];

    foreach ($pipelineLeads as $pl) {
        Database::execute("INSERT INTO sales_leads 
            (rep_id, school_name, contact_person, email, phone, address, state, estimated_students, stage, notes)
            VALUES (?, ?, ?, ?, ?, ?, 'Lagos', ?, ?, ?)
        ", [$salesRepId, $pl[0], $pl[1], $pl[2], $pl[3], $pl[4], $pl[5], $pl[6], $pl[7]]);
        $lId = (int)Database::lastInsertId();

        Database::execute("INSERT INTO sales_activities (lead_id, rep_id, activity_type, summary, completed_at) VALUES
            (?, ?, 'note', ?, NOW())
        ", [$lId, $salesRepId, $pl[7]]);
    }

    echo "11. Seeding Library, Clinic, Inventory & Public CMS Pages...\n";
    Database::execute("INSERT INTO library_books (school_id, isbn, title, author, category, total_copies, available_copies, shelf_location) VALUES
        (?, '978-0199142835', 'New General Mathematics for JSS 1', 'M.F. Macrae et al.', 'Mathematics', 40, 38, 'Shelf M-01'),
        (?, '978-9781754029', 'Essential Agricultural Science for Schools', 'O.A. Iwena', 'Agriculture', 30, 29, 'Shelf A-03'),
        (?, '978-0582522718', 'Junior English Project for Secondary Schools', 'N. Grant', 'English', 35, 35, 'Shelf E-02')
    ", [$apexSchoolId, $apexSchoolId, $apexSchoolId]);

    Database::execute("INSERT INTO inventory_items (school_id, item_code, name, category, quantity, unit, unit_price, minimum_threshold, status) VALUES
        (?, 'STN-PPR-A4', 'Standard A4 Printing Ream Paper (80gsm)', 'Stationery', 65, 'reams', 4500.00, 15, 'in_stock'),
        (?, 'LAB-MIC-01', 'Binocular Compound Microscopes', 'Science Lab', 12, 'units', 85000.00, 4, 'in_stock'),
        (?, 'SPT-FB-01', 'FIFA Approved Leather Footballs', 'Sports', 8, 'balls', 12000.00, 3, 'in_stock')
    ", [$apexSchoolId, $apexSchoolId, $apexSchoolId]);

    Database::execute("INSERT INTO clinic_records (school_id, student_id, complaint, diagnosis, treatment, administered_by, recorded_at) VALUES
        (?, ?, 'Headache and mild elevated temperature during second period.', 'Mild fatigue / tension headache. Temp 37.2C.', 'Administered oral Paracetamol syrup and 1-hour rest in clinic ward.', 'Nurse Patricia Adams, RN', NOW() - INTERVAL 3 DAY)
    ", [$apexSchoolId, $chineduStudentId]);

    Database::execute("INSERT INTO announcements (school_id, title, content, target_audience, is_published) VALUES
        (?, 'Resumption for Academic Session 2025/2026', 'Welcome back all students and parents. First term academic activities commence at 7:45am prompt.', 'all', 1),
        (?, 'Inter-House Sports Festival Announcement', 'Annual track and field championships scheduled for Green House, Blue House, Yellow House and Red House.', 'all', 1),
        (NULL, 'Platform Maintenance Notice: Security & Core Updates', 'EduSphere SaaS will undergo routine optimization this Sunday between 2:00 AM and 3:00 AM WAT.', 'all', 1)
    ", [$apexSchoolId, $apexSchoolId]);

    // Public CMS Website Pages for Apex Crown College
    Database::execute("INSERT INTO school_website_pages (school_id, page_slug, title, banner_title, banner_subtitle, content, is_published) VALUES
        (?, 'home', 'Welcome to Apex Crown College', 'Inspiring Excellence, Fostering Global Leaders', 'Providing holistic academic education, moral integrity and 21st-century technological acumen since 2008.', 'At Apex Crown College, we believe in unlocking the unique potential of every student through innovative curricula, world-class laboratory facilities, and dedicated faculty.', 1),
        (?, 'about', 'About Apex Crown College', 'Our Heritage of Academic Distinction', 'Founded with a vision to revolutionize secondary education in Nigeria.', 'Apex Crown College combines the Nigerian National Curriculum with Cambridge IGCSE standards, giving our students the competitive edge required for top universities worldwide.', 1),
        (?, 'admissions', 'Admissions & Enrollment', 'Join Our Vibrant Academic Community', 'Applications for the 2025/2026 Academic Session are now open.', 'We welcome applications from motivated learners into JSS 1 through SSS 2. Complete our streamlined online application to begin the journey.', 1),
        (?, 'academics', 'Academic Excellence & Curriculum', 'Rigorous Academic Rigor with Real-World Application', 'Nurturing well-rounded scholars through STEM, Humanities, and Vocational disciplines.', 'Our low teacher-to-student ratio ensures tailored mentorship and continuous performance tracking.', 1),
        (?, 'contact', 'Contact Our Campus', 'Get in Touch with Our Administration', 'Our admissions counselors are available Monday through Friday from 8:00 AM to 4:00 PM.', 'Visit us at Plot 14 Commercial Avenue, Ikeja GRA, Lagos or call +234 802 334 4551.', 1)
    ", [$apexSchoolId, $apexSchoolId, $apexSchoolId, $apexSchoolId, $apexSchoolId]);

    echo "12. Seeding School #2: Horizon International Academy (Independent Multi-Tenant Demo)...\n";
    // Horizon Staff & Users
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (?, 'Dr. Fatima Garba', 'admin@horizonacademy.sch.ng', '+2348029988771', ?, 'school_admin', 1),
        (?, 'Alhaji Ibrahim Danjuma', 'principal@horizonacademy.sch.ng', '+2348029988772', ?, 'principal', 1),
        (?, 'Miss Blessing Adeyemi', 'teacher@horizonacademy.sch.ng', '+2348029988773', ?, 'teacher', 1),
        (?, 'Mr. Usman Bello', 'bursar@horizonacademy.sch.ng', '+2348029988774', ?, 'bursar', 1)
    ", [
        $horizonSchoolId, $defaultHash,
        $horizonSchoolId, $defaultHash,
        $horizonSchoolId, $defaultHash,
        $horizonSchoolId, $defaultHash
    ]);

    $hrzPrincipalId = (int)Database::fetch("SELECT id FROM users WHERE email = 'principal@horizonacademy.sch.ng'")['id'];
    $hrzTeacherId = (int)Database::fetch("SELECT id FROM users WHERE email = 'teacher@horizonacademy.sch.ng'")['id'];
    $hrzBursarId = (int)Database::fetch("SELECT id FROM users WHERE email = 'bursar@horizonacademy.sch.ng'")['id'];

    Database::execute("INSERT INTO staff (school_id, user_id, employee_number, designation, department, qualification, date_of_joining, salary_base, status, phone) VALUES
        (?, ?, 'HRZ-EMP-001', 'Head of School / Principal', 'Administration', 'B.Ed, M.Sc Educational Leadership', '2021-08-01', 310000.00, 'active', '+2348029988772'),
        (?, ?, 'HRZ-EMP-002', 'Senior Primary 3 Form Tutor', 'Primary Section', 'B.Ed Early Childhood Education', '2022-09-01', 200000.00, 'active', '+2348029988773'),
        (?, ?, 'HRZ-EMP-003', 'Lead Bursar & Accounts Officer', 'Finance', 'HND Accounting', '2020-01-10', 250000.00, 'active', '+2348029988774')
    ", [
        $horizonSchoolId, $hrzPrincipalId,
        $horizonSchoolId, $hrzTeacherId,
        $horizonSchoolId, $hrzBursarId
    ]);
    $hrzTeacherStaffId = (int)Database::fetch("SELECT id FROM staff WHERE user_id = ?", [$hrzTeacherId])['id'];

    // Horizon Academic Structure
    Database::execute("INSERT INTO academic_sessions (school_id, name, start_date, end_date, is_current) VALUES
        (?, '2025/2026', '2025-09-15', '2026-07-18', 1)
    ", [$horizonSchoolId]);
    $hrzSessionId = (int)Database::lastInsertId();

    Database::execute("INSERT INTO academic_terms (school_id, session_id, name, start_date, end_date, is_current) VALUES
        (?, ?, 'First Term', '2025-09-15', '2025-12-18', 1),
        (?, ?, 'Second Term', '2026-01-15', '2026-04-15', 0),
        (?, ?, 'Third Term', '2026-05-02', '2026-07-18', 0)
    ", [$horizonSchoolId, $hrzSessionId, $horizonSchoolId, $hrzSessionId, $horizonSchoolId, $hrzSessionId]);
    $hrzTermId = (int)Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? AND name = 'First Term'", [$horizonSchoolId])['id'];

    Database::execute("INSERT INTO class_levels (school_id, name, section, rank_order) VALUES
        (?, 'Primary 1', 'primary', 1),
        (?, 'Primary 2', 'primary', 2),
        (?, 'Primary 3', 'primary', 3),
        (?, 'Primary 4', 'primary', 4),
        (?, 'Primary 5', 'primary', 5)
    ", [$horizonSchoolId, $horizonSchoolId, $horizonSchoolId, $horizonSchoolId, $horizonSchoolId]);
    $hrzPri3Id = (int)Database::fetch("SELECT id FROM class_levels WHERE school_id = ? AND name = 'Primary 3'", [$horizonSchoolId])['id'];

    Database::execute("INSERT INTO class_arms (school_id, class_level_id, name, capacity, class_teacher_id) VALUES
        (?, ?, 'Emerald', 28, ?),
        (?, ?, 'Ruby', 28, NULL)
    ", [$horizonSchoolId, $hrzPri3Id, $hrzTeacherStaffId, $horizonSchoolId, $hrzPri3Id]);
    $hrzPri3EmeraldId = (int)Database::fetch("SELECT id FROM class_arms WHERE school_id = ? AND name = 'Emerald'", [$horizonSchoolId])['id'];

    Database::execute("INSERT INTO subjects (school_id, code, name, category, is_active) VALUES
        (?, 'MTH', 'Mathematics', 'core', 1),
        (?, 'ENG', 'English Language', 'core', 1),
        (?, 'BSC', 'Basic Science', 'core', 1),
        (?, 'SST', 'Social Studies', 'core', 1),
        (?, 'FRN', 'French Language', 'elective', 1)
    ", [$horizonSchoolId, $horizonSchoolId, $horizonSchoolId, $horizonSchoolId, $horizonSchoolId]);
    $hrzSubjects = Database::query("SELECT id, code FROM subjects WHERE school_id = ?", [$horizonSchoolId]);

    Database::execute("INSERT INTO assessment_configs (school_id, session_id, term_id, ca1_weight, ca2_weight, exam_weight, pass_mark, is_locked) VALUES
        (?, ?, ?, 20.00, 20.00, 60.00, 50.00, 0)
    ", [$horizonSchoolId, $hrzSessionId, $hrzTermId]);

    Database::execute("INSERT INTO grading_scales (school_id, grade_name, min_score, max_score, grade_point, remark) VALUES
        (?, 'A1', 75.00, 100.00, 4.00, 'Distinction'),
        (?, 'B2', 70.00, 74.99, 3.50, 'Very Good'),
        (?, 'B3', 65.00, 69.99, 3.00, 'Good'),
        (?, 'C4', 60.00, 64.99, 2.50, 'Credit'),
        (?, 'C5', 55.00, 59.99, 2.00, 'Credit'),
        (?, 'C6', 50.00, 54.99, 1.50, 'Credit'),
        (?, 'D7', 45.00, 49.99, 1.00, 'Pass'),
        (?, 'E8', 40.00, 44.99, 0.50, 'Pass'),
        (?, 'F9', 0.00, 39.99, 0.00, 'Fail')
    ", [
        $horizonSchoolId, $horizonSchoolId, $horizonSchoolId, $horizonSchoolId, $horizonSchoolId,
        $horizonSchoolId, $horizonSchoolId, $horizonSchoolId, $horizonSchoolId
    ]);

    // Horizon Parent & Students
    Database::execute("INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active) VALUES
        (?, 'Alhaji Musa Garba', 'parent@horizonacademy.sch.ng', '+2348029988780', ?, 'parent', 1),
        (?, 'Zainab Garba', 'student@horizonacademy.sch.ng', '+2348029988781', ?, 'student', 1)
    ", [$horizonSchoolId, $defaultHash, $horizonSchoolId, $defaultHash]);
    $hrzParentUserId = (int)Database::fetch("SELECT id FROM users WHERE email = 'parent@horizonacademy.sch.ng'")['id'];
    $hrzStudentUserId = (int)Database::fetch("SELECT id FROM users WHERE email = 'student@horizonacademy.sch.ng'")['id'];

    Database::execute("INSERT INTO parents (school_id, user_id, title, occupation, address, emergency_phone, relationship_type) VALUES
        (?, ?, 'Alhaji', 'Director of Infrastructure', '18 Maitama Avenue, Abuja', '+2348029988780', 'father')
    ", [$horizonSchoolId, $hrzParentUserId]);
    $hrzParentRecordId = (int)Database::lastInsertId();

    Database::execute("INSERT INTO students (school_id, user_id, parent_id, class_arm_id, admission_number, first_name, last_name, middle_name, date_of_birth, gender, blood_group, genotype, status, enrolled_at) VALUES
        (?, ?, ?, ?, 'HRZ/2025/001', 'Zainab', 'Garba', 'Amina', '2016-04-12', 'female', 'AA', 'O+', 'enrolled', '2025-09-15'),
        (?, NULL, ?, ?, 'HRZ/2025/002', 'Tariq', 'Danjuma', 'Aliyu', '2016-06-25', 'male', 'AA', 'A+', 'enrolled', '2025-09-15'),
        (?, NULL, ?, ?, 'HRZ/2025/003', 'Maryam', 'Bello', 'Khadijah', '2016-08-10', 'female', 'AS', 'B+', 'enrolled', '2025-09-15')
    ", [
        $horizonSchoolId, $hrzStudentUserId, $hrzParentRecordId, $hrzPri3EmeraldId,
        $horizonSchoolId, $hrzParentRecordId, $hrzPri3EmeraldId,
        $horizonSchoolId, $hrzParentRecordId, $hrzPri3EmeraldId
    ]);

    $hrzStudent1Id = (int)Database::fetch("SELECT id FROM students WHERE admission_number = 'HRZ/2025/001'")['id'];
    $hrzStudent2Id = (int)Database::fetch("SELECT id FROM students WHERE admission_number = 'HRZ/2025/002'")['id'];
    $hrzStudent3Id = (int)Database::fetch("SELECT id FROM students WHERE admission_number = 'HRZ/2025/003'")['id'];

    // Horizon Scores & Positions
    $hrzScoresConfig = [
        $hrzStudent1Id => [18, 19, 52], // 89
        $hrzStudent2Id => [16, 17, 48], // 81
        $hrzStudent3Id => [14, 15, 42], // 71
    ];
    foreach ($hrzSubjects as $sub) {
        foreach ($hrzScoresConfig as $stId => $sc) {
            $tot = (float)($sc[0] + $sc[1] + $sc[2]);
            $grd = GradingService::determineGrade($horizonSchoolId, $tot);
            Database::execute("INSERT INTO exam_scores 
                (school_id, session_id, term_id, class_arm_id, subject_id, student_id, ca1_score, ca2_score, exam_score, total_score, grade, remark, teacher_id, status, approved_by, approved_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, NOW())",
                [$horizonSchoolId, $hrzSessionId, $hrzTermId, $hrzPri3EmeraldId, $sub['id'], $stId, $sc[0], $sc[1], $sc[2], $tot, $grd['grade'], $grd['remark'], $hrzTeacherStaffId, $hrzPrincipalId]
            );
        }
    }
    GradingService::computeClassPositions($horizonSchoolId, $hrzSessionId, $hrzTermId, $hrzPri3EmeraldId);

    // Horizon Attendance
    $hrzDates = ['2025-10-06', '2025-10-07', '2025-10-08', '2025-10-09', '2025-10-10'];
    foreach ([$hrzStudent1Id, $hrzStudent2Id, $hrzStudent3Id] as $stId) {
        foreach ($hrzDates as $d) {
            Database::execute("INSERT INTO attendance (school_id, session_id, term_id, student_id, class_arm_id, attendance_date, status, recorded_by)
                VALUES (?, ?, ?, ?, ?, ?, 'present', ?)",
                [$horizonSchoolId, $hrzSessionId, $hrzTermId, $stId, $hrzPri3EmeraldId, $d, $hrzTeacherStaffId]
            );
        }
    }

    // Horizon Fee Structures & Invoices
    Database::execute("INSERT INTO fee_categories (school_id, name, description) VALUES
        (?, 'Primary Tuition & Academic Instruction', 'Tuition for basic educational foundation.'),
        (?, 'Learning Resources & Workbooks', 'Educational text and activity workbooks.'),
        (?, 'Campus Development Levy', 'Infrastructure and campus maintenance.')
    ", [$horizonSchoolId, $horizonSchoolId, $horizonSchoolId]);
    $hrzFeeCats = Database::query("SELECT id FROM fee_categories WHERE school_id = ?", [$horizonSchoolId]);

    Database::execute("INSERT INTO fee_structures (school_id, session_id, term_id, class_level_id, fee_category_id, amount, is_mandatory) VALUES
        (?, ?, ?, ?, ?, 95000.00, 1),
        (?, ?, ?, ?, ?, 20000.00, 1),
        (?, ?, ?, ?, ?, 15000.00, 1)
    ", [
        $horizonSchoolId, $hrzSessionId, $hrzTermId, $hrzPri3Id, $hrzFeeCats[0]['id'],
        $horizonSchoolId, $hrzSessionId, $hrzTermId, $hrzPri3Id, $hrzFeeCats[1]['id'],
        $horizonSchoolId, $hrzSessionId, $hrzTermId, $hrzPri3Id, $hrzFeeCats[2]['id']
    ]);

    // Total fee = 95,000 + 20,000 + 15,000 = 130,000 NGN
    Database::execute("INSERT INTO fee_invoices 
        (school_id, invoice_number, session_id, term_id, student_id, class_arm_id, total_amount, discount_amount, paid_amount, balance_due, status, due_date)
        VALUES 
        (?, 'INV-HRZ-2025-001', ?, ?, ?, ?, 130000.00, 0.00, 130000.00, 0.00, 'paid', '2025-09-30'),
        (?, 'INV-HRZ-2025-002', ?, ?, ?, ?, 130000.00, 0.00, 65000.00, 65000.00, 'partially_paid', '2025-09-30')
    ", [
        $horizonSchoolId, $hrzSessionId, $hrzTermId, $hrzStudent1Id, $hrzPri3EmeraldId,
        $horizonSchoolId, $hrzSessionId, $hrzTermId, $hrzStudent2Id, $hrzPri3EmeraldId
    ]);
    $hrzInv1Id = (int)Database::fetch("SELECT id FROM fee_invoices WHERE invoice_number = 'INV-HRZ-2025-001'")['id'];

    Database::execute("INSERT INTO payment_receipts 
        (school_id, receipt_number, invoice_id, student_id, amount_paid, payment_method, payment_reference, transaction_id, payment_date, status, verified_by)
        VALUES (?, 'REC-HRZ-2025-001', ?, ?, 130000.00, 'bank_transfer', 'ZENITH_TRF_1192', 'TRX_HRZ_001', '2025-09-20', 'verified', ?)
    ", [$horizonSchoolId, $hrzInv1Id, $hrzStudent1Id, $hrzBursarId]);

    // Horizon Library, Inventory & CMS
    Database::execute("INSERT INTO library_books (school_id, isbn, title, author, category, total_copies, available_copies, shelf_location) VALUES
        (?, '978-0199142839', 'Primary Mathematics for Nigerian Schools Bk 3', 'Universal Basic Education Board', 'Mathematics', 30, 29, 'Shelf P-01'),
        (?, '978-0582522712', 'Active Primary English Bk 3', 'Longman Nigeria', 'English', 30, 30, 'Shelf P-02')
    ", [$horizonSchoolId, $horizonSchoolId]);

    Database::execute("INSERT INTO inventory_items (school_id, item_code, name, category, quantity, unit, unit_price, minimum_threshold, status) VALUES
        (?, 'HRZ-ART-01', 'Children Art Paint Kits (Non-Toxic)', 'Art & Craft', 40, 'sets', 3500.00, 10, 'in_stock'),
        (?, 'HRZ-SPT-01', 'Mini Football Training Cones', 'Sports', 25, 'units', 1200.00, 5, 'in_stock')
    ", [$horizonSchoolId, $horizonSchoolId]);

    Database::execute("INSERT INTO school_website_pages (school_id, page_slug, title, banner_title, banner_subtitle, content, is_published) VALUES
        (?, 'home', 'Welcome to Horizon International Academy', 'Nurturing Global Thinkers & Future Leaders', 'Leading early years and primary school education in Maitama, Abuja.', 'At Horizon International Academy, we provide a nurturing, inquiry-based learning environment where young minds blossom.', 1),
        (?, 'about', 'About Horizon International Academy', 'Excellence from the Early Years', 'Dedicated to academic curiosity and moral development.', 'Founded with a deep commitment to modern, child-centered pedagogy in the heart of the Federal Capital Territory.', 1),
        (?, 'admissions', 'Admissions at Horizon', 'Enroll Your Child in Primary Excellence', 'Now accepting applications for Primary 1 through Primary 5.', 'Join our caring, stimulating academic environment.', 1),
        (?, 'academics', 'Academic Curriculum & STEM', 'Holistic Primary Education', 'Blending international standards with foundational Nigerian curricula.', 'Focusing on literacy, numeracy, critical thinking and creativity.', 1),
        (?, 'contact', 'Contact Horizon Academy', 'Visit Our Maitama Campus', 'Open Monday to Friday 7:30 AM to 3:30 PM.', '12 Diplomatic Drive, Maitama, Abuja. Phone: +234 802 998 8772.', 1)
    ", [$horizonSchoolId, $horizonSchoolId, $horizonSchoolId, $horizonSchoolId, $horizonSchoolId]);

    // Comprehensive Audit Trail
    Database::execute("INSERT INTO audit_logs (school_id, user_id, action, entity_type, entity_id, new_values, ip_address, user_agent, created_at) VALUES
        (NULL, ?, 'system_database_seeded', 'database', 1, '{\"status\":\"seeded_successfully\"}', '127.0.0.1', 'CLI Seeder Engine', NOW() - INTERVAL 10 DAY),
        (?, ?, 'academic_scores_published', 'exam_scores', ?, '{\"term\":\"First Term\",\"session\":\"2025/2026\",\"status\":\"published\"}', '102.89.44.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW() - INTERVAL 5 DAY),
        (?, ?, 'fee_payment_verified', 'payment_receipt', 1, '{\"receipt\":\"REC-2025-001\",\"amount\":160000}', '197.210.84.18', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NOW() - INTERVAL 4 DAY),
        (?, ?, 'student_admitted_enrolled', 'student', ?, '{\"admission_number\":\"APX/2025/001\"}', '102.89.44.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW() - INTERVAL 3 DAY),
        (?, ?, 'domain_verified_active', 'tenant_domain', 1, '{\"domain\":\"apexschools.edu.ng\",\"status\":\"verified\"}', '127.0.0.1', 'Domain Health Checker', NOW() - INTERVAL 2 DAY),
        (NULL, ?, 'platform_security_backup', 'backup', 1, '{\"archive\":\"edusphere_daily_enc.tar.gz\",\"status\":\"success\"}', '127.0.0.1', 'Automated Cron Engine', NOW() - INTERVAL 1 DAY)
    ", [
        $superAdminId,
        $apexSchoolId, $principalUserId, $jss1GoldId,
        $apexSchoolId, $bursarUserId,
        $apexSchoolId, $registrarUserId, $chineduStudentId,
        $apexSchoolId, $superAdminId,
        $superAdminId
    ]);

    // 14. Seeding Per-Student Term Portal Billing & Revenue-Sharing Engine
    echo "14. Seeding Per-Student Term Portal Billing & Revenue-Sharing Engine...\n";
    // Pricing Policies
    Database::execute("INSERT INTO portal_pricing_policies 
        (policy_code, school_id, fee_per_student, currency, fee_payer, effective_date, status, notes, created_by, approved_by) VALUES
        ('DEFAULT-TERM-2025', NULL, 3000.00, 'NGN', 'parent', '2025-09-01', 'active', 'Standard Platform Default Portal Fee: ₦3,000/student/term', ?, ?),
        ('HORIZON-SPECIAL-2025', ?, 2500.00, 'NGN', 'parent', '2025-09-01', 'active', 'Discounted Partner Rate for Horizon Academy: ₦2,500/student/term', ?, ?)
    ", [$superAdminId, $superAdminId, $horizonSchoolId, $superAdminId, $superAdminId]);
    $defaultPricingId = (int)Database::fetch("SELECT id FROM portal_pricing_policies WHERE policy_code = 'DEFAULT-TERM-2025'")['id'];
    $horizonPricingId = (int)Database::fetch("SELECT id FROM portal_pricing_policies WHERE policy_code = 'HORIZON-SPECIAL-2025'")['id'];

    // Revenue Sharing Policies
    Database::execute("INSERT INTO revenue_sharing_policies
        (policy_code, school_id, allocation_basis, platform_percentage, school_incentive_percentage, sales_rep_initial_rate, sales_rep_recurring_rate, gateway_fee_bearer, is_active, created_by, approved_by) VALUES
        ('DEFAULT-REVSHARE-2025', NULL, 'net_after_gateway', 45.00, 40.00, 15.00, 7.50, 'platform', 1, ?, ?)
    ", [$superAdminId, $superAdminId]);
    $defaultRevPolicyId = (int)Database::fetch("SELECT id FROM revenue_sharing_policies WHERE policy_code = 'DEFAULT-REVSHARE-2025'")['id'];

    // School Agreements
    // Apex Crown College: Accepted
    Database::execute("INSERT INTO school_portal_fee_agreements
        (school_id, session_id, term_id, pricing_policy_id, revenue_policy_id, proposed_fee_per_student, estimated_eligible_students, status, accepted_by, accepted_at, school_notes) VALUES
        (?, ?, ?, ?, ?, 3000.00, 5, 'accepted', ?, NOW() - INTERVAL 10 DAY, 'Agreement reviewed and accepted by School Management for 2025/2026 First Term.')
    ", [$apexSchoolId, $sessionId, $termId, $defaultPricingId, $defaultRevPolicyId, $principalUserId]);
    $apexAgreementId = (int)Database::lastInsertId();

    // Horizon Academy: Proposed
    Database::execute("INSERT INTO school_portal_fee_agreements
        (school_id, session_id, term_id, pricing_policy_id, revenue_policy_id, proposed_fee_per_student, estimated_eligible_students, status, school_notes) VALUES
        (?, ?, ?, ?, ?, 2500.00, 3, 'proposed', 'Pending governing board review.')
    ", [$horizonSchoolId, $hrzSessionId, $hrzTermId, $horizonPricingId, $defaultRevPolicyId]);
    $hrzAgreementId = (int)Database::lastInsertId();

    // Portal Notices
    Database::execute("INSERT INTO portal_billing_notices
        (school_id, agreement_id, term_id, title, notice_body, notice_type, sent_via, recipient_count, is_published, created_by) VALUES
        (?, ?, ?, 'Official Notice: 2025/2026 First Term Student Portal Access Fee', 
         'Dear Parents and Guardians, please be informed that the approved digital portal access fee of ₦3,000 per pupil for the 2025/2026 First Term is now active. This provides continuous access to live continuous assessments, terminal digital report cards, lesson notes, and fee reconciliations.',
         'term_announcement', 'portal_announcement', 5, 1, ?)
    ", [$apexSchoolId, $apexAgreementId, $termId, $principalUserId]);

    // Sample invoices for Apex students
    $apexStudents = [
        ['id' => $chineduStudentId, 'status' => 'paid', 'gross' => 3000.00, 'disc' => 0.00, 'paid' => 3000.00, 'bal' => 0.00, 'reason' => null],
        ['id' => $otherStudentIds[0], 'status' => 'paid', 'gross' => 3000.00, 'disc' => 0.00, 'paid' => 3000.00, 'bal' => 0.00, 'reason' => null],
        ['id' => $otherStudentIds[1], 'status' => 'partially_paid', 'gross' => 3000.00, 'disc' => 0.00, 'paid' => 1500.00, 'bal' => 1500.00, 'reason' => null],
        ['id' => $otherStudentIds[2], 'status' => 'unpaid', 'gross' => 3000.00, 'disc' => 0.00, 'paid' => 0.00, 'bal' => 3000.00, 'reason' => null],
        ['id' => $otherStudentIds[3], 'status' => 'waived', 'gross' => 3000.00, 'disc' => 3000.00, 'paid' => 0.00, 'bal' => 0.00, 'reason' => 'Approved Academic Merit Scholarship'],
    ];

    $invCounter = 1;
    foreach ($apexStudents as $as) {
        $invNum = sprintf('PINV-2025-%04d', $invCounter++);
        $idempHash = hash('sha256', "{$apexSchoolId}:{$sessionId}:{$termId}:{$as['id']}");
        $due = $as['gross'] - $as['disc'];
        Database::execute("INSERT INTO portal_invoices
            (school_id, student_id, parent_id, agreement_id, session_id, term_id, invoice_number, gross_amount, discount_amount, waiver_reason, amount_due, paid_amount, balance_due, status, due_date, idempotency_hash) VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '2025-10-31', ?)
        ", [
            $apexSchoolId, $as['id'], $parentRecordId, $apexAgreementId, $sessionId, $termId,
            $invNum, $as['gross'], $as['disc'], $as['reason'], $due, $as['paid'], $as['bal'], $as['status'], $idempHash
        ]);
        $invId = (int)Database::lastInsertId();

        // If paid or partially paid, create payment, allocation, incentive & commission
        if ($as['paid'] > 0) {
            $payRef = 'PAY-PTL-' . date('Ymd') . '-' . rand(10000, 99999);
            $gwFee = round($as['paid'] * 0.015, 2); // 1.5% Paystack fee
            $net = $as['paid'] - $gwFee;
            Database::execute("INSERT INTO portal_payments
                (school_id, invoice_id, student_id, parent_id, payment_reference, gateway, amount, gateway_fee, net_amount, status, verified_at, verified_by) VALUES
                (?, ?, ?, ?, ?, 'paystack', ?, ?, ?, 'verified', NOW(), ?)
            ", [$apexSchoolId, $invId, $as['id'], $parentRecordId, $payRef, $as['paid'], $gwFee, $net, $superAdminId]);
            $payId = (int)Database::lastInsertId();

            // Allocation: Net basis
            $schIncentive = round($net * 0.40, 2);
            $repComm = round($net * 0.15, 2);
            $platShare = round($net - ($schIncentive + $repComm), 2);

            Database::execute("INSERT INTO financial_allocations
                (payment_id, school_id, invoice_id, revenue_policy_id, gross_amount, gateway_fee, distributable_amount, platform_amount, school_incentive_amount, sales_rep_id, sales_rep_commission_amount, commission_tier, status) VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'initial', 'allocated')
            ", [$payId, $apexSchoolId, $invId, $defaultRevPolicyId, $as['paid'], $gwFee, $net, $platShare, $schIncentive, $salesRepId, $repComm]);
            $allocId = (int)Database::lastInsertId();

            // School Incentive Ledger entry
            $lastBal = (float)(Database::fetch("SELECT running_balance FROM school_incentive_ledger WHERE school_id = ? ORDER BY id DESC LIMIT 1", [$apexSchoolId])['running_balance'] ?? 0.0);
            $newBal = $lastBal + $schIncentive;
            Database::execute("INSERT INTO school_incentive_ledger
                (school_id, allocation_id, term_id, entry_type, amount, running_balance, reference, notes) VALUES
                (?, ?, ?, 'accrual', ?, ?, ?, 'Portal fee collection incentive accrual')
            ", [$apexSchoolId, $allocId, $termId, $schIncentive, $newBal, $payRef]);

            // Sales Commission Ledger entry
            Database::execute("INSERT INTO portal_commission_ledger
                (rep_id, school_id, allocation_id, term_id, commission_tier, rate_applied, commission_amount, status, created_at) VALUES
                (?, ?, ?, ?, 'initial', 15.00, ?, 'pending', NOW())
            ", [$salesRepId, $apexSchoolId, $allocId, $termId, $repComm]);
        }
    }

    // ============================================================================
    // 16. Seed Demo School Notices
    // ============================================================================
    Database::execute("INSERT INTO school_notices
        (school_id, title, content, category, priority, target_audience, is_pinned, expires_at, created_by, published_at, created_at) VALUES
        (?, 'Resumption Guidelines & Assembly Schedule for Term 1', 'Dear Students and Parents,\n\nWelcome to the 2025/2026 Academic Session! All pupils are required to be in full school uniform for the morning devotion at 7:45 AM prompt. Class registers will close at 8:15 AM.\n\nLooking forward to an inspiring academic session.', 'academic', 'important', 'all', 1, '2026-11-30', ?, NOW(), NOW()),
        (?, 'Annual Inter-House Athletics Championship 2026', 'The Apex Crown College Inter-House Sports competition will take place on Friday, November 14th at the campus sports arena. Parents and guardians are cordially invited to cheer their wards.', 'sports', 'normal', 'all', 0, '2026-11-15', ?, NOW(), NOW()),
        (?, 'Mid-Term Break & Visiting Day Notice', 'Please be advised that mid-term break commences on Thursday, October 23rd. Boarding students may be visited between 1:00 PM and 5:00 PM on Sunday.', 'holiday', 'normal', 'parents', 0, '2026-10-31', ?, NOW(), NOW())
    ", [$apexSchoolId, $principalUserId, $apexSchoolId, $principalUserId, $apexSchoolId, $principalUserId]);

    // ============================================================================
    // 17. Seed Demo Parent-School Direct Messages
    // ============================================================================
    $parentUser = Database::fetch("SELECT user_id FROM parents WHERE id = ?", [$parentRecordId]);
    $parentUserId = (int)($parentUser['user_id'] ?? 10);
    $firstStuId = (int)($apexStudents[0]['id'] ?? 1);

    // Initial message from Form Teacher to Parent
    Database::execute("INSERT INTO parent_school_messages
        (school_id, parent_id, student_id, sender_type, sender_id, recipient_id, subject, message, is_read, read_at, created_at, updated_at) VALUES
        (?, ?, ?, 'school', ?, ?, 'Commendation in Mathematics & Class Engagement', 'Dear Mr. & Mrs. Adeyemi,\n\nI am delighted to inform you that Adeyemi performed exceptionally well during our recent continuous assessment in Advanced Algebra, securing 18.5/20. His classroom participation has been exemplary.\n\nPlease continue encouraging his study discipline at home.', 1, NOW(), DATE_SUB(NOW(), INTERVAL 2 DAY), NOW())
    ", [$apexSchoolId, $parentRecordId, $firstStuId, $teacherUserId, $parentUserId]);
    $threadId1 = (int)Database::lastInsertId();

    // Reply from Parent back to School
    Database::execute("INSERT INTO parent_school_messages
        (school_id, parent_id, student_id, sender_type, sender_id, recipient_id, subject, message, is_read, read_at, parent_message_id, created_at, updated_at) VALUES
        (?, ?, ?, 'parent', ?, ?, 'Re: Commendation in Mathematics & Class Engagement', 'Thank you very much Mr. Teacher for the uplifting feedback! We will continue to supervise his evening revision schedules. Warm regards.', 1, NOW(), ?, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW())
    ", [$apexSchoolId, $parentRecordId, $firstStuId, $parentUserId, $teacherUserId, $threadId1]);

    // Second message from Parent initiating an inquiry
    Database::execute("INSERT INTO parent_school_messages
        (school_id, parent_id, student_id, sender_type, sender_id, recipient_id, subject, message, is_read, created_at, updated_at) VALUES
        (?, ?, ?, 'parent', ?, ?, 'Late Afternoon Music Club Pickup Notice', 'Good day Administration,\n\nPlease be informed that Adeyemi will be attending the Wednesday afternoon orchestra rehearsal. His uncle, Mr. Tunde Adeyemi, will handle his pickup at 4:30 PM. Thank you.', 0, DATE_SUB(NOW(), INTERVAL 4 HOUR), NOW())
    ", [$apexSchoolId, $parentRecordId, $firstStuId, $parentUserId, $principalUserId]);

    echo "✓ Database seeding completed successfully!\n";
    echo "=================================================================\n";
    echo "DEMO ACCOUNTS READY (Password for all accounts: password123):\n";
    echo "1. Super Admin:       superadmin@edusphere.example\n";
    echo "2. Sales Manager:     salesmanager@edusphere.example\n";
    echo "3. Sales Rep:         salesrep@edusphere.example\n";
    echo "4. School Admin:      admin@apexcollege.edu.ng (Apex Crown College)\n";
    echo "5. Principal:         principal@apexcollege.edu.ng\n";
    echo "6. Registrar:         registrar@apexcollege.edu.ng\n";
    echo "7. Teacher:           teacher@apexcollege.edu.ng\n";
    echo "8. Bursar:            bursar@apexcollege.edu.ng\n";
    echo "9. Student Portal:    student@apexcollege.edu.ng (Admission No: APX/2025/001)\n";
    echo "10. Parent Portal:    parent@apexcollege.edu.ng\n";
    echo "=================================================================\n";

} catch (PDOException $e) {
    echo "✗ Seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}
