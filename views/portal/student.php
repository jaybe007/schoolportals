<div class="page-header">
    <div>
        <h1 class="page-title">Welcome back, <?= e($student['first_name']) ?>!</h1>
        <p class="page-subtitle">
            Admission No: <strong><?= e($student['admission_number']) ?></strong> &bull; 
            Class: <strong><?= e(($student['class_name'] ?? '') . ' ' . ($student['arm_name'] ?? '')) ?></strong> &bull; 
            Session: <strong><?= e($session['name'] ?? '2025/2026') ?></strong>
        </p>
    </div>
    <?php if ($summary): ?>
        <div>
            <a href="<?= url('school/students/' . $student['id'] . '/report-card') ?>" class="btn btn-primary" target="_blank">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>Download Official Terminal Report Card</span>
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Term Average</div>
            <div class="stat-value" style="color: #1e3a8a;">
                <?= $summary ? number_format((float)$summary['average_score'], 1) . '%' : 'In Progress' ?>
            </div>
            <div class="stat-trend positive">Total Score: <?= $summary ? number_format((float)$summary['total_score'], 1) : '—' ?></div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Class Position</div>
            <div class="stat-value" style="color: #059669;">
                <?= $summary ? '#' . $summary['position'] : '—' ?>
            </div>
            <div class="stat-trend">Out of <?= $summary['total_students'] ?? '—' ?> students</div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Attendance Record</div>
            <div class="stat-value">
                <?php 
                    $tot = (int)($attStats['total'] ?? 0);
                    $prs = (int)($attStats['present_count'] ?? 0);
                    echo $tot > 0 ? round(($prs / $tot) * 100, 1) . '%' : '100%';
                ?>
            </div>
            <div class="stat-trend positive"><?= $prs ?> days present of <?= $tot ?> session days</div>
        </div>
        <div class="stat-icon-wrapper icon-purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
        </div>
    </div>
</div>

