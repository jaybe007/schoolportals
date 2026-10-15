<div class="page-header">
    <div>
        <h1 class="page-title">School Tenants Directory</h1>
        <p class="page-subtitle">Configure institutional boundaries, domain mapping, per-student term portal fee agreements, and tenant account states</p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center;">
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('createSchoolModal')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Onboard School</span>
        </button>
        <input type="text" class="form-control" placeholder="Search schools..." data-table-search="#schoolsTable" style="width: 240px;">
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table" id="schoolsTable">
            <thead>
                <tr>
                    <th>School Name & Code</th>
                    <th>Type & State</th>
                    <th>Primary Domain</th>
                    <th>Term Portal Fee</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schools as $s): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;"><?= e($s['name']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b; font-family: monospace;"><?= e($s['code']) ?></div>
                        </td>
                        <td>
                            <div style="text-transform: capitalize;"><?= e(str_replace('_', ' ', $s['school_type'])) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($s['state'] ?? 'Nigeria') ?></div>
                        </td>
                        <td>
                            <code style="font-size: 0.8125rem; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #1e3a8a;">
                                <?= e($s['primary_domain'] ?? 'edusphere.example') ?>
                            </code>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;"><?= money($s['active_portal_fee'] ?? 3000) ?></div>
                            <div style="font-size: 0.7rem; color: #16a34a; font-weight: 600;">per student / term</div>
                        </td>
                        <td><strong><?= number_format((int)$s['student_count']) ?></strong></td>
                        <td>
                            <span class="badge badge-<?= $s['status'] === 'active' ? 'success' : 'danger' ?>">
                                <?= e($s['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                <a href="<?= url('school?school=' . $s['slug']) ?>" class="btn btn-secondary btn-sm" target="_blank" title="Launch Tenant Portal">
                                    Portal
                                </a>
                                <a href="<?= url('site/' . $s['slug']) ?>" class="btn btn-secondary btn-sm" target="_blank" title="View Public Website">
                                    Website
                                </a>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="openDomainModal(<?= $s['id'] ?>, '<?= e(addslashes($s['name'])) ?>')" title="Map Custom Domain">
                                    + Domain
                                </button>
                                <form method="POST" action="<?= url('superadmin/schools/' . $s['id'] . '/toggle-status') ?>" style="display: inline;" onsubmit="return confirm('Toggle school account status?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-<?= $s['status'] === 'active' ? 'danger' : 'success' ?> btn-sm">
                                        <?= $s['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Onboard School Modal -->
<div id="createSchoolModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 600px;">
        <form method="POST" action="<?= url('superadmin/schools/create') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="card-title">Onboard Institutional Tenant School</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('createSchoolModal')">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">School Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Royal Crest Academy" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">School Type *</label>
                        <select name="school_type" class="form-select" required>
                            <option value="combined">Combined (Nursery, Primary & Secondary)</option>
                            <option value="secondary">Secondary Only</option>
                            <option value="primary">Primary & Nursery Only</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">School Official Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="info@school.edu.ng" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="08033344455">
                    </div>
                </div>

                <!-- Per-Student Commercial Model (Replaces Legacy Fixed Subscription Plans) -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.875rem; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 700; font-size: 0.8125rem; color: #166534; text-transform: uppercase; letter-spacing: 0.03em;">Commercial Model: Per-Student Term Portal Fee</span>
                        <span class="badge badge-success" style="font-size: 0.7rem;">Term Billing Model</span>
                    </div>
                    <div class="form-grid">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 0.75rem; color: #14532d;">Term Portal Fee per Student (₦) *</label>
                            <input type="number" step="100" min="500" name="portal_fee" class="form-control" value="3000" placeholder="3000" required style="background: #ffffff; font-weight: 600;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 0.75rem; color: #14532d;">School Incentive Share</label>
                            <input type="text" class="form-control" value="40% (Accrued to School)" readonly style="background: #e2e8f0; color: #334155; font-weight: 600;">
                        </div>
                    </div>
                    <small style="display: block; font-size: 0.7rem; color: #15803d; margin-top: 0.4rem;">
                        Default is ₦3,000 / student per academic term. Each term is reconciled with 40% incentive to the school and 15% initial / 7.5% renewal commission to assigned sales partners.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Attributed Sales Partner / Representative (Optional)</label>
                    <select name="attributed_rep_id" class="form-select">
                        <option value="0">-- Direct Platform Acquisition (No Representative) --</option>
                        <?php foreach (($salesReps ?? []) as $rep): ?>
                            <option value="<?= $rep['id'] ?>">
                                <?= e($rep['name']) ?> (<?= e($rep['rep_code']) ?> &mdash; <?= e($rep['territory']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.35rem;">
                        Linking a sales partner enables automated commission allocations (15% initial / 7.5% recurring) only when genuine student portal fees are collected.
                    </small>
                </div>

                <div style="font-weight: 700; font-size: 0.875rem; color: #1e3a8a; margin: 1rem 0 0.5rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
                    Initial School Administrator Account
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Admin Full Name</label>
                        <input type="text" name="admin_name" class="form-control" placeholder="e.g. Dr. John Doe">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Admin Login Email *</label>
                        <input type="email" name="admin_email" class="form-control" placeholder="admin@school.edu.ng" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createSchoolModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Provision Tenant School</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Custom Domain Modal -->
<div id="addDomainModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 500px;">
        <form method="POST" id="domainForm" action="">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="card-title">Map Custom Domain</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('addDomainModal')">✕</button>
            </div>
            <div class="modal-body">
                <div style="background: #f8fafc; padding: 0.85rem; border-radius: var(--radius-md); margin-bottom: 1rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.8125rem; color: #64748b;">School: <strong id="modalSchoolName" style="color: #0f172a;"></strong></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Custom Fully-Qualified Domain Name *</label>
                    <input type="text" name="domain" class="form-control" placeholder="e.g. portal.schoolname.edu.ng" required>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.35rem;">
                        Requires school CNAME or A-record pointing to this platform cluster.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addDomainModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Attach Domain Mapping</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDomainModal(schoolId, schoolName) {
    document.getElementById('modalSchoolName').textContent = schoolName;
    document.getElementById('domainForm').action = '<?= url('superadmin/schools/') ?>' + '/' + schoolId + '/add-domain';
    openModal('addDomainModal');
}
</script>
