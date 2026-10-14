<?php

declare(strict_types=1);

namespace EduSphere\Controllers;

use EduSphere\Core\Database;
use EduSphere\Core\Request;
use EduSphere\Core\Response;
use EduSphere\Core\View;
use EduSphere\Core\AuditLogger;
use EduSphere\Core\Validator;

class SuperAdminController
{
    public function dashboard(Request $request): void
    {
        // 1. School stats
        $totalSchools = (int)(Database::fetch("SELECT COUNT(*) AS c FROM schools")['c'] ?? 0);
        $activeSchools = (int)(Database::fetch("SELECT COUNT(*) AS c FROM schools WHERE status = 'active'")['c'] ?? 0);
        $trialSchools = (int)(Database::fetch("SELECT COUNT(*) AS c FROM schools WHERE status = 'trial'")['c'] ?? 0);
        $suspendedSchools = (int)(Database::fetch("SELECT COUNT(*) AS c FROM schools WHERE status = 'suspended'")['c'] ?? 0);

        // 2. Global student & staff count
        $totalStudents = (int)(Database::fetch("SELECT COUNT(*) AS c FROM students")['c'] ?? 0);
        $totalStaff = (int)(Database::fetch("SELECT COUNT(*) AS c FROM staff")['c'] ?? 0);

        // 3. Platform Subscription Financials
        $revenueRow = Database::fetch("SELECT SUM(amount) AS total FROM subscriptions WHERE payment_status = 'paid'");
        $totalRevenue = (float)($revenueRow['total'] ?? 0.0);
        $mrr = round($totalRevenue / 12, 2);

        // 4. Recent schools
        $recentSchools = Database::query(
            "SELECT s.*, p.name AS plan_name, 
                (SELECT domain FROM tenant_domains WHERE school_id = s.id AND is_primary = 1 LIMIT 1) AS primary_domain
             FROM schools s
             LEFT JOIN subscription_plans p ON p.id = s.current_plan_id
             ORDER BY s.created_at DESC LIMIT 6"
        );

        // 5. Recent Audit Logs
        $recentLogs = Database::query(
            "SELECT a.*, u.name AS user_name, s.name AS school_name 
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             LEFT JOIN schools s ON s.id = a.school_id
             ORDER BY a.created_at DESC LIMIT 8"
        );

        // 6. Sales reps stats
        $salesStats = Database::query(
            "SELECT sr.*, u.name AS rep_name,
                (SELECT COUNT(*) FROM sales_leads WHERE rep_id = sr.id) AS total_leads,
                (SELECT COUNT(*) FROM sales_leads WHERE rep_id = sr.id AND stage = 'won') AS won_leads,
                (SELECT IFNULL(SUM(commission_amount), 0) FROM sales_commissions WHERE rep_id = sr.id AND status = 'approved') AS earned_commission
             FROM sales_representatives sr
             JOIN users u ON u.id = sr.user_id
             LIMIT 5"
        );

        View::render('superadmin.dashboard', [
            'title' => 'Central Super Admin Console - EduSphere SaaS',
            'activeNav' => 'dashboard',
            'totalSchools' => $totalSchools,
            'activeSchools' => $activeSchools,
            'trialSchools' => $trialSchools,
            'suspendedSchools' => $suspendedSchools,
            'totalStudents' => $totalStudents,
            'totalStaff' => $totalStaff,
            'totalRevenue' => $totalRevenue,
            'mrr' => $mrr,
            'recentSchools' => $recentSchools,
            'recentLogs' => $recentLogs,
            'salesStats' => $salesStats,
        ], 'superadmin');
    }

    public function schools(Request $request): void
    {
        $schools = Database::query(
            "SELECT s.*, p.name AS plan_name,
                (SELECT domain FROM tenant_domains WHERE school_id = s.id AND is_primary = 1 LIMIT 1) AS primary_domain,
                (SELECT COUNT(*) FROM students WHERE school_id = s.id) AS student_count
             FROM schools s
             LEFT JOIN subscription_plans p ON p.id = s.current_plan_id
             ORDER BY s.id DESC"
        );

        $plans = Database::query("SELECT * FROM subscription_plans WHERE is_active = 1");

        View::render('superadmin.schools', [
            'title' => 'Tenant Schools Directory - Super Admin',
            'activeNav' => 'schools',
            'schools' => $schools,
            'plans' => $plans,
        ], 'superadmin');
    }

