<div class="page-header">
    <div>
        <h1 class="page-title"><?= e(current_school()['name'] ?? 'School Portal') ?></h1>
        <p class="page-subtitle">
            Current Academic Session: <strong><?= e($currentSession['name'] ?? '2025/2026') ?></strong> &bull; 
            Term: <strong><?= e($currentTerm['name'] ?? 'First Term') ?></strong>
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= url('school/exams') ?>" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span>Enter Exam Scores</span>
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Enrolled Students</div>
            <div class="stat-value"><?= number_format($studentCount) ?></div>
            <div class="stat-trend positive">Active in current session</div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Faculty & Staff</div>
            <div class="stat-value"><?= number_format($staffCount) ?></div>
            <div class="stat-trend">Teachers & administrative staff</div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">School Fees Collected</div>
            <div class="stat-value"><?= money($feeCollected) ?></div>
            <div class="stat-trend positive">Verified term payments</div>
        </div>
        <div class="stat-icon-wrapper icon-purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Outstanding Fees</div>
            <div class="stat-value" style="color: #dc2626;"><?= money($outstandingFee) ?></div>
            <div class="stat-trend negative">Pending term fee balances</div>
        </div>
        <div class="stat-icon-wrapper icon-rose">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Recent Enrolled Students -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Recently Enrolled Students</div>
            <a href="<?= url('school/students') ?>" class="btn btn-secondary btn-sm">Full Directory</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th>Class & Arm</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentStudents as $stu): ?>
                        <tr>
                            <td><code><?= e($stu['admission_number']) ?></code></td>
                            <td style="font-weight: 600;"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></td>
                            <td><?= e(($stu['class_name'] ?? '') . ' ' . ($stu['arm_name'] ?? '')) ?></td>
                            <td><span class="badge badge-success"><?= e($stu['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Fee Receipts -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Recent Fee Collections</div>
            <a href="<?= url('school/finance') ?>" class="btn btn-secondary btn-sm">Bursary Hub</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Receipt No</th>
                        <th>Student</th>
                        <th>Amount Paid</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPayments as $pay): ?>
                        <tr>
                            <td><code><?= e($pay['receipt_number']) ?></code></td>
                            <td>
                                <div style="font-weight: 600;"><?= e($pay['first_name'] . ' ' . $pay['last_name']) ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;"><?= e($pay['admission_number']) ?></div>
                            </td>
                            <td style="font-weight: 700; color: #059669;"><?= money($pay['amount_paid']) ?></td>
                            <td><span class="badge badge-info"><?= e($pay['payment_method']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
