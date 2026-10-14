<div class="page-header">
    <div>
        <h1 class="page-title">Platform Users & Role Assignments</h1>
        <p class="page-subtitle">Central oversight of all 10 specialized roles across HQ and tenant schools</p>
    </div>
    <div>
        <input type="text" class="form-control" placeholder="Search users by name, email, or role..." data-table-search="#usersTable" style="width: 300px;">
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table" id="usersTable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email & Contact</th>
                    <th>Role</th>
                    <th>Tenant Scope</th>
                    <th>2FA</th>
                    <th>Status</th>
                    <th>Last Active</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #0f172a;"><?= e($u['name']) ?></div>
                        </td>
                        <td>
                            <div><?= e($u['email']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($u['phone'] ?? '—') ?></div>
                        </td>
                        <td>
                            <span class="badge badge-<?= $u['role'] === 'super_admin' ? 'danger' : ($u['role'] === 'school_admin' ? 'info' : 'neutral') ?>">
                                <?= e(str_replace('_', ' ', $u['role'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['school_name']): ?>
                                <span style="font-weight: 500; color: #1e3a8a;"><?= e($u['school_name']) ?></span>
                            <?php else: ?>
                                <span style="color: #64748b; font-style: italic;">Platform HQ</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= $u['two_factor_enabled'] ? 'success' : 'neutral' ?>">
                                <?= $u['two_factor_enabled'] ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= $u['is_active'] ? 'success' : 'danger' ?>">
                                <?= $u['is_active'] ? 'Active' : 'Suspended' ?>
                            </span>
                        </td>
                        <td style="font-size: 0.8125rem; color: #64748b;">
                            <?= $u['last_login_at'] ? format_date($u['last_login_at'], 'd M Y H:i') : 'Never' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
