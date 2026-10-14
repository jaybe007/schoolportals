<!-- Hero Banner -->
<div class="website-hero">
    <div style="max-width: 900px; margin: 0 auto;">
        <span class="badge badge-info" style="background: rgba(255, 255, 255, 0.15); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.25); margin-bottom: 1.25rem;">
            <?= e($school['name']) ?> &bull; <?= e($school['state'] ?? 'Nigeria') ?>
        </span>
        <h1 style="color: #ffffff; font-size: 2.75rem; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 1rem; line-height: 1.15;">
            <?= e($page['banner_title'] ?? $school['name']) ?>
        </h1>
        <p style="color: #cbd5e1; font-size: 1.125rem; max-width: 700px; margin: 0 auto 2rem; line-height: 1.6;">
            <?= e($page['banner_subtitle'] ?? ($school['motto'] ?? '')) ?>
        </p>
        <div style="display: flex; justify-content: center; gap: 1rem;">
            <a href="<?= url('site/' . $currentSlug . '/apply') ?>" class="btn btn-primary" style="padding: 0.85rem 1.75rem; font-size: 1rem;">
                Apply for Admission
            </a>
            <a href="<?= url('login') ?>" class="btn btn-secondary" style="padding: 0.85rem 1.75rem; font-size: 1rem; background: rgba(255, 255, 255, 0.1); color: white; border-color: rgba(255, 255, 255, 0.2);">
                Parent / Staff Login
            </a>
        </div>
    </div>
</div>

<!-- Main Body -->
<div style="max-width: 1000px; margin: 3.5rem auto 0; padding: 0 1.5rem;">
    <div style="background: #ffffff; border: 1px solid var(--color-slate-200); border-radius: var(--radius-lg); padding: 2.5rem; box-shadow: var(--shadow-sm); line-height: 1.8; font-size: 1.0625rem; color: #334155;">
        <h2 style="font-size: 1.75rem; color: #0f172a; margin-bottom: 1.25rem;">
            <?= e($page['title'] ?? 'Welcome') ?>
        </h2>
        <div>
            <?= nl2br(e($page['content'] ?? '')) ?>
        </div>
    </div>

    <!-- Highlights Grid -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 2.5rem;">
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 1.5rem; text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; color: #1e3a8a; margin-bottom: 0.25rem;">100%</div>
            <div style="font-weight: 600; color: #0f172a; font-size: 0.9375rem;">WAEC & BECE Success</div>
            <p style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem;">Consistently high distinction performance across all academic subjects.</p>
        </div>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 1.5rem; text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; color: #059669; margin-bottom: 0.25rem;">15:1</div>
            <div style="font-weight: 600; color: #0f172a; font-size: 0.9375rem;">Student to Teacher Ratio</div>
            <p style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem;">Personalized instructional attention and dedicated mentor support.</p>
        </div>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 1.5rem; text-align: center;">
            <div style="font-size: 2rem; font-weight: 800; color: #7c3aed; margin-bottom: 0.25rem;">STEM & ICT</div>
            <div style="font-weight: 600; color: #0f172a; font-size: 0.9375rem;">Robotics & Sciences</div>
            <p style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem;">Modern computer labs, science apparatus and digital learning skills.</p>
        </div>
    </div>
</div>
