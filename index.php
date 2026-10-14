<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use EduSphere\Core\Request;
use EduSphere\Core\Response;
use EduSphere\Core\Router;
use EduSphere\Core\Session;
use EduSphere\Core\Auth;
use EduSphere\Core\TenantContext;
use EduSphere\Core\SecurityHeaders;

use EduSphere\Controllers\AuthController;
use EduSphere\Controllers\SuperAdminController;
use EduSphere\Controllers\TenantController;
use EduSphere\Controllers\SalesController;
use EduSphere\Controllers\PortalController;
use EduSphere\Controllers\SchoolWebsiteController;

// 1. Load Environment Configuration
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// 2. Start Secure Session & Security Headers
Session::start();
SecurityHeaders::apply();

// 3. Resolve Tenant Context
TenantContext::resolveSchool();

// 4. Initialize Router
$router = new Router();
$request = new Request();

// Root route dispatcher
$router->get('/', function(Request $req) {
    if (Auth::check()) {
        $role = Auth::role();
        if ($role === 'super_admin') {
            Response::redirect('/superadmin');
        } elseif (in_array($role, ['sales_rep', 'sales_manager'], true)) {
            Response::redirect('/sales');
        } elseif ($role === 'student') {
            Response::redirect('/portal/student');
        } elseif ($role === 'parent') {
            Response::redirect('/portal/parent');
        } else {
            Response::redirect('/school');
        }
    } else {
        Response::redirect('/login');
    }
});

// Authentication Routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/demo-login', [AuthController::class, 'quickDemoLogin']);
$router->get('/logout', [AuthController::class, 'logout']);

// Commercial Sales Representative & Growth Partner Onboarding Routes
$router->get('/become-a-sales-rep', [AuthController::class, 'showSalesRepRegister']);
$router->post('/become-a-sales-rep', [AuthController::class, 'registerSalesRep']);
$router->get('/sales/register', [AuthController::class, 'showSalesRepRegister']);
$router->post('/sales/register', [AuthController::class, 'registerSalesRep']);

// Super Administrator HQ Routes
$router->group(['prefix' => '/superadmin', 'middleware' => ['auth', 'role:super_admin']], function(Router $r) {
    $r->get('/', [SuperAdminController::class, 'dashboard']);
    $r->get('/schools', [SuperAdminController::class, 'schools']);
    $r->post('/schools/create', [SuperAdminController::class, 'createSchool']);
    $r->post('/schools/{id}/toggle-status', [SuperAdminController::class, 'toggleSchoolStatus']);
    $r->post('/schools/{id}/add-domain', [SuperAdminController::class, 'addDomain']);
    $r->get('/portal-billing', [SuperAdminController::class, 'portalBilling']);
    $r->post('/portal-billing/pricing', [SuperAdminController::class, 'createPricingPolicy']);
    $r->post('/portal-billing/payouts/{id}/approve', [SuperAdminController::class, 'approvePayout']);
    $r->get('/subscriptions', [SuperAdminController::class, 'subscriptions']);
    $r->post('/subscriptions/create-plan', [SuperAdminController::class, 'createPlan']);
    $r->get('/users', [SuperAdminController::class, 'users']);
    $r->get('/audit-logs', [SuperAdminController::class, 'auditLogs']);
    $r->get('/settings', [SuperAdminController::class, 'settings']);
    $r->post('/settings', [SuperAdminController::class, 'settings']);
});

