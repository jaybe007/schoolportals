-- ============================================================================
-- EduSphere Migration 006: Advanced Revenue Sharing Allocation Policies & Governance
-- ============================================================================

ALTER TABLE revenue_sharing_policies
    ADD COLUMN IF NOT EXISTS allocation_method ENUM('percentage', 'fixed_per_student', 'tiered') NOT NULL DEFAULT 'percentage',
    ADD COLUMN IF NOT EXISTS min_collection_threshold DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS effective_date DATE NULL,
    ADD COLUMN IF NOT EXISTS status ENUM('draft', 'pending_approval', 'approved', 'active', 'archived') NOT NULL DEFAULT 'active',
    ADD COLUMN IF NOT EXISTS notes TEXT NULL;
