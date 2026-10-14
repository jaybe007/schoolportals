<?php

declare(strict_types=1);

namespace EduSphere\Controllers;

use EduSphere\Core\Database;
use EduSphere\Core\Request;
use EduSphere\Core\Response;
use EduSphere\Core\View;
use EduSphere\Core\TenantContext;
use EduSphere\Core\AuditLogger;
use EduSphere\Core\Auth;
use EduSphere\Services\GradingService;
use EduSphere\Services\ReportCardPdfService;
use EduSphere\Services\FinancialPdfService;
use EduSphere\Services\PaystackService;

class TenantController
{
    private function getSchoolId(): int
    {
        $schoolId = TenantContext::getSchoolId();
        if (!$schoolId) {
            Response::redirect('/login', 'School tenant context could not be resolved.', 'danger');
        }
        return $schoolId;
    }

    public function dashboard(Request $request): void
    {
        $schoolId = $this->getSchoolId();

        $studentCount = (int)(Database::fetch("SELECT COUNT(*) AS c FROM students WHERE school_id = ? AND status = 'enrolled'", [$schoolId])['c'] ?? 0);
        $staffCount = (int)(Database::fetch("SELECT COUNT(*) AS c FROM staff WHERE school_id = ? AND status = 'active'", [$schoolId])['c'] ?? 0);
        
        $feeCollected = (float)(Database::fetch("SELECT SUM(amount_paid) AS total FROM payment_receipts WHERE school_id = ? AND status = 'verified'", [$schoolId])['total'] ?? 0.0);
        $outstandingFee = (float)(Database::fetch("SELECT SUM(balance_due) AS total FROM fee_invoices WHERE school_id = ? AND status IN ('unpaid', 'partially_paid')", [$schoolId])['total'] ?? 0.0);

        $pendingAdmissions = (int)(Database::fetch("SELECT COUNT(*) AS c FROM admissions WHERE school_id = ? AND status = 'pending'", [$schoolId])['c'] ?? 0);

        // Recent students
        $recentStudents = Database::query(
            "SELECT s.*, ca.name AS arm_name, cl.name AS class_name
             FROM students s
             LEFT JOIN class_arms ca ON ca.id = s.class_arm_id
             LEFT JOIN class_levels cl ON cl.id = ca.class_level_id
             WHERE s.school_id = ?
             ORDER BY s.id DESC LIMIT 5",
            [$schoolId]
        );

        // Recent fee payments
        $recentPayments = Database::query(
            "SELECT pr.*, s.first_name, s.last_name, s.admission_number
             FROM payment_receipts pr
             JOIN students s ON s.id = pr.student_id
             WHERE pr.school_id = ?
             ORDER BY pr.id DESC LIMIT 5",
            [$schoolId]
        );

        $currentSession = Database::fetch("SELECT * FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);
        $currentTerm = Database::fetch("SELECT * FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);

        View::render('tenant.dashboard', [
            'title' => 'School Dashboard - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'dashboard',
            'studentCount' => $studentCount,
            'staffCount' => $staffCount,
            'feeCollected' => $feeCollected,
            'outstandingFee' => $outstandingFee,
            'pendingAdmissions' => $pendingAdmissions,
            'recentStudents' => $recentStudents,
            'recentPayments' => $recentPayments,
            'currentSession' => $currentSession,
            'currentTerm' => $currentTerm,
        ], 'tenant');
    }

    public function admissions(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $admissions = Database::query(
            "SELECT a.*, cl.name AS applied_class_name
             FROM admissions a
             LEFT JOIN class_levels cl ON cl.id = a.applied_class_level_id
             WHERE a.school_id = ?
             ORDER BY a.id DESC",
            [$schoolId]
        );

        $classLevels = Database::query("SELECT * FROM class_levels WHERE school_id = ? ORDER BY rank_order ASC", [$schoolId]);
        $classArms = Database::query("SELECT * FROM class_arms WHERE school_id = ? ORDER BY id ASC", [$schoolId]);

        View::render('tenant.admissions', [
            'title' => 'Admissions & Enrollment - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'admissions',
            'admissions' => $admissions,
            'classLevels' => $classLevels,
            'classArms' => $classArms,
        ], 'tenant');
    }

    public function processAdmission(Request $request, string $id): void
    {
        $schoolId = $this->getSchoolId();
        $admissionId = (int)$id;
        $action = (string)$request->post('action', 'admit'); // admit, reject, waitlist
        $classArmId = (int)$request->post('class_arm_id', 0);

        $app = Database::fetch("SELECT * FROM admissions WHERE id = ? AND school_id = ?", [$admissionId, $schoolId]);
        if (!$app) {
            AuditLogger::log('unauthorized_cross_tenant_admission_attempt', 'admissions', $admissionId, [
                'school_id' => $schoolId,
                'user_id' => Auth::id()
            ]);
            Response::forbidden('Access Denied: Admission application does not belong to your school tenant.');
        }

        if ($action === 'admit') {
            // Verify class arm belongs to this school
            if ($classArmId > 0) {
                $armCheck = Database::fetch("SELECT id FROM class_arms WHERE id = ? AND school_id = ?", [$classArmId, $schoolId]);
                if (!$armCheck) {
                    $classArmId = 0;
                }
            }

            // Generate unique admission number
            $school = TenantContext::getSchool();
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $school['code'] ?? $school['slug'] ?? 'SCH'), 0, 3));
            $admCount = (int)Database::fetch("SELECT COUNT(*) AS c FROM students WHERE school_id = ?", [$schoolId])['c'] + 1;
            $admNo = sprintf('%s/%s/%03d', $prefix, date('Y'), $admCount);

            Database::transaction(function() use ($schoolId, $admissionId, $app, $admNo, $classArmId) {
                // Update admission application
                Database::execute(
                    "UPDATE admissions SET status = 'admitted', assigned_admission_number = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?",
                    [$admNo, Auth::id(), $admissionId]
                );

                // Create student record
                Database::execute(
                    "INSERT INTO students (school_id, class_arm_id, admission_number, first_name, last_name, date_of_birth, gender, status, enrolled_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'enrolled', CURDATE())",
                    [$schoolId, $classArmId ?: null, $admNo, $app['applicant_first_name'], $app['applicant_last_name'], $app['date_of_birth'], $app['applicant_gender']]
                );

                AuditLogger::log('admission_approved_enrolled', 'student', (int)Database::lastInsertId(), [
                    'admission_number' => $admNo,
                    'name' => "{$app['applicant_first_name']} {$app['applicant_last_name']}",
                ]);
            });

            Response::redirect('/school/admissions', "Applicant admitted successfully! Assigned Admission No: {$admNo}.", 'success');
        } else {
            $newStatus = $action === 'reject' ? 'rejected' : 'waitlisted';
            Database::execute("UPDATE admissions SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?", [$newStatus, Auth::id(), $admissionId]);
            Response::redirect('/school/admissions', "Application status updated to {$newStatus}.", 'info');
        }
    }

    public function createStudent(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $firstName = trim((string)$request->post('first_name', ''));
        $lastName = trim((string)$request->post('last_name', ''));
        $middleName = trim((string)$request->post('middle_name', ''));
        $gender = (string)$request->post('gender', 'male');
        $dob = (string)$request->post('date_of_birth', '2015-01-01');
        $classArmId = (int)$request->post('class_arm_id', 0);
        $genotype = (string)$request->post('genotype', 'AA');
        $bloodGroup = (string)$request->post('blood_group', 'O+');
        $parentName = trim((string)$request->post('parent_name', ''));
        $parentPhone = trim((string)$request->post('parent_phone', ''));
        $parentEmail = trim((string)$request->post('parent_email', ''));

        if (empty($firstName) || empty($lastName) || $classArmId <= 0) {
            Response::redirect('/school/students', 'First name, last name, and class arm are required.', 'danger');
        }

        $armCheck = Database::fetch("SELECT id FROM class_arms WHERE id = ? AND school_id = ?", [$classArmId, $schoolId]);
        if (!$armCheck) {
            Response::redirect('/school/students', 'Invalid class arm selected.', 'danger');
        }

        $school = TenantContext::getSchool();
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $school['code'] ?? $school['slug'] ?? 'SCH'), 0, 3));
        $admCount = (int)Database::fetch("SELECT COUNT(*) AS c FROM students WHERE school_id = ?", [$schoolId])['c'] + 1;
        $admNo = sprintf('%s/%s/%03d', $prefix, date('Y'), $admCount);

        Database::transaction(function() use ($schoolId, $firstName, $lastName, $middleName, $gender, $dob, $classArmId, $genotype, $bloodGroup, $admNo, $parentName, $parentPhone, $parentEmail) {
            $parentId = null;
            if (!empty($parentName)) {
                $pUser = null;
                if (!empty($parentEmail)) {
                    $pUser = Database::fetch("SELECT id FROM users WHERE email = ?", [$parentEmail]);
                    if (!$pUser) {
                        Database::execute(
                            "INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active)
                             VALUES (?, ?, ?, ?, ?, 'parent', 1)",
                            [$schoolId, $parentName, $parentEmail, $parentPhone, password_hash('password123', PASSWORD_BCRYPT)]
                        );
                        $pUserId = (int)Database::lastInsertId();
                    } else {
                        $pUserId = (int)$pUser['id'];
                    }
                    $parentRecord = Database::fetch("SELECT id FROM parents WHERE user_id = ? AND school_id = ?", [$pUserId, $schoolId]);
                    if ($parentRecord) {
                        $parentId = (int)$parentRecord['id'];
                    } else {
                        Database::execute(
                            "INSERT INTO parents (school_id, user_id, title, occupation) VALUES (?, ?, 'Mr/Mrs', 'Guardian')",
                            [$schoolId, $pUserId]
                        );
                        $parentId = (int)Database::lastInsertId();
                    }
                }
            }

            Database::execute(
                "INSERT INTO students (school_id, parent_id, class_arm_id, admission_number, first_name, last_name, middle_name, date_of_birth, gender, blood_group, genotype, status, enrolled_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'enrolled', CURDATE())",
                [$schoolId, $parentId, $classArmId, $admNo, $firstName, $lastName, $middleName ?: null, $dob, $gender, $bloodGroup, $genotype]
            );
            $newStuId = (int)Database::lastInsertId();

            AuditLogger::log('student_enrolled_direct', 'student', $newStuId, [
                'admission_number' => $admNo,
                'name' => "{$firstName} {$lastName}",
                'class_arm_id' => $classArmId,
            ]);
        });

        Response::redirect('/school/students', "Student {$firstName} {$lastName} successfully enrolled! Admission No: {$admNo}", 'success');
    }

    public function students(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $classArmFilter = $request->get('arm_id');

        $query = "SELECT s.*, ca.name AS arm_name, cl.name AS class_name, p.title AS parent_title, u_p.name AS parent_name, u_p.phone AS parent_phone
                  FROM students s
                  LEFT JOIN class_arms ca ON ca.id = s.class_arm_id
                  LEFT JOIN class_levels cl ON cl.id = ca.class_level_id
                  LEFT JOIN parents p ON p.id = s.parent_id
                  LEFT JOIN users u_p ON u_p.id = p.user_id
                  WHERE s.school_id = ?";
        
        $params = [$schoolId];
        if ($classArmFilter) {
            $query .= " AND s.class_arm_id = ?";
            $params[] = (int)$classArmFilter;
        }
        $query .= " ORDER BY s.id DESC";

        $students = Database::query($query, $params);
        $classArms = Database::query("SELECT ca.*, cl.name AS class_name FROM class_arms ca JOIN class_levels cl ON cl.id = ca.class_level_id WHERE ca.school_id = ? ORDER BY cl.rank_order ASC", [$schoolId]);

        View::render('tenant.students', [
            'title' => 'Student Directory - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'students',
            'students' => $students,
            'classArms' => $classArms,
            'selectedArm' => $classArmFilter,
        ], 'tenant');
    }

    public function academics(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $sessions = Database::query("SELECT * FROM academic_sessions WHERE school_id = ? ORDER BY id DESC", [$schoolId]);
        $terms = Database::query("SELECT t.*, s.name AS session_name FROM academic_terms t JOIN academic_sessions s ON s.id = t.session_id WHERE t.school_id = ? ORDER BY t.id DESC", [$schoolId]);
        $classLevels = Database::query("SELECT * FROM class_levels WHERE school_id = ? ORDER BY rank_order ASC", [$schoolId]);
        $classArms = Database::query("SELECT ca.*, cl.name AS level_name, u.name AS teacher_name FROM class_arms ca JOIN class_levels cl ON cl.id = ca.class_level_id LEFT JOIN staff st ON st.id = ca.class_teacher_id LEFT JOIN users u ON u.id = st.user_id WHERE ca.school_id = ? ORDER BY cl.rank_order ASC", [$schoolId]);
        $subjects = Database::query("SELECT * FROM subjects WHERE school_id = ? ORDER BY name ASC", [$schoolId]);

        View::render('tenant.academics', [
            'title' => 'Academic Structure - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'academics',
            'sessions' => $sessions,
            'terms' => $terms,
            'classLevels' => $classLevels,
            'classArms' => $classArms,
            'subjects' => $subjects,
        ], 'tenant');
    }

    public function createSession(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $name = trim((string)$request->post('name', ''));
        $startDate = (string)$request->post('start_date', date('Y-09-01'));
        $endDate = (string)$request->post('end_date', date('Y-07-31', strtotime('+1 year')));
        $isCurrent = (int)$request->post('is_current', 0);

        if (empty($name)) {
            Response::redirect('/school/academics', 'Session name is required.', 'danger');
        }

        if ($isCurrent === 1) {
            Database::execute("UPDATE academic_sessions SET is_current = 0 WHERE school_id = ?", [$schoolId]);
        }

        Database::execute(
            "INSERT INTO academic_sessions (school_id, name, start_date, end_date, is_current)
             VALUES (?, ?, ?, ?, ?)",
            [$schoolId, $name, $startDate, $endDate, $isCurrent]
        );

        AuditLogger::log('academic_session_created', 'academic_sessions', (int)Database::lastInsertId(), [
            'name' => $name,
            'school_id' => $schoolId,
        ]);

        Response::redirect('/school/academics', "Academic Session '{$name}' created successfully.", 'success');
    }

    public function createClassArm(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $classLevelId = (int)$request->post('class_level_id', 0);
        $name = trim((string)$request->post('name', ''));
        $capacity = (int)$request->post('capacity', 35);
        $classTeacherId = (int)$request->post('class_teacher_id', 0);

        $levelCheck = Database::fetch("SELECT id FROM class_levels WHERE id = ? AND school_id = ?", [$classLevelId, $schoolId]);
        if (!$levelCheck || empty($name)) {
            Response::redirect('/school/academics', 'Valid Class Level and Arm Name are required.', 'danger');
        }

        Database::execute(
            "INSERT INTO class_arms (school_id, class_level_id, name, capacity, class_teacher_id)
             VALUES (?, ?, ?, ?, ?)",
            [$schoolId, $classLevelId, $name, $capacity, $classTeacherId ?: null]
        );

        AuditLogger::log('class_arm_created', 'class_arms', (int)Database::lastInsertId(), [
            'name' => $name,
            'class_level_id' => $classLevelId,
        ]);

        Response::redirect('/school/academics', "Class Arm '{$name}' created successfully.", 'success');
    }

    public function createSubject(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $name = trim((string)$request->post('name', ''));
        $code = strtoupper(trim((string)$request->post('code', '')));
        $category = (string)$request->post('category', 'general');

        if (empty($name) || empty($code)) {
            Response::redirect('/school/academics', 'Subject title and code are required.', 'danger');
        }

        Database::execute(
            "INSERT INTO subjects (school_id, name, code, category, is_active)
             VALUES (?, ?, ?, ?, 1)",
            [$schoolId, $name, $code, $category]
        );

        AuditLogger::log('subject_created', 'subjects', (int)Database::lastInsertId(), [
            'name' => $name,
            'code' => $code,
        ]);

        Response::redirect('/school/academics', "Subject '{$name}' ({$code}) added to curriculum.", 'success');
    }

    public function attendance(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $armId = (int)($request->get('arm_id') ?? Database::fetch("SELECT id FROM class_arms WHERE school_id = ? ORDER BY id ASC LIMIT 1", [$schoolId])['id'] ?? 0);
        $date = (string)($request->get('date') ?? date('Y-m-d'));

        if ($armId > 0) {
            $armCheck = Database::fetch("SELECT id FROM class_arms WHERE id = ? AND school_id = ?", [$armId, $schoolId]);
            if (!$armCheck) {
                AuditLogger::log('unauthorized_cross_tenant_attendance_attempt', 'class_arms', $armId, [
                    'school_id' => $schoolId,
                    'user_id' => Auth::id()
                ]);
                Response::forbidden('Access Denied: Class arm does not belong to your school tenant.');
            }
        }

        $session = Database::fetch("SELECT id FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);
        $term = Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);

        if ($request->isPost()) {
            $statuses = $request->post('attendance', []);
            foreach ($statuses as $stuId => $status) {
                $stuId = (int)$stuId;
                // Strict tenant & arm validation
                $stuCheck = Database::fetch("SELECT id FROM students WHERE id = ? AND school_id = ? AND class_arm_id = ?", [$stuId, $schoolId, $armId]);
                if (!$stuCheck) {
                    continue;
                }
                $status = in_array($status, ['present', 'absent', 'late', 'excused'], true) ? $status : 'present';

                $existing = Database::fetch("SELECT id FROM attendance WHERE school_id = ? AND student_id = ? AND attendance_date = ?", [$schoolId, $stuId, $date]);
                if ($existing) {
                    Database::execute("UPDATE attendance SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $existing['id']]);
                } else {
                    Database::execute(
                        "INSERT INTO attendance (school_id, session_id, term_id, student_id, class_arm_id, attendance_date, status, recorded_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                        [$schoolId, $session['id'], $term['id'], $stuId, $armId, $date, $status, Auth::id()]
                    );
                }
            }
            Response::redirect("/school/attendance?arm_id={$armId}&date={$date}", 'Attendance saved successfully!', 'success');
        }

        $students = Database::query(
            "SELECT s.*, 
                (SELECT status FROM attendance WHERE student_id = s.id AND attendance_date = ? LIMIT 1) AS current_status
             FROM students s
             WHERE s.school_id = ? AND s.class_arm_id = ? AND s.status = 'enrolled'
             ORDER BY s.last_name ASC",
            [$date, $schoolId, $armId]
        );

        $classArms = Database::query("SELECT ca.*, cl.name AS class_name FROM class_arms ca JOIN class_levels cl ON cl.id = ca.class_level_id WHERE ca.school_id = ? ORDER BY cl.rank_order ASC", [$schoolId]);

        View::render('tenant.attendance', [
            'title' => 'Daily Attendance Register - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'attendance',
            'students' => $students,
            'classArms' => $classArms,
            'selectedArm' => $armId,
            'selectedDate' => $date,
        ], 'tenant');
    }

    public function exams(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $armId = (int)($request->get('arm_id') ?? Database::fetch("SELECT id FROM class_arms WHERE school_id = ? ORDER BY id ASC LIMIT 1", [$schoolId])['id'] ?? 0);
        $subjectId = (int)($request->get('subject_id') ?? Database::fetch("SELECT id FROM subjects WHERE school_id = ? ORDER BY id ASC LIMIT 1", [$schoolId])['id'] ?? 0);

        if ($armId > 0) {
            $armCheck = Database::fetch("SELECT id FROM class_arms WHERE id = ? AND school_id = ?", [$armId, $schoolId]);
            if (!$armCheck) {
                AuditLogger::log('unauthorized_cross_tenant_exam_attempt', 'class_arms', $armId, [
                    'school_id' => $schoolId,
                    'user_id' => Auth::id()
                ]);
                Response::forbidden('Access Denied: Class arm does not belong to your school tenant.');
            }
        }

        if ($subjectId > 0) {
            $subjCheck = Database::fetch("SELECT id FROM subjects WHERE id = ? AND school_id = ?", [$subjectId, $schoolId]);
            if (!$subjCheck) {
                AuditLogger::log('unauthorized_cross_tenant_exam_attempt', 'subjects', $subjectId, [
                    'school_id' => $schoolId,
                    'user_id' => Auth::id()
                ]);
                Response::forbidden('Access Denied: Subject does not belong to your school tenant.');
            }
        }

        $session = Database::fetch("SELECT id, name FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);
        $term = Database::fetch("SELECT id, name FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);

        if ($request->isPost()) {
            $action = $request->post('action', 'save_scores');

            if ($action === 'save_scores') {
                $scoresInput = $request->post('scores', []);
                foreach ($scoresInput as $stuId => $sc) {
                    $stuId = (int)$stuId;
                    // Strict tenant & arm validation
                    $stuCheck = Database::fetch("SELECT id FROM students WHERE id = ? AND school_id = ? AND class_arm_id = ?", [$stuId, $schoolId, $armId]);
                    if (!$stuCheck) {
                        continue;
                    }

                    $ca1 = min(20.0, max(0.0, (float)($sc['ca1'] ?? 0)));
                    $ca2 = min(20.0, max(0.0, (float)($sc['ca2'] ?? 0)));
                    $exam = min(60.0, max(0.0, (float)($sc['exam'] ?? 0)));
                    $tot = GradingService::calculateTotal($ca1, $ca2, $exam);
                    $gradeData = GradingService::determineGrade($schoolId, $tot);

                    $existing = Database::fetch(
                        "SELECT id FROM exam_scores WHERE school_id = ? AND session_id = ? AND term_id = ? AND class_arm_id = ? AND subject_id = ? AND student_id = ?",
                        [$schoolId, $session['id'], $term['id'], $armId, $subjectId, $stuId]
                    );

                    if ($existing) {
                        Database::execute(
                            "UPDATE exam_scores SET ca1_score = ?, ca2_score = ?, exam_score = ?, total_score = ?, grade = ?, remark = ?, status = 'approved', approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ?",
                            [$ca1, $ca2, $exam, $tot, $gradeData['grade'], $gradeData['remark'], Auth::id(), $existing['id']]
                        );
                    } else {
                        Database::execute(
                            "INSERT INTO exam_scores (school_id, session_id, term_id, class_arm_id, subject_id, student_id, ca1_score, ca2_score, exam_score, total_score, grade, remark, teacher_id, status, approved_by, approved_at)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved', ?, NOW())",
                            [$schoolId, $session['id'], $term['id'], $armId, $subjectId, $stuId, $ca1, $ca2, $exam, $tot, $gradeData['grade'], $gradeData['remark'], Auth::id(), Auth::id()]
                        );
                    }
                }

                // Compute rankings & averages for class arm
                GradingService::computeClassPositions($schoolId, (int)$session['id'], (int)$term['id'], $armId);

                Response::redirect("/school/exams?arm_id={$armId}&subject_id={$subjectId}", 'Scores updated and rankings calculated successfully!', 'success');
            }
        }

        $scores = Database::query(
            "SELECT s.id AS student_id, s.first_name, s.last_name, s.admission_number,
                es.ca1_score, es.ca2_score, es.exam_score, es.total_score, es.grade, es.remark, es.status AS score_status
             FROM students s
             LEFT JOIN exam_scores es ON es.student_id = s.id 
                AND es.session_id = ? AND es.term_id = ? AND es.class_arm_id = ? AND es.subject_id = ?
             WHERE s.school_id = ? AND s.class_arm_id = ? AND s.status = 'enrolled'
             ORDER BY s.last_name ASC",
            [$session['id'] ?? 0, $term['id'] ?? 0, $armId, $subjectId, $schoolId, $armId]
        );

        $summaries = Database::query(
            "SELECT sts.*, s.first_name, s.last_name, s.admission_number
             FROM student_term_summaries sts
             JOIN students s ON s.id = sts.student_id
             WHERE sts.school_id = ? AND sts.session_id = ? AND sts.term_id = ? AND sts.class_arm_id = ?
             ORDER BY sts.position ASC",
            [$schoolId, $session['id'] ?? 0, $term['id'] ?? 0, $armId]
        );

        $classArms = Database::query("SELECT ca.*, cl.name AS class_name FROM class_arms ca JOIN class_levels cl ON cl.id = ca.class_level_id WHERE ca.school_id = ? ORDER BY cl.rank_order ASC", [$schoolId]);
        $subjects = Database::query("SELECT * FROM subjects WHERE school_id = ? ORDER BY name ASC", [$schoolId]);

        View::render('tenant.exams', [
            'title' => 'Examinations & Results - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'exams',
            'scores' => $scores,
            'summaries' => $summaries,
            'classArms' => $classArms,
            'subjects' => $subjects,
            'selectedArm' => $armId,
            'selectedSubject' => $subjectId,
            'session' => $session,
            'term' => $term,
        ], 'tenant');
    }

    public function downloadReportCard(Request $request, string $studentId): void
    {
        $schoolId = $this->getSchoolId();
        $stuId = (int)$studentId;

        // Strict cross-tenant check: verify student belongs to this school
        $student = Database::fetch("SELECT * FROM students WHERE id = ? AND school_id = ?", [$stuId, $schoolId]);
        if (!$student) {
            AuditLogger::log('unauthorized_cross_tenant_report_card_attempt', 'student', $stuId, [
                'attempted_student_id' => $stuId,
                'user_school_id' => $schoolId,
            ]);
            http_response_code(403);
            View::render('errors.403', ['message' => 'Access Denied: You cannot access or download report cards from another school tenant.'], 'auth');
            exit;
        }

        $session = Database::fetch("SELECT id FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);
        $term = Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);

        if (!$session || !$term) {
            http_response_code(400);
            die("Current academic session and term not configured.");
        }

        $dompdf = ReportCardPdfService::generateReportCard($schoolId, $stuId, (int)$session['id'], (int)$term['id']);
        
        $dompdf->stream("ReportCard_{$stuId}_{$session['id']}_{$term['id']}.pdf", [
            'Attachment' => false // Preview in browser
        ]);
        exit;
    }

    public function downloadReceipt(Request $request, string $id): void
    {
        $schoolId = $this->getSchoolId();
        $receiptId = (int)$id;

        try {
            $dompdf = FinancialPdfService::generateReceiptPdf($schoolId, $receiptId);
            $dompdf->stream("Payment_Receipt_{$receiptId}.pdf", ['Attachment' => false]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(404);
            die(htmlspecialchars($e->getMessage()));
        }
    }

    public function downloadInvoice(Request $request, string $id): void
    {
        $schoolId = $this->getSchoolId();
        $invoiceId = (int)$id;

        try {
            $dompdf = FinancialPdfService::generateInvoicePdf($schoolId, $invoiceId);
            $dompdf->stream("Fee_Invoice_{$invoiceId}.pdf", ['Attachment' => false]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(404);
            die(htmlspecialchars($e->getMessage()));
        }
    }

    public function staff(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $staff = Database::query(
            "SELECT st.*, u.name, u.email, u.role
             FROM staff st
             JOIN users u ON u.id = st.user_id
             WHERE st.school_id = ?
             ORDER BY st.id DESC",
            [$schoolId]
        );

        View::render('tenant.staff', [
            'title' => 'Teachers & Staff Directory - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'staff',
            'staff' => $staff,
        ], 'tenant');
    }

    public function createStaff(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $name = trim((string)$request->post('name', ''));
        $email = trim((string)$request->post('email', ''));
        $phone = trim((string)$request->post('phone', ''));
        $designation = trim((string)$request->post('designation', ''));
        $department = trim((string)$request->post('department', 'Academic'));
        $role = (string)$request->post('role', 'teacher');
        $qualification = trim((string)$request->post('qualification', 'B.Ed'));
        $salaryBase = (float)$request->post('salary_base', 120000.0);

        if (empty($name) || empty($email) || empty($designation)) {
            Response::redirect('/school/staff', 'Staff name, email, and designation are required.', 'danger');
        }

        $existingUser = Database::fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existingUser) {
            Response::redirect('/school/staff', "A user with email {$email} already exists.", 'danger');
        }

        Database::transaction(function() use ($schoolId, $name, $email, $phone, $designation, $department, $role, $qualification, $salaryBase) {
            $pwdHash = password_hash('password123', PASSWORD_BCRYPT);
            Database::execute(
                "INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, 1)",
                [$schoolId, $name, $email, $phone, $pwdHash, $role]
            );
            $userId = (int)Database::lastInsertId();

            $empCount = (int)Database::fetch("SELECT COUNT(*) AS c FROM staff WHERE school_id = ?", [$schoolId])['c'] + 1;
            $empNo = sprintf('EMP/%s/%03d', date('Y'), $empCount);

            Database::execute(
                "INSERT INTO staff (school_id, user_id, employee_number, designation, department, qualification, salary_base, date_of_joining, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), 'active')",
                [$schoolId, $userId, $empNo, $designation, $department, $qualification, $salaryBase]
            );
            $staffId = (int)Database::lastInsertId();

            AuditLogger::log('staff_onboarded', 'staff', $staffId, [
                'employee_number' => $empNo,
                'name' => $name,
                'role' => $role,
            ]);
        });

        Response::redirect('/school/staff', "Staff member '{$name}' onboarded successfully.", 'success');
    }

    public function finance(Request $request): void
    {
        $schoolId = $this->getSchoolId();

        if ($request->isPost()) {
            $action = $request->post('action', 'record_payment');

            if ($action === 'record_payment') {
                $invoiceId = (int)$request->post('invoice_id', 0);
                $amountPaid = (float)$request->post('amount_paid', 0.0);
                $paymentMethod = (string)$request->post('payment_method', 'cash');
                $reference = (string)$request->post('payment_reference', 'CASH_' . time());

                $inv = Database::fetch("SELECT * FROM fee_invoices WHERE id = ? AND school_id = ?", [$invoiceId, $schoolId]);
                if (!$inv) {
                    AuditLogger::log('unauthorized_cross_tenant_finance_attempt', 'fee_invoices', $invoiceId, [
                        'school_id' => $schoolId,
                        'user_id' => Auth::id()
                    ]);
                    Response::forbidden('Access Denied: Invoice does not belong to your school tenant.');
                }

                $recCount = (int)Database::fetch("SELECT COUNT(*) AS c FROM payment_receipts WHERE school_id = ?", [$schoolId])['c'] + 1;
                $receiptNo = sprintf('REC-%s-%03d', date('Y'), $recCount);

                Database::transaction(function() use ($schoolId, $inv, $amountPaid, $paymentMethod, $reference, $receiptNo) {
                    Database::execute(
                        "INSERT INTO payment_receipts (school_id, receipt_number, invoice_id, student_id, amount_paid, payment_method, payment_reference, transaction_id, payment_date, status, verified_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'verified', ?)",
                        [$schoolId, $receiptNo, $inv['id'], $inv['student_id'], $amountPaid, $paymentMethod, $reference, 'TXN_' . rand(100000, 999999), Auth::id()]
                    );

                    $newPaid = (float)$inv['paid_amount'] + $amountPaid;
                    $newBalance = max(0.0, (float)$inv['total_amount'] - $newPaid);
                    $newStatus = $newBalance <= 0.0 ? 'paid' : 'partially_paid';

                    Database::execute(
                        "UPDATE fee_invoices SET paid_amount = ?, balance_due = ?, status = ?, updated_at = NOW() WHERE id = ?",
                        [$newPaid, $newBalance, $newStatus, $inv['id']]
                    );

                    AuditLogger::log('fee_payment_recorded', 'payment_receipt', (int)Database::lastInsertId(), [
                        'invoice_number' => $inv['invoice_number'],
                        'amount_paid' => $amountPaid,
                        'receipt_number' => $receiptNo,
                    ]);
                });

                Response::redirect('/school/finance', "Payment of " . money($amountPaid) . " recorded. Receipt No: {$receiptNo}", 'success');
            }
        }

        $invoices = Database::query(
            "SELECT fi.*, s.first_name, s.last_name, s.admission_number, ca.name AS arm_name, cl.name AS class_name
             FROM fee_invoices fi
             JOIN students s ON s.id = fi.student_id
             LEFT JOIN class_arms ca ON ca.id = fi.class_arm_id
             LEFT JOIN class_levels cl ON cl.id = ca.class_level_id
             WHERE fi.school_id = ?
             ORDER BY fi.id DESC",
            [$schoolId]
        );

        $receipts = Database::query(
            "SELECT pr.*, s.first_name, s.last_name, s.admission_number, fi.invoice_number
             FROM payment_receipts pr
             JOIN students s ON s.id = pr.student_id
             JOIN fee_invoices fi ON fi.id = pr.invoice_id
             WHERE pr.school_id = ?
             ORDER BY pr.id DESC",
            [$schoolId]
        );

        $categories = Database::query("SELECT * FROM fee_categories WHERE school_id = ?", [$schoolId]);
        $students = Database::query(
            "SELECT s.id, s.first_name, s.last_name, s.admission_number, cl.name AS class_name, ca.name AS arm_name 
             FROM students s 
             LEFT JOIN class_arms ca ON ca.id = s.class_arm_id 
             LEFT JOIN class_levels cl ON cl.id = ca.class_level_id 
             WHERE s.school_id = ? AND s.status = 'enrolled' 
             ORDER BY s.last_name ASC",
            [$schoolId]
        );

        View::render('tenant.finance', [
            'title' => 'Finance & Fees - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'finance',
            'invoices' => $invoices,
            'receipts' => $receipts,
            'categories' => $categories,
            'students' => $students,
        ], 'tenant');
    }

    public function createInvoice(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $studentId = (int)$request->post('student_id', 0);
        $categoryId = (int)$request->post('fee_category_id', 0);
        $amount = (float)$request->post('amount', 0.0);
        $dueDate = (string)$request->post('due_date', date('Y-m-d', strtotime('+30 days')));

        $student = Database::fetch("SELECT * FROM students WHERE id = ? AND school_id = ?", [$studentId, $schoolId]);
        if (!$student || $amount <= 0) {
            Response::redirect('/school/finance', 'Valid enrolled student and fee amount are required.', 'danger');
        }

        $session = Database::fetch("SELECT id FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);
        $term = Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);

        $sessionId = (int)($session['id'] ?? Database::fetch("SELECT id FROM academic_sessions WHERE school_id = ? LIMIT 1", [$schoolId])['id'] ?? 1);
        $termId = (int)($term['id'] ?? Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? LIMIT 1", [$schoolId])['id'] ?? 1);
        $classArmId = (int)($student['class_arm_id'] ?? Database::fetch("SELECT id FROM class_arms WHERE school_id = ? LIMIT 1", [$schoolId])['id'] ?? 1);

        $invCount = (int)Database::fetch("SELECT COUNT(*) AS c FROM fee_invoices WHERE school_id = ?", [$schoolId])['c'] + 1;
        $invNo = sprintf('INV-%s-%03d', date('Y'), $invCount);

        Database::execute(
            "INSERT INTO fee_invoices (school_id, invoice_number, session_id, term_id, student_id, class_arm_id, total_amount, paid_amount, balance_due, due_date, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 0.00, ?, ?, 'unpaid')",
            [$schoolId, $invNo, $sessionId, $termId, $studentId, $classArmId, $amount, $amount, $dueDate]
        );

        AuditLogger::log('fee_invoice_created', 'fee_invoices', (int)Database::lastInsertId(), [
            'invoice_number' => $invNo,
            'student_id' => $studentId,
            'total_amount' => $amount,
        ]);

        Response::redirect('/school/finance', "Invoice {$invNo} of " . money($amount) . " created for student.", 'success');
    }

    public function library(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $books = Database::query("SELECT * FROM library_books WHERE school_id = ? ORDER BY id DESC", [$schoolId]);
        $inventory = Database::query("SELECT * FROM inventory_items WHERE school_id = ? ORDER BY id DESC", [$schoolId]);

        View::render('tenant.library', [
            'title' => 'Library & Asset Inventory - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'library',
            'books' => $books,
            'inventory' => $inventory,
        ], 'tenant');
    }

    public function websiteCms(Request $request): void
    {
        $schoolId = $this->getSchoolId();

        if ($request->isPost()) {
            $pageSlug = (string)$request->post('page_slug', 'home');
            $title = (string)$request->post('title', '');
            $bannerTitle = (string)$request->post('banner_title', '');
            $bannerSubtitle = (string)$request->post('banner_subtitle', '');
            $content = (string)$request->post('content', '');

            Database::execute(
                "UPDATE school_website_pages 
                 SET title = ?, banner_title = ?, banner_subtitle = ?, content = ?, updated_at = NOW() 
                 WHERE school_id = ? AND page_slug = ?",
                [$title, $bannerTitle, $bannerSubtitle, $content, $schoolId, $pageSlug]
            );

            Response::redirect('/school/website-cms', "Website page '{$pageSlug}' updated successfully!", 'success');
        }

        $pages = Database::query("SELECT * FROM school_website_pages WHERE school_id = ?", [$schoolId]);

        View::render('tenant.website_cms', [
            'title' => 'Public Website CMS - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'website-cms',
            'pages' => $pages,
        ], 'tenant');
    }

    public function settings(Request $request): void
    {
        $schoolId = $this->getSchoolId();

        if ($request->isPost()) {
            $name = trim((string)$request->post('name', ''));
            $motto = trim((string)$request->post('motto', ''));
            $phone = trim((string)$request->post('phone', ''));
            $address = trim((string)$request->post('address', ''));

            Database::execute(
                "UPDATE schools SET name = ?, motto = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?",
                [$name, $motto, $phone, $address, $schoolId]
            );

            AuditLogger::log('school_profile_updated', 'school', $schoolId, ['name' => $name, 'motto' => $motto]);
            Response::redirect('/school/settings', 'School profile updated successfully.', 'success');
        }

        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);
        $domains = Database::query("SELECT * FROM tenant_domains WHERE school_id = ?", [$schoolId]);
        $grades = Database::query("SELECT * FROM grading_scales WHERE school_id = ? ORDER BY min_score DESC", [$schoolId]);

        View::render('tenant.settings', [
            'title' => 'School Settings - ' . ($school['name'] ?? ''),
            'activeNav' => 'settings',
            'school' => $school,
            'domains' => $domains,
            'grades' => $grades,
        ], 'tenant');
    }

    public function termPortalFee(Request $request): void
    {
        $schoolId = $this->getSchoolId();

        $session = Database::fetch("SELECT id, name FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId])
            ?: Database::fetch("SELECT id, name FROM academic_sessions WHERE school_id = ? ORDER BY id DESC LIMIT 1", [$schoolId]);
        $term = Database::fetch("SELECT id, name FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId])
            ?: Database::fetch("SELECT id, name FROM academic_terms WHERE school_id = ? ORDER BY id DESC LIMIT 1", [$schoolId]);

        $sessionId = (int)($session['id'] ?? 1);
        $termId = (int)($term['id'] ?? 1);

        $agreement = \EduSphere\Services\PortalBillingService::getOrCreateAgreement($schoolId, $sessionId, $termId);
        $summary = \EduSphere\Services\PortalBillingService::getTermSummary($schoolId, $sessionId, $termId);

        $invoices = Database::query(
            "SELECT i.*, s.admission_number, s.first_name, s.last_name, ca.name AS arm_name, cl.name AS class_name
             FROM portal_invoices i
             JOIN students s ON s.id = i.student_id
             LEFT JOIN class_arms ca ON ca.id = s.class_arm_id
             LEFT JOIN class_levels cl ON cl.id = ca.class_level_id
             WHERE i.school_id = ? AND i.session_id = ? AND i.term_id = ?
             ORDER BY i.id DESC",
            [$schoolId, $sessionId, $termId]
        );

        $notices = Database::query(
            "SELECT * FROM portal_billing_notices WHERE school_id = ? AND term_id = ? ORDER BY id DESC",
            [$schoolId, $termId]
        );

        View::render('tenant.term_portal_fee', [
            'title' => 'Term Portal Fee - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'term_portal_fee',
            'session' => $session,
            'term' => $term,
            'agreement' => $agreement,
            'summary' => $summary,
            'invoices' => $invoices,
            'notices' => $notices,
        ], 'tenant');
    }

    public function acceptFeeAgreement(Request $request, string $id): void
    {
        $schoolId = $this->getSchoolId();
        $agreementId = (int)$id;

        $ag = Database::fetch("SELECT id, school_id FROM school_portal_fee_agreements WHERE id = ?", [$agreementId]);
        if (!$ag || (int)$ag['school_id'] !== $schoolId) {
            http_response_code(403);
            die("Access Forbidden: Cross-tenant agreement manipulation is strictly prohibited.");
        }

        $notes = trim((string)$request->post('school_notes', ''));
        \EduSphere\Services\PortalBillingService::acceptAgreement($agreementId, \EduSphere\Core\Auth::id(), $notes);

        Response::redirect('/school/term-portal-fee', 'Term portal fee agreement accepted! You can now generate student invoices.', 'success');
    }

    public function rejectFeeAgreement(Request $request, string $id): void
    {
        $schoolId = $this->getSchoolId();
        $agreementId = (int)$id;

        $ag = Database::fetch("SELECT id, school_id FROM school_portal_fee_agreements WHERE id = ?", [$agreementId]);
        if (!$ag || (int)$ag['school_id'] !== $schoolId) {
            http_response_code(403);
            die("Access Forbidden: Cross-tenant agreement manipulation is strictly prohibited.");
        }

        $reason = trim((string)$request->post('rejection_reason', 'School requested rate adjustment'));
        \EduSphere\Services\PortalBillingService::rejectAgreement($agreementId, \EduSphere\Core\Auth::id(), $reason);

        Response::redirect('/school/term-portal-fee', 'Fee agreement rejected. Central platform administration has been notified.', 'warning');
    }

    public function generatePortalInvoices(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $session = Database::fetch("SELECT id FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId])
            ?: Database::fetch("SELECT id FROM academic_sessions WHERE school_id = ? ORDER BY id DESC LIMIT 1", [$schoolId]);
        $term = Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId])
            ?: Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? ORDER BY id DESC LIMIT 1", [$schoolId]);

        $sessionId = (int)($session['id'] ?? 1);
        $termId = (int)($term['id'] ?? 1);

        try {
            $result = \EduSphere\Services\PortalBillingService::generateTermInvoices($schoolId, $sessionId, $termId);
            Response::redirect(
                '/school/term-portal-fee',
                "Generated {$result['created']} student portal invoices! ({$result['skipped']} were already billed)",
                'success'
            );
        } catch (\Throwable $e) {
            Response::redirect('/school/term-portal-fee', $e->getMessage(), 'danger');
        }
    }

    public function publishBillingNotice(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $agreementId = (int)$request->post('agreement_id', 0);
        $termId = (int)$request->post('term_id', 0);
        $title = trim((string)$request->post('title', ''));
        $body = trim((string)$request->post('notice_body', ''));

        if (empty($title) || empty($body)) {
            Response::redirect('/school/term-portal-fee', 'Title and Notice Body are required.', 'danger');
        }

        $stuCount = (int)(Database::fetch("SELECT COUNT(*) AS c FROM students WHERE school_id = ? AND status = 'enrolled'", [$schoolId])['c'] ?? 0);

        Database::execute(
            "INSERT INTO portal_billing_notices
             (school_id, agreement_id, term_id, title, notice_body, notice_type, sent_via, recipient_count, is_published, created_by)
             VALUES (?, ?, ?, ?, ?, 'term_announcement', 'portal_announcement', ?, 1, ?)",
            [$schoolId, $agreementId, $termId, $title, $body, $stuCount, \EduSphere\Core\Auth::id()]
        );

        AuditLogger::log('portal_notice_published', 'portal_billing_notices', (int)Database::lastInsertId(), [
            'school_id' => $schoolId,
            'title' => $title,
        ]);

        Response::redirect('/school/term-portal-fee', 'Notice published! Parents will see this on their family billing portal.', 'success');
    }

    public function incentives(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $summary = \EduSphere\Services\PortalBillingService::getTermSummary($schoolId);

        $ledger = Database::query(
            "SELECT l.*, t.name AS term_name 
             FROM school_incentive_ledger l
             LEFT JOIN academic_terms t ON t.id = l.term_id
             WHERE l.school_id = ?
             ORDER BY l.id DESC",
            [$schoolId]
        );

        $payoutRequests = Database::query(
            "SELECT * FROM school_payout_requests WHERE school_id = ? ORDER BY id DESC",
            [$schoolId]
        );

        View::render('tenant.incentives', [
            'title' => 'School Incentives & Revenue Share - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'incentives',
            'summary' => $summary,
            'ledger' => $ledger,
            'payoutRequests' => $payoutRequests,
        ], 'tenant');
    }

    public function requestIncentivePayout(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $amount = (float)$request->post('amount', 0.0);
        $bank = trim((string)$request->post('bank_name', ''));
        $acctNo = trim((string)$request->post('account_number', ''));
        $acctName = trim((string)$request->post('account_name', ''));

        if ($amount <= 0 || empty($bank) || empty($acctNo) || empty($acctName)) {
            Response::redirect('/school/incentives', 'All banking details and a positive payout amount are required.', 'danger');
        }

        try {
            \EduSphere\Services\RevenueSharingService::requestSchoolPayout($schoolId, $amount, $bank, $acctNo, $acctName, \EduSphere\Core\Auth::id());
            Response::redirect('/school/incentives', "Incentive payout request for " . money($amount) . " submitted to Super Admin HQ.", 'success');
        } catch (\Throwable $e) {
            Response::redirect('/school/incentives', $e->getMessage(), 'danger');
        }
    }

    public function notices(Request $request): void
    {
        $schoolId = $this->getSchoolId();

        $category = (string)($request->get('category') ?? '');
        $audience = (string)($request->get('audience') ?? '');

        $sql = "SELECT n.*, u.name as author_name 
                FROM school_notices n 
                JOIN users u ON u.id = n.created_by 
                WHERE n.school_id = ?";
        $params = [$schoolId];

        if (!empty($category)) {
            $sql .= " AND n.category = ?";
            $params[] = $category;
        }

        if (!empty($audience)) {
            $sql .= " AND n.target_audience = ?";
            $params[] = $audience;
        }

        $sql .= " ORDER BY n.is_pinned DESC, n.published_at DESC";
        $notices = Database::query($sql, $params);

        View::render('tenant.notices', [
            'title' => 'School Notice Board - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'notices',
            'notices' => $notices,
            'selectedCategory' => $category,
            'selectedAudience' => $audience,
        ], 'tenant');
    }

    public function createNotice(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $title = trim((string)$request->post('title', ''));
        $content = trim((string)$request->post('content', ''));
        $category = (string)$request->post('category', 'general');
        $priority = (string)$request->post('priority', 'normal');
        $audience = (string)$request->post('target_audience', 'all');
        $isPinned = (int)($request->post('is_pinned', 0) ? 1 : 0);
        $expiresAt = trim((string)$request->post('expires_at', '')) ?: null;

        if (empty($title) || empty($content)) {
            Response::redirect('/school/notices', 'Notice Title and Content are required.', 'danger');
        }

        Database::execute(
            "INSERT INTO school_notices
             (school_id, title, content, category, priority, target_audience, is_pinned, expires_at, created_by, published_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [$schoolId, $title, $content, $category, $priority, $audience, $isPinned, $expiresAt, Auth::id()]
        );

        AuditLogger::log('school_notice_created', 'school_notices', (int)Database::lastInsertId(), [
            'title' => $title,
            'category' => $category,
            'target_audience' => $audience,
        ]);

        Response::redirect('/school/notices', 'School announcement published successfully to the Notice Board!', 'success');
    }

    public function deleteNotice(Request $request, string $id): void
    {
        $schoolId = $this->getSchoolId();
        $noticeId = (int)$id;

        $notice = Database::fetch("SELECT id FROM school_notices WHERE id = ? AND school_id = ?", [$noticeId, $schoolId]);
        if (!$notice) {
            http_response_code(403);
            die("Access Forbidden.");
        }

        Database::execute("DELETE FROM school_notices WHERE id = ?", [$noticeId]);
        Response::redirect('/school/notices', 'Notice deleted successfully.', 'info');
    }

    public function messages(Request $request): void
    {
        $schoolId = $this->getSchoolId();

        // Mark incoming parent messages as read for school
        Database::execute(
            "UPDATE parent_school_messages SET is_read = 1, read_at = NOW() 
             WHERE school_id = ? AND sender_type = 'parent' AND is_read = 0",
            [$schoolId]
        );

        // Fetch all top-level conversations
        $threads = Database::query(
            "SELECT m.*, p.father_name, p.mother_name, p.father_phone, s.first_name as student_first, s.last_name as student_last, s.admission_number, u.name as sender_name,
                    (SELECT COUNT(*) FROM parent_school_messages r WHERE r.parent_message_id = m.id) as reply_count
             FROM parent_school_messages m
             JOIN parents p ON p.id = m.parent_id
             LEFT JOIN students s ON s.id = m.student_id
             JOIN users u ON u.id = m.sender_id
             WHERE m.school_id = ? AND m.parent_message_id IS NULL
             ORDER BY m.updated_at DESC",
            [$schoolId]
        );

        // Active thread selection
        $activeThreadId = (int)($request->get('thread_id') ?? ($threads[0]['id'] ?? 0));
        $activeThread = null;
        $replies = [];

        if ($activeThreadId > 0) {
            $activeThread = Database::fetch(
                "SELECT m.*, p.father_name, p.mother_name, p.father_phone, s.first_name as student_first, s.last_name as student_last, s.admission_number, u.name as sender_name
                 FROM parent_school_messages m
                 JOIN parents p ON p.id = m.parent_id
                 LEFT JOIN students s ON s.id = m.student_id
                 JOIN users u ON u.id = m.sender_id
                 WHERE m.id = ? AND m.school_id = ?",
                [$activeThreadId, $schoolId]
            );

            if ($activeThread) {
                $replies = Database::query(
                    "SELECT r.*, u.name as sender_name, u.role as sender_role
                     FROM parent_school_messages r
                     JOIN users u ON u.id = r.sender_id
                     WHERE r.parent_message_id = ?
                     ORDER BY r.created_at ASC",
                    [$activeThreadId]
                );
            }
        }

        // Parents list for compose modal
        $parents = Database::query(
            "SELECT p.id, p.father_name, p.mother_name, p.father_phone, s.first_name as student_first, s.last_name as student_last, s.id as student_id, s.admission_number
             FROM parents p
             JOIN students s ON s.parent_id = p.id
             WHERE p.school_id = ?
             ORDER BY s.last_name ASC",
            [$schoolId]
        );

        View::render('tenant.messages', [
            'title' => 'Parent Communication Desk - ' . (TenantContext::getSchool()['name'] ?? ''),
            'activeNav' => 'messages',
            'threads' => $threads,
            'activeThread' => $activeThread,
            'replies' => $replies,
            'parents' => $parents,
        ], 'tenant');
    }

    public function sendMessageToParent(Request $request): void
    {
        $schoolId = $this->getSchoolId();
        $parentId = (int)$request->post('parent_id', 0);
        $studentId = (int)$request->post('student_id', 0) ?: null;
        $subject = trim((string)$request->post('subject', ''));
        $message = trim((string)$request->post('message', ''));

        $parent = Database::fetch("SELECT * FROM parents WHERE id = ? AND school_id = ?", [$parentId, $schoolId]);
        if (!$parent || empty($subject) || empty($message)) {
            Response::redirect('/school/messages', 'Valid parent, subject, and message are required.', 'danger');
        }

        $recipientUserId = (int)$parent['user_id'];

        Database::execute(
            "INSERT INTO parent_school_messages
             (school_id, parent_id, student_id, sender_type, sender_id, recipient_id, subject, message, is_read, created_at, updated_at)
             VALUES (?, ?, ?, 'school', ?, ?, ?, ?, 0, NOW(), NOW())",
            [$schoolId, $parentId, $studentId, Auth::id(), $recipientUserId, $subject, $message]
        );

        $threadId = (int)Database::lastInsertId();

        AuditLogger::log('school_message_to_parent_sent', 'parent_school_messages', $threadId, [
            'parent_id' => $parentId,
            'student_id' => $studentId,
            'subject' => $subject,
        ]);

        Response::redirect('/school/messages?thread_id=' . $threadId, 'Message sent directly to parent portal!', 'success');
    }

    public function replyMessage(Request $request, string $id): void
    {
        $schoolId = $this->getSchoolId();
        $threadId = (int)$id;

        $thread = Database::fetch("SELECT * FROM parent_school_messages WHERE id = ? AND school_id = ?", [$threadId, $schoolId]);
        if (!$thread) {
            http_response_code(403);
            die("Access Forbidden: Thread not found.");
        }

        $message = trim((string)$request->post('message', ''));
        if (empty($message)) {
            Response::redirect('/school/messages?thread_id=' . $threadId, 'Reply message cannot be empty.', 'danger');
        }

        $parent = Database::fetch("SELECT user_id FROM parents WHERE id = ?", [(int)$thread['parent_id']]);
        $recipientId = (int)($parent['user_id'] ?? $thread['sender_id']);

        Database::execute(
            "INSERT INTO parent_school_messages
             (school_id, parent_id, student_id, sender_type, sender_id, recipient_id, subject, message, is_read, parent_message_id, created_at, updated_at)
             VALUES (?, ?, ?, 'school', ?, ?, ?, ?, 0, ?, NOW(), NOW())",
            [$schoolId, $thread['parent_id'], $thread['student_id'], Auth::id(), $recipientId, 'Re: ' . $thread['subject'], $message, $threadId]
        );

        Database::execute("UPDATE parent_school_messages SET updated_at = NOW() WHERE id = ?", [$threadId]);

        Response::redirect('/school/messages?thread_id=' . $threadId, 'Reply delivered to parent.', 'success');
    }
}