// Tenant School Management Portal Routes
$router->group(['prefix' => '/school', 'middleware' => ['auth', 'role:school_admin,principal,registrar,teacher,bursar,super_admin']], function(Router $r) {
    $r->get('/', [TenantController::class, 'dashboard']);
    $r->get('/admissions', [TenantController::class, 'admissions']);
    $r->post('/admissions/{id}/process', [TenantController::class, 'processAdmission']);
    $r->get('/students', [TenantController::class, 'students']);
    $r->post('/students/create', [TenantController::class, 'createStudent']);
    $r->get('/students/{id}/report-card', [TenantController::class, 'downloadReportCard']);
    $r->get('/academics', [TenantController::class, 'academics']);
    $r->post('/academics/create-session', [TenantController::class, 'createSession']);
    $r->post('/academics/create-arm', [TenantController::class, 'createClassArm']);
    $r->post('/academics/create-subject', [TenantController::class, 'createSubject']);
    $r->get('/attendance', [TenantController::class, 'attendance']);
    $r->post('/attendance', [TenantController::class, 'attendance']);
    $r->get('/exams', [TenantController::class, 'exams']);
    $r->post('/exams', [TenantController::class, 'exams']);
    $r->get('/staff', [TenantController::class, 'staff']);
    $r->post('/staff/create', [TenantController::class, 'createStaff']);
    $r->get('/finance', [TenantController::class, 'finance']);
    $r->post('/finance', [TenantController::class, 'finance']);
    $r->post('/finance/create-invoice', [TenantController::class, 'createInvoice']);
    $r->get('/finance/receipts/{id}/pdf', [TenantController::class, 'downloadReceipt']);
    $r->get('/finance/invoices/{id}/pdf', [TenantController::class, 'downloadInvoice']);
    $r->get('/term-portal-fee', [TenantController::class, 'termPortalFee']);
    $r->post('/term-portal-fee/{id}/accept', [TenantController::class, 'acceptFeeAgreement']);
    $r->post('/term-portal-fee/{id}/reject', [TenantController::class, 'rejectFeeAgreement']);
    $r->post('/term-portal-fee/generate-invoices', [TenantController::class, 'generatePortalInvoices']);
    $r->post('/term-portal-fee/publish-notice', [TenantController::class, 'publishBillingNotice']);
    $r->get('/incentives', [TenantController::class, 'incentives']);
    $r->post('/incentives/request-payout', [TenantController::class, 'requestIncentivePayout']);
    $r->get('/library', [TenantController::class, 'library']);
    $r->get('/notices', [TenantController::class, 'notices']);
    $r->post('/notices/create', [TenantController::class, 'createNotice']);
    $r->post('/notices/{id}/delete', [TenantController::class, 'deleteNotice']);
    $r->get('/messages', [TenantController::class, 'messages']);
    $r->post('/messages/send', [TenantController::class, 'sendMessageToParent']);
    $r->post('/messages/{id}/reply', [TenantController::class, 'replyMessage']);
    $r->get('/website-cms', [TenantController::class, 'websiteCms']);
    $r->post('/website-cms', [TenantController::class, 'websiteCms']);
    $r->get('/settings', [TenantController::class, 'settings']);
    $r->post('/settings', [TenantController::class, 'settings']);
});

// Sales CRM & Commercial Engine Routes
$router->group(['prefix' => '/sales', 'middleware' => ['auth', 'role:sales_rep,sales_manager,super_admin']], function(Router $r) {
    $r->get('/', [SalesController::class, 'dashboard']);
    $r->get('/pipeline', [SalesController::class, 'pipeline']);
    $r->get('/leads', [SalesController::class, 'leads']);
    $r->post('/leads', [SalesController::class, 'leads']);
    $r->post('/leads/{id}/move-stage', [SalesController::class, 'moveLeadStage']);
    $r->get('/commissions', [SalesController::class, 'commissions']);
    $r->post('/commissions/{id}/approve', [SalesController::class, 'approveCommission']);
    $r->post('/commissions/{id}/pay', [SalesController::class, 'payCommission']);
    $r->post('/portal-commissions/{id}/approve', [SalesController::class, 'approvePortalCommission']);
    $r->post('/portal-commissions/{id}/pay', [SalesController::class, 'payPortalCommission']);
});

// Student & Parent Portals
$router->group(['prefix' => '/portal', 'middleware' => ['auth']], function(Router $r) {
    $r->get('/student', [PortalController::class, 'studentDashboard'], ['role:student,super_admin']);
    $r->post('/student/pay-fee', [PortalController::class, 'payStudentFee'], ['role:student,super_admin']);
    $r->post('/student/pay-portal-fee', [PortalController::class, 'payStudentPortalFee'], ['role:student,super_admin']);
    $r->get('/parent', [PortalController::class, 'parentDashboard'], ['role:parent,super_admin']);
    $r->post('/parent/pay-portal-fee', [PortalController::class, 'payPortalInvoice'], ['role:parent,super_admin']);
    $r->post('/parent/dispute-portal-fee', [PortalController::class, 'disputePortalInvoice'], ['role:parent,super_admin']);
    $r->get('/parent/messages', [PortalController::class, 'parentMessages'], ['role:parent,super_admin']);
    $r->post('/parent/messages/send', [PortalController::class, 'sendParentMessage'], ['role:parent,super_admin']);
    $r->post('/parent/messages/{id}/reply', [PortalController::class, 'replyParentMessage'], ['role:parent,super_admin']);
    $r->get('/receipts/{id}/pdf', [PortalController::class, 'downloadReceipt'], ['role:student,parent,super_admin']);
    $r->get('/invoices/{id}/pdf', [PortalController::class, 'downloadInvoice'], ['role:student,parent,super_admin']);
    $r->get('/portal-payments/{id}/pdf', [PortalController::class, 'downloadPortalReceipt'], ['role:student,parent,super_admin']);
});

// Public Tenant School Website Routes (Accessible by parents, prospective applicants, and visitors)
$router->get('/site/{slug}/apply', [SchoolWebsiteController::class, 'showApplyForm']);
$router->post('/site/{slug}/apply', [SchoolWebsiteController::class, 'submitApplication']);
$router->get('/site/{slug}', [SchoolWebsiteController::class, 'page']);
$router->get('/site/{slug}/{page}', [SchoolWebsiteController::class, 'page']);

// Dispatch Request
$router->dispatch($request);
