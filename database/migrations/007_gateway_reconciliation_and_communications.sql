-- ============================================================================
-- EduSphere SaaS Migration 007: Payment Gateway Webhooks, Settlement Reconciliation,
-- Refunds, Itemized Invoice Line Items, and Billing Communication Engine
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Extend Users Role ENUM to include finance_admin and school_owner
ALTER TABLE users 
    MODIFY COLUMN role ENUM(
        'super_admin',
        'finance_admin',
        'school_admin',
        'school_owner',
        'principal',
        'registrar',
        'teacher',
        'bursar',
        'student',
        'parent',
        'sales_rep',
        'sales_manager'
    ) NOT NULL;

-- 2. Extend Portal Payments with Disputed status and Gateway Response metadata
ALTER TABLE portal_payments 
    MODIFY COLUMN status ENUM('pending', 'verified', 'failed', 'reversed', 'refunded', 'disputed') NOT NULL DEFAULT 'verified';

ALTER TABLE portal_payments 
    ADD COLUMN IF NOT EXISTS channel VARCHAR(50) NOT NULL DEFAULT 'card',
    ADD COLUMN IF NOT EXISTS gateway_response TEXT NULL;

-- 3. Payment Gateway Webhook & Server Event Log (Audit trail for raw gateway payloads)
CREATE TABLE IF NOT EXISTS payment_gateway_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id VARCHAR(100) NULL,
    gateway VARCHAR(50) NOT NULL DEFAULT 'paystack',
    event_type VARCHAR(100) NOT NULL,
    reference VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    signature VARCHAR(255) NULL,
    status ENUM('received', 'processed', 'ignored', 'failed') NOT NULL DEFAULT 'received',
    processed_at TIMESTAMP NULL,
    error_message TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pge_ref (reference),
    INDEX idx_pge_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Authorized Portal Refunds and Reversals
