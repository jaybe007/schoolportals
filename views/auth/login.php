<form method="POST" action="<?= url('login') ?>">
    <?= csrf_field() ?>

    <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="name@edusphere.example" required autofocus>
    </div>

    <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••••••" required>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem; padding: 0.75rem;">
        <span>Sign In to Portal</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </button>
</form>

<!-- Commercial Sales Partner Callout -->
<div style="margin-top: 1.25rem; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #86efac; border-radius: 0.5rem; padding: 0.875rem 1rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
    <div>
        <div style="font-weight: 700; color: #166534; font-size: 0.8125rem; display: flex; align-items: center; gap: 0.35rem;">
            <span>💼 Become a Commercial Sales Partner</span>
        </div>
        <div style="font-size: 0.75rem; color: #15803d; margin-top: 0.15rem;">
            Onboard schools & earn up to 15% recurring commissions.
        </div>
    </div>
    <a href="<?= url('become-a-sales-rep') ?>" class="btn btn-sm" style="background: #16a34a; color: #ffffff; font-weight: 700; white-space: nowrap; padding: 0.4rem 0.75rem; font-size: 0.75rem; border-radius: 0.375rem;">
        Register Now &rarr;
    </a>
</div>

<div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-slate-200);">
    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-slate-500); margin-bottom: 0.75rem; text-align: center;">
        ⚡ Instant Quick-Switch Demo Accounts
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; font-size: 0.8125rem;">
        <a href="<?= url('demo-login?role=super_admin') ?>" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">
            👑 Super Admin
        </a>
        <a href="<?= url('demo-login?role=school_admin') ?>" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">
            🏫 School Admin
        </a>
        <a href="<?= url('demo-login?role=principal') ?>" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">
            🎓 Principal
        </a>
        <a href="<?= url('demo-login?role=bursar') ?>" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">
            💳 Bursar / Finance
        </a>
        <a href="<?= url('demo-login?role=teacher') ?>" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">
            👨‍🏫 Teacher
        </a>
        <a href="<?= url('demo-login?role=registrar') ?>" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">
            📋 Registrar
        </a>
        <a href="<?= url('demo-login?role=student') ?>" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">
            🎒 Student Portal
        </a>
        <a href="<?= url('demo-login?role=parent') ?>" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">
            👨‍👩‍👧 Parent Portal
        </a>
        <a href="<?= url('demo-login?role=sales_rep') ?>" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">
            💼 Sales Rep CRM
        </a>
        <a href="<?= url('demo-login?role=sales_manager') ?>" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">
            📊 Sales Manager
        </a>
    </div>
</div>
