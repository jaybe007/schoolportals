<?php

declare(strict_types=1);

namespace EduSphere\Controllers;

use EduSphere\Core\Database;
use EduSphere\Core\Request;
use EduSphere\Core\Response;
use EduSphere\Core\View;
use EduSphere\Core\AuditLogger;
use EduSphere\Core\Validator;
use EduSphere\Core\Auth;
use EduSphere\Services\PortalBillingService;

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
                (SELECT proposed_fee_per_student FROM school_portal_fee_agreements WHERE school_id = s.id ORDER BY id DESC LIMIT 1) AS active_portal_fee,
                (SELECT domain FROM tenant_domains WHERE school_id = s.id AND is_primary = 1 LIMIT 1) AS primary_domain,
                (SELECT COUNT(*) FROM students WHERE school_id = s.id) AS student_count
             FROM schools s
             LEFT JOIN subscription_plans p ON p.id = s.current_plan_id
             ORDER BY s.id DESC"
        );

        $plans = Database::query("SELECT * FROM subscription_plans WHERE is_active = 1");
        $salesReps = Database::query(
            "SELECT sr.id, sr.rep_code, u.name, sr.territory 
             FROM sales_representatives sr 
             JOIN users u ON u.id = sr.user_id 
             WHERE sr.status = 'active' 
             ORDER BY u.name ASC"
        );
        $unlinkedLeads = Database::query(
            "SELECT sl.id, sl.school_name, sl.rep_id, u.name AS rep_name, sr.rep_code
             FROM sales_leads sl
             JOIN sales_representatives sr ON sr.id = sl.rep_id
             JOIN users u ON u.id = sr.user_id
             WHERE sl.created_school_id IS NULL
             ORDER BY sl.id DESC"
        );

        View::render('superadmin.schools', [
            'title' => 'Tenant Schools Directory - Super Admin',
            'activeNav' => 'schools',
            'schools' => $schools,
            'plans' => $plans,
            'salesReps' => $salesReps,
            'unlinkedLeads' => $unlinkedLeads,
        ], 'superadmin');
    }

    public function createSchool(Request $request): void
    {
        $name = trim((string)$request->post('name', ''));
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $email = trim((string)$request->post('email', ''));
        $phone = trim((string)$request->post('phone', ''));
        $portalFee = (float)$request->post('portal_fee', 3000.00);
        if ($portalFee <= 0) {
            $portalFee = 3000.00;
        }
        $schoolType = (string)$request->post('school_type', 'combined');
        $adminName = trim((string)$request->post('admin_name', ''));
        $adminEmail = trim((string)$request->post('admin_email', ''));
        $attributedLeadId = (int)$request->post('attributed_lead_id', 0);
        $attributedRepId = (int)$request->post('attributed_rep_id', 0);

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

        Database::transaction(function($pdo) use (
            $uuid, $code, $name, $slug, $schoolType, $email, $phone, $portalFee,
            $adminName, $adminEmail, $attributedLeadId, $attributedRepId
        ) {
            // 1. Insert School (current_plan_id is NULL under the per-student portal fee commercial model)
            Database::execute(
                "INSERT INTO schools (uuid, code, name, slug, motto, school_type, email, phone, current_plan_id, status, subscription_status, subscription_expires_at)
                 VALUES (?, ?, ?, ?, 'Knowledge and Integrity', ?, ?, ?, NULL, 'active', 'active', DATE_ADD(CURDATE(), INTERVAL 1 YEAR))",
                [$uuid, $code, $name, $slug, $schoolType, $email, $phone]
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
            $termId = (int)Database::lastInsertId();

            // 4b. Initialize Per-Student Term Portal Fee Agreement
            $pricing = PortalBillingService::getEffectivePricing($schoolId, $sessionId, $termId);
            $revPolicy = PortalBillingService::getRevenueSharingPolicy($schoolId);
            Database::execute(
                "INSERT INTO school_portal_fee_agreements
                 (school_id, session_id, term_id, pricing_policy_id, revenue_policy_id, proposed_fee_per_student, estimated_eligible_students, status)
                 VALUES (?, ?, ?, ?, ?, ?, 0, 'proposed')",
                [$schoolId, $sessionId, $termId, $pricing['id'], $revPolicy['id'], $portalFee]
            );

            // 5. Default CMS Page
            Database::execute(
                "INSERT INTO school_website_pages (school_id, page_slug, title, banner_title, banner_subtitle, content, is_published)
                 VALUES (?, 'home', 'Welcome to {$name}', 'Fostering Academic Excellence', 'Providing premier foundational education.', 'Welcome to {$name}. Our modern learning community promotes intellectual vigor and character.', 1)",
                [$schoolId]
            );

            // 6. Link Sales Lead or Rep Attribution if provided
            if ($attributedLeadId > 0) {
                Database::execute(
                    "UPDATE sales_leads SET created_school_id = ?, stage = 'won', updated_at = NOW() WHERE id = ?",
                    [$schoolId, $attributedLeadId]
                );
            } elseif ($attributedRepId > 0) {
                Database::execute(
                    "INSERT INTO sales_leads (rep_id, school_name, contact_person, email, phone, state, lead_source, stage, created_school_id, notes, created_at)
                     VALUES (?, ?, ?, ?, ?, 'State', 'Direct Provisioning', 'won', ?, 'School attributed to partner during provisioning', NOW())",
                    [$attributedRepId, $name, $adminName ?: "Admin {$name}", $adminEmail, $phone, $schoolId]
                );
            } else {
                // Auto-match if an unlinked lead with identical school name or phone exists
                Database::execute(
                    "UPDATE sales_leads SET created_school_id = ?, stage = 'won', updated_at = NOW() 
                     WHERE created_school_id IS NULL AND (school_name = ? OR (phone IS NOT NULL AND phone = ?)) LIMIT 1",
                    [$schoolId, $name, $phone]
                );
            }

            AuditLogger::log('school_created_by_superadmin', 'school', $schoolId, [
                'name' => $name,
                'slug' => $slug,
                'code' => $code,
                'attributed_lead_id' => $attributedLeadId,
                'attributed_rep_id' => $attributedRepId,
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
            "SELECT u.*, s.name AS school_name,
                    (SELECT COUNT(*) FROM user_permissions up WHERE up.user_id = u.id AND up.granted = 1) AS custom_permissions_count
             FROM users u
             LEFT JOIN schools s ON s.id = u.school_id
             ORDER BY u.id DESC"
        );

        $allPermissions = Database::query("SELECT * FROM permissions ORDER BY category, id ASC");
        $schools = Database::query("SELECT id, name, code FROM schools ORDER BY name ASC");

        $userPermMap = [];
        $upRows = Database::query("SELECT user_id, permission_id FROM user_permissions WHERE granted = 1");
        foreach ($upRows as $r) {
            $userPermMap[(int)$r['user_id']][] = (int)$r['permission_id'];
        }

        $rolePermMap = [];
        $rpRows = Database::query("SELECT role, permission_id FROM role_permissions");
        foreach ($rpRows as $r) {
            $rolePermMap[$r['role']][] = (int)$r['permission_id'];
        }

        View::render('superadmin.users', [
            'title' => 'Platform Users & Role Assignments - Super Admin',
            'activeNav' => 'users',
            'users' => $users,
            'allPermissions' => $allPermissions,
            'schools' => $schools,
            'userPermMap' => $userPermMap,
            'rolePermMap' => $rolePermMap,
        ], 'superadmin');
    }

    public function createUser(Request $request): void
    {
        $name = trim((string)$request->post('name', ''));
        $email = strtolower(trim((string)$request->post('email', '')));
        $phone = trim((string)$request->post('phone', ''));
        $password = trim((string)$request->post('password', ''));
        $role = trim((string)$request->post('role', 'school_admin'));
        $schoolId = !empty($request->post('school_id')) ? (int)$request->post('school_id') : null;
        $permissionIds = (array)$request->post('permissions', []);

        if (empty($name) || empty($email) || empty($password)) {
            Response::redirect('/superadmin/users', 'Name, Email, and Password are required to create a platform user.', 'danger');
        }

        if (strlen($password) < 6) {
            Response::redirect('/superadmin/users', 'Password must be at least 6 characters.', 'danger');
        }

        $existing = Database::fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            Response::redirect('/superadmin/users', "A user with email '{$email}' already exists.", 'danger');
        }

        $pwdHash = password_hash($password, PASSWORD_BCRYPT);

        Database::transaction(function($pdo) use ($name, $email, $phone, $pwdHash, $role, $schoolId, $permissionIds) {
            Database::execute(
                "INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, 1)",
                [$schoolId, $name, $email, $phone, $pwdHash, $role]
            );
            $newUserId = (int)Database::lastInsertId();

            if (!empty($permissionIds)) {
                Auth::syncPermissions($newUserId, $permissionIds, Auth::id());
            }

            AuditLogger::log('admin_created_by_superadmin', 'users', $newUserId, [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'school_id' => $schoolId,
                'permissions_count' => count($permissionIds),
                'created_by' => Auth::id(),
            ]);
        });

        Response::redirect('/superadmin/users', "Administrator '{$name}' created successfully with role '{$role}'!", 'success');
    }

    public function updateUser(Request $request, string $id): void
    {
        $userId = (int)$id;
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            Response::redirect('/superadmin/users', 'User not found.', 'danger');
        }

        $name = trim((string)$request->post('name', $user['name']));
        $phone = trim((string)$request->post('phone', $user['phone'] ?? ''));
        $role = trim((string)$request->post('role', $user['role']));
        $schoolId = !empty($request->post('school_id')) ? (int)$request->post('school_id') : null;
        $isActive = (int)$request->post('is_active', 1);
        $newPassword = trim((string)$request->post('password', ''));
        $permissionIds = (array)$request->post('permissions', []);

        Database::transaction(function($pdo) use ($userId, $name, $phone, $role, $schoolId, $isActive, $newPassword, $permissionIds) {
            if (!empty($newPassword) && strlen($newPassword) >= 6) {
                $pwdHash = password_hash($newPassword, PASSWORD_BCRYPT);
                Database::execute(
                    "UPDATE users SET name = ?, phone = ?, role = ?, school_id = ?, is_active = ?, password_hash = ?, updated_at = NOW() WHERE id = ?",
                    [$name, $phone, $role, $schoolId, $isActive, $pwdHash, $userId]
                );
            } else {
                Database::execute(
                    "UPDATE users SET name = ?, phone = ?, role = ?, school_id = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
                    [$name, $phone, $role, $schoolId, $isActive, $userId]
                );
            }

            Auth::syncPermissions($userId, $permissionIds, Auth::id());

            AuditLogger::log('user_role_permissions_updated', 'users', $userId, [
                'name' => $name,
                'role' => $role,
                'school_id' => $schoolId,
                'is_active' => $isActive,
                'updated_by' => Auth::id(),
            ]);
        });

        Response::redirect('/superadmin/users', "User '{$name}' role and permissions updated successfully.", 'success');
    }

    public function toggleUserStatus(Request $request, string $id): void
    {
        $userId = (int)$id;
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            Response::redirect('/superadmin/users', 'User not found.', 'danger');
        }

        if ($userId === Auth::id()) {
            Response::redirect('/superadmin/users', 'You cannot suspend your own Super Admin account.', 'danger');
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        Database::execute("UPDATE users SET is_active = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $userId]);

        AuditLogger::log('user_status_toggled', 'users', $userId, [
            'status' => $newStatus ? 'active' : 'suspended',
            'toggled_by' => Auth::id(),
        ]);

        $statusLabel = $newStatus ? 'activated' : 'suspended';
        Response::redirect('/superadmin/users', "User '{$user['name']}' has been {$statusLabel}.", 'success');
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
        $termTrends = \EduSphere\Services\PortalBillingService::getTermTrends();

        // 1. Current pricing policies with sessions, terms, and creator/approver
        $pricingPolicies = Database::query(
            "SELECT p.*, s.name AS school_name, u.name AS creator_name, ua.name AS approver_name,
                    ses.name AS session_name, t.name AS term_name
             FROM portal_pricing_policies p
             LEFT JOIN schools s ON s.id = p.school_id
             LEFT JOIN users u ON u.id = p.created_by
             LEFT JOIN users ua ON ua.id = p.approved_by
             LEFT JOIN academic_sessions ses ON ses.id = p.session_id
             LEFT JOIN academic_terms t ON t.id = p.term_id
             ORDER BY p.id DESC"
        );

        // 2. Active Revenue Sharing policies
        $revPolicies = Database::query(
            "SELECT r.*, s.name AS school_name 
             FROM revenue_sharing_policies r
             LEFT JOIN schools s ON s.id = r.school_id
             ORDER BY r.id DESC"
        );

        // 3. School-by-school collection breakdown with calculated rates
        $schoolBreakdown = Database::query(
            "SELECT s.id, s.name, s.code, s.slug,
                (SELECT COUNT(*) FROM students WHERE school_id = s.id AND status = 'enrolled') AS eligible_students,
                (SELECT COUNT(*) FROM portal_invoices WHERE school_id = s.id) AS billed_invoices,
                (SELECT IFNULL(SUM(amount_due), 0) FROM portal_invoices WHERE school_id = s.id) AS total_invoiced,
                (SELECT IFNULL(SUM(paid_amount), 0) FROM portal_invoices WHERE school_id = s.id) AS total_collected,
                (SELECT IFNULL(SUM(balance_due), 0) FROM portal_invoices WHERE school_id = s.id) AS total_balance,
                (SELECT status FROM school_portal_fee_agreements WHERE school_id = s.id ORDER BY id DESC LIMIT 1) AS agreement_status,
                (SELECT IFNULL(SUM(amount), 0) FROM school_incentive_ledger WHERE school_id = s.id AND entry_type = 'accrual') AS incentives_accrued
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

        // 6. Gateway Settlement Reconciliations & Webhook Events
        $settlementReconciliations = \EduSphere\Services\PaymentReconciliationService::getReconciliationHistory(15);
        $gatewayEvents = Database::fetchAll("SELECT * FROM payment_gateway_events ORDER BY id DESC LIMIT 15");
        $refundRequests = \EduSphere\Services\RefundManagementService::getRefunds(null, null, 15);

        $schools = Database::query("SELECT id, name, code FROM schools ORDER BY name ASC");
        $sessions = Database::query("SELECT id, name FROM academic_sessions GROUP BY name ORDER BY id DESC");
        $terms = Database::query("SELECT id, name FROM academic_terms GROUP BY name ORDER BY id ASC");

        View::render('superadmin.portal_billing', [
            'title' => 'Portal Billing & Revenue Management - EduSphere HQ',
            'activeNav' => 'portal_billing',
            'stats' => $stats,
            'termTrends' => $termTrends,
            'pricingPolicies' => $pricingPolicies,
            'revPolicies' => $revPolicies,
            'schoolBreakdown' => $schoolBreakdown,
            'recentAllocations' => $recentAllocations,
            'payoutRequests' => $payoutRequests,
            'settlementReconciliations' => $settlementReconciliations,
            'gatewayEvents' => $gatewayEvents,
            'refundRequests' => $refundRequests,
            'schools' => $schools,
            'sessions' => $sessions,
            'terms' => $terms,
        ], 'superadmin');
    }

    public function createPricingPolicy(Request $request): void
    {
        $code = strtoupper(trim((string)$request->post('policy_code', '')));
        $fee = (float)$request->post('fee_per_student', 3000.00);
        $currency = strtoupper(trim((string)$request->post('currency', 'NGN')));
        $schoolId = !empty($request->post('school_id')) ? (int)$request->post('school_id') : null;
        $sessionId = !empty($request->post('session_id')) ? (int)$request->post('session_id') : null;
        $termId = !empty($request->post('term_id')) ? (int)$request->post('term_id') : null;
        $payer = (string)$request->post('fee_payer', 'parent');
        $minFee = !empty($request->post('min_fee')) ? (float)$request->post('min_fee') : null;
        $maxFee = !empty($request->post('max_fee')) ? (float)$request->post('max_fee') : null;
        $schoolTypes = trim((string)$request->post('eligible_school_types', 'all'));
        $studentCategories = trim((string)$request->post('eligible_student_categories', 'all'));
        $discountPolicy = trim((string)$request->post('discount_policy', ''));
        $exemptionsPolicy = trim((string)$request->post('exemptions_policy', ''));
        $taxTreatment = (string)$request->post('tax_treatment', 'tax_inclusive');
        $taxRate = (float)$request->post('tax_rate', 0.00);
        $effectiveDate = (string)$request->post('effective_date', date('Y-m-d'));
        $expiryDate = !empty($request->post('expiry_date')) ? (string)$request->post('expiry_date') : null;
        $status = (string)$request->post('status', 'active');
        $notes = trim((string)$request->post('notes', ''));
        $confirmed = (bool)$request->post('confirm_authorization', false);

        if (empty($code)) {
            Response::redirect('/superadmin/portal-billing', 'Policy Code is required.', 'danger');
        }

        if (!$confirmed) {
            Response::redirect('/superadmin/portal-billing', 'Explicit confirmation and authorization checkbox is required before configuring portal pricing.', 'warning');
        }

        $userId = \EduSphere\Core\Auth::id();
        $approvedBy = in_array($status, ['approved', 'active']) ? $userId : null;

        Database::execute(
            "INSERT INTO portal_pricing_policies
             (policy_code, school_id, session_id, term_id, fee_per_student, currency, fee_payer, min_fee, max_fee,
              eligible_school_types, eligible_student_categories, discount_policy, exemptions_policy,
              tax_treatment, tax_rate, effective_date, expiry_date, status, notes, created_by, approved_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $code, $schoolId, $sessionId, $termId, $fee, $currency, $payer, $minFee, $maxFee,
                $schoolTypes, $studentCategories, $discountPolicy, $exemptionsPolicy,
                $taxTreatment, $taxRate, $effectiveDate, $expiryDate, $status, $notes, $userId, $approvedBy
            ]
        );

        $policyId = (int)Database::lastInsertId();

        AuditLogger::log('portal_pricing_policy_created', 'portal_pricing_policies', $policyId, [
            'code' => $code,
            'fee_per_student' => $fee,
            'currency' => $currency,
            'school_id' => $schoolId,
            'session_id' => $sessionId,
            'term_id' => $termId,
            'fee_payer' => $payer,
            'status' => $status,
            'authorized_by' => $userId,
        ]);

        Response::redirect('/superadmin/portal-billing', "Pricing policy '{$code}' configured successfully with status '{$status}'!", 'success');
    }

    public function updatePricingPolicyStatus(Request $request, string $id): void
    {
        $policyId = (int)$id;
        $newStatus = trim((string)$request->post('status', ''));
        $validStatuses = ['draft', 'pending_approval', 'approved', 'active', 'expired', 'archived'];

        if (!in_array($newStatus, $validStatuses, true)) {
            Response::redirect('/superadmin/portal-billing', 'Invalid policy status requested.', 'danger');
        }

        $pol = Database::fetch("SELECT * FROM portal_pricing_policies WHERE id = ?", [$policyId]);
        if (!$pol) {
            Response::redirect('/superadmin/portal-billing', 'Pricing policy record not found.', 'danger');
        }

        $userId = \EduSphere\Core\Auth::id();
        $approvedBy = in_array($newStatus, ['approved', 'active']) ? $userId : $pol['approved_by'];

        Database::execute(
            "UPDATE portal_pricing_policies SET status = ?, approved_by = ?, updated_at = NOW() WHERE id = ?",
            [$newStatus, $approvedBy, $policyId]
        );

        AuditLogger::log('portal_pricing_policy_status_updated', 'portal_pricing_policies', $policyId, [
            'old_status' => $pol['status'],
            'new_status' => $newStatus,
            'authorized_by' => $userId,
        ]);

        Response::redirect('/superadmin/portal-billing', "Pricing policy '{$pol['policy_code']}' transitioned to status '{$newStatus}'.", 'success');
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

    public function createRevenuePolicy(Request $request): void
    {
        $code = strtoupper(trim((string)$request->post('policy_code', '')));
        $schoolId = !empty($request->post('school_id')) ? (int)$request->post('school_id') : null;
        $basis = (string)$request->post('allocation_basis', 'net_after_gateway');
        $method = (string)$request->post('allocation_method', 'percentage');
        $platformPct = (float)$request->post('platform_percentage', 45.00);
        $schoolPct = (float)$request->post('school_incentive_percentage', 40.00);
        $repInitial = (float)$request->post('sales_rep_initial_rate', 15.00);
        $repRecurring = (float)$request->post('sales_rep_recurring_rate', 7.50);
        $bearer = (string)$request->post('gateway_fee_bearer', 'platform');
        $minThreshold = (float)$request->post('min_collection_threshold', 0.00);
        $effectiveDate = (string)$request->post('effective_date', date('Y-m-d'));
        $status = (string)$request->post('status', 'active');
        $notes = trim((string)$request->post('notes', ''));

        if (empty($code)) {
            Response::redirect('/superadmin/portal-billing', 'Policy Code is required.', 'danger');
        }

        $userId = \EduSphere\Core\Auth::id();
        $approvedBy = in_array($status, ['approved', 'active']) ? $userId : null;

        Database::execute(
            "INSERT INTO revenue_sharing_policies
             (policy_code, school_id, allocation_basis, allocation_method, platform_percentage,
              school_incentive_percentage, sales_rep_initial_rate, sales_rep_recurring_rate,
              gateway_fee_bearer, min_collection_threshold, effective_date, status, notes,
              created_by, approved_by, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)",
            [
                $code, $schoolId, $basis, $method, $platformPct,
                $schoolPct, $repInitial, $repRecurring,
                $bearer, $minThreshold, $effectiveDate, $status, $notes,
                $userId, $approvedBy
            ]
        );

        $policyId = (int)Database::lastInsertId();

        AuditLogger::log('revenue_sharing_policy_created', 'revenue_sharing_policies', $policyId, [
            'code' => $code,
            'school_id' => $schoolId,
            'platform' => $platformPct,
            'school' => $schoolPct,
            'rep_initial' => $repInitial,
            'rep_recurring' => $repRecurring,
            'status' => $status,
        ]);

        Response::redirect('/superadmin/portal-billing', "Revenue sharing policy '{$code}' configured successfully!", 'success');
    }

    public function runSettlementReconciliation(Request $request): void
    {
        $startDate = (string)$request->post('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = (string)$request->post('end_date', date('Y-m-d'));

        try {
            $res = \EduSphere\Services\PaymentReconciliationService::reconcileGatewaySettlement(
                $startDate,
                $endDate,
                \EduSphere\Core\Auth::id()
            );

            $msg = "Reconciliation completed for {$startDate} to {$endDate}. Batch {$res['batch_id']}: {$res['matched_count']} matched, Discrepancy: ₦" . number_format($res['discrepancy_amount'], 2) . " ({$res['status']}).";
            Response::redirect('/superadmin/portal-billing', $msg, $res['status'] === 'matched' ? 'success' : 'warning');
        } catch (\Throwable $e) {
            Response::redirect('/superadmin/portal-billing', "Reconciliation error: " . $e->getMessage(), 'danger');
        }
    }

    public function resolveSettlementDiscrepancy(Request $request): void
    {
        $reconId = (int)$request->post('reconciliation_id', 0);
        $notes = trim((string)$request->post('notes', 'Resolved by authorized super admin'));

        try {
            \EduSphere\Services\PaymentReconciliationService::resolveDiscrepancy(
                $reconId,
                $notes,
                \EduSphere\Core\Auth::id()
            );
            Response::redirect('/superadmin/portal-billing', "Settlement discrepancy #{$reconId} manually marked as resolved.", 'success');
        } catch (\Throwable $e) {
            Response::redirect('/superadmin/portal-billing', $e->getMessage(), 'danger');
        }
    }

    public function approvePortalRefund(Request $request): void
    {
        $refundId = (int)$request->post('refund_id', 0);
        $notes = trim((string)$request->post('notes', 'Approved by authorized platform administrator'));

        try {
            \EduSphere\Services\RefundManagementService::approveAndProcessRefund(
                $refundId,
                \EduSphere\Core\Auth::id(),
                $notes
            );
            Response::redirect('/superadmin/portal-billing', "Refund #{$refundId} has been approved, executed, and symmetrically reversed across ledgers.", 'success');
        } catch (\Throwable $e) {
            Response::redirect('/superadmin/portal-billing', "Refund processing error: " . $e->getMessage(), 'danger');
        }
    }

    public function rejectPortalRefund(Request $request): void
    {
        $refundId = (int)$request->post('refund_id', 0);
        $reason = trim((string)$request->post('reason', 'Refund request rejected following compliance review'));

        try {
            \EduSphere\Services\RefundManagementService::rejectRefund(
                $refundId,
                \EduSphere\Core\Auth::id(),
                $reason
            );
            Response::redirect('/superadmin/portal-billing', "Refund request #{$refundId} has been rejected.", 'info');
        } catch (\Throwable $e) {
            Response::redirect('/superadmin/portal-billing', $e->getMessage(), 'danger');
        }
    }
}

