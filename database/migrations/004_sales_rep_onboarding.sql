-- ==============================================================================
-- 004_sales_rep_onboarding.sql
-- EduSphere SaaS - Commercial Sales Representative Onboarding & Agreement Schema
-- ==============================================================================

-- 1. Extend sales_representatives table with banking, phone, and agreement tracking
ALTER TABLE sales_representatives
    ADD COLUMN IF NOT EXISTS phone VARCHAR(50) NULL AFTER territory,
    ADD COLUMN IF NOT EXISTS bank_name VARCHAR(100) NULL AFTER notes,
    ADD COLUMN IF NOT EXISTS account_number VARCHAR(30) NULL AFTER bank_name,
    ADD COLUMN IF NOT EXISTS account_name VARCHAR(150) NULL AFTER account_number,
    ADD COLUMN IF NOT EXISTS agreement_accepted_at TIMESTAMP NULL AFTER account_name,
    ADD COLUMN IF NOT EXISTS agreement_version VARCHAR(20) DEFAULT 'v1.0' AFTER agreement_accepted_at,
    ADD COLUMN IF NOT EXISTS agreement_ip_address VARCHAR(45) NULL AFTER agreement_version;

-- 2. Legal Partner Agreement Audit Trail Table
CREATE TABLE IF NOT EXISTS sales_partner_agreements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rep_id INT NOT NULL,
    user_id INT NOT NULL,
    agreement_version VARCHAR(20) NOT NULL DEFAULT 'v1.0',
    signer_name VARCHAR(150) NOT NULL,
    signer_email VARCHAR(150) NOT NULL,
    signer_phone VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    terms_snapshot TEXT NOT NULL,
    accepted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rep_id) REFERENCES sales_representatives(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
