<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sales CRM & Commission Engine - EduSphere') ?></title>
    <link rel="stylesheet" href="<?= asset('css/edusphere.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="app-wrapper">
    <!-- Sales CRM Sidebar -->
    <aside class="app-sidebar">
        <div class="sidebar-header">
            <a href="<?= url('sales') ?>" class="brand-logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                    <polyline points="17 6 23 6 23 12"/>
                </svg>
                <span>EduSphere</span>
                <span class="brand-badge" style="background: linear-gradient(135deg, #10b981, #059669);">CRM</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Pipeline & Deals</div>
            <a href="<?= url('sales') ?>" class="nav-link <?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                <span>Sales Dashboard</span>
            </a>
            <a href="<?= url('sales/pipeline') ?>" class="nav-link <?= ($activeNav ?? '') === 'pipeline' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg>
                <span>Visual Kanban Pipeline</span>
            </a>
            <a href="<?= url('sales/leads') ?>" class="nav-link <?= ($activeNav ?? '') === 'leads' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Prospective Schools</span>
            </a>

            <div class="nav-section-title">Compensation</div>
            <a href="<?= url('sales/commissions') ?>" class="nav-link <?= ($activeNav ?? '') === 'commissions' ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
                <span>Commission Ledger</span>
            </a>

            <?php if (auth_user()['role'] === 'super_admin'): ?>
                <div class="nav-section-title">Platform</div>
                <a href="<?= url('superadmin') ?>" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <span>Return to Super Admin</span>
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="user-snippet">
                <div class="avatar" style="background: linear-gradient(135deg, #10b981, #047857);">
                    <?= strtoupper(substr(auth_user()['name'] ?? 'SL', 0, 2)) ?>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div class="user-meta-name"><?= e(auth_user()['name'] ?? 'Sales Rep') ?></div>
                    <div class="user-meta-role"><?= e(str_replace('_', ' ', auth_user()['role'] ?? 'Sales')) ?></div>
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
                <div class="tenant-badge-pill" style="background-color: var(--color-success-50); border-color: rgba(16, 185, 129, 0.3); color: var(--color-success-600);">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #10b981;"></span>
                    <span><strong>EduSphere Commercial Engine</strong> &bull; Growth & Partnerships</span>
                </div>
            </div>

            <div class="topbar-right">
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
