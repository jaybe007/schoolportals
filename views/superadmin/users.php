<div class="page-header">
    <div>
        <h1 class="page-title">Platform Users & Role Assignments</h1>
        <p class="page-subtitle">Create administrators, configure multi-tenant roles, and grant granular platform permissions</p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center;">
        <input type="text" class="form-control" placeholder="Search users by name, email, or role..." data-table-search="#usersTable" style="width: 280px;">
        <button type="button" class="btn btn-primary" onclick="openModal('createUserModal')" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
            <span>+</span> Create Administrator
        </button>
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
                    <th>Permissions</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <?php
                        $uId = (int)$u['id'];
                        $isSuper = ($u['role'] === 'super_admin');
                        $activePerms = $userPermMap[$uId] ?? ($rolePermMap[$u['role']] ?? []);
                        $permCount = count($activePerms);
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #0f172a;"><?= e($u['name']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;">ID: #<?= $uId ?></div>
                        </td>
                        <td>
                            <div><?= e($u['email']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($u['phone'] ?? '—') ?></div>
                        </td>
                        <td>
                            <?php
                                $roleBadge = match($u['role']) {
                                    'super_admin' => 'danger',
                                    'finance_admin' => 'info',
                                    'sales_manager', 'sales_rep' => 'success',
                                    'school_owner', 'school_admin' => 'primary',
                                    'bursar' => 'warning',
                                    default => 'neutral'
                                };
                            ?>
                            <span class="badge badge-<?= $roleBadge ?>">
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
                            <?php if ($isSuper): ?>
                                <span class="badge badge-danger" title="Super Admin has omnipotent root access to all system capabilities">
                                    Full Root Access
                                </span>
                            <?php elseif (!empty($u['custom_permissions_count'])): ?>
                                <span class="badge badge-info" title="User has <?= (int)$u['custom_permissions_count'] ?> explicitly assigned custom permissions">
                                    <?= (int)$u['custom_permissions_count'] ?> Custom Granted
                                </span>
                            <?php else: ?>
                                <span class="badge badge-neutral" title="Default permissions inherited from <?= e($u['role']) ?>">
                                    <?= $permCount ?> Role Defaults
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= $u['is_active'] ? 'success' : 'danger' ?>">
                                <?= $u['is_active'] ? 'Active' : 'Suspended' ?>
                            </span>
                        </td>
                        <td style="font-size: 0.8125rem; color: #64748b;">
                            <?= $u['last_login_at'] ? format_date($u['last_login_at'], 'd M Y H:i') : 'Never' ?>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick='openEditUserModal(<?= json_encode($u) ?>, <?= json_encode($activePerms) ?>)'>
                                Edit Role & Perms
                            </button>
                            <?php if ($uId !== \EduSphere\Core\Auth::id()): ?>
                                <form method="POST" action="<?= url('superadmin/users/' . $uId . '/toggle-status') ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to <?= $u['is_active'] ? 'suspend' : 'activate' ?> user <?= e(addslashes($u['name'])) ?>?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-<?= $u['is_active'] ? 'danger' : 'success' ?>" style="padding: 0.25rem 0.55rem; font-size: 0.75rem;">
                                        <?= $u['is_active'] ? 'Suspend' : 'Activate' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal 1: Create Administrator -->
<div id="createUserModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 780px;">
        <form method="POST" action="<?= url('superadmin/users/create') ?>" id="createUserForm">
            <?= csrf_field() ?>
            <div class="modal-header">
                <div>
                    <h3 class="card-title">Create Platform Administrator</h3>
                    <div style="font-size: 0.8125rem; color: #64748b;">Provision an administrative user, assign their role, and configure granular permissions</div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('createUserModal')">✕</button>
            </div>
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Barr. Folake Adeleke" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Official Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="f.adeleke@edusphere.example" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="08022233344">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Initial Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Administrative Role *</label>
                        <select name="role" id="createRoleSelect" class="form-select" onchange="handleRoleChange(this.value, 'create')">
                            <option value="super_admin">Platform Super Administrator (Full Root Access)</option>
                            <option value="finance_admin">Platform Finance Administrator (Reconciliations & Payouts)</option>
                            <option value="sales_manager">Platform Sales Manager (CRM & Commissions)</option>
                            <option value="school_owner">School Owner / Director</option>
                            <option value="school_admin" selected>School Administrator / Principal</option>
                            <option value="bursar">School Bursar (Finance & Invoices)</option>
                            <option value="teacher">Faculty Member / Teacher</option>
                            <option value="sales_rep">Sales Representative</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tenant School Scope</label>
                        <select name="school_id" class="form-select">
                            <option value="0">-- Platform HQ Scope (Global) --</option>
                            <?php foreach ($schools as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #64748b; font-size: 0.75rem;">Leave as HQ for platform-wide admins; select a school for tenant-scoped staff.</small>
                    </div>
                </div>

                <div style="margin-top: 1.25rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <div>
                            <span style="font-weight: 700; color: #1e3a8a; font-size: 0.9375rem;">Granular Permissions Assignment</span>
                            <div style="font-size: 0.75rem; color: #64748b;">Fine-tune exact operational privileges granted to this account</div>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAllPerms('create', true)">Select All</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAllPerms('create', false)">Clear</button>
                        </div>
                    </div>

                    <?php
                        $groupedPerms = [];
                        foreach ($allPermissions as $p) {
                            $cat = $p['category'];
                            $groupedPerms[$cat][] = $p;
                        }
                        $categoryTitles = [
                            'platform_governance' => 'Platform Governance & Tenants',
                            'finance_reconciliation' => 'Finance, Gateway & Revenue Operations',
                            'sales_crm' => 'Sales Pipeline & Commissions CRM',
                            'school_academics' => 'School Operations, Academics & Staff',
                            'communication' => 'Communications, Notices & Website CMS',
                        ];
                    ?>

                    <?php foreach ($groupedPerms as $cat => $perms): ?>
                        <div style="margin-bottom: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 0.75rem;">
                            <div style="font-weight: 600; font-size: 0.8125rem; color: #334155; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.03em;">
                                <?= e($categoryTitles[$cat] ?? ucfirst($cat)) ?>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 0.5rem;">
                                <?php foreach ($perms as $p): ?>
                                    <label style="display: flex; align-items: flex-start; gap: 0.45rem; font-size: 0.8125rem; color: #1e293b; cursor: pointer;">
                                        <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" class="create-perm-cb" id="create_perm_<?= $p['id'] ?>" style="margin-top: 0.15rem;">
                                        <div>
                                            <div style="font-weight: 600;"><?= e($p['display_name']) ?></div>
                                            <div style="font-size: 0.72rem; color: #64748b; line-height: 1.2;"><?= e($p['description'] ?? '') ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Administrator</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit User Role & Permissions -->
<div id="editUserModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 780px;">
        <form method="POST" action="" id="editUserForm">
            <?= csrf_field() ?>
            <div class="modal-header">
                <div>
                    <h3 class="card-title">Modify Administrator Role & Permissions</h3>
                    <div style="font-size: 0.8125rem; color: #64748b;">Editing: <strong id="editUserNameDisplay" style="color: #0f172a;"></strong></div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('editUserModal')">✕</button>
            </div>
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="phone" id="editPhone" class="form-control">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Administrative Role *</label>
                        <select name="role" id="editRoleSelect" class="form-select" onchange="handleRoleChange(this.value, 'edit')">
                            <option value="super_admin">Platform Super Administrator (Full Root Access)</option>
                            <option value="finance_admin">Platform Finance Administrator (Reconciliations & Payouts)</option>
                            <option value="sales_manager">Platform Sales Manager (CRM & Commissions)</option>
                            <option value="school_owner">School Owner / Director</option>
                            <option value="school_admin">School Administrator / Principal</option>
                            <option value="bursar">School Bursar (Finance & Invoices)</option>
                            <option value="teacher">Faculty Member / Teacher</option>
                            <option value="sales_rep">Sales Representative</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tenant School Scope</label>
                        <select name="school_id" id="editSchoolSelect" class="form-select">
                            <option value="0">-- Platform HQ Scope (Global) --</option>
                            <?php foreach ($schools as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Account Status</label>
                        <select name="is_active" id="editIsActive" class="form-select">
                            <option value="1">Active Account</option>
                            <option value="0">Suspended Account</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reset Password (Leave blank to keep unchanged)</label>
                        <input type="password" name="password" class="form-control" placeholder="New password (optional)" minlength="6">
                    </div>
                </div>

                <div style="margin-top: 1.25rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <div>
                            <span style="font-weight: 700; color: #1e3a8a; font-size: 0.9375rem;">Assigned Permissions</span>
                            <div style="font-size: 0.75rem; color: #64748b;">Explicit permission overrides assigned to this administrator</div>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAllPerms('edit', true)">Select All</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAllPerms('edit', false)">Clear</button>
                        </div>
                    </div>

                    <?php foreach ($groupedPerms as $cat => $perms): ?>
                        <div style="margin-bottom: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 0.75rem;">
                            <div style="font-weight: 600; font-size: 0.8125rem; color: #334155; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.03em;">
                                <?= e($categoryTitles[$cat] ?? ucfirst($cat)) ?>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 0.5rem;">
                                <?php foreach ($perms as $p): ?>
                                    <label style="display: flex; align-items: flex-start; gap: 0.45rem; font-size: 0.8125rem; color: #1e293b; cursor: pointer;">
                                        <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" class="edit-perm-cb" id="edit_perm_<?= $p['id'] ?>" style="margin-top: 0.15rem;">
                                        <div>
                                            <div style="font-weight: 600;"><?= e($p['display_name']) ?></div>
                                            <div style="font-size: 0.72rem; color: #64748b; line-height: 1.2;"><?= e($p['description'] ?? '') ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
const rolePermMap = <?= json_encode($rolePermMap) ?>;

function handleRoleChange(role, mode) {
    const checkboxes = document.querySelectorAll('.' + mode + '-perm-cb');
    if (role === 'super_admin') {
        checkboxes.forEach(cb => cb.checked = true);
        return;
    }
    const defaultIds = rolePermMap[role] || [];
    checkboxes.forEach(cb => {
        cb.checked = defaultIds.includes(parseInt(cb.value));
    });
}

function toggleAllPerms(mode, state) {
    document.querySelectorAll('.' + mode + '-perm-cb').forEach(cb => cb.checked = state);
}

function openEditUserModal(user, activePermIds) {
    document.getElementById('editUserNameDisplay').textContent = user.name + ' (' + user.email + ')';
    document.getElementById('editUserForm').action = '<?= url('superadmin/users') ?>/' + user.id + '/update';
    document.getElementById('editName').value = user.name || '';
    document.getElementById('editPhone').value = user.phone || '';
    document.getElementById('editRoleSelect').value = user.role || 'school_admin';
    document.getElementById('editSchoolSelect').value = user.school_id ? user.school_id : '0';
    document.getElementById('editIsActive').value = user.is_active ? '1' : '0';

    // Set permission checkboxes
    document.querySelectorAll('.edit-perm-cb').forEach(cb => {
        cb.checked = activePermIds.includes(parseInt(cb.value));
    });

    openModal('editUserModal');
}

// Initialize create modal with school_admin defaults on load
document.addEventListener('DOMContentLoaded', function() {
    handleRoleChange('school_admin', 'create');
});
</script>
