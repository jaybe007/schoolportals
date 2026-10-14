<div style="text-align: center; padding: 3rem 1rem;">
    <h1 style="font-size: 3rem; color: #dc2626; margin-bottom: 0.5rem;">500</h1>
    <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">Server Error</h2>
    <p style="color: #64748b; margin-bottom: 2rem;"><?= e($message ?? 'An unexpected error occurred. Please contact the administrator.') ?></p>
    <a href="<?= url('/') ?>" class="btn btn-secondary">Go to Dashboard</a>
</div>
