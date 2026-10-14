<?php

declare(strict_types=1);

namespace EduSphere\Controllers;

use EduSphere\Core\Auth;
use EduSphere\Core\Database;
use EduSphere\Core\Request;
use EduSphere\Core\Response;
use EduSphere\Core\View;
use EduSphere\Core\AuditLogger;
use EduSphere\Core\RateLimiter;

class AuthController
{
    public function showLogin(Request $request): void
    {
        View::render('auth.login', ['title' => 'Sign In - EduSphere SaaS'], 'auth');
    }

    public function login(Request $request): void
    {
        $email = trim((string)$request->post('email', ''));
        $password = (string)$request->post('password', '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $rateKey = "login:{$ip}:" . strtolower($email);

        if (empty($email) || empty($password)) {
            Response::redirect('/login', 'Please provide both email and password.', 'danger');
        }

        // Maximum 5 failed attempts per 15 minutes (900 seconds)
        if (RateLimiter::tooManyAttempts($rateKey, 5, 900)) {
            $secondsRemaining = RateLimiter::availableIn($rateKey, 900);
            $minutesRemaining = ceil($secondsRemaining / 60);
            AuditLogger::log('rate_limit_lockout', 'auth', null, [
                'email' => $email,
                'ip' => $ip,
                'seconds' => $secondsRemaining
            ]);
            Response::redirect('/login', "Too many failed login attempts. For security, please wait {$minutesRemaining} minute(s) before trying again.", 'danger');
        }

        $user = Database::fetch("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            RateLimiter::hit($rateKey, 900);
            AuditLogger::log('user_login_failed', 'auth', null, ['email' => $email, 'ip' => $ip]);
            Response::redirect('/login', 'Invalid email or password.', 'danger');
        }

        // Check if tenant school is active
        if ($user['school_id'] !== null) {
            $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$user['school_id']]);
            if ($school && $school['status'] === 'suspended') {
                Response::redirect('/login', 'Your school account is currently suspended. Please contact platform support.', 'danger');
            }
        }

        // Reset rate limiter on successful authentication
        RateLimiter::clear($rateKey);

        Auth::login($user);
        AuditLogger::log('user_login_success', 'user', $user['id'], ['email' => $email]);

