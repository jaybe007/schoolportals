<div class="page-header">
    <div>
        <h1 class="page-title">Admissions & Application Processing</h1>
        <p class="page-subtitle">Manage online entrance applications, evaluate candidates, and issue formal admission numbers</p>
    </div>
    <div>
        <input type="text" class="form-control" placeholder="Search applicants..." data-table-search="#admissionsTable" style="width: 260px;">
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table" id="admissionsTable">
            <thead>
                <tr>
                    <th>App Number</th>
                    <th>Applicant Name</th>
                    <th>Gender & Age</th>
                    <th>Applied Class</th>
                    <th>Parent / Guardian</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admissions as $a): ?>
                    <tr>
                        <td><code><?= e($a['application_number']) ?></code></td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;"><?= e($a['applicant_first_name'] . ' ' . $a['applicant_last_name']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;">Prev: <?= e($a['previous_school'] ?? 'N/A') ?></div>
                        </td>
                        <td>
                            <div style="text-transform: capitalize;"><?= e($a['applicant_gender']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;">DOB: <?= format_date($a['date_of_birth']) ?></div>
                        </td>
                        <td><strong><?= e($a['applied_class_name'] ?? 'Class') ?></strong></td>
                        <td>
                            <div><?= e($a['parent_name']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($a['parent_phone']) ?> &bull; <?= e($a['parent_email']) ?></div>
                        </td>
                        <td>
                            <span class="badge badge-<?= $a['status'] === 'admitted' ? 'success' : ($a['status'] === 'pending' ? 'warning' : 'neutral') ?>">
                                <?= e($a['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($a['status'] === 'pending' || $a['status'] === 'under_review'): ?>
                                <button type="button" class="btn btn-primary btn-sm" onclick="openAdmitModal(<?= $a['id'] ?>, '<?= e($a['applicant_first_name'] . ' ' . $a['applicant_last_name']) ?>')">
                                    Admit Student
                                </button>
                            <?php else: ?>
                                <span style="font-size: 0.75rem; color: #64748b;">Processed (<?= e($a['assigned_admission_number'] ?? '') ?>)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Admit Modal -->
<div id="admitModal" class="modal-overlay">
    <div class="modal-container">
        <form method="POST" id="admitForm" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="admit">
            <div class="modal-header">
                <h3 class="card-title">Admit Applicant to School</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('admitModal')">✕</button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 1rem;">Approving admission for: <strong id="applicantNameText"></strong></p>
                <div class="form-group">
                    <label class="form-label">Assign Class Section / Arm *</label>
                    <select name="class_arm_id" class="form-select" required>
                        <option value="">Select Class Arm...</option>
                        <?php foreach ($classArms as $arm): ?>
                            <option value="<?= $arm['id'] ?>"><?= e($arm['name']) ?> (Capacity: <?= $arm['capacity'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #64748b; font-size: 0.75rem;">A unique institutional admission number will be generated immediately.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('admitModal')">Cancel</button>
                <button type="submit" class="btn btn-success">Confirm & Enroll Student</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdmitModal(id, name) {
    document.getElementById('admitForm').action = '<?= url('school/admissions') ?>/' + id + '/process';
    document.getElementById('applicantNameText').textContent = name;
    openModal('admitModal');
}
</script>
