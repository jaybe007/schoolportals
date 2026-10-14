<div class="page-header">
    <div>
        <h1 class="page-title">Daily Attendance Register</h1>
        <p class="page-subtitle">Record daily student classroom roll call, monitor absenteeism trends, and flag attendance anomalies</p>
    </div>
</div>

<!-- Filter controls -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.25rem;">
        <form method="GET" action="<?= url('school/attendance') ?>" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">Select Class Arm</label>
                <select name="arm_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($classArms as $arm): ?>
                        <option value="<?= $arm['id'] ?>" <?= ($selectedArm == $arm['id']) ? 'selected' : '' ?>>
                            <?= e($arm['class_name'] . ' ' . $arm['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <label class="form-label">Register Date</label>
                <input type="date" name="date" class="form-control" value="<?= e($selectedDate) ?>" onchange="this.form.submit()">
            </div>

            <div>
                <button type="submit" class="btn btn-secondary">Load Sheet</button>
            </div>
        </form>
    </div>
</div>

<form method="POST" action="<?= url('school/attendance?arm_id=' . $selectedArm . '&date=' . $selectedDate) ?>">
    <?= csrf_field() ?>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Roll Call Sheet &bull; <?= format_date($selectedDate, 'l, d F Y') ?></div>
            <button type="submit" class="btn btn-primary btn-sm">Save Register</button>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th style="width: 45%;">Attendance Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #64748b; padding: 2rem;">
                                No students enrolled in this class arm.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php $idx = 1; foreach ($students as $stu): ?>
                        <?php $currStatus = $stu['current_status'] ?? 'present'; ?>
                        <tr>
                            <td><?= $idx++ ?></td>
                            <td><code><?= e($stu['admission_number']) ?></code></td>
                            <td>
                                <strong><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></strong>
                            </td>
                            <td>
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; font-size: 0.875rem;">
                                        <input type="radio" name="attendance[<?= $stu['id'] ?>]" value="present" <?= $currStatus === 'present' ? 'checked' : '' ?>>
                                        <span style="color: #059669; font-weight: 600;">Present</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; font-size: 0.875rem;">
                                        <input type="radio" name="attendance[<?= $stu['id'] ?>]" value="absent" <?= $currStatus === 'absent' ? 'checked' : '' ?>>
                                        <span style="color: #dc2626; font-weight: 600;">Absent</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; font-size: 0.875rem;">
                                        <input type="radio" name="attendance[<?= $stu['id'] ?>]" value="late" <?= $currStatus === 'late' ? 'checked' : '' ?>>
                                        <span style="color: #d97706; font-weight: 600;">Late</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; font-size: 0.875rem;">
                                        <input type="radio" name="attendance[<?= $stu['id'] ?>]" value="excused" <?= $currStatus === 'excused' ? 'checked' : '' ?>>
                                        <span style="color: #475569;">Excused</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($students)): ?>
            <div class="card-body" style="border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">Save Register Changes</button>
            </div>
        <?php endif; ?>
    </div>
</form>
