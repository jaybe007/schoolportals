<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? (current_school()['name'] ?? 'School Website')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/edusphere.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background-color: #ffffff;">

<!-- Website Navigation -->
<nav class="website-nav">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: linear-gradient(135deg, #1e3a8a, #3b82f6); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.25rem;">
            <?= strtoupper(substr(current_school()['name'] ?? 'SC', 0, 1)) ?>
        </div>
        <div>
            <div style="font-weight: 800; font-size: 1.125rem; color: #0f172a;"><?= e(current_school()['name'] ?? 'School Name') ?></div>
            <div style="font-size: 0.75rem; color: #64748b;"><?= e(current_school()['motto'] ?? '') ?></div>
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 1.5rem;">
        <a href="<?= url('site/' . (current_school()['slug'] ?? 'apex')) ?>" style="font-size: 0.875rem; font-weight: 600; color: #334155;">Home</a>
        <a href="<?= url('site/' . (current_school()['slug'] ?? 'apex') . '/about') ?>" style="font-size: 0.875rem; font-weight: 600; color: #334155;">About</a>
        <a href="<?= url('site/' . (current_school()['slug'] ?? 'apex') . '/admissions') ?>" style="font-size: 0.875rem; font-weight: 600; color: #334155;">Admissions</a>
        <a href="<?= url('site/' . (current_school()['slug'] ?? 'apex') . '/academics') ?>" style="font-size: 0.875rem; font-weight: 600; color: #334155;">Academics</a>
        <a href="<?= url('site/' . (current_school()['slug'] ?? 'apex') . '/contact') ?>" style="font-size: 0.875rem; font-weight: 600; color: #334155;">Contact</a>
        <a href="<?= url('site/' . (current_school()['slug'] ?? 'apex') . '/apply') ?>" class="btn btn-primary btn-sm">Apply Online</a>
        <a href="<?= url('login') ?>" class="btn btn-secondary btn-sm">Portal Login</a>
    </div>
</nav>

<main>
    <?php if ($msg = flash('success')): ?>
        <div style="max-width: 1200px; margin: 1.5rem auto 0; padding: 0 1rem;">
            <div class="alert alert-success"><?= e($msg) ?></div>
        </div>
    <?php endif; ?>
    <?php if ($msg = flash('danger')): ?>
        <div style="max-width: 1200px; margin: 1.5rem auto 0; padding: 0 1rem;">
            <div class="alert alert-danger"><?= e($msg) ?></div>
        </div>
    <?php endif; ?>

    <?= $content ?? '' ?>
</main>

<footer style="background-color: #0f172a; color: #94a3b8; padding: 3rem 1.5rem; border-top: 1px solid #1e293b; margin-top: 4rem;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 2rem;">
        <div>
            <div style="color: white; font-weight: 700; font-size: 1.125rem;"><?= e(current_school()['name'] ?? '') ?></div>
            <p style="font-size: 0.875rem; color: #94a3b8; margin-top: 0.5rem; max-width: 320px;">
                <?= e(current_school()['address'] ?? '') ?><br>
                Tel: <?= e(current_school()['phone'] ?? '') ?><br>
                Email: <?= e(current_school()['email'] ?? '') ?>
            </p>
        </div>
        <div>
            <div style="color: white; font-weight: 600; font-size: 0.875rem; text-transform: uppercase;">Quick Portals</div>
            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.75rem; font-size: 0.875rem;">
                <a href="<?= url('login') ?>" style="color: #cbd5e1;">Staff & Admin Sign In</a>
                <a href="<?= url('login') ?>" style="color: #cbd5e1;">Student & Parent Access</a>
                <a href="<?= url('site/' . (current_school()['slug'] ?? 'apex') . '/apply') ?>" style="color: #cbd5e1;">Online Admission Application</a>
            </div>
        </div>
    </div>
    <div style="max-width: 1200px; margin: 2rem auto 0; padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.08); font-size: 0.75rem; text-align: center;">
        Powered by EduSphere Enterprise Multi-Tenant SaaS Engine &bull; All Rights Reserved &copy; <?= date('Y') ?>
    </div>
</footer>

<script src="<?= asset('js/edusphere.js') ?>"></script>
</body>
</html>
