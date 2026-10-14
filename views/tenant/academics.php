<div class="page-header">
    <div>
        <h1 class="page-title">Academic Structure & Configurations</h1>
        <p class="page-subtitle">Configure academic sessions, terms, class levels, arms/sections, and instructional subjects</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Sessions & Terms -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">Academic Sessions & Terms</div>
            <button type="button" class="btn btn-primary btn-sm" onclick="openModal('newSessionModal')">+ New Session</button>
        </div>
        <div class="card-body">
            <div style="margin-bottom: 1rem;">
                <div style="font-weight: 700; font-size: 0.9375rem; color: #1e3a8a;">Academic Sessions</div>
                <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem;">
                    <?php foreach ($sessions as $s): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.75rem; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid #e2e8f0;">
                            <div>
                                <strong><?= e($s['name']) ?></strong>
                                <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;"><?= format_date($s['start_date']) ?> to <?= format_date($s['end_date']) ?></span>
                            </div>
                            <?php if ($s['is_current']): ?>
                                <span class="badge badge-success">Current Session</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <div style="font-weight: 700; font-size: 0.9375rem; color: #1e3a8a;">Terms in Active Session</div>
                <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem;">
                    <?php foreach ($terms as $t): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.75rem; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid #e2e8f0;">
                            <div>
                                <strong><?= e($t['name']) ?></strong> (<?= e($t['session_name']) ?>)
                                <div style="font-size: 0.75rem; color: #64748b;"><?= format_date($t['start_date']) ?> to <?= format_date($t['end_date']) ?></div>
                            </div>
                            <?php if ($t['is_current']): ?>
                                <span class="badge badge-success">Active Term</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Levels & Arms -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">Class Levels & Arms (Sections)</div>
            <button type="button" class="btn btn-primary btn-sm" onclick="openModal('newArmModal')">+ New Arm</button>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Class Level</th>
                        <th>Arm</th>
                        <th>Capacity</th>
                        <th>Class Teacher</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classArms as $arm): ?>
                        <tr>
                            <td><strong><?= e($arm['level_name']) ?></strong></td>
                            <td><?= e($arm['name']) ?></td>
                            <td><?= $arm['capacity'] ?> students</td>
                            <td><?= e($arm['teacher_name'] ?? 'Unassigned') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Subjects Catalog -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">Subject Catalog & Classification</div>
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('newSubjectModal')">+ New Subject</button>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Subject Title</th>
                    <th>Category</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $sub): ?>
                    <tr>
                        <td><code><?= e($sub['code']) ?></code></td>
                        <td><strong><?= e($sub['name']) ?></strong></td>
                        <td><span class="badge badge-info" style="text-transform: uppercase;"><?= e($sub['category']) ?></span></td>
                        <td><span class="badge badge-success"><?= $sub['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Session Modal -->
<div id="newSessionModal" class="modal-overlay">
    <div class="modal-container">
        <form method="POST" action="<?= url('school/academics/create-session') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="card-title">Create Academic Session</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('newSessionModal')">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Session Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. 2026/2027" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="start_date" class="form-control" value="2026-09-01" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date *</label>
                        <input type="date" name="end_date" class="form-control" value="2027-07-31" required>
                    </div>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_current" value="1">
                        <span>Set as Current Active Session</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('newSessionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Session</button>
            </div>
        </form>
    </div>
</div>

<!-- New Class Arm Modal -->
<div id="newArmModal" class="modal-overlay">
    <div class="modal-container">
        <form method="POST" action="<?= url('school/academics/create-arm') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="card-title">Add Class Arm / Section</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('newArmModal')">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Class Level *</label>
                    <select name="class_level_id" class="form-select" required>
                        <option value="">Select Level</option>
                        <?php foreach ($classLevels as $cl): ?>
                            <option value="<?= $cl['id'] ?>"><?= e($cl['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Arm / Section Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Gold, Diamond, A" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Student Capacity</label>
                        <input type="number" name="capacity" class="form-control" value="35" min="5" max="100">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('newArmModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Arm</button>
            </div>
        </form>
    </div>
</div>

<!-- New Subject Modal -->
<div id="newSubjectModal" class="modal-overlay">
    <div class="modal-container">
        <form method="POST" action="<?= url('school/academics/create-subject') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="card-title">Add Subject to Curriculum</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('newSubjectModal')">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Subject Title *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Information Technology" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Subject Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. ICT" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="general">General</option>
                            <option value="sciences">Sciences</option>
                            <option value="arts">Arts / Humanities</option>
                            <option value="commercial">Commercial / Business</option>
                            <option value="vocational">Vocational / Technical</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('newSubjectModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Subject</button>
            </div>
        </form>
    </div>
</div>
