-- Migration 008: Dynamic Roles and Granular Permissions Engine
-- Supports Super Admin creation of administrators, role assignments, and custom permission grants

CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(80) NOT NULL UNIQUE,
    `category` VARCHAR(50) NOT NULL,
    `display_name` VARCHAR(150) NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role` VARCHAR(50) NOT NULL,
    `permission_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_role_perm` (`role`, `permission_id`),
    CONSTRAINT `fk_role_perm_pid` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    `granted` TINYINT(1) DEFAULT 1,
    `granted_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_perm` (`user_id`, `permission_id`),
    CONSTRAINT `fk_user_perm_uid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_perm_pid` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_perm_grantor` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pre-seed Core System Permissions
INSERT IGNORE INTO `permissions` (`slug`, `category`, `display_name`, `description`) VALUES
-- Platform Governance
('manage_platform', 'platform_governance', 'Manage Platform Core', 'Full root administrative capabilities across system configuration'),
('manage_schools', 'platform_governance', 'Manage Schools & Tenants', 'Provision, edit, map domains, and suspend tenant schools'),
('manage_users', 'platform_governance', 'Manage Users & Permissions', 'Create administrators, assign roles, and grant granular permissions'),
('manage_plans', 'platform_governance', 'Manage Subscription Plans', 'Create and modify platform subscription tiers'),
('view_audit_logs', 'platform_governance', 'View Audit Logs', 'Inspect immutable NDPA 2023 compliance and security audit logs'),

-- Finance & Revenue Operations
('manage_pricing', 'finance_reconciliation', 'Manage Portal Fee Pricing', 'Define default and negotiated per-student portal fee policies'),
('manage_revenue_policy', 'finance_reconciliation', 'Manage Revenue Policies', 'Configure revenue sharing allocation percentages and rules'),
('reconcile_payments', 'finance_reconciliation', 'Payment Reconciliation', 'Reconcile gateway settlement batches and resolve discrepancies'),
('manage_refunds', 'finance_reconciliation', 'Manage Refunds & Reversals', 'Authorize, approve, and execute portal fee refunds and ledger reversals'),
('approve_payouts', 'finance_reconciliation', 'Approve Bank Payouts', 'Authorize school incentive and sales commission disbursements'),
('view_financial_reports', 'finance_reconciliation', 'View Financial Telemetry', 'Inspect platform and tenant financial summaries and revenue trends'),

-- Sales & Partner CRM
('manage_pipeline', 'sales_crm', 'Manage Sales Pipeline', 'View and update deals, prospective schools, and outreach pipeline'),
('onboard_sales_reps', 'sales_crm', 'Onboard Sales Partners', 'Review and approve sales representatives and assign territories'),
('approve_commissions', 'sales_crm', 'Approve Sales Commissions', 'Authorize earned sales commissions for payout'),

-- School Operations & Academics
('manage_students', 'school_academics', 'Manage Student Enrollment', 'Direct admission and enrollment of students into class arms'),
('manage_academics', 'school_academics', 'Manage Academic Structure', 'Configure academic sessions, terms, class levels, and class arms'),
('manage_attendance', 'school_academics', 'Manage Attendance', 'Record and review daily student and faculty attendance'),
('manage_grades', 'school_academics', 'Manage Examinations & Grades', 'Enter continuous assessment and exam marks, compile report cards'),
('manage_staff', 'school_academics', 'Manage Faculty & Staff', 'Onboard teachers and faculty members, assign classes and subjects'),
('manage_school_fees', 'school_academics', 'Manage School Fees & Tuition', 'Issue tuition fee invoices, record collections, and issue receipts'),
('manage_term_portal_fee', 'school_academics', 'Manage Term Portal Billing', 'Review fee agreements, generate term invoices, and apply waivers'),

-- Communication & Notices
('broadcast_notices', 'communication', 'Broadcast School Notices', 'Publish announcements to notice board and broadcast billing notices'),
('send_messages', 'communication', 'Direct Two-Way Messaging', 'Initiate and respond to direct communication threads with parents'),
('manage_cms', 'communication', 'Manage Website CMS', 'Edit public-facing school website content and banner announcements');

-- Pre-seed Standard Role-Permission Mappings
-- Super Admin gets ALL permissions
INSERT IGNORE INTO `role_permissions` (`role`, `permission_id`)
SELECT 'super_admin', id FROM `permissions`;

-- Finance Admin
INSERT IGNORE INTO `role_permissions` (`role`, `permission_id`)
SELECT 'finance_admin', id FROM `permissions` WHERE `slug` IN (
    'view_financial_reports', 'reconcile_payments', 'manage_refunds', 'approve_payouts',
    'manage_pricing', 'manage_revenue_policy', 'view_audit_logs'
);

-- Sales Manager
INSERT IGNORE INTO `role_permissions` (`role`, `permission_id`)
SELECT 'sales_manager', id FROM `permissions` WHERE `slug` IN (
    'manage_pipeline', 'onboard_sales_reps', 'approve_commissions', 'view_financial_reports'
);

-- School Admin & School Owner
INSERT IGNORE INTO `role_permissions` (`role`, `permission_id`)
SELECT 'school_admin', id FROM `permissions` WHERE `slug` IN (
    'manage_students', 'manage_academics', 'manage_attendance', 'manage_grades',
    'manage_staff', 'manage_school_fees', 'manage_term_portal_fee', 'broadcast_notices',
    'send_messages', 'manage_cms', 'view_financial_reports'
);

INSERT IGNORE INTO `role_permissions` (`role`, `permission_id`)
SELECT 'school_owner', id FROM `permissions` WHERE `slug` IN (
    'manage_students', 'manage_academics', 'manage_attendance', 'manage_grades',
    'manage_staff', 'manage_school_fees', 'manage_term_portal_fee', 'broadcast_notices',
    'send_messages', 'manage_cms', 'view_financial_reports'
);

-- Bursar
INSERT IGNORE INTO `role_permissions` (`role`, `permission_id`)
SELECT 'bursar', id FROM `permissions` WHERE `slug` IN (
    'manage_school_fees', 'manage_term_portal_fee', 'view_financial_reports'
);

-- Teacher
INSERT IGNORE INTO `role_permissions` (`role`, `permission_id`)
SELECT 'teacher', id FROM `permissions` WHERE `slug` IN (
    'manage_attendance', 'manage_grades', 'send_messages'
);