        $this->redirectByRole($user['role']);
    }

    public function quickDemoLogin(Request $request): void
    {
        $isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';
        $allowDemo = (($_ENV['ALLOW_DEMO_LOGIN'] ?? 'true') === 'true') && !$isProduction;

        // Strictly disallow backdoor demo login in production environments
        if (!$allowDemo) {
            AuditLogger::log('unauthorized_production_demo_login_attempt', 'auth', null, [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'query' => $_SERVER['QUERY_STRING'] ?? '',
            ]);
            http_response_code(403);
            View::render('errors.403', ['message' => 'Demo quick-login is strictly disabled in production mode.'], 'auth');
            exit;
        }

        $email = $request->get('email');
        if (!empty($email)) {
            $user = Database::fetch("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1", [$email]);
        } else {
            $role = $request->get('role', 'super_admin');
            $schoolId = $request->get('school_id');
            if ($schoolId) {
                $user = Database::fetch("SELECT * FROM users WHERE role = ? AND school_id = ? AND is_active = 1 LIMIT 1", [$role, (int)$schoolId]);
            } else {
                $user = Database::fetch("SELECT * FROM users WHERE role = ? AND is_active = 1 LIMIT 1", [$role]);
            }
        }

        if (!$user) {
            Response::redirect('/login', "Demo account not found.", 'danger');
        }

        Auth::login($user);
        AuditLogger::log('demo_quick_login', 'user', $user['id'], ['email' => $user['email']]);

        $this->redirectByRole($user['role']);
    }

    public function logout(Request $request): void
    {
        AuditLogger::log('user_logout', 'user', Auth::id());
        Auth::logout();
        Response::redirect('/login', 'You have been successfully logged out.', 'info');
    }

    public function showSalesRepRegister(Request $request): void
    {
        View::render('auth.sales_rep_register', [
            'title' => 'Register as Commercial Sales Partner & Onboard a School - EduSphere',
        ], 'auth');
    }

    public function registerSalesRep(Request $request): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        // 1. Rep Personal Info
        $repName = trim((string)$request->post('rep_name', ''));
        $repEmail = strtolower(trim((string)$request->post('rep_email', '')));
        $repPhone = trim((string)$request->post('rep_phone', ''));
        $repPassword = (string)$request->post('rep_password', '');
        $repPasswordConfirm = (string)$request->post('rep_password_confirm', '');
        $territory = trim((string)$request->post('territory', ''));

        // 2. Settlement Bank Info
        $bankName = trim((string)$request->post('bank_name', ''));
        $accountNumber = trim((string)$request->post('account_number', ''));
        $accountName = trim((string)$request->post('account_name', ''));

        // 3. Agreement Acceptance
        $agreeTerms = (bool)$request->post('agree_terms');
        $agreeConduct = (bool)$request->post('agree_conduct');
        $agreePayout = (bool)$request->post('agree_payout');

        // 4. Prospect School Dossier
        $schoolName = trim((string)$request->post('school_name', ''));
        $schoolType = (string)$request->post('school_type', 'combined');
        $schoolState = trim((string)$request->post('school_state', ''));
        $schoolAddress = trim((string)$request->post('school_address', ''));
        $contactPerson = trim((string)$request->post('contact_person', ''));
        $schoolPhone = trim((string)$request->post('school_phone', ''));
        $schoolEmail = strtolower(trim((string)$request->post('school_email', '')));
        $estimatedStudents = max(20, (int)$request->post('estimated_students', 300));
        $currentSystem = trim((string)$request->post('current_system', ''));
        $notes = trim((string)$request->post('notes', ''));

        // Validation Checks
        if (!$agreeTerms || !$agreeConduct || !$agreePayout) {
            Response::redirect('/become-a-sales-rep', 'You must read and agree to all terms of the Commercial Sales Partner Agreement before proceeding.', 'danger');
        }

        if (empty($repName) || empty($repEmail) || empty($repPhone) || empty($repPassword) || empty($territory)) {
            Response::redirect('/become-a-sales-rep', 'Please fill in all your representative personal details.', 'danger');
        }

        if (!filter_var($repEmail, FILTER_VALIDATE_EMAIL)) {
            Response::redirect('/become-a-sales-rep', 'Please provide a valid email address.', 'danger');
        }

        if (strlen($repPassword) < 6) {
            Response::redirect('/become-a-sales-rep', 'Password must be at least 6 characters long.', 'danger');
        }

        if ($repPassword !== $repPasswordConfirm) {
            Response::redirect('/become-a-sales-rep', 'Passwords do not match. Please re-enter your password.', 'danger');
        }

        if (empty($bankName) || empty($accountNumber) || empty($accountName)) {
            Response::redirect('/become-a-sales-rep', 'Bank Name, 10-digit Account Number, and Account Name are required for commission payouts.', 'danger');
        }

        if (empty($schoolName) || empty($contactPerson) || empty($schoolPhone) || empty($schoolState)) {
            Response::redirect('/become-a-sales-rep', 'Please provide the required prospective school information (School Name, Contact Person, Phone, and State).', 'danger');
        }

        // Check if user already exists
        $existingUser = Database::fetch("SELECT id FROM users WHERE email = ?", [$repEmail]);
        if ($existingUser) {
            Response::redirect('/become-a-sales-rep', 'An account with this email address already exists. Please log in instead or use another email.', 'danger');
        }

        // Check if school already exists as active tenant
        $existingSchool = Database::fetch("SELECT id, name FROM schools WHERE name = ?", [$schoolName]);
        if ($existingSchool) {
            Response::redirect('/become-a-sales-rep', "School '{$schoolName}' is already a registered school on EduSphere. Please register a new prospect school.", 'warning');
        }

        // Check if lead already exists in CRM
        $existingLead = Database::fetch(
            "SELECT id, school_name FROM sales_leads WHERE (phone = ? OR (email IS NOT NULL AND email != '' AND email = ?) OR school_name = ?) LIMIT 1",
            [$schoolPhone, $schoolEmail, $schoolName]
        );
        if ($existingLead) {
            Response::redirect('/become-a-sales-rep', "A CRM prospect matching this school or phone number ('{$existingLead['school_name']}') is already registered.", 'warning');
        }

        // Generate Unique Rep Code (REP-TER-XXXX)
        $cleanTerritory = strtoupper(preg_replace('/[^A-Za-z]/', '', $territory));
        $territoryPrefix = substr($cleanTerritory, 0, 3);
        if (strlen($territoryPrefix) < 3) {
            $territoryPrefix = 'SLS';
        }

        $repCode = '';
        do {
            $repCode = 'REP-' . $territoryPrefix . '-' . rand(1000, 9999);
            $codeCheck = Database::fetch("SELECT id FROM sales_representatives WHERE rep_code = ?", [$repCode]);
        } while ($codeCheck);

        // Standardized Terms Snapshot for Legal Compliance
        $acceptedAt = date('Y-m-d H:i:s');
        $termsSnapshot = "EDUSPHERE COMMERCIAL SALES PARTNER AGREEMENT (v1.0)\n" .
            "Signed Digitally By: {$repName} ({$repEmail})\n" .
            "Phone: {$repPhone}\n" .
            "Territory: {$territory}\n" .
            "Settlement Bank: {$bankName} | Account: {$accountNumber} | Name: {$accountName}\n" .
            "Timestamp: {$acceptedAt}\n" .
            "IP Address: {$ip}\n" .
            "User Agent: {$userAgent}\n\n" .
            "CORE TERMS AGREED:\n" .
            "1. Commission Schedule: 15.0% on verified initial academic term portal fees and subscription payments, and 7.5% recurring commission on subsequent terms.\n" .
            "2. Governance & Anti-Collusion: All commission payouts require Sales Manager compliance verification. Representatives cannot approve their own commissions.\n" .
            "3. Code of Conduct: Representation must adhere to EduSphere standards. Direct submission of authentic school prospect data required.\n" .
            "4. Payout Channel: Settlements are electronically disbursed to the verified Nigerian bank account provided.";

        $newUser = null;
        $newRepId = 0;
        $newLeadId = 0;

        Database::transaction(function($pdo) use (
            $repName, $repEmail, $repPhone, $repPassword, $territory,
            $bankName, $accountNumber, $accountName, $repCode, $ip, $userAgent,
            $termsSnapshot, $schoolName, $schoolType, $schoolState, $schoolAddress,
            $contactPerson, $schoolPhone, $schoolEmail, $estimatedStudents, $currentSystem, $notes,
            &$newUser, &$newRepId, &$newLeadId
        ) {
            // 1. Create User
            $pwdHash = password_hash($repPassword, PASSWORD_BCRYPT);
            Database::execute(
                "INSERT INTO users (school_id, name, email, phone, password_hash, role, is_active)
                 VALUES (NULL, ?, ?, ?, ?, 'sales_rep', 1)",
                [$repName, $repEmail, $repPhone, $pwdHash]
            );
            $userId = (int)Database::lastInsertId();

            // 2. Create Sales Representative Profile
            Database::execute(
                "INSERT INTO sales_representatives
                 (user_id, rep_code, territory, phone, commission_type, commission_rate, monthly_target, status, notes,
                  bank_name, account_number, account_name, agreement_accepted_at, agreement_version, agreement_ip_address)
                 VALUES (?, ?, ?, ?, 'percentage', 15.00, 500000.00, 'active', ?, ?, ?, ?, NOW(), 'v1.0', ?)",
                [
                    $userId, $repCode, $territory, $repPhone,
                    "Self-registered partner. Territory: {$territory}",
                    $bankName, $accountNumber, $accountName, $ip
                ]
            );
            $newRepId = (int)Database::lastInsertId();

            // 3. Log Legal Agreement Record
            Database::execute(
                "INSERT INTO sales_partner_agreements
                 (rep_id, user_id, agreement_version, signer_name, signer_email, signer_phone, ip_address, user_agent, terms_snapshot, accepted_at)
                 VALUES (?, ?, 'v1.0', ?, ?, ?, ?, ?, ?, NOW())",
                [$newRepId, $userId, $repName, $repEmail, $repPhone, $ip, $userAgent, $termsSnapshot]
            );

            // 4. Create Prospective School Lead
            $combinedNotes = "Current Management: " . ($currentSystem ?: 'Not specified');
            if (!empty($notes)) {
                $combinedNotes .= " | Notes: " . $notes;
            }
            $combinedNotes .= " | School Category: " . ucfirst($schoolType);

            Database::execute(
                "INSERT INTO sales_leads
                 (rep_id, school_name, contact_person, email, phone, address, state, estimated_students, lead_source, stage, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Partner Self-Registration', 'new_lead', ?)",
                [
                    $newRepId, $schoolName, $contactPerson, $schoolEmail ?: null, $schoolPhone,
                    $schoolAddress ?: null, $schoolState, $estimatedStudents, $combinedNotes
                ]
            );
            $newLeadId = (int)Database::lastInsertId();

            // 5. Activity Log
            Database::execute(
                "INSERT INTO sales_activities (lead_id, rep_id, activity_type, summary, completed_at)
                 VALUES (?, ?, 'stage_change', ?, NOW())",
                [$newLeadId, $newRepId, "Prospect school registered during commercial sales partner onboarding"]
            );

            // Fetch created user for session
            $newUser = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        });

        // Audit Logging
        AuditLogger::log('sales_rep_self_registered', 'sales_representative', $newRepId, [
            'rep_code' => $repCode,
            'email' => $repEmail,
            'initial_lead_id' => $newLeadId,
            'school_name' => $schoolName,
            'ip' => $ip,
        ]);

        // Auto-authenticate rep into session
        if ($newUser) {
            Auth::login($newUser);
        }

        Response::redirect(
            '/sales',
            "Congratulations {$repName}! Your Sales Partner account has been activated with Partner Code: {$repCode}. Your initial prospective school '{$schoolName}' is now active in your CRM pipeline.",
            'success'
        );
    }

    private function redirectByRole(string $role): void
    {
        switch ($role) {
            case 'super_admin':
                Response::redirect('/superadmin');
                break;
            case 'sales_rep':
            case 'sales_manager':
                Response::redirect('/sales');
                break;
            case 'student':
                Response::redirect('/portal/student');
                break;
            case 'parent':
                Response::redirect('/portal/parent');
                break;
            default:
                // school_admin, principal, registrar, teacher, bursar
                Response::redirect('/school');
                break;
        }
    }
}
