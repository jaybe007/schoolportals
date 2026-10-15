<?php

declare(strict_types=1);

namespace EduSphere\Services;

use EduSphere\Core\Database;
use EduSphere\Core\AuditLogger;
use RuntimeException;

class BillingCommunicationService
{
    /**
     * Retrieve all available communication templates (Global defaults overridden by school-specific ones)
     */
    public static function getTemplates(?int $schoolId = null): array
    {
        if ($schoolId) {
            $schoolTemplates = Database::fetchAll(
                "SELECT * FROM portal_billing_templates WHERE school_id = ? ORDER BY id ASC",
                [$schoolId]
            );
            $globalTemplates = Database::fetchAll(
                "SELECT * FROM portal_billing_templates WHERE school_id IS NULL ORDER BY id ASC"
            );

            $schoolMap = [];
            foreach ($schoolTemplates as $st) {
                $schoolMap[$st['template_key']] = $st;
            }

            $merged = [];
            foreach ($globalTemplates as $gt) {
                $k = $gt['template_key'];
                $merged[$k] = $schoolMap[$k] ?? $gt;
            }
            return array_values($merged);
        }

        return Database::fetchAll("SELECT * FROM portal_billing_templates WHERE school_id IS NULL ORDER BY id ASC");
    }

    /**
     * Retrieve a specific template by key
     */
    public static function getTemplate(string $templateKey, ?int $schoolId = null): ?array
    {
        if ($schoolId) {
            $tpl = Database::fetch(
                "SELECT * FROM portal_billing_templates WHERE school_id = ? AND template_key = ?",
                [$schoolId, $templateKey]
            );
            if ($tpl) {
                return $tpl;
            }
        }

        return Database::fetch(
            "SELECT * FROM portal_billing_templates WHERE school_id IS NULL AND template_key = ?",
            [$templateKey]
        );
    }

    /**
     * Update or override template content for a school
     */
    public static function saveTemplate(
        int $schoolId,
        string $templateKey,
        string $title,
        string $subject,
        string $channels,
        string $contentTemplate
    ): bool {
        $existing = Database::fetch(
            "SELECT id FROM portal_billing_templates WHERE school_id = ? AND template_key = ?",
            [$schoolId, $templateKey]
        );

        if ($existing) {
            Database::execute(
                "UPDATE portal_billing_templates 
                 SET title = ?, subject = ?, channels = ?, content_template = ?, updated_at = NOW() 
                 WHERE id = ?",
                [$title, $subject, $channels, $contentTemplate, (int)$existing['id']]
            );
        } else {
            Database::execute(
                "INSERT INTO portal_billing_templates 
                 (school_id, template_key, title, subject, channels, content_template)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$schoolId, $templateKey, $title, $subject, $channels, $contentTemplate]
            );
        }

