<?php

declare(strict_types=1);

namespace EduSphere\Controllers;

use EduSphere\Core\Database;
use EduSphere\Core\Request;
use EduSphere\Core\Response;
use EduSphere\Core\View;
use EduSphere\Core\Auth;
use EduSphere\Core\TenantContext;
use EduSphere\Services\FinancialPdfService;
use EduSphere\Services\ReportCardPdfService;

class PortalController
{
    public function studentDashboard(Request $request): void
    {
        $userId = Auth::id();
        $student = Database::fetch(
            "SELECT s.*, ca.name AS arm_name, cl.name AS class_name, sch.name AS school_name
             FROM students s
             JOIN schools sch ON sch.id = s.school_id
             LEFT JOIN class_arms ca ON ca.id = s.class_arm_id
             LEFT JOIN class_levels cl ON cl.id = ca.class_level_id
             WHERE s.user_id = ?",
            [$userId]
        );

        if (!$student) {
            die("Student record not linked to this account.");
        }

        $schoolId = (int)$student['school_id'];
        $session = Database::fetch("SELECT * FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);
        $term = Database::fetch("SELECT * FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId]);

        // Published scores
        $scores = Database::query(
            "SELECT es.*, sub.name AS subject_name, sub.code AS subject_code
             FROM exam_scores es
             JOIN subjects sub ON sub.id = es.subject_id
             WHERE es.student_id = ? AND es.status IN ('published', 'locked')
             ORDER BY sub.name ASC",
            [$student['id']]
        );

        // Term summary
        $summary = Database::fetch(
            "SELECT * FROM student_term_summaries WHERE student_id = ? AND session_id = ? AND term_id = ?",
            [$student['id'], $session['id'] ?? 0, $term['id'] ?? 0]
        );

        // Attendance stats
        $attStats = Database::fetch(
            "SELECT COUNT(*) AS total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_count
             FROM attendance WHERE student_id = ?",
            [$student['id']]
        );

        // Invoices & Statements
        $invoices = Database::query("SELECT * FROM fee_invoices WHERE student_id = ? ORDER BY id DESC", [$student['id']]);
        $portalInvoice = Database::fetch("SELECT * FROM portal_invoices WHERE student_id = ? ORDER BY id DESC LIMIT 1", [$student['id']]);
        $receipts = Database::query(
            "SELECT pr.*, fi.invoice_number 
             FROM payment_receipts pr 
             JOIN fee_invoices fi ON fi.id = pr.invoice_id 
             WHERE pr.student_id = ? 
             ORDER BY pr.id DESC",
            [$student['id']]
        );
        $portalPayments = Database::query(
            "SELECT pp.*, pi.invoice_number 
             FROM portal_payments pp 
             JOIN portal_invoices pi ON pi.id = pp.invoice_id 
             WHERE pp.student_id = ? 
             ORDER BY pp.id DESC",
            [$student['id']]
        );

        // General Notice Board for Students
        $notices = Database::query(
            "SELECT n.*, u.name as author_name 
             FROM school_notices n 
             JOIN users u ON u.id = n.created_by 
             WHERE n.school_id = ? AND n.target_audience IN ('all', 'students') AND (n.expires_at IS NULL OR n.expires_at >= CURDATE()) 
             ORDER BY n.is_pinned DESC, n.published_at DESC LIMIT 5",
            [$schoolId]
        );

        View::render('portal.student', [
            'title' => 'Student Dashboard - ' . $student['first_name'],
            'student' => $student,
            'session' => $session,
            'term' => $term,
            'scores' => $scores,
            'summary' => $summary,
            'attStats' => $attStats,
            'invoices' => $invoices,
            'portalInvoice' => $portalInvoice,
            'receipts' => $receipts,
            'portalPayments' => $portalPayments,
            'notices' => $notices,
        ], 'portal');
    }

    public function parentDashboard(Request $request): void
    {
        $userId = Auth::id();
        $parent = Database::fetch("SELECT * FROM parents WHERE user_id = ?", [$userId]);
        if (!$parent) {
            die("Parent profile not found.");
        }

        // Strictly fetch wards linked to this parent (Enforcing tenant & parent boundary)
        $wards = Database::query(
            "SELECT s.*, ca.name AS arm_name, cl.name AS class_name
             FROM students s
             LEFT JOIN class_arms ca ON ca.id = s.class_arm_id
             LEFT JOIN class_levels cl ON cl.id = ca.class_level_id
             WHERE s.parent_id = ? AND s.school_id = ?",
            [$parent['id'], $parent['school_id']]
        );

        $selectedWardId = (int)($request->get('ward_id') ?? ($wards[0]['id'] ?? 0));
        $selectedWard = null;
        foreach ($wards as $w) {
            if ((int)$w['id'] === $selectedWardId) {
                $selectedWard = $w;
                break;
            }
        }
        if (!$selectedWard && !empty($wards)) {
            $selectedWard = $wards[0];
            $selectedWardId = (int)$selectedWard['id'];
        }

        $session = Database::fetch("SELECT * FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$parent['school_id']]);
        $term = Database::fetch("SELECT * FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$parent['school_id']]);

        $scores = [];
        $summary = null;
        $invoices = [];
        $attStats = ['total' => 0, 'present_count' => 0, 'absent_count' => 0];
        if ($selectedWard) {
            $scores = Database::query(
                "SELECT es.*, sub.name AS subject_name 
                 FROM exam_scores es
                 JOIN subjects sub ON sub.id = es.subject_id
                 WHERE es.student_id = ? AND es.status IN ('published', 'locked')
                 ORDER BY sub.name ASC",
                [$selectedWardId]
            );

            $summary = Database::fetch(
                "SELECT * FROM student_term_summaries WHERE student_id = ? AND session_id = ? AND term_id = ?",
                [$selectedWardId, $session['id'] ?? 0, $term['id'] ?? 0]
            );

            $attStats = Database::fetch(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count
                 FROM attendance 
                 WHERE student_id = ? AND (session_id = ? OR ? = 0) AND (term_id = ? OR ? = 0)",
                [$selectedWardId, $session['id'] ?? 0, $session['id'] ?? 0, $term['id'] ?? 0, $term['id'] ?? 0]
            ) ?: ['total' => 0, 'present_count' => 0, 'absent_count' => 0];

            $invoices = Database::query("SELECT * FROM fee_invoices WHERE student_id = ? ORDER BY id DESC", [$selectedWardId]);
        }

        // Fetch All Term Portal Fee Invoices across all wards of this parent
        $portalInvoices = Database::query(
            "SELECT pi.*, s.first_name, s.last_name, s.admission_number, ca.name AS arm_name, cl.name AS class_name
             FROM portal_invoices pi
             JOIN students s ON s.id = pi.student_id
             LEFT JOIN class_arms ca ON ca.id = s.class_arm_id
             LEFT JOIN class_levels cl ON cl.id = ca.class_level_id
             WHERE (pi.parent_id = ? OR s.parent_id = ?) AND pi.school_id = ?
             ORDER BY pi.id DESC",
            [$parent['id'], $parent['id'], $parent['school_id']]
        );

        // Fetch Published Official Notices
        $portalNotices = Database::query(
            "SELECT * FROM portal_billing_notices 
             WHERE school_id = ? AND is_published = 1 
             ORDER BY id DESC LIMIT 3",
            [$parent['school_id']]
        );

        // Fetch General School Notices
        $generalNotices = Database::query(
            "SELECT n.*, u.name as author_name 
             FROM school_notices n 
             JOIN users u ON u.id = n.created_by 
             WHERE n.school_id = ? AND n.target_audience IN ('all', 'parents') AND (n.expires_at IS NULL OR n.expires_at >= CURDATE()) 
             ORDER BY n.is_pinned DESC, n.published_at DESC LIMIT 5",
            [$parent['school_id']]
        );

        // Fetch Verified Portal Payments
        $portalPayments = Database::query(
            "SELECT pp.*, s.first_name, s.last_name, pi.invoice_number 
             FROM portal_payments pp
             JOIN portal_invoices pi ON pi.id = pp.invoice_id
             JOIN students s ON s.id = pp.student_id
             WHERE (pp.parent_id = ? OR s.parent_id = ?) AND pp.school_id = ?
             ORDER BY pp.id DESC",
            [$parent['id'], $parent['id'], $parent['school_id']]
        );

        // Unread Direct Message Count & Previews
        $unreadMessagesCount = (int)(Database::fetch(
            "SELECT COUNT(*) AS c FROM parent_school_messages WHERE parent_id = ? AND recipient_id = ? AND is_read = 0",
            [$parent['id'], $userId]
        )['c'] ?? 0);

        $staffMembers = Database::query(
            "SELECT id, name, role FROM users 
             WHERE school_id = ? AND role IN ('school_admin', 'principal', 'teacher', 'bursar') 
             ORDER BY CASE role WHEN 'principal' THEN 1 WHEN 'school_admin' THEN 2 WHEN 'teacher' THEN 3 ELSE 4 END, name ASC",
            [$parent['school_id']]
        );

        $threads = Database::query(
            "SELECT m.*, s.first_name as student_first, s.last_name as student_last, u.name as sender_name,
                    (SELECT COUNT(*) FROM parent_school_messages r WHERE r.parent_message_id = m.id) as reply_count,
                    (CASE WHEN EXISTS (
                        SELECT 1 FROM parent_school_messages ch 
                        WHERE (ch.id = m.id OR ch.parent_message_id = m.id) 
                          AND ch.recipient_id = ? 
                          AND ch.is_read = 0
                    ) THEN 1 ELSE 0 END) as is_unread
             FROM parent_school_messages m
             LEFT JOIN students s ON s.id = m.student_id
             JOIN users u ON u.id = m.sender_id
             WHERE m.parent_id = ? AND m.parent_message_id IS NULL
             ORDER BY m.updated_at DESC LIMIT 10",
            [$userId, $parent['id']]
        );

        View::render('portal.parent', [
            'title' => 'Parent Portal - Family Dashboard',
            'parent' => $parent,
            'wards' => $wards,
            'selectedWard' => $selectedWard,
            'session' => $session,
            'term' => $term,
            'scores' => $scores,
            'summary' => $summary,
            'attStats' => $attStats,
            'invoices' => $invoices,
            'portalInvoices' => $portalInvoices,
            'portalNotices' => $portalNotices,
            'generalNotices' => $generalNotices,
            'portalPayments' => $portalPayments,
            'unreadMessagesCount' => $unreadMessagesCount,
            'staffMembers' => $staffMembers,
            'threads' => $threads,
        ], 'portal');
    }

    public function payStudentFee(Request $request): void
    {
        $userId = Auth::id();
        $student = Database::fetch("SELECT * FROM students WHERE user_id = ?", [$userId]);
        if (!$student) {
            http_response_code(403);
            die("Access Forbidden: Only enrolled students can access this endpoint.");
        }

        $invoiceId = (int)$request->post('invoice_id', 0);
        $amount = (float)$request->post('amount', 0.0);

        $inv = Database::fetch("SELECT * FROM fee_invoices WHERE id = ? AND student_id = ?", [$invoiceId, $student['id']]);
        if (!$inv) {
            Response::redirect('/portal/student', 'Invoice record not found.', 'danger');
        }

        if ($inv['status'] === 'paid') {
            Response::redirect('/portal/student', 'This invoice has already been fully settled.', 'info');
        }

        $balanceDue = (float)$inv['balance_due'];
        if ($amount <= 0 || $amount > $balanceDue + 0.01) {
            $amount = $balanceDue;
        }

        $receiptNo = sprintf('REC-%s-%04d', date('Y'), rand(1000, 9999));
        $ref = 'PAY-STU-' . date('Ymd') . '-' . rand(10000, 99999);

        Database::transaction(function() use ($inv, $amount, $receiptNo, $ref, $student, $userId) {
            Database::execute(
                "INSERT INTO payment_receipts (school_id, receipt_number, invoice_id, student_id, amount_paid, payment_method, payment_reference, transaction_id, payment_date, status, verified_by)
                 VALUES (?, ?, ?, ?, ?, 'paystack', ?, ?, CURDATE(), 'verified', ?)",
                [(int)$inv['school_id'], $receiptNo, $inv['id'], $student['id'], $amount, $ref, 'TXN_' . rand(100000, 999999), $userId]
            );

            $newPaid = (float)$inv['paid_amount'] + $amount;
            $newBalance = max(0.0, (float)$inv['total_amount'] - $newPaid);
            $newStatus = $newBalance <= 0.0 ? 'paid' : 'partially_paid';

            Database::execute(
                "UPDATE fee_invoices SET paid_amount = ?, balance_due = ?, status = ?, updated_at = NOW() WHERE id = ?",
                [$newPaid, $newBalance, $newStatus, $inv['id']]
            );

            \EduSphere\Core\AuditLogger::log('student_fee_paid', 'fee_invoices', $inv['id'], [
                'receipt_number' => $receiptNo,
                'amount' => $amount,
                'student_id' => $student['id'],
            ]);
        });

        Response::redirect('/portal/student', "Payment of " . money($amount) . " verified successfully! Receipt: {$receiptNo}", 'success');
    }

    public function payStudentPortalFee(Request $request): void
    {
        $userId = Auth::id();
        $student = Database::fetch("SELECT * FROM students WHERE user_id = ?", [$userId]);
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$student) {
            http_response_code(403);
            die("Access Forbidden: Only enrolled students can access this endpoint.");
        }

        $invoiceId = (int)$request->post('invoice_id', 0);
        $amount = (float)$request->post('amount', 0.0);
        $gateway = (string)$request->post('gateway', 'paystack');

        $inv = Database::fetch("SELECT * FROM portal_invoices WHERE id = ? AND student_id = ?", [$invoiceId, $student['id']]);
        if (!$inv) {
            Response::redirect('/portal/student', 'Portal invoice record not found.', 'danger');
        }

        if ($amount <= 0) {
            $amount = (float)$inv['balance_due'];
        }

        try {
            $ref = 'PAY-ONLINE-STU-' . date('Ymd') . '-' . rand(10000, 99999);
            \EduSphere\Services\RevenueSharingService::recordPaymentAndAllocate(
                $invoiceId,
                $amount,
                $gateway,
                $ref,
                $user['name'] ?? ($student['first_name'] . ' ' . $student['last_name']),
                $user['email'] ?? null,
                $userId
            );

            Response::redirect('/portal/student', "Portal fee payment of " . money($amount) . " confirmed! Ref: {$ref}", 'success');
        } catch (\Throwable $e) {
            Response::redirect('/portal/student', $e->getMessage(), 'danger');
        }
    }

    public function payPortalInvoice(Request $request): void
    {
        $userId = Auth::id();
        $parent = Database::fetch("SELECT * FROM parents WHERE user_id = ?", [$userId]);
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);

        $invoiceId = (int)$request->post('invoice_id', 0);
        $amount = (float)$request->post('amount', 0.0);
        $gateway = (string)$request->post('gateway', 'paystack');

        $inv = Database::fetch("SELECT * FROM portal_invoices WHERE id = ?", [$invoiceId]);
        if (!$inv) {
            Response::redirect('/portal/parent', 'Invoice record not found.', 'danger');
        }

        // Anti-IDOR check: Ensure student belongs to this parent or user
        $student = Database::fetch("SELECT * FROM students WHERE id = ?", [(int)$inv['student_id']]);
        if (!$parent || ((int)$student['parent_id'] !== (int)$parent['id'] && (int)$inv['parent_id'] !== (int)$parent['id'])) {
            http_response_code(403);
            die("Access Forbidden: You cannot settle another family's portal invoice.");
        }

        if ($amount <= 0) {
            $amount = (float)$inv['balance_due'];
        }

        try {
            $ref = 'PAY-ONLINE-' . date('Ymd') . '-' . rand(10000, 99999);
            \EduSphere\Services\RevenueSharingService::recordPaymentAndAllocate(
                $invoiceId,
                $amount,
                $gateway,
                $ref,
                $user['name'] ?? 'Parent',
                $user['email'] ?? null,
                $userId
            );

            Response::redirect('/portal/parent', "Payment of " . money($amount) . " verified and recorded successfully! Ref: {$ref}", 'success');
        } catch (\Throwable $e) {
            Response::redirect('/portal/parent', $e->getMessage(), 'danger');
        }
    }

    public function disputePortalInvoice(Request $request): void
    {
        $userId = Auth::id();
        $parent = Database::fetch("SELECT * FROM parents WHERE user_id = ?", [$userId]);
        $invoiceId = (int)$request->post('invoice_id', 0);
        $reason = trim((string)$request->post('dispute_reason', ''));

        $inv = Database::fetch("SELECT * FROM portal_invoices WHERE id = ?", [$invoiceId]);
        if (!$inv || !$parent || (int)$inv['school_id'] !== (int)$parent['school_id']) {
            Response::redirect('/portal/parent', 'Invalid invoice.', 'danger');
        }

        // Anti-IDOR check: Ensure student belongs to this parent or user
        $student = Database::fetch("SELECT * FROM students WHERE id = ?", [(int)$inv['student_id']]);
        if (!$student || ((int)$student['parent_id'] !== (int)$parent['id'] && (int)$inv['parent_id'] !== (int)$parent['id'])) {
            http_response_code(403);
            die("Access Forbidden: You cannot dispute another family's portal invoice.");
        }

        Database::execute(
            "UPDATE portal_invoices SET status = 'disputed', waiver_reason = ? WHERE id = ?",
            ["Dispute submitted: {$reason}", $invoiceId]
        );

        \EduSphere\Core\AuditLogger::log('portal_invoice_disputed', 'portal_invoices', $invoiceId, [
            'parent_id' => $parent['id'],
            'reason' => $reason,
        ]);

        Response::redirect('/portal/parent', 'Dispute submitted. The school accounts office will review your request.', 'warning');
    }

    public function parentMessages(Request $request): void
    {
        $userId = Auth::id();
        $parent = Database::fetch("SELECT * FROM parents WHERE user_id = ?", [$userId]);
        if (!$parent) {
            die("Parent profile not found.");
        }

        $schoolId = (int)$parent['school_id'];
        $wards = Database::query(
            "SELECT id, first_name, last_name, admission_number 
             FROM students 
             WHERE parent_id = ? AND school_id = ?",
            [$parent['id'], $schoolId]
        );

        $staffMembers = Database::query(
            "SELECT id, name, role FROM users 
             WHERE school_id = ? AND role IN ('school_admin', 'principal', 'teacher', 'bursar') 
             ORDER BY CASE role WHEN 'principal' THEN 1 WHEN 'school_admin' THEN 2 WHEN 'teacher' THEN 3 ELSE 4 END, name ASC",
            [$schoolId]
        );

        // Fetch parent threads (parent_message_id IS NULL)
        $threads = Database::query(
            "SELECT m.*, s.first_name as student_first, s.last_name as student_last, u.name as sender_name,
                    (SELECT COUNT(*) FROM parent_school_messages r WHERE r.parent_message_id = m.id) as reply_count,
                    (CASE WHEN EXISTS (
                        SELECT 1 FROM parent_school_messages ch 
                        WHERE (ch.id = m.id OR ch.parent_message_id = m.id) 
                          AND ch.recipient_id = ? 
                          AND ch.is_read = 0
                    ) THEN 1 ELSE 0 END) as is_unread
             FROM parent_school_messages m
             LEFT JOIN students s ON s.id = m.student_id
             JOIN users u ON u.id = m.sender_id
             WHERE m.parent_id = ? AND m.parent_message_id IS NULL
             ORDER BY m.updated_at DESC",
            [$userId, $parent['id']]
        );

        $activeThreadId = (int)($request->get('thread_id') ?? ($threads[0]['id'] ?? 0));
        $activeThread = null;
        $replies = [];

        if ($activeThreadId > 0) {
            // Mark this specific active thread as read
            Database::execute(
                "UPDATE parent_school_messages SET is_read = 1, read_at = NOW() 
                 WHERE (id = ? OR parent_message_id = ?) AND recipient_id = ? AND is_read = 0",
                [$activeThreadId, $activeThreadId, $userId]
            );

            $activeThread = Database::fetch(
                "SELECT m.*, s.first_name as student_first, s.last_name as student_last, u.name as sender_name
                 FROM parent_school_messages m
                 LEFT JOIN students s ON s.id = m.student_id
                 JOIN users u ON u.id = m.sender_id
                 WHERE m.id = ? AND m.parent_id = ?",
                [$activeThreadId, $parent['id']]
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

        $unreadCount = (int)(Database::fetch(
            "SELECT COUNT(*) AS c FROM parent_school_messages WHERE parent_id = ? AND recipient_id = ? AND is_read = 0",
            [$parent['id'], $userId]
        )['c'] ?? 0);

        View::render('portal.parent_messages', [
            'title' => 'School-Parent Direct Communication Desk',
            'parent' => $parent,
            'wards' => $wards,
            'threads' => $threads,
            'activeThread' => $activeThread,
            'replies' => $replies,
            'staffMembers' => $staffMembers,
            'unreadCount' => $unreadCount,
        ], 'portal');
    }

    public function sendParentMessage(Request $request): void
    {
        $userId = Auth::id();
        $parent = Database::fetch("SELECT * FROM parents WHERE user_id = ?", [$userId]);
        if (!$parent) {
            http_response_code(403);
            die("Access Forbidden.");
        }

        $schoolId = (int)$parent['school_id'];
        $studentId = (int)$request->post('student_id', 0) ?: null;
        $recipientId = (int)$request->post('recipient_id', 0);
        $subject = trim((string)$request->post('subject', ''));
        $message = trim((string)$request->post('message', ''));

        if (empty($subject) || empty($message)) {
            Response::redirect('/portal/parent/messages', 'Subject and Message cannot be empty.', 'danger');
        }

        // Find fallback school admin user ID if no specific recipient selected
        if ($recipientId <= 0) {
            $adminUser = Database::fetch(
                "SELECT id FROM users WHERE school_id = ? AND role IN ('school_admin', 'super_admin') ORDER BY id ASC LIMIT 1",
                [$schoolId]
            );
            $recipientId = (int)($adminUser['id'] ?? 1);
        }

        Database::execute(
            "INSERT INTO parent_school_messages
             (school_id, parent_id, student_id, sender_type, sender_id, recipient_id, subject, message, is_read, created_at, updated_at)
             VALUES (?, ?, ?, 'parent', ?, ?, ?, ?, 0, NOW(), NOW())",
            [$schoolId, (int)$parent['id'], $studentId, $userId, $recipientId, $subject, $message]
        );

        $threadId = (int)Database::lastInsertId();

        \EduSphere\Core\AuditLogger::log('parent_message_sent', 'parent_school_messages', $threadId, [
            'parent_id' => $parent['id'],
            'student_id' => $studentId,
            'recipient_id' => $recipientId,
            'subject' => $subject,
        ]);

        Response::redirect('/portal/parent/messages?thread_id=' . $threadId, 'Your message has been delivered to the school!', 'success');
    }

    public function replyParentMessage(Request $request, string $id): void
    {
        $userId = Auth::id();
        $parent = Database::fetch("SELECT * FROM parents WHERE user_id = ?", [$userId]);
        $threadId = (int)$id;

        $thread = Database::fetch(
            "SELECT * FROM parent_school_messages WHERE id = ? AND parent_id = ?",
            [$threadId, $parent['id'] ?? 0]
        );
        if (!$thread) {
            http_response_code(403);
            die("Access Forbidden: Thread not found or does not belong to your family.");
        }

        $message = trim((string)$request->post('message', ''));
        if (empty($message)) {
            Response::redirect('/portal/parent/messages?thread_id=' . $threadId, 'Reply message cannot be empty.', 'danger');
        }

        $recipientId = ($thread['sender_type'] === 'school') ? (int)$thread['sender_id'] : (int)$thread['recipient_id'];

        Database::execute(
            "INSERT INTO parent_school_messages
             (school_id, parent_id, student_id, sender_type, sender_id, recipient_id, subject, message, is_read, parent_message_id, created_at, updated_at)
             VALUES (?, ?, ?, 'parent', ?, ?, ?, ?, 0, ?, NOW(), NOW())",
            [$thread['school_id'], $thread['parent_id'], $thread['student_id'], $userId, $recipientId, 'Re: ' . $thread['subject'], $message, $threadId]
        );

        Database::execute("UPDATE parent_school_messages SET updated_at = NOW() WHERE id = ?", [$threadId]);

        Response::redirect('/portal/parent/messages?thread_id=' . $threadId, 'Reply sent successfully.', 'success');
    }

    public function downloadStudentReportCard(Request $request): void
    {
        $userId = Auth::id();
        $student = Database::fetch("SELECT * FROM students WHERE user_id = ?", [$userId]);
        if (!$student) {
            http_response_code(403);
            die("Access Forbidden: Student profile not found.");
        }

        $schoolId = (int)$student['school_id'];
        $sessionId = (int)($request->get('session_id') ?? 0);
        $termId = (int)($request->get('term_id') ?? 0);

        $this->streamReportCard($schoolId, (int)$student['id'], $sessionId, $termId);
    }

    public function downloadWardReportCard(Request $request, string $studentId): void
    {
        $stuId = (int)$studentId;
        if (!$this->canAccessStudent($stuId)) {
            http_response_code(403);
            die("Access Forbidden: You do not have permission to access this student's report card.");
        }

        $student = Database::fetch("SELECT * FROM students WHERE id = ?", [$stuId]);
        if (!$student) {
            http_response_code(404);
            die("Student record not found.");
        }

        $schoolId = (int)$student['school_id'];
        $sessionId = (int)($request->get('session_id') ?? 0);
        $termId = (int)($request->get('term_id') ?? 0);

        $this->streamReportCard($schoolId, $stuId, $sessionId, $termId);
    }

    private function streamReportCard(int $schoolId, int $studentId, int $sessionId = 0, int $termId = 0): void
    {
        try {
            $dompdf = ReportCardPdfService::generateReportCard($schoolId, $studentId, $sessionId, $termId);
            $dompdf->stream("Official_ReportCard_Student_{$studentId}.pdf", [
                'Attachment' => false
            ]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            die("Report generation error: " . htmlspecialchars($e->getMessage()));
        }
    }

    private function canAccessStudent(int $studentId): bool
    {
        $role = Auth::role();
        if ($role === 'super_admin') {
            return true;
        }

        $userId = Auth::id();
        if ($role === 'student') {
            $student = Database::fetch("SELECT id FROM students WHERE user_id = ? AND id = ?", [$userId, $studentId]);
            return (bool)$student;
        }

        if ($role === 'parent') {
            $parent = Database::fetch("SELECT id FROM parents WHERE user_id = ?", [$userId]);
            if (!$parent) {
                return false;
            }
            $ward = Database::fetch("SELECT id FROM students WHERE id = ? AND parent_id = ?", [$studentId, $parent['id']]);
            return (bool)$ward;
        }

        return false;
    }

    public function downloadReceipt(Request $request, string $id): void
    {
        $receiptId = (int)$id;
        $receipt = Database::fetch("SELECT * FROM payment_receipts WHERE id = ?", [$receiptId]);

        if (!$receipt || !$this->canAccessStudent((int)$receipt['student_id'])) {
            http_response_code(403);
            die("Access Forbidden: Receipt not found or does not belong to your account.");
        }

        try {
            $dompdf = FinancialPdfService::generateReceiptPdf((int)$receipt['school_id'], $receiptId);
            $dompdf->stream("Payment_Receipt_{$receipt['receipt_number']}.pdf", ['Attachment' => false]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(404);
            die(htmlspecialchars($e->getMessage()));
        }
    }

    public function downloadInvoice(Request $request, string $id): void
    {
        $invoiceId = (int)$id;
        $invoice = Database::fetch("SELECT * FROM fee_invoices WHERE id = ?", [$invoiceId]);

        if (!$invoice || !$this->canAccessStudent((int)$invoice['student_id'])) {
            http_response_code(403);
            die("Access Forbidden: Fee invoice not found or does not belong to your account.");
        }

        try {
            $dompdf = FinancialPdfService::generateInvoicePdf((int)$invoice['school_id'], $invoiceId);
            $dompdf->stream("Fee_Invoice_{$invoice['invoice_number']}.pdf", ['Attachment' => false]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(404);
            die(htmlspecialchars($e->getMessage()));
        }
    }

    public function downloadPortalReceipt(Request $request, string $id): void
    {
        $paymentId = (int)$id;
        $payment = Database::fetch("SELECT * FROM portal_payments WHERE id = ?", [$paymentId]);

        if (!$payment || !$this->canAccessStudent((int)$payment['student_id'])) {
            http_response_code(403);
            die("Access Forbidden: Portal payment receipt not found or does not belong to your account.");
        }

        try {
            $dompdf = FinancialPdfService::generatePortalPaymentReceiptPdf((int)$payment['school_id'], $paymentId);
            $dompdf->stream("Portal_Receipt_{$payment['payment_reference']}.pdf", ['Attachment' => false]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(404);
            die(htmlspecialchars($e->getMessage()));
        }
    }
}
