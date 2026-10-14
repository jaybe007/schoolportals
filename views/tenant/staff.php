<div class="page-header">
    <div>
        <h1 class="page-title">Faculty & Staff Members</h1>
        <p class="page-subtitle">Academic teachers, department heads, bursary officers, and administrative personnel</p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center;">
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('onboardStaffModal')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Onboard Staff</span>
        </button>
        <input type="text" class="form-control" placeholder="Search staff members..." data-table-search="#staffTable" style="width: 240px;">
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table" id="staffTable">
            <thead>
                <tr>
                    <th>Emp ID</th>
                    <th>Staff Name & Email</th>
                    <th>Designation & Dept</th>
                    <th>Portal Role</th>
                    <th>Base Salary</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff as $st): ?>
                    <tr>
                        <td><code><?= e($st['employee_number']) ?></code></td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;"><?= e($st['name']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($st['email']) ?> &bull; <?= e($st['phone'] ?? '') ?></div>
                        </td>
                        <td>
                            <strong><?= e($st['designation']) ?></strong>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($st['department'] ?? 'General') ?> &bull; <?= e($st['qualification'] ?? '') ?></div>
                        </td>
                        <td>
                            <span class="badge badge-info"><?= e(str_replace('_', ' ', $st['role'])) ?></span>
                        </td>
                        <td style="font-weight: 600;"><?= money($st['salary_base']) ?></td>
                        <td>
                            <span class="badge badge-success"><?= e($st['status']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Onboard Staff Modal -->
<div id="onboardStaffModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 580px;">
        <form method="POST" action="<?= url('school/staff/create') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="card-title">Onboard New Faculty / Staff Member</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('onboardStaffModal')">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Samuel Adekunle" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. samuel@apexcollege.edu.ng" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="08012345678">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Portal Access Role *</label>
                        <select name="role" class="form-select" required>
                            <option value="teacher">Class / Subject Teacher</option>
                            <option value="bursar">Bursar / Accounts Officer</option>
                            <option value="registrar">Academic Registrar</option>
                            <option value="principal">Vice Principal / Principal</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Official Designation *</label>
                        <input type="text" name="designation" class="form-control" placeholder="e.g. Senior Physics Tutor" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" placeholder="e.g. Science & Tech">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Academic Qualification</label>
                        <input type="text" name="qualification" class="form-control" placeholder="e.g. B.Sc, PGDE">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Base Monthly Salary (₦)</label>
                        <input type="number" name="salary_base" class="form-control" value="120000" min="0" step="5000">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('onboardStaffModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Staff Profile</button>
            </div>
        </form>
    </div>
</div>
