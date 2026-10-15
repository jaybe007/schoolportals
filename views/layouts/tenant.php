<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'School Management Portal - EduSphere') ?></title>
    <link rel="stylesheet" href="<?= asset('css/edusphere.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="app-wrapper">
    <!-- Tenant Sidebar -->
    <aside class="app-sidebar">
        <div class="sidebar-header">
            <a href="<?= url('school') ?>" class="brand-logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
                <span style="font-size: 1.1rem;"><?= e(current_school()['name'] ?? 'EduSphere School') ?></span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Core Management</div>
            <a href="<?= url('school') ?>" class="nav-link <?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                <span>Overview</span>
            </a>
            <a href="<?= url('school/admissions') ?>" class="nav-link <?= ($activeNav ?? '') === 'admissions' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                <span>Admissions & Inquiries</span>
            </a>
            <a href="<?= url('school/students') ?>" class="nav-link <?= ($activeNav ?? '') === 'students' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Student Directory</span>
            </a>

            <div class="nav-section-title">Academics & Operations</div>
            <a href="<?= url('school/academics') ?>" class="nav-link <?= ($activeNav ?? '') === 'academics' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10M6 10h10"/></svg>
                <span>Academic Structure</span>
            </a>
            <a href="<?= url('school/attendance') ?>" class="nav-link <?= ($activeNav ?? '') === 'attendance' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                <span>Daily Attendance</span>
            </a>
            <a href="<?= url('school/exams') ?>" class="nav-link <?= ($activeNav ?? '') === 'exams' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15h6M9 11h6"/></svg>
                <span>Exams & Report Cards</span>
            </a>
            <a href="<?= url('school/staff') ?>" class="nav-link <?= ($activeNav ?? '') === 'staff' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Teachers & Staff</span>
            </a>

            <div class="nav-section-title">Communication & Notices</div>
            <a href="<?= url('school/notices') ?>" class="nav-link <?= ($activeNav ?? '') === 'notices' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span>Notice Board</span>
            </a>
            <a href="<?= url('school/messages') ?>" class="nav-link <?= ($activeNav ?? '') === 'messages' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Parent Messages</span>
            </a>

            <div class="nav-section-title">Finance & Bursary</div>
            <a href="<?= url('school/finance') ?>" class="nav-link <?= ($activeNav ?? '') === 'finance' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/><path d="M12 14h.01"/></svg>
                <span>Fees & Invoices</span>
            </a>
            <a href="<?= url('school/term-portal-fee') ?>" class="nav-link <?= ($activeNav ?? '') === 'term_portal_fee' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/></svg>
                <span>Term Portal Fee</span>
            </a>
            <a href="<?= url('school/incentives') ?>" class="nav-link <?= ($activeNav ?? '') === 'incentives' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
                <span>School Incentives</span>
            </a>
            <a href="<?= url('school/billing-notices') ?>" class="nav-link <?= ($activeNav ?? '') === 'billing_notices' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                <span>Billing Notices & Comms</span>
            </a>

            <div class="nav-section-title">Support & Website</div>
            <a href="<?= url('school/library') ?>" class="nav-link <?= ($activeNav ?? '') === 'library' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m16 6 4 14M12 6v14M8 8v12M4 4v16"/></svg>
                <span>Library & Assets</span>
            </a>
            <a href="<?= url('school/website-cms') ?>" class="nav-link <?= ($activeNav ?? '') === 'website-cms' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                <span>Public Website CMS</span>
            </a>
            <a href="<?= url('school/settings') ?>" class="nav-link <?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>School Profile & Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-snippet">
                <div class="avatar"><?= strtoupper(substr(auth_user()['name'] ?? 'SC', 0, 2)) ?></div>
                <div style="flex: 1; min-width: 0;">
                    <div class="user-meta-name"><?= e(auth_user()['name'] ?? 'School Admin') ?></div>
                    <div class="user-meta-role"><?= e(str_replace('_', ' ', auth_user()['role'] ?? 'Staff')) ?></div>
                </div>
                <a href="<?= url('logout') ?>" title="Log out" style="color: #94a3b8;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Workspace -->
    <div class="app-main">
        <header class="app-topbar">
            <div class="topbar-left">
                <div class="tenant-badge-pill">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #3b82f6;"></span>
                    <span><strong><?= e(current_school()['name'] ?? 'Tenant') ?></strong> &bull; <?= e(current_school()['code'] ?? '') ?></span>
                </div>
            </div>

            <div class="topbar-right">
                <a href="<?= url('site/' . (current_school()['slug'] ?? 'apex')) ?>" class="btn btn-secondary btn-sm" target="_blank">
                    <span>View Public Website</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                </a>
                <a href="<?= url('logout') ?>" class="btn btn-danger btn-sm">Log Out</a>
            </div>
        </header>

        <main class="page-container">
            <?php if ($msg = flash('success')): ?>
                <div class="alert alert-success"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('danger')): ?>
                <div class="alert alert-danger"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('warning')): ?>
                <div class="alert alert-warning"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('info')): ?>
                <div class="alert alert-info"><?= e($msg) ?></div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </main>
    </div>
</div>

<script src="<?= asset('js/edusphere.js') ?>"></script>
</body>
</html>