CREATE TABLE IF NOT EXISTS portal_refunds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    invoice_id INT NOT NULL,
    school_id INT NOT NULL,
    refund_reference VARCHAR(100) NOT NULL UNIQUE,
    gateway_refund_id VARCHAR(100) NULL,
    amount DECIMAL(12, 2) NOT NULL,
    reason VARCHAR(255) NOT NULL,
    status ENUM('requested', 'approved', 'processed', 'rejected') NOT NULL DEFAULT 'requested',
    requested_by INT NOT NULL,
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    processed_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES portal_payments(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES portal_invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_refund_school (school_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Gateway Settlement Reconciliation Ledger
CREATE TABLE IF NOT EXISTS gateway_settlement_reconciliations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    settlement_batch_id VARCHAR(100) NOT NULL UNIQUE,
    gateway VARCHAR(50) NOT NULL DEFAULT 'paystack',
    settlement_date DATE NOT NULL,
    total_transactions INT NOT NULL DEFAULT 0,
    gross_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    gateway_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    net_settled_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    internal_matched_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    discrepancy_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    reconciliation_status ENUM('matched', 'discrepancy_found', 'manual_resolved') NOT NULL DEFAULT 'matched',
    reconciled_by INT NULL,
    reconciled_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reconciled_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_recon_date (settlement_date, reconciliation_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Itemized Invoice Line Items
CREATE TABLE IF NOT EXISTS portal_invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    item_type ENUM('base_fee', 'sibling_discount', 'scholarship_waiver', 'indigent_waiver', 'midterm_proration', 'tax', 'surcharge') NOT NULL DEFAULT 'base_fee',
    description VARCHAR(255) NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    quantity INT NOT NULL DEFAULT 1,
    amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES portal_invoices(id) ON DELETE CASCADE,
    INDEX idx_inv_item (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Configurable Portal Billing Communication Templates
CREATE TABLE IF NOT EXISTS portal_billing_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NULL,
    template_key VARCHAR(60) NOT NULL,
    title VARCHAR(200) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    channels VARCHAR(100) NOT NULL DEFAULT 'email,sms,portal',
    content_template TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_template_school_key (school_id, template_key),
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Communication Delivery Logs & Audit Trail
CREATE TABLE IF NOT EXISTS portal_notification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    template_key VARCHAR(60) NOT NULL,
    recipient_type ENUM('parent', 'student', 'all_parents', 'guardian') NOT NULL DEFAULT 'parent',
    recipient_id INT NULL,
    recipient_name VARCHAR(150) NOT NULL,
    recipient_contact VARCHAR(150) NOT NULL,
    channel ENUM('email', 'sms', 'portal_notice', 'in_app') NOT NULL DEFAULT 'portal_notice',
    subject VARCHAR(255) NOT NULL,
    message_body TEXT NOT NULL,
    status ENUM('queued', 'sent', 'delivered', 'failed', 'retrying') NOT NULL DEFAULT 'delivered',
    delivery_attempts INT NOT NULL DEFAULT 1,
    error_message TEXT NULL,
    approved_by INT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_pnl_school (school_id, status),
    INDEX idx_pnl_key (template_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Seed Global Communication Templates (All 8 Core Lifecycle Templates)
-- ============================================================================

INSERT IGNORE INTO portal_billing_templates 
(school_id, template_key, title, subject, channels, content_template)
VALUES
(
    NULL,
    'term_portal_fee_announcement',
    'Term Portal Fee Announcement',
    'Important Notice: Term Portal Fee Schedule for {{term_name}}',
    'email,sms,portal',
    'Dear Parents & Guardians of {{school_name}},\n\nPlease be informed that the approved Student Portal Fee for {{term_name}} is ₦{{fee_amount}} per student.\n\nPurpose: This fee covers continuous academic portal access, CBT examinations, digitized term report cards, attendance tracking, and school communication services.\n\nDue Date: {{due_date}}\nPayment Methods: Secure online payment via Paystack (Debit Card, USSD, Bank Transfer) or through the Parent Portal.\n\nFor support, please contact the school bursary at {{support_contact}}.'
),
(
    NULL,
    'student_invoice_notification',
    'Individual Student Invoice Notification',
    'Invoice Issued: {{student_name}} - {{term_name}} Portal Fee',
    'email,sms,portal',
    'Dear {{parent_name}},\n\nA portal fee invoice #{{invoice_number}} of ₦{{amount_due}} has been generated for your ward, {{student_name}} for {{term_name}}.\n\nDue Date: {{due_date}}\nYou can inspect the itemized breakdown and make immediate payment via your Parent Portal: {{portal_link}}.\n\nThank you for your prompt partnership.'
),
(
    NULL,
    'payment_due_reminder',
    'Payment Due Reminder',
    'Reminder: Outstanding Term Portal Fee for {{student_name}}',
    'email,sms,portal',
    'Dear {{parent_name}},\n\nThis is a friendly reminder that the portal fee of ₦{{balance_due}} for {{student_name}} (Invoice #{{invoice_number}}) is due on {{due_date}}.\n\nPlease settle promptly to ensure uninterrupted access to learning materials and term assessments: {{portal_link}}.'
),
(
    NULL,
    'payment_confirmation',
    'Successful Payment Confirmation',
    'Payment Confirmed: ₦{{paid_amount}} Received for {{student_name}}',
    'email,sms,portal',
    'Dear {{parent_name}},\n\nWe have received your payment of ₦{{paid_amount}} via {{gateway}} (Ref: {{payment_reference}}) for {{student_name}}.\n\nRemaining Balance: ₦{{balance_due}}.\nOfficial electronic receipt is available in your portal: {{portal_link}}.'
),
(
    NULL,
    'outstanding_balance_notice',
    'Outstanding Balance Notice',
    'Overdue Notice: Pending Portal Fee for {{student_name}}',
    'email,sms,portal',
    'Dear {{parent_name}},\n\nOur records indicate an outstanding portal fee balance of ₦{{balance_due}} for {{student_name}} regarding {{term_name}}.\n\nPlease complete payment as soon as possible via {{portal_link}} or contact the bursary office.'
),
(
    NULL,
    'refund_confirmation',
    'Refund Confirmation',
    'Refund Processed: ₦{{refund_amount}} for Invoice #{{invoice_number}}',
    'email,sms,portal',
    'Dear {{parent_name}},\n\nA refund of ₦{{refund_amount}} has been approved and processed for payment reference {{payment_reference}} (Reason: {{refund_reason}}).\n\nThe funds have been credited back via {{gateway}}.'
),
(
    NULL,
    'billing_correction',
    'Billing Correction Notice',
    'Notice of Billing Adjustment: Invoice #{{invoice_number}}',
    'email,sms,portal',
    'Dear {{parent_name}},\n\nPlease note that an authorized adjustment of ₦{{adjustment_amount}} has been applied to Invoice #{{invoice_number}} for {{student_name}} (Reason: {{adjustment_reason}}).\n\nYour revised balance due is ₦{{balance_due}}.'
),
(
    NULL,
    'term_closure_billing',
    'Term Closure and New-Term Billing',
    'Academic Term Closure & Upcoming Billing Preview - {{school_name}}',
    'email,sms,portal',
    'Dear Parents & Guardians,\n\nAs we conclude {{current_term_name}}, we thank you for your ongoing commitment. Please be prepared for the upcoming {{next_term_name}} portal onboarding and fee schedule.\n\nAll accounts in good standing will experience seamless progression.'
);

SET FOREIGN_KEY_CHECKS = 1;
