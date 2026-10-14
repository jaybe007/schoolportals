<div class="page-header">
    <div>
        <h1 class="page-title">Examination & Continuous Assessment (CA) Engine</h1>
        <p class="page-subtitle">
            Enter marks, automatically calculate totals & grades, compute class rankings, and issue official PDF report cards
        </p>
    </div>
</div>

<!-- Selection Bar -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.25rem;">
        <form method="GET" action="<?= url('school/exams') ?>" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
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
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?= $sub['id'] ?>" <?= ($selectedSubject == $sub['id']) ? 'selected' : '' ?>>
                            <?= e($sub['name']) ?> (<?= e($sub['code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-secondary">Load Mark Sheet</button>
            </div>
        </form>
    </div>
</div>

<!-- Score Entry Form -->
<form method="POST" action="<?= url('school/exams?arm_id=' . $selectedArm . '&subject_id=' . $selectedSubject) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_scores">

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Continuous Assessment & Terminal Exam Marks</div>
                <div style="font-size: 0.75rem; color: #64748b;">Weights: CA 1 (20) + CA 2 (20) + Exam (60) = Total (100)</div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <span>Save Scores & Recompute Positions</span>
            </button>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th style="width: 14%;">CA 1 (Max 20)</th>
                        <th style="width: 14%;">CA 2 (Max 20)</th>
                        <th style="width: 14%;">Exam (Max 60)</th>
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($scores)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 2rem;">
                                No students found in this class arm.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($scores as $s): ?>
                        <tr>
                            <td><code><?= e($s['admission_number']) ?></code></td>
                            <td>
                                <strong><?= e($s['first_name'] . ' ' . $s['last_name']) ?></strong>
                            </td>
                            <td>
                                <input type="number" step="0.5" min="0" max="20" name="scores[<?= $s['student_id'] ?>][ca1]" class="form-control" value="<?= (float)($s['ca1_score'] ?? 0) ?>" style="padding: 0.35rem 0.5rem;">
                            </td>
                            <td>
                                <input type="number" step="0.5" min="0" max="20" name="scores[<?= $s['student_id'] ?>][ca2]" class="form-control" value="<?= (float)($s['ca2_score'] ?? 0) ?>" style="padding: 0.35rem 0.5rem;">
                            </td>
                            <td>
                                <input type="number" step="0.5" min="0" max="60" name="scores[<?= $s['student_id'] ?>][exam]" class="form-control" value="<?= (float)($s['exam_score'] ?? 0) ?>" style="padding: 0.35rem 0.5rem;">
                            </td>
                            <td>
                                <strong style="font-size: 1rem; color: #0f172a;"><?= number_format((float)($s['total_score'] ?? 0), 1) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-weight: 700; font-size: 0.8125rem;">
                                    <?= e($s['grade'] ?? '—') ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.8125rem; color: #475569;"><?= e($s['remark'] ?? '—') ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body" style="border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary">Save Scores & Compute Rankings</button>
        </div>
    </div>
</form>

<!-- Computed Class Standing & Official Report Cards -->
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Term Class Standings & Official Report Card Downloads</div>
            <div style="font-size: 0.75rem; color: #64748b;">Automatically tabulated cumulative rankings and official printable PDF reports</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Rank / Position</th>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>Cumulative Score</th>
                    <th>Term Average</th>
                    <th>Teacher Remarks</th>
                    <th>Official PDF</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($summaries)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #64748b; padding: 2rem;">
                            Class rankings have not yet been generated for this class arm. Save scores above to compute positions.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($summaries as $sum): ?>
                    <tr>
                        <td>
                            <div style="font-size: 1.125rem; font-weight: 800; color: #1e3a8a;">
                                #<?= $sum['position'] ?? '—' ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b;">out of <?= $sum['total_students'] ?? '—' ?></div>
                        </td>
                        <td><code><?= e($sum['admission_number']) ?></code></td>
                        <td><strong><?= e($sum['first_name'] . ' ' . $sum['last_name']) ?></strong></td>
                        <td><?= number_format((float)$sum['total_score'], 1) ?></td>
                        <td>
                            <strong style="color: #059669; font-size: 1rem;"><?= number_format((float)$sum['average_score'], 1) ?>%</strong>
                        </td>
                        <td style="font-size: 0.8125rem; max-width: 280px;">
                            <?= e($sum['teacher_remark'] ?? 'Good performance. Keep it up.') ?>
                        </td>
                        <td>
                            <a href="<?= url('school/students/' . $sum['student_id'] . '/report-card') ?>" class="btn btn-secondary btn-sm" target="_blank">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <span>Download PDF</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