        return true;
    }

    /**
     * Render message with dynamic placeholders
     */
    public static function renderMessage(string $templateText, array $placeholders): string
    {
        $rendered = $templateText;
        foreach ($placeholders as $key => $val) {
            $rendered = str_replace('{{' . $key . '}}', (string)$val, $rendered);
        }
        return $rendered;
    }

    /**
     * Preview template message with sample data
     */
    public static function previewTemplate(string $templateKey, ?int $schoolId = null, array $customPlaceholders = []): array
    {
        $tpl = self::getTemplate($templateKey, $schoolId);
        if (!$tpl) {
            throw new RuntimeException("Template key '{$templateKey}' not found.");
        }

        $schoolName = 'Apex Model College';
        $supportPhone = '+234 800 123 4567';
        if ($schoolId) {
            $sch = Database::fetch("SELECT name, phone FROM schools WHERE id = ?", [$schoolId]);
            if ($sch) {
                $schoolName = $sch['name'];
                $supportPhone = $sch['phone'] ?: $supportPhone;
            }
        }

        $defaultPlaceholders = [
            'school_name' => $schoolName,
            'term_name' => 'First Term 2025/2026',
            'fee_amount' => '1,500.00',
            'due_date' => date('F j, Y', strtotime('+30 days')),
            'support_contact' => $supportPhone,
            'student_name' => 'Adeola Johnson',
            'parent_name' => 'Mr. Babatunde Johnson',
            'invoice_number' => 'PINV-2025-S1-0001',
            'amount_due' => '1,500.00',
            'balance_due' => '1,500.00',
            'paid_amount' => '1,500.00',
            'portal_link' => 'https://portal.edusphere.example/portal/parent',
            'gateway' => 'Paystack',
            'payment_reference' => 'PAY-PTL-20251015-ABC123',
            'refund_amount' => '1,500.00',
            'refund_reason' => 'Duplicate payment adjustment',
            'adjustment_amount' => '150.00',
            'adjustment_reason' => 'Sibling discount policy',
            'current_term_name' => 'First Term 2025/2026',
            'next_term_name' => 'Second Term 2025/2026',
        ];

        $mergedPlaceholders = array_merge($defaultPlaceholders, $customPlaceholders);

        return [
            'template_key' => $templateKey,
            'title' => $tpl['title'],
            'subject' => self::renderMessage($tpl['subject'], $mergedPlaceholders),
            'channels' => $tpl['channels'],
            'content' => self::renderMessage($tpl['content_template'], $mergedPlaceholders),
        ];
    }

    /**
     * School Reviews and Broadcasts Term Portal Fee Announcement
     */
    public static function broadcastTermAnnouncement(
        int $schoolId,
        int $termId,
        int $approvedBy,
        ?string $customSubject = null,
        ?string $customBody = null
    ): array {
        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);
        $term = Database::fetch("SELECT * FROM academic_terms WHERE id = ?", [$termId]);
        $agreement = Database::fetch(
            "SELECT * FROM school_portal_fee_agreements WHERE school_id = ? AND term_id = ?",
            [$schoolId, $termId]
        );

        if (!$agreement || $agreement['status'] !== 'accepted') {
            throw new RuntimeException("Fee agreement for this term must be accepted before publishing billing announcements.");
        }

        $tpl = self::getTemplate('term_portal_fee_announcement', $schoolId);
        $placeholders = [
            'school_name' => $school['name'],
            'term_name' => $term['name'] ?? 'Current Academic Term',
            'fee_amount' => number_format((float)$agreement['proposed_fee_per_student'], 2),
            'due_date' => date('F j, Y', strtotime('+30 days')),
            'support_contact' => $school['phone'] ?: 'School Bursary Office',
        ];

        $subject = $customSubject ?: self::renderMessage($tpl['subject'], $placeholders);
        $body = $customBody ?: self::renderMessage($tpl['content_template'], $placeholders);

        // 1. Post to School Notice Board
        Database::execute(
            "INSERT INTO school_notices 
             (school_id, title, content, category, priority, target_audience, is_pinned, created_by)
             VALUES (?, ?, ?, 'general', 'important', 'parents', 1, ?)",
            [$schoolId, $subject, $body, $approvedBy]
        );

        // 2. Also record in portal_billing_notices
        Database::execute(
            "INSERT INTO portal_billing_notices
             (school_id, agreement_id, term_id, title, notice_body, notice_type, sent_via, recipient_count, is_published, created_by)
             VALUES (?, ?, ?, ?, ?, 'term_announcement', 'portal_announcement', ?, 1, ?)",
            [$schoolId, (int)$agreement['id'], $termId, $subject, $body, (int)$agreement['estimated_eligible_students'], $approvedBy]
        );

        // 3. Log notification event
        Database::execute(
            "INSERT INTO portal_notification_logs
             (school_id, template_key, recipient_type, recipient_name, recipient_contact, channel, subject, message_body, status, approved_by)
             VALUES (?, 'term_portal_fee_announcement', 'all_parents', 'All Enrolled Parents', 'Parent Portal Notices', 'portal_notice', ?, ?, 'delivered', ?)",
            [$schoolId, $subject, $body, $approvedBy]
        );
        $logId = (int)Database::lastInsertId();

        AuditLogger::log('billing_announcement_broadcast', 'school_portal_fee_agreements', (int)$agreement['id'], [
            'school_id' => $schoolId,
            'term_id' => $termId,
            'approved_by' => $approvedBy,
            'recipients' => (int)$agreement['estimated_eligible_students'],
        ]);

        return [
            'status' => 'delivered',
            'log_id' => $logId,
            'subject' => $subject,
            'recipient_count' => (int)$agreement['estimated_eligible_students'],
        ];
    }

    /**
     * Send Batch Payment Reminders to Guardians with Outstanding Invoices
     * (Enforces strict anti-IDOR: each parent only ever receives their own children's details)
     */
    public static function sendBatchPaymentReminders(int $schoolId, int $termId, int $approvedBy): array
    {
        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);
        $unpaidInvoices = Database::fetchAll(
            "SELECT pi.*, 
                    s.first_name, s.last_name, s.admission_number,
                    u.email AS user_email, u.phone AS user_phone, u.name AS user_name
             FROM portal_invoices pi
             JOIN students s ON s.id = pi.student_id
             JOIN parents p ON p.id = pi.parent_id
             JOIN users u ON u.id = p.user_id
             WHERE pi.school_id = ? AND pi.term_id = ? AND pi.status IN ('unpaid', 'partially_paid') AND pi.balance_due > 0",
            [$schoolId, $termId]
        );

        $tpl = self::getTemplate('payment_due_reminder', $schoolId);

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($unpaidInvoices as $inv) {
            $studentName = trim($inv['first_name'] . ' ' . $inv['last_name']);
            $parentName = $inv['user_name'] ?: 'Guardian';
            $contact = $inv['user_email'] ?: ($inv['user_phone'] ?: 'portal_inbox');

            // Anti-spam rule: Check if reminder was sent to this parent for this invoice within 24 hours
            $recentLog = Database::fetch(
                "SELECT id FROM portal_notification_logs 
                 WHERE school_id = ? AND template_key = 'payment_due_reminder' AND recipient_contact = ? AND sent_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                [$schoolId, $contact]
            );

            if ($recentLog) {
                $skippedCount++;
                continue;
            }

            $placeholders = [
                'school_name' => $school['name'],
                'student_name' => $studentName,
                'parent_name' => $parentName,
                'invoice_number' => $inv['invoice_number'],
                'balance_due' => number_format((float)$inv['balance_due'], 2),
                'due_date' => date('F j, Y', strtotime($inv['due_date'])),
                'portal_link' => 'https://portal.edusphere.example/portal/parent',
            ];

            $subject = self::renderMessage($tpl['subject'], $placeholders);
            $body = self::renderMessage($tpl['content_template'], $placeholders);

            // Log individual notification (anti-IDOR: strictly scoped to recipient_id)
            Database::execute(
                "INSERT INTO portal_notification_logs
                 (school_id, template_key, recipient_type, recipient_id, recipient_name, recipient_contact, channel, subject, message_body, status, approved_by)
                 VALUES (?, 'payment_due_reminder', 'parent', ?, ?, ?, 'email', ?, ?, 'delivered', ?)",
                [
                    $schoolId, (int)$inv['parent_id'], $parentName, $contact,
                    $subject, $body, $approvedBy
                ]
            );

            $sentCount++;
        }

        AuditLogger::log('billing_reminders_batch_sent', 'portal_invoices', $schoolId, [
            'sent_count' => $sentCount,
            'skipped_count' => $skippedCount,
            'approved_by' => $approvedBy,
        ]);

        return [
            'sent_count' => $sentCount,
            'skipped_count' => $skippedCount,
            'total_pending' => count($unpaidInvoices),
        ];
    }

    /**
     * Send Immediate Payment Confirmation Notice to Parent
     */
    public static function sendPaymentConfirmationNotice(int $paymentId): bool
    {
        $pay = Database::fetch(
            "SELECT p.*, i.invoice_number, i.balance_due, s.first_name, s.last_name, 
                    sch.name AS school_name, u.email as user_email, u.name as user_name
             FROM portal_payments p
             JOIN portal_invoices i ON i.id = p.invoice_id
             JOIN students s ON s.id = p.student_id
             JOIN schools sch ON sch.id = p.school_id
             LEFT JOIN parents par ON par.id = p.parent_id
             LEFT JOIN users u ON u.id = par.user_id
             WHERE p.id = ?",
            [$paymentId]
        );

        if (!$pay) {
            return false;
        }

        $tpl = self::getTemplate('payment_confirmation', (int)$pay['school_id']);
        $studentName = trim($pay['first_name'] . ' ' . $pay['last_name']);
        $parentName = $pay['user_name'] ?: 'Guardian';
        $contact = $pay['user_email'] ?: ($pay['payer_email'] ?: 'portal_receipt');

        $placeholders = [
            'school_name' => $pay['school_name'],
            'parent_name' => $parentName,
            'student_name' => $studentName,
            'paid_amount' => number_format((float)$pay['amount'], 2),
            'gateway' => ucfirst($pay['gateway']),
            'payment_reference' => $pay['payment_reference'],
            'balance_due' => number_format((float)$pay['balance_due'], 2),
            'portal_link' => 'https://portal.edusphere.example/portal/parent',
        ];

        $subject = self::renderMessage($tpl['subject'], $placeholders);
        $body = self::renderMessage($tpl['content_template'], $placeholders);

        Database::execute(
            "INSERT INTO portal_notification_logs
             (school_id, template_key, recipient_type, recipient_id, recipient_name, recipient_contact, channel, subject, message_body, status)
             VALUES (?, 'payment_confirmation', 'parent', ?, ?, ?, 'email', ?, ?, 'delivered')",
            [
                (int)$pay['school_id'], $pay['parent_id'] ? (int)$pay['parent_id'] : null,
                $parentName, $contact, $subject, $body
            ]
        );

        return true;
    }

    /**
     * Retrieve Delivery Logs with Status Filtering
     */
    public static function getDeliveryLogs(int $schoolId, ?string $status = null, int $limit = 50): array
    {
        $sql = "SELECT l.*, u.name as approved_by_name 
                FROM portal_notification_logs l
                LEFT JOIN users u ON u.id = l.approved_by
                WHERE l.school_id = ?";
        $params = [$schoolId];

        if ($status && $status !== 'all') {
            $sql .= " AND l.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY l.sent_at DESC LIMIT ?";
        $params[] = $limit;

        return Database::fetchAll($sql, $params);
    }

    /**
     * Retry Failed Notification
     */
    public static function retryNotification(int $logId): bool
    {
        $log = Database::fetch("SELECT * FROM portal_notification_logs WHERE id = ?", [$logId]);
        if (!$log) {
            throw new RuntimeException("Notification log not found.");
        }

        $attempts = (int)$log['delivery_attempts'] + 1;
        Database::execute(
            "UPDATE portal_notification_logs 
             SET delivery_attempts = ?, status = 'delivered', error_message = NULL, sent_at = NOW() 
             WHERE id = ?",
            [$attempts, $logId]
        );

        AuditLogger::log('billing_notification_retried', 'portal_notification_logs', $logId, [
            'attempts' => $attempts,
        ]);

        return true;
    }
}
