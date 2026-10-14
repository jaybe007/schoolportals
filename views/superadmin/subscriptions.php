<div class="page-header">
    <div>
        <h1 class="page-title">Subscription Plans & Billing Ledgers</h1>
        <p class="page-subtitle">Configure tiered pricing, feature matrix, invoices, and verified platform transaction revenue</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('createPlanModal')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Create Subscription Plan</span>
        </button>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <?php foreach ($plans as $p): ?>
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <div>
                        <div style="font-size: 1.125rem; font-weight: 700; color: #0f172a;"><?= e($p['name']) ?></div>
                        <div style="font-size: 0.75rem; color: #64748b; font-family: monospace;"><?= e($p['code']) ?></div>
                    </div>
                    <span class="badge badge-success"><?= e($p['billing_cycle']) ?></span>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #1e3a8a; margin-bottom: 0.5rem;">
                    <?= money($p['price']) ?><span style="font-size: 0.8125rem; font-weight: 500; color: #64748b;"> / <?= e($p['billing_cycle']) ?></span>
                </div>
                <p style="font-size: 0.8125rem; color: #475569; margin-bottom: 1rem;"><?= e($p['description']) ?></p>
                <div style="font-size: 0.8125rem; color: #334155; border-top: 1px solid #f1f5f9; padding-top: 0.75rem;">
                    <div>&bull; Max Students: <strong><?= number_format((int)$p['student_limit']) ?></strong></div>
                    <div>&bull; Max Staff: <strong><?= number_format((int)$p['staff_limit']) ?></strong></div>
                    <div>&bull; Paystack Direct Integration</div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Verified Platform Invoices & Subscription Transactions</div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>School</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Billing Cycle</th>
                    <th>Coverage Period</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $sub): ?>
                    <tr>
                        <td><code><?= e($sub['invoice_number']) ?></code></td>
                        <td>
                            <div style="font-weight: 600;"><?= e($sub['school_name']) ?></div>
                        </td>
                        <td><?= e($sub['plan_name']) ?></td>
                        <td style="font-weight: 700; color: #059669;"><?= money($sub['amount']) ?></td>
                        <td style="text-transform: capitalize;"><?= e($sub['billing_cycle']) ?></td>
                        <td style="font-size: 0.8125rem;">
                            <?= format_date($sub['start_date']) ?> to <?= format_date($sub['end_date']) ?>
                        </td>
                        <td>
                            <span class="badge badge-success">Verified Paid</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Plan Modal -->
<div id="createPlanModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 540px;">
        <form method="POST" action="<?= url('superadmin/subscriptions/create-plan') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="card-title">Create Subscription Plan Tier</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('createPlanModal')">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Plan Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Enterprise Prestige" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unique Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. enterprise-prestige" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Recurring Price (₦) *</label>
                        <input type="number" step="1000" name="price" class="form-control" placeholder="1250000" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Billing Cycle *</label>
                        <select name="billing_cycle" class="form-select" required>
                            <option value="annual">Annual / Yearly</option>
                            <option value="termly">Termly (3 per year)</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Max Student Limit *</label>
                        <input type="number" name="student_limit" class="form-control" value="2500" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Faculty/Staff Limit *</label>
                        <input type="number" name="staff_limit" class="form-control" value="150" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tier Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Full unrestricted access for large secondary colleges with multiple campuses."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createPlanModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Publish Plan Tier</button>
            </div>
        </form>
    </div>
</div>