    public function createSchool(Request $request): void
    {
        $name = trim((string)$request->post('name', ''));
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $email = trim((string)$request->post('email', ''));
        $phone = trim((string)$request->post('phone', ''));
        $planId = (int)$request->post('plan_id', 1);
        $schoolType = (string)$request->post('school_type', 'combined');
        $adminName = trim((string)$request->post('admin_name', ''));
        $adminEmail = trim((string)$request->post('admin_email', ''));

        if (empty($name) || empty($email) || empty($adminEmail)) {
            Response::redirect('/superadmin/schools', 'School Name, Email, and Administrator Email are required.', 'danger');
        }

        // Check duplicate slug
        $existing = Database::fetch("SELECT id FROM schools WHERE slug = ? OR email = ?", [$slug, $email]);
        if ($existing) {
            $slug .= '-' . rand(10, 99);
        }

        $code = 'SCH-' . strtoupper(substr($slug, 0, 4)) . '-' . rand(100, 999);
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        Database::transaction(function($pdo) use ($uuid, $code, $name, $slug, $schoolType, $email, $phone, $planId, $adminName, $adminEmail) {
            // 1. Insert School
            Database::execute(
                "INSERT INTO schools (uuid, code, name, slug, motto, school_type, email, phone, current_plan_id, status, subscription_status, subscription_expires_at)
                 VALUES (?, ?, ?, ?, 'Knowledge and Integrity', ?, ?, ?, ?, 'active', 'active', DATE_ADD(CURDATE(), INTERVAL 1 YEAR))",
                [$uuid, $code, $name, $slug, $schoolType, $email, $phone, $planId]
            );
            $schoolId = (int)Database::lastInsertId();

            // 2. Primary Domain
            $primaryDomain = "{$slug}.edusphere.example";
            Database::execute(
                "INSERT INTO tenant_domains (school_id, domain, is_primary, is_custom, is_verified, status)
                 VALUES (?, ?, 1, 0, 1, 'active')",
                [$schoolId, $primaryDomain]
            );

            // 3. School Administrator User
            $pwdHash = password_hash('password123', PASSWORD_BCRYPT);
            Database::execute(
                "INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active)
                 VALUES (?, ?, ?, ?, ?, 'school_admin', 1)",
                [$schoolId, $adminName ?: "Admin {$name}", $adminEmail, $phone, $pwdHash]
            );

            // 4. Default Session & Term
            Database::execute(
                "INSERT INTO academic_sessions (school_id, name, start_date, end_date, is_current)
                 VALUES (?, '2025/2026', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 10 MONTH), 1)",
                [$schoolId]
            );
            $sessionId = (int)Database::lastInsertId();

            Database::execute(
                "INSERT INTO academic_terms (school_id, session_id, name, start_date, end_date, is_current)
                 VALUES (?, ?, 'First Term', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 1)",
                [$schoolId, $sessionId]
            );

            // 5. Default CMS Page
            Database::execute(
                "INSERT INTO school_website_pages (school_id, page_slug, title, banner_title, banner_subtitle, content, is_published)
                 VALUES (?, 'home', 'Welcome to {$name}', 'Fostering Academic Excellence', 'Providing premier foundational education.', 'Welcome to {$name}. Our modern learning community promotes intellectual vigor and character.', 1)",
                [$schoolId]
            );

            AuditLogger::log('school_created_by_superadmin', 'school', $schoolId, [
                'name' => $name,
                'slug' => $slug,
                'code' => $code,
            ]);
        });

