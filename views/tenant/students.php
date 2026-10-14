<div class="page-header">
    <div>
        <h1 class="page-title">Enrolled Student Directory</h1>
        <p class="page-subtitle">Student profiles, parent linkages, academic placement, and cumulative performance reports</p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center;">
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('enrollStudentModal')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Enroll Student</span>
        </button>
        <form method="GET" action="<?= url('school/students') ?>" style="display: flex; gap: 0.5rem;">
            <select name="arm_id" class="form-select" onchange="this.form.submit()" style="width: 180px;">
                <option value="">All Class Arms</option>
                <?php foreach ($classArms as $ca): ?>
                    <option value="<?= $ca['id'] ?>" <?= ($selectedArm == $ca['id']) ? 'selected' : '' ?>>
                        <?= e($ca['class_name'] . ' ' . $ca['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <input type="text" class="form-control" placeholder="Search student name, adm no..." data-table-search="#studentsTable" style="width: 220px;">
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table" id="studentsTable">
            <thead>
                <tr>
                    <th>Admission No</th>
                    <th>Full Name</th>
                    <th>Class & Arm</th>
                    <th>Gender & Age</th>
                    <th>Linked Parent / Guardian</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $stu): ?>
                    <tr>
                        <td>
                            <code style="font-weight: 700; color: #1e3a8a; background: #eff6ff; padding: 2px 6px; border-radius: 4px;">
                                <?= e($stu['admission_number']) ?>
                            </code>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;">
                                <?= e($stu['first_name'] . ' ' . ($stu['middle_name'] ? $stu['middle_name'] . ' ' : '') . $stu['last_name']) ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                Genotype: <?= e($stu['genotype'] ?? 'AA') ?> &bull; Blood: <?= e($stu['blood_group'] ?? 'O+') ?>
                            </div>
                        </td>
                        <td>
                            <strong><?= e(($stu['class_name'] ?? '') . ' ' . ($stu['arm_name'] ?? '')) ?></strong>
                        </td>
                        <td>
                            <div style="text-transform: capitalize;"><?= e($stu['gender']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= format_date($stu['date_of_birth']) ?></div>
                        </td>
                        <td>
                            <?php if ($stu['parent_name']): ?>
                                <div><?= e($stu['parent_title'] ? $stu['parent_title'] . ' ' : '') ?><?= e($stu['parent_name']) ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;"><?= e($stu['parent_phone']) ?></div>
                            <?php else: ?>
                                <span style="color: #94a3b8; font-style: italic;">Not linked</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-success"><?= e($stu['status']) ?></span>
                        </td>
                        <td>
                            <a href="<?= url('school/students/' . $stu['id'] . '/report-card') ?>" class="btn btn-secondary btn-sm" target="_blank" title="Download Official PDF Report Card">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <span>PDF Report</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Enroll Student Modal -->
<div id="enrollStudentModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 600px;">
        <form method="POST" action="<?= url('school/students/create') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="card-title">Enroll New Student</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('enrollStudentModal')">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control" placeholder="e.g. Oluwaseun" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control" placeholder="e.g. Adebayo" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" placeholder="e.g. Emmanuel">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-select" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Date of Birth *</label>
                        <input type="date" name="date_of_birth" class="form-control" value="2016-05-15" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assigned Class Arm *</label>
                        <select name="class_arm_id" class="form-select" required>
                            <option value="">Select Class Arm</option>
                            <?php foreach ($classArms as $ca): ?>
                                <option value="<?= $ca['id'] ?>"><?= e($ca['class_name'] . ' ' . $ca['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Blood Group</label>
                        <select name="blood_group" class="form-select">
                            <option value="O+">O+</option>
                            <option value="A+">A+</option>
                            <option value="B+">B+</option>
                            <option value="AB+">AB+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Genotype</label>
                        <select name="genotype" class="form-select">
                            <option value="AA">AA</option>
                            <option value="AS">AS</option>
                            <option value="AC">AC</option>
                            <option value="SS">SS</option>
                        </select>
                    </div>
                </div>

                <div style="font-weight: 700; font-size: 0.875rem; color: #1e3a8a; margin: 1rem 0 0.5rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
                    Parent / Guardian Information
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Parent Full Name</label>
                        <input type="text" name="parent_name" class="form-control" placeholder="e.g. Dr. Kolawole Adebayo">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Parent Phone</label>
                        <input type="text" name="parent_phone" class="form-control" placeholder="08023456789">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Parent Email (for Portal Account)</label>
                    <input type="email" name="parent_email" class="form-control" placeholder="kolawole@example.ng">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('enrollStudentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Complete Enrollment</button>
            </div>
        </form>
    </div>
</div>