<!-- School Notice Board Feed for Students -->
<?php if (!empty($notices)): ?>
<div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid #2563eb;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <h3 class="card-title" style="margin: 0; font-size: 1.1rem;">Official School Notice Board</h3>
        </div>
    </div>
    <div class="card-body" style="padding: 1rem 1.25rem;">
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($notices as $nt): ?>
                <div style="padding: 1rem 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; border-left: 3px solid <?= $nt['priority'] === 'urgent' ? '#dc2626' : ($nt['priority'] === 'important' ? '#f59e0b' : '#3b82f6') ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.35rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <?php if ($nt['is_pinned']): ?><span class="badge badge-primary" style="font-size: 0.7rem;">📌 Pinned</span><?php endif; ?>
                            <span class="badge badge-info" style="font-size: 0.7rem; text-transform: uppercase;"><?= e($nt['category']) ?></span>
                            <strong style="color: #0f172a; font-size: 0.95rem;"><?= e($nt['title']) ?></strong>
                        </div>
                        <span style="font-size: 0.75rem; color: #64748b;"><?= date('M j, Y', strtotime($nt['published_at'])) ?></span>
                    </div>
                    <div style="font-size: 0.875rem; color: #334155; line-height: 1.5; white-space: pre-wrap;">
                        <?= e($nt['content']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Published Examination Results -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <div class="card-title">Published Terminal Examination Scores</div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Subject Title</th>
                    <th>CA 1 (20)</th>
                    <th>CA 2 (20)</th>
                    <th>Exam (60)</th>
                    <th>Total (100)</th>
                    <th>Grade</th>
                    <th>Teacher Remark</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($scores)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #64748b; padding: 2rem;">
                            No examination marks have been published yet for this term.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($scores as $sc): ?>
                    <tr>
                        <td><strong><?= e($sc['subject_name']) ?></strong></td>
                        <td><?= number_format((float)$sc['ca1_score'], 1) ?></td>
                        <td><?= number_format((float)$sc['ca2_score'], 1) ?></td>
                        <td><?= number_format((float)$sc['exam_score'], 1) ?></td>
                        <td><strong style="color: #0f172a; font-size: 1rem;"><?= number_format((float)$sc['total_score'], 1) ?></strong></td>
                        <td><span class="badge badge-info" style="font-weight: 700;"><?= e($sc['grade']) ?></span></td>
                        <td><?= e($sc['remark']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Financial Statement & Online Fee Checkout -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">School Fees & Tuition Invoices</h3>
            <p class="card-subtitle">Official school fee billing statements & online card settlement</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Fee Schedule</th>
                    <th>Total Amount</th>
                    <th>Paid</th>
                    <th>Balance Due</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #64748b; padding: 2rem;">No fee invoices issued for your account.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><strong><?= e($inv['invoice_number']) ?></strong></td>
                            <td>Tuition & Institutional Ancillaries</td>
                            <td><?= money((float)$inv['total_amount']) ?></td>
                            <td style="color: #059669; font-weight: 600;"><?= money((float)$inv['paid_amount']) ?></td>
                            <td style="font-weight: 700; color: <?= (float)$inv['balance_due'] > 0 ? '#dc2626' : '#64748b' ?>;">
                                <?= money((float)$inv['balance_due']) ?>
                            </td>
                            <td><?= date('M j, Y', strtotime($inv['due_date'])) ?></td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge badge-success">Paid</span>
                                <?php elseif ($inv['status'] === 'partially_paid'): ?>
                                    <span class="badge badge-warning">Partial</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((float)$inv['balance_due'] > 0): ?>
                                    <button class="btn btn-primary btn-sm" onclick="openPayFeeModal(<?= (int)$inv['id'] ?>, '<?= e($inv['invoice_number']) ?>', <?= (float)$inv['balance_due'] ?>)">
                                        Pay Online
                                    </button>
                                <?php else: ?>
                                    <span style="color: #059669; font-weight: 600; font-size: 0.85rem;">✓ Settled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Term Portal Maintenance Fee Section -->
<?php if ($portalInvoice): ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Digital Portal Access & Cloud Fee</h3>
            <p class="card-subtitle">Active term digital marksheet & system maintenance access</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Gross Charge</th>
                    <th>Waiver</th>
                    <th>Amount Due</th>
                    <th>Paid</th>
                    <th>Balance Due</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><?= e($portalInvoice['invoice_number']) ?></strong></td>
                    <td><?= money((float)$portalInvoice['gross_amount']) ?></td>
                    <td><?= (float)$portalInvoice['discount_amount'] > 0 ? '-' . money((float)$portalInvoice['discount_amount']) : '—' ?></td>
                    <td style="font-weight: 700; color: #1e3a8a;"><?= money((float)$portalInvoice['amount_due']) ?></td>
                    <td style="color: #059669; font-weight: 600;"><?= money((float)$portalInvoice['paid_amount']) ?></td>
                    <td style="font-weight: 700; color: <?= (float)$portalInvoice['balance_due'] > 0 ? '#dc2626' : '#64748b' ?>;">
                        <?= money((float)$portalInvoice['balance_due']) ?>
                    </td>
                    <td>
                        <?php if ($portalInvoice['status'] === 'paid'): ?>
                            <span class="badge badge-success">Paid</span>
                        <?php elseif ($portalInvoice['status'] === 'partially_paid'): ?>
                            <span class="badge badge-warning">Partial</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Unpaid</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ((float)$portalInvoice['balance_due'] > 0): ?>
                            <button class="btn btn-primary btn-sm" onclick="openPayPortalModal(<?= (int)$portalInvoice['id'] ?>, '<?= e($portalInvoice['invoice_number']) ?>', <?= (float)$portalInvoice['balance_due'] ?>)">
                                Pay Online
                            </button>
                        <?php else: ?>
                            <span style="color: #059669; font-weight: 600; font-size: 0.85rem;">✓ Active Access</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Payment Receipts Table -->
<?php if (!empty($receipts) || !empty($portalPayments)): ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Verified Payment Receipts</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Receipt / Reference</th>
                    <th>Invoice No</th>
                    <th>Amount Paid</th>
                    <th>Payment Method</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Receipt Slip</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($receipts as $rc): ?>
                    <tr>
                        <td><strong><?= e($rc['receipt_number']) ?></strong></td>
                        <td><?= e($rc['invoice_number']) ?></td>
                        <td style="font-weight: 700; color: #059669;"><?= money((float)$rc['amount_paid']) ?></td>
                        <td><span class="badge badge-info"><?= ucfirst(e($rc['payment_method'])) ?></span></td>
                        <td><?= date('M j, Y', strtotime($rc['payment_date'])) ?></td>
                        <td><span class="badge badge-success">Verified</span></td>
                        <td>
                            <a href="<?= url('portal/receipts/' . $rc['id'] . '/pdf') ?>" target="_blank" class="btn btn-secondary btn-sm" title="Download & Print Official Fee Receipt">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                                <span>Print Receipt</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php foreach ($portalPayments as $pp): ?>
                    <tr>
                        <td><strong><?= e($pp['payment_reference']) ?></strong></td>
                        <td><?= e($pp['invoice_number']) ?></td>
                        <td style="font-weight: 700; color: #059669;"><?= money((float)$pp['amount']) ?></td>
                        <td><span class="badge badge-info"><?= ucfirst(e($pp['gateway'])) ?></span></td>
                        <td><?= date('M j, Y', strtotime($pp['verified_at'] ?? $pp['created_at'])) ?></td>
                        <td><span class="badge badge-success">Portal Verified</span></td>
                        <td>
                            <a href="<?= url('portal/portal-payments/' . $pp['id'] . '/pdf') ?>" target="_blank" class="btn btn-secondary btn-sm" title="Download & Print Official Portal Receipt">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                                <span>Print Receipt</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if ($summary): ?>
    <div class="card">
        <div class="card-header">
            <div class="card-title">Institutional Faculty Remarks</div>
        </div>
        <div class="card-body">
            <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border-left: 4px solid #1e3a8a; margin-bottom: 1rem;">
                <div style="font-weight: 700; font-size: 0.8125rem; color: #1e3a8a; margin-bottom: 0.25rem;">CLASS TEACHER'S APPRAISAL:</div>
                <div style="font-size: 0.875rem; color: #1e293b;"><?= e($summary['teacher_remark']) ?></div>
            </div>
            <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border-left: 4px solid #059669;">
                <div style="font-weight: 700; font-size: 0.8125rem; color: #059669; margin-bottom: 0.25rem;">PRINCIPAL'S EXECUTIVE COMMENDATION:</div>
                <div style="font-size: 0.875rem; color: #1e293b;"><?= e($summary['principal_remark']) ?></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal: Pay School Fee Invoice -->
<div class="modal" id="payFeeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Pay Tuition / School Fee</h3>
                <button class="modal-close" onclick="closeModal('payFeeModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('portal/student/pay-fee') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="invoice_id" id="payFeeInvoiceId" value="">
                <div class="modal-body">
                    <p style="color: #334155; margin-bottom: 1rem;">
                        Paying fee invoice <strong id="payFeeInvoiceNumber"></strong>
                    </p>
                    <div class="form-group">
                        <label class="form-label">Payment Amount (NGN) *</label>
                        <input type="number" step="0.01" id="payFeeAmountInput" name="amount" class="form-control" value="" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Channel</label>
                        <select class="form-control" disabled>
                            <option>Paystack Secure Gateway (Card, USSD, Bank Transfer)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('payFeeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Proceed to Pay Online</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Pay Portal Fee Invoice -->
<div class="modal" id="payPortalModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Pay Student Portal Fee</h3>
                <button class="modal-close" onclick="closeModal('payPortalModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('portal/student/pay-portal-fee') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="invoice_id" id="payPortalInvoiceId" value="">
                <div class="modal-body">
                    <p style="color: #334155; margin-bottom: 1rem;">
                        Settling digital portal fee invoice <strong id="payPortalInvoiceNumber"></strong>
                    </p>
                    <div class="form-group">
                        <label class="form-label">Payment Amount (NGN) *</label>
                        <input type="number" step="0.01" id="payPortalAmountInput" name="amount" class="form-control" value="" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Channel</label>
                        <select name="gateway" class="form-control">
                            <option value="paystack">Paystack Online Checkout (Card, USSD, Transfer)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('payPortalModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Proceed to Paystack Checkout</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPayFeeModal(id, invNum, balance) {
    document.getElementById('payFeeInvoiceId').value = id;
    document.getElementById('payFeeInvoiceNumber').textContent = invNum;
    document.getElementById('payFeeAmountInput').value = balance;
    document.getElementById('payFeeAmountInput').max = balance;
    openModal('payFeeModal');
}

function openPayPortalModal(id, invNum, balance) {
    document.getElementById('payPortalInvoiceId').value = id;
    document.getElementById('payPortalInvoiceNumber').textContent = invNum;
    document.getElementById('payPortalAmountInput').value = balance;
    document.getElementById('payPortalAmountInput').max = balance;
    openModal('payPortalModal');
}
</script>
