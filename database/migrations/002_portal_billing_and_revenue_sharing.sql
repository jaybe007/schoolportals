-- ============================================================================
-- EduSphere SaaS Migration 002: Per-Student Term Portal Billing & Revenue Sharing
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Portal Pricing Policies (Default & School-Specific)
CREATE TABLE IF NOT EXISTS portal_pricing_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_code VARCHAR(50) NOT NULL UNIQUE,
    school_id INT NULL, -- NULL = Global default, otherwise school-specific
    session_id INT NULL,
    term_id INT NULL,
    fee_per_student DECIMAL(12, 2) NOT NULL DEFAULT 3000.00,
    currency VARCHAR(10) NOT NULL DEFAULT 'NGN',
    fee_payer ENUM('parent', 'school_sponsored') NOT NULL DEFAULT 'parent',
    min_fee DECIMAL(12, 2) NULL,
    max_fee DECIMAL(12, 2) NULL,
    discount_policy JSON NULL,
    effective_date DATE NOT NULL,
    expiry_date DATE NULL,
    status ENUM('draft', 'pending_approval', 'approved', 'active', 'expired', 'archived') NOT NULL DEFAULT 'active',
    notes TEXT NULL,
    created_by INT NULL,
    approved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Revenue Sharing Policies
CREATE TABLE IF NOT EXISTS revenue_sharing_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_code VARCHAR(50) NOT NULL UNIQUE,
    school_id INT NULL, -- NULL = Global default
    allocation_basis ENUM('gross', 'net_after_gateway') NOT NULL DEFAULT 'net_after_gateway',
    platform_percentage DECIMAL(5, 2) NOT NULL DEFAULT 45.00,
    school_incentive_percentage DECIMAL(5, 2) NOT NULL DEFAULT 40.00,
    sales_rep_initial_rate DECIMAL(5, 2) NOT NULL DEFAULT 15.00,
    sales_rep_recurring_rate DECIMAL(5, 2) NOT NULL DEFAULT 7.50,
    gateway_fee_bearer ENUM('platform', 'school', 'parent', 'split') NOT NULL DEFAULT 'platform',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NULL,
    approved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. School Portal Fee Agreements (Term Approval by School)
