<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Super Admin Console - EduSphere SaaS') ?></title>
    <link rel="stylesheet" href="<?= asset('css/edusphere.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="app-wrapper">
    <!-- Super Admin Sidebar -->
    <aside class="app-sidebar">
        <div class="sidebar-header">
            <a href="<?= url('superadmin') ?>" class="brand-logo">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
                <span>EduSphere</span>
                <span class="brand-badge">HQ</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Central Operations</div>
            <a href="<?= url('superadmin') ?>" class="nav-link <?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                <span>HQ Dashboard</span>
            </a>
            <a href="<?= url('superadmin/schools') ?>" class="nav-link <?= ($activeNav ?? '') === 'schools' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 21h18M3 7v14M21 7v14M6 21V7l6-4 6 4v14M9 9h1M14 9h1M9 13h1M14 13h1M9 17h1M14 17h1"/></svg>
                <span>School Tenants</span>
            </a>
            <a href="<?= url('superadmin/portal-billing') ?>" class="nav-link <?= ($activeNav ?? '') === 'portal_billing' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>Portal Billing & Revenue</span>
            </a>
            <a href="<?= url('superadmin/subscriptions') ?>" class="nav-link <?= ($activeNav ?? '') === 'subscriptions' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                <span>Plans & Subscriptions</span>
            </a>

            <div class="nav-section-title">Sales & Governance</div>
            <a href="<?= url('sales') ?>" class="nav-link <?= ($activeNav ?? '') === 'sales' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Sales CRM & Leads</span>
            </a>
            <a href="<?= url('sales/commissions') ?>" class="nav-link <?= ($activeNav ?? '') === 'commissions' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
                <span>Commission Approvals</span>
            </a>
            <a href="<?= url('superadmin/users') ?>" class="nav-link <?= ($activeNav ?? '') === 'users' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/></svg>
                <span>Platform Users</span>
            </a>
            <a href="<?= url('superadmin/audit-logs') ?>" class="nav-link <?= ($activeNav ?? '') === 'audit' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span>NDPA Audit Trail</span>
            </a>
            <a href="<?= url('superadmin/settings') ?>" class="nav-link <?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>Global Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-snippet">
                <div class="avatar"><?= strtoupper(substr(auth_user()['name'] ?? 'SA', 0, 2)) ?></div>
                <div style="flex: 1; min-width: 0;">
                    <div class="user-meta-name"><?= e(auth_user()['name'] ?? 'Super Admin') ?></div>
                    <div class="user-meta-role">Platform Owner</div>
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
                <button id="sidebarToggle" class="btn btn-secondary btn-sm" style="display: none;">☰</button>
                <div class="tenant-badge-pill">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981;"></span>
                    <span>EduSphere Multi-Tenant HQ Engine &bull; System Operational</span>
                </div>
            </div>

            <div class="topbar-right">
                <a href="<?= url('school?school=apex') ?>" class="btn btn-secondary btn-sm" target="_blank">
                    <span>Inspect Apex Tenant</span>
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
