-- ============================================================================
-- EduSphere Migration 005: Advanced Portal Pricing Policies, Dispute & Eligibility Controls
-- ============================================================================

ALTER TABLE portal_pricing_policies
    ADD COLUMN IF NOT EXISTS eligible_school_types VARCHAR(255) NULL DEFAULT 'all',
    ADD COLUMN IF NOT EXISTS eligible_student_categories VARCHAR(255) NULL DEFAULT 'all',
    ADD COLUMN IF NOT EXISTS exemptions_policy TEXT NULL,
    ADD COLUMN IF NOT EXISTS tax_treatment ENUM('tax_inclusive', 'tax_exclusive') NOT NULL DEFAULT 'tax_inclusive',
    ADD COLUMN IF NOT EXISTS tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS requires_authorization TINYINT(1) NOT NULL DEFAULT 1;

ALTER TABLE school_portal_fee_agreements
    ADD COLUMN IF NOT EXISTS clarification_requested TEXT NULL;

ALTER TABLE portal_invoices
    ADD COLUMN IF NOT EXISTS dispute_reason VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS adjustment_reason VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS adjusted_by INT NULL,
    ADD COLUMN IF NOT EXISTS adjusted_at TIMESTAMP NULL,
    ADD COLUMN IF NOT EXISTS eligibility_category VARCHAR(50) NOT NULL DEFAULT 'enrolled';