CREATE TABLE IF NOT EXISTS school_portal_fee_agreements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    session_id INT NOT NULL,
    term_id INT NOT NULL,
    pricing_policy_id INT NOT NULL,
    revenue_policy_id INT NOT NULL,
    proposed_fee_per_student DECIMAL(12, 2) NOT NULL,
    estimated_eligible_students INT NOT NULL DEFAULT 0,
    status ENUM('proposed', 'accepted', 'rejected', 'amendment_requested') NOT NULL DEFAULT 'proposed',
    accepted_by INT NULL,
    accepted_at TIMESTAMP NULL,
    school_notes TEXT NULL,
    rejection_reason VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (pricing_policy_id) REFERENCES portal_pricing_policies(id) ON DELETE RESTRICT,
    FOREIGN KEY (revenue_policy_id) REFERENCES revenue_sharing_policies(id) ON DELETE RESTRICT,
    FOREIGN KEY (accepted_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_school_term_agreement (school_id, session_id, term_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Portal Invoices (Per-Student Term Charges)
CREATE TABLE IF NOT EXISTS portal_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    student_id INT NOT NULL,
    parent_id INT NULL,
    agreement_id INT NOT NULL,
    session_id INT NOT NULL,
    term_id INT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    gross_amount DECIMAL(12, 2) NOT NULL,
    discount_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    waiver_reason VARCHAR(255) NULL,
    amount_due DECIMAL(12, 2) NOT NULL,
    paid_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    balance_due DECIMAL(12, 2) NOT NULL,
    status ENUM('unpaid', 'partially_paid', 'paid', 'waived', 'disputed', 'cancelled') NOT NULL DEFAULT 'unpaid',
    due_date DATE NOT NULL,
    idempotency_hash VARCHAR(64) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE SET NULL,
    FOREIGN KEY (agreement_id) REFERENCES school_portal_fee_agreements(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    UNIQUE KEY uq_portal_inv_term_student (school_id, session_id, term_id, student_id),
    INDEX idx_portal_inv_school (school_id, status),
    INDEX idx_portal_inv_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Portal Payments (Verified Collections)
CREATE TABLE IF NOT EXISTS portal_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    invoice_id INT NOT NULL,
    student_id INT NOT NULL,
    parent_id INT NULL,
    payment_reference VARCHAR(100) NOT NULL UNIQUE,
    gateway ENUM('paystack', 'bank_transfer', 'cash', 'pos') NOT NULL DEFAULT 'paystack',
    amount DECIMAL(12, 2) NOT NULL,
    gateway_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    net_amount DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'NGN',
    payer_name VARCHAR(150) NULL,
    payer_email VARCHAR(150) NULL,
    status ENUM('pending', 'verified', 'failed', 'reversed', 'refunded') NOT NULL DEFAULT 'verified',
    notes TEXT NULL,
    verified_at TIMESTAMP NULL,
    verified_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES portal_invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_portal_pay_school (school_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Financial Allocations (Distribution per payment)
CREATE TABLE IF NOT EXISTS financial_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL UNIQUE,
    school_id INT NOT NULL,
    invoice_id INT NOT NULL,
    revenue_policy_id INT NOT NULL,
    gross_amount DECIMAL(12, 2) NOT NULL,
    gateway_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    distributable_amount DECIMAL(12, 2) NOT NULL,
    platform_amount DECIMAL(12, 2) NOT NULL,
    school_incentive_amount DECIMAL(12, 2) NOT NULL,
    sales_rep_id INT NULL,
    sales_rep_commission_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    commission_tier ENUM('initial', 'recurring', 'none') NOT NULL DEFAULT 'initial',
    status ENUM('allocated', 'adjusted', 'reversed') NOT NULL DEFAULT 'allocated',
    reversal_reason VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES portal_payments(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES portal_invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (revenue_policy_id) REFERENCES revenue_sharing_policies(id) ON DELETE RESTRICT,
    FOREIGN KEY (sales_rep_id) REFERENCES sales_representatives(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. School Incentive Ledger (Double-entry tracking for School)
CREATE TABLE IF NOT EXISTS school_incentive_ledger (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    allocation_id INT NULL,
    term_id INT NULL,
    entry_type ENUM('accrual', 'payout', 'adjustment', 'reversal') NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    running_balance DECIMAL(12, 2) NOT NULL,
    reference VARCHAR(100) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (allocation_id) REFERENCES financial_allocations(id) ON DELETE SET NULL,
    FOREIGN KEY (term_id) REFERENCES academic_terms(id) ON DELETE SET NULL,
    INDEX idx_school_incentive_sch (school_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. School Payout Requests (Cash disbursements to School bank)
CREATE TABLE IF NOT EXISTS school_payout_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    request_code VARCHAR(50) NOT NULL UNIQUE,
    requested_amount DECIMAL(12, 2) NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(30) NOT NULL,
    account_name VARCHAR(150) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'paid') NOT NULL DEFAULT 'pending',
    requested_by INT NOT NULL,
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    disbursement_reference VARCHAR(100) NULL,
    rejection_reason VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Portal Commission Ledger (Sales Rep Term Commissions)
CREATE TABLE IF NOT EXISTS portal_commission_ledger (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rep_id INT NOT NULL,
    school_id INT NOT NULL,
    allocation_id INT NOT NULL,
    term_id INT NOT NULL,
    commission_tier ENUM('initial', 'recurring') NOT NULL DEFAULT 'initial',
    rate_applied DECIMAL(5, 2) NOT NULL,
    commission_amount DECIMAL(12, 2) NOT NULL,
    status ENUM('pending', 'approved', 'paid', 'reversed', 'disputed') NOT NULL DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    payout_reference VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rep_id) REFERENCES sales_representatives(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (allocation_id) REFERENCES financial_allocations(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_portal_comm_rep (rep_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Portal Billing Notices (School communications to parents)
CREATE TABLE IF NOT EXISTS portal_billing_notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    agreement_id INT NOT NULL,
    term_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    notice_body TEXT NOT NULL,
    notice_type ENUM('term_announcement', 'invoice_notification', 'payment_reminder', 'payment_receipt', 'refund_notice') NOT NULL DEFAULT 'term_announcement',
    sent_via ENUM('portal_announcement', 'email', 'sms') NOT NULL DEFAULT 'portal_announcement',
    recipient_count INT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (agreement_id) REFERENCES school_portal_fee_agreements(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Seed Default Platform Policies
-- ============================================================================

INSERT IGNORE INTO portal_pricing_policies 
(policy_code, school_id, fee_per_student, currency, fee_payer, effective_date, status, notes)
VALUES
('DEFAULT-TERM-2025', NULL, 3000.00, 'NGN', 'parent', '2025-09-01', 'active', 'Standard Platform Default Portal Fee: ₦3,000 per student per term');

INSERT IGNORE INTO revenue_sharing_policies
(policy_code, school_id, allocation_basis, platform_percentage, school_incentive_percentage, sales_rep_initial_rate, sales_rep_recurring_rate, gateway_fee_bearer, is_active)
VALUES
('DEFAULT-REVSHARE-2025', NULL, 'net_after_gateway', 45.00, 40.00, 15.00, 7.50, 'platform', 1);

SET FOREIGN_KEY_CHECKS = 1;