        Response::redirect('/superadmin/schools', "School '{$name}' onboarded successfully with domain '{$slug}.edusphere.example'!", 'success');
    }

    public function toggleSchoolStatus(Request $request, string $id): void
    {
        $schoolId = (int)$id;
        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);
        if (!$school) {
            Response::redirect('/superadmin/schools', 'School not found.', 'danger');
        }

        $newStatus = $school['status'] === 'active' ? 'suspended' : 'active';
        Database::execute("UPDATE schools SET status = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $schoolId]);

        AuditLogger::log('school_status_toggled', 'school', $schoolId, [
            'previous' => $school['status'],
            'new' => $newStatus,
        ]);

        Response::redirect('/superadmin/schools', "School '{$school['name']}' status changed to '{$newStatus}'.", 'success');
    }

    public function subscriptions(Request $request): void
    {
        $plans = Database::query("SELECT * FROM subscription_plans ORDER BY price ASC");
        $subscriptions = Database::query(
            "SELECT s.*, sch.name AS school_name, sch.slug AS school_slug, p.name AS plan_name
             FROM subscriptions s
             JOIN schools sch ON sch.id = s.school_id
             JOIN subscription_plans p ON p.id = s.plan_id
             ORDER BY s.id DESC"
        );

        View::render('superadmin.subscriptions', [
            'title' => 'SaaS Subscriptions & Billing - Super Admin',
            'activeNav' => 'subscriptions',
            'plans' => $plans,
            'subscriptions' => $subscriptions,
        ], 'superadmin');
    }

    public function users(Request $request): void
    {
        $users = Database::query(
            "SELECT u.*, s.name AS school_name
             FROM users u
             LEFT JOIN schools s ON s.id = u.school_id
             ORDER BY u.id DESC"
        );

        View::render('superadmin.users', [
            'title' => 'Platform Users Directory - Super Admin',
            'activeNav' => 'users',
            'users' => $users,
        ], 'superadmin');
    }

    public function auditLogs(Request $request): void
    {
        $logs = Database::query(
            "SELECT a.*, u.name AS user_name, u.email AS user_email, s.name AS school_name
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             LEFT JOIN schools s ON s.id = a.school_id
             ORDER BY a.id DESC LIMIT 100"
        );

        View::render('superadmin.audit_logs', [
            'title' => 'NDPA Audit Logs Trail - Super Admin',
            'activeNav' => 'audit',
            'logs' => $logs,
        ], 'superadmin');
    }

    public function settings(Request $request): void
    {
        if ($request->isPost()) {
            $platformName = trim((string)$request->post('platform_name', 'EduSphere SaaS'));
            $supportEmail = trim((string)$request->post('support_email', ''));
            $currency = trim((string)$request->post('currency_symbol', '₦'));

            Database::execute("UPDATE platform_settings SET setting_value = ? WHERE setting_key = 'platform_name'", [$platformName]);
            Database::execute("UPDATE platform_settings SET setting_value = ? WHERE setting_key = 'support_email'", [$supportEmail]);
            Database::execute("UPDATE platform_settings SET setting_value = ? WHERE setting_key = 'currency_symbol'", [$currency]);

            AuditLogger::log('platform_settings_updated', 'settings', 1, [
                'platform_name' => $platformName,
                'support_email' => $supportEmail,
            ]);

            Response::redirect('/superadmin/settings', 'Platform settings updated successfully.', 'success');
        }

        $settingsRows = Database::query("SELECT * FROM platform_settings");
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        View::render('superadmin.settings', [
            'title' => 'Platform Configuration - Super Admin',
            'activeNav' => 'settings',
            'settings' => $settings,
        ], 'superadmin');
    }

    public function addDomain(Request $request, string $id): void
    {
        $schoolId = (int)$id;
        $domain = strtolower(trim((string)$request->post('domain', '')));

        if (empty($domain)) {
            Response::redirect('/superadmin/schools', 'Custom domain name is required.', 'danger');
        }

        $existing = Database::fetch("SELECT id FROM tenant_domains WHERE domain = ?", [$domain]);
        if ($existing) {
            Response::redirect('/superadmin/schools', "Domain '{$domain}' is already assigned to a school.", 'danger');
        }

        Database::execute(
            "INSERT INTO tenant_domains (school_id, domain, is_primary, is_custom, is_verified, status)
             VALUES (?, ?, 0, 1, 1, 'active')",
            [$schoolId, $domain]
        );

        AuditLogger::log('tenant_custom_domain_added', 'tenant_domains', (int)Database::lastInsertId(), [
            'school_id' => $schoolId,
            'domain' => $domain,
        ]);

        Response::redirect('/superadmin/schools', "Custom domain '{$domain}' successfully mapped!", 'success');
    }

    public function createPlan(Request $request): void
    {
        $name = trim((string)$request->post('name', ''));
        $code = strtolower(trim((string)$request->post('code', '')));
        $price = (float)$request->post('price', 0.0);
        $cycle = (string)$request->post('billing_cycle', 'annual');
        $studentLimit = (int)$request->post('student_limit', 500);
        $staffLimit = (int)$request->post('staff_limit', 50);
        $desc = trim((string)$request->post('description', ''));

        if (empty($name) || empty($code)) {
            Response::redirect('/superadmin/subscriptions', 'Plan Name and Code are required.', 'danger');
        }

        Database::execute(
            "INSERT INTO subscription_plans (name, code, description, price, billing_cycle, student_limit, staff_limit, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
            [$name, $code, $desc, $price, $cycle, $studentLimit, $staffLimit]
        );

        AuditLogger::log('subscription_plan_created', 'subscription_plans', (int)Database::lastInsertId(), [
            'name' => $name,
            'price' => $price,
            'code' => $code,
        ]);

        Response::redirect('/superadmin/subscriptions', "Subscription plan '{$name}' created successfully.", 'success');
    }

    public function portalBilling(Request $request): void
    {
        $stats = \EduSphere\Services\PortalBillingService::getPlatformGlobalStats();

        // 1. Current pricing policies
        $pricingPolicies = Database::query(
            "SELECT p.*, s.name AS school_name, u.name AS creator_name 
             FROM portal_pricing_policies p
             LEFT JOIN schools s ON s.id = p.school_id
             LEFT JOIN users u ON u.id = p.created_by
             ORDER BY p.id DESC"
        );

        // 2. Active Revenue Sharing policies
        $revPolicies = Database::query(
            "SELECT r.*, s.name AS school_name 
             FROM revenue_sharing_policies r
             LEFT JOIN schools s ON s.id = r.school_id
             ORDER BY r.id DESC"
        );

        // 3. School-by-school collection breakdown
        $schoolBreakdown = Database::query(
            "SELECT s.id, s.name, s.code, s.slug,
                (SELECT COUNT(*) FROM students WHERE school_id = s.id AND status = 'enrolled') AS eligible_students,
                (SELECT COUNT(*) FROM portal_invoices WHERE school_id = s.id) AS billed_invoices,
                (SELECT IFNULL(SUM(amount_due), 0) FROM portal_invoices WHERE school_id = s.id) AS total_invoiced,
                (SELECT IFNULL(SUM(paid_amount), 0) FROM portal_invoices WHERE school_id = s.id) AS total_collected,
                (SELECT IFNULL(SUM(balance_due), 0) FROM portal_invoices WHERE school_id = s.id) AS total_balance,
                (SELECT status FROM school_portal_fee_agreements WHERE school_id = s.id ORDER BY id DESC LIMIT 1) AS agreement_status
             FROM schools s
             ORDER BY s.id ASC"
        );

        // 4. Recent verified payments & allocations
        $recentAllocations = Database::query(
            "SELECT a.*, s.name AS school_name, p.payment_reference, p.gateway, p.payer_name,
                    u.name AS rep_name
             FROM financial_allocations a
             JOIN schools s ON s.id = a.school_id
             JOIN portal_payments p ON p.id = a.payment_id
             LEFT JOIN sales_representatives sr ON sr.id = a.sales_rep_id
             LEFT JOIN users u ON u.id = sr.user_id
             ORDER BY a.id DESC LIMIT 15"
        );

        // 5. School Payout Requests
        $payoutRequests = Database::query(
            "SELECT pr.*, s.name AS school_name, u.name AS requester_name, u_app.name AS approver_name
             FROM school_payout_requests pr
             JOIN schools s ON s.id = pr.school_id
             JOIN users u ON u.id = pr.requested_by
             LEFT JOIN users u_app ON u_app.id = pr.approved_by
             ORDER BY pr.id DESC"
        );

        $schools = Database::query("SELECT id, name FROM schools ORDER BY name ASC");

        View::render('superadmin.portal_billing', [
            'title' => 'Portal Billing & Revenue Management - EduSphere HQ',
            'activeNav' => 'portal_billing',
            'stats' => $stats,
            'pricingPolicies' => $pricingPolicies,
            'revPolicies' => $revPolicies,
            'schoolBreakdown' => $schoolBreakdown,
            'recentAllocations' => $recentAllocations,
            'payoutRequests' => $payoutRequests,
            'schools' => $schools,
        ], 'superadmin');
    }

    public function createPricingPolicy(Request $request): void
    {
        $code = strtoupper(trim((string)$request->post('policy_code', '')));
        $fee = (float)$request->post('fee_per_student', 3000.00);
        $schoolId = !empty($request->post('school_id')) ? (int)$request->post('school_id') : null;
        $payer = (string)$request->post('fee_payer', 'parent');
        $effectiveDate = (string)$request->post('effective_date', date('Y-m-d'));
        $notes = trim((string)$request->post('notes', ''));

        if (empty($code)) {
            Response::redirect('/superadmin/portal-billing', 'Policy Code is required.', 'danger');
        }

        Database::execute(
            "INSERT INTO portal_pricing_policies
             (policy_code, school_id, fee_per_student, currency, fee_payer, effective_date, status, notes, created_by, approved_by)
             VALUES (?, ?, ?, 'NGN', ?, ?, 'active', ?, ?, ?)",
            [$code, $schoolId, $fee, $payer, $effectiveDate, $notes, \EduSphere\Core\Auth::id(), \EduSphere\Core\Auth::id()]
        );

        AuditLogger::log('portal_pricing_policy_created', 'portal_pricing_policies', (int)Database::lastInsertId(), [
            'code' => $code,
            'fee_per_student' => $fee,
            'school_id' => $schoolId,
        ]);

        Response::redirect('/superadmin/portal-billing', "Pricing policy '{$code}' configured successfully!", 'success');
    }

    public function approvePayout(Request $request, string $id): void
    {
        $payoutId = (int)$id;
        $ref = trim((string)$request->post('disbursement_reference', 'DISB-' . date('Ymd') . '-' . rand(1000, 9999)));

        try {
            \EduSphere\Services\RevenueSharingService::approveSchoolPayout($payoutId, \EduSphere\Core\Auth::id(), $ref);
            Response::redirect('/superadmin/portal-billing', "School payout #{$payoutId} approved and recorded with reference {$ref}.", 'success');
        } catch (\Throwable $e) {
            Response::redirect('/superadmin/portal-billing', $e->getMessage(), 'danger');
        }
    }
}

