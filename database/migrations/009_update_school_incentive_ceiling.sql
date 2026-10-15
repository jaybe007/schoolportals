-- ============================================================================
-- EduSphere Migration 009: Set School Incentive Ceiling to 25% Maximum
-- ============================================================================

UPDATE revenue_sharing_policies 
SET school_incentive_percentage = 25.00, platform_percentage = 60.00 
WHERE policy_code = 'DEFAULT-REVSHARE-2025' OR id = 1;
