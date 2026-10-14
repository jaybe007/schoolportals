<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'EduSphere - Enterprise School Management SaaS') ?></title>
    <link rel="stylesheet" href="<?= asset('css/edusphere.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top right, #1e3a8a, #0a1128);
            padding: 2rem 1rem;
        }
        .auth-box {
            background: #ffffff;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 460px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .auth-box-header {
            background: linear-gradient(135deg, var(--color-primary-950), var(--color-primary-900));
            padding: 2rem;
            text-align: center;
            color: #ffffff;
            border-bottom: 3px solid var(--color-primary-500);
        }
        .auth-box-body {
            padding: 2rem;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-box">
        <div class="auth-box-header">
            <div style="font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
                <span>EduSphere</span>
                <span class="brand-badge">SaaS</span>
            </div>
            <p style="color: #94a3b8; font-size: 0.8125rem; margin-top: 0.5rem;">
                Enterprise Multi-Tenant School Administration Engine
            </p>
        </div>

        <div class="auth-box-body">
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
        </div>
    </div>
</div>

<script src="<?= asset('js/edusphere.js') ?>"></script>
</body>
</html>
