<div class="page-header">
    <div>
        <h1 class="page-title">Global SaaS Configuration</h1>
        <p class="page-subtitle">Configure white-label branding, global currencies, email notification servers, and compliance presets</p>
    </div>
</div>

<div style="max-width: 720px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Platform Identity & Regional Settings</div>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('superadmin/settings') ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label">Platform Product Brand Name *</label>
                    <input type="text" name="platform_name" class="form-control" value="<?= e($settings['platform_name'] ?? 'EduSphere SaaS') ?>" required>
                    <small style="color: #64748b; font-size: 0.75rem;">Can be rebranded dynamically without affecting individual school tenant domains.</small>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Default Platform Currency Symbol</label>
                        <input type="text" name="currency_symbol" class="form-control" value="<?= e($settings['currency_symbol'] ?? '₦') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Central Support & Escalations Email *</label>
                        <input type="email" name="support_email" class="form-control" value="<?= e($settings['support_email'] ?? 'support@edusphere.example') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Data Protection & Privacy Policy Framework</label>
                    <input type="text" class="form-control" value="<?= e($settings['compliance_standard'] ?? 'Nigeria Data Protection Act 2023') ?>" readonly style="background-color: #f1f5f9;">
                </div>

                <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <span>Save Global Settings</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
