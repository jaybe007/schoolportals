<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Student & Parent Portal - EduSphere') ?></title>
    <link rel="stylesheet" href="<?= asset('css/edusphere.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="app-wrapper">
    <!-- Student / Parent Sidebar -->
    <aside class="app-sidebar">
        <div class="sidebar-header">
            <a href="<?= url(auth_user()['role'] === 'parent' ? 'portal/parent' : 'portal/student') ?>" class="brand-logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
                <span style="font-size: 1rem;"><?= e(current_school()['name'] ?? 'School Portal') ?></span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">My Dashboard</div>
            <?php if (auth_user()['role'] === 'parent'): ?>
                <a href="<?= url('portal/parent') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'messages') === false ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    <span>Ward Overview</span>
                </a>
                <a href="<?= url('portal/parent/messages') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'messages') !== false ? 'active' : '' ?>">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Direct Messages</span>
                </a>
            <?php else: ?>
                <a href="<?= url('portal/student') ?>" class="nav-link active">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    <span>Student Dashboard</span>
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="user-snippet">
                <div class="avatar"><?= strtoupper(substr(auth_user()['name'] ?? 'PT', 0, 2)) ?></div>
                <div style="flex: 1; min-width: 0;">
                    <div class="user-meta-name"><?= e(auth_user()['name'] ?? 'Portal User') ?></div>
                    <div class="user-meta-role"><?= e(ucfirst(auth_user()['role'] ?? 'User')) ?></div>
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
                    <span><strong><?= e(current_school()['name'] ?? '') ?></strong> &bull; Student & Family Portal</span>
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

            <?= $content ?? '' ?>
        </main>
    </div>
</div>

<script src="<?= asset('js/edusphere.js') ?>"></script>
</body>
</html>
