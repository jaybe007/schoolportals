<div class="page-header">
    <div>
        <h1 class="page-title">Term Portal Fee Management</h1>
        <p class="page-subtitle"><?= e($term['name'] ?? 'Current Term') ?> &bull; Session <?= e($session['name'] ?? '2025/2026') ?></p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <?php if ($agreement['status'] === 'accepted'): ?>
            <form method="POST" action="<?= url('school/term-portal-fee/generate-invoices') ?>" style="display: inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/></svg>
                    <span>Generate Student Invoices</span>
                </button>
            </form>
            <button class="btn btn-outline btn-sm" onclick="openModal('previewInvoiceModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>Preview Parent Invoice</span>
            </button>
            <button class="btn btn-outline btn-sm" onclick="openModal('dispatchNoticeModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Notify Parents</span>
            </button>
            <?php if ((int)$counts['count_unpaid'] + (int)$counts['count_partial'] > 0): ?>
            <form method="POST" action="<?= url('school/term-portal-fee/send-reminders') ?>" style="display: inline;" onsubmit="return confirm('Send automated payment reminders to parents of outstanding invoices?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline btn-sm" style="color: #d97706; border-color: #fde68a;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span>Send Reminders (<?= (int)$counts['count_unpaid'] + (int)$counts['count_partial'] ?>)</span>
                </button>
            </form>
            <?php endif; ?>
        <?php endif; ?>
        <a href="<?= url('school/term-portal-fee/export') ?>" class="btn btn-secondary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
            <span>Export CSV Report</span>
        </a>
    </div>
</div>

<!-- Term Agreement Review Card -->
<div class="card" style="border-left: 4px solid <?= $agreement['status'] === 'accepted' ? '#059669' : ($agreement['status'] === 'rejected' ? '#dc2626' : ($agreement['status'] === 'amendment_requested' ? '#d97706' : '#2563eb')) ?>; margin-bottom: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Portal Fee Term Agreement</h3>
            <p class="card-subtitle">Governed by Policy: <strong><?= e($agreement['policy_code']) ?></strong></p>
        </div>
        <div>
            <?php if ($agreement['status'] === 'accepted'): ?>
                <span class="badge badge-success" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                    ✓ Agreement Accepted & Active (<?= date('M j, Y', strtotime($agreement['accepted_at'] ?? 'now')) ?>)
                </span>
            <?php elseif ($agreement['status'] === 'proposed'): ?>
                <span class="badge badge-warning" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                    Action Required: Review & Authorize Proposed Rate
                </span>
            <?php elseif ($agreement['status'] === 'amendment_requested'): ?>
                <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                    Clarification / Rate Amendment Requested
                </span>
            <?php else: ?>
                <span class="badge badge-danger">Rejected / Adjustment Requested</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; background: #f8fafc; padding: 1.25rem; border-radius: 8px; margin-bottom: 1.25rem;">
            <div>
                <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Proposed Fee / Student</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #1e3a8a;"><?= money((float)$agreement['proposed_fee_per_student']) ?></div>
                <div style="font-size: 0.75rem; color: #64748b;"><?= ucfirst(str_replace('_', ' ', e($agreement['fee_payer']))) ?> Payer Model</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Eligible Students</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #0f172a;"><?= (int)$agreement['estimated_eligible_students'] ?></div>
                <div style="font-size: 0.75rem; color: #64748b;">Active enrolled cohort</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Estimated Term Bill</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #7c3aed;">
                    <?= money((float)$agreement['proposed_fee_per_student'] * (int)$agreement['estimated_eligible_students']) ?>
                </div>
                <div style="font-size: 0.75rem; color: #64748b;">Parent-billed projection</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600;">School Incentive Share</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #059669;"><?= $agreement['school_incentive_percentage'] ?>%</div>
                <div style="font-size: 0.75rem; color: #64748b;">Accrued upon collection verification</div>
            </div>
        </div>

        <?php if ($agreement['status'] === 'proposed'): ?>
            <div style="display: flex; gap: 0.75rem; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 1rem; flex-wrap: wrap;">
                <p style="margin: 0; color: #475569; flex: 1; min-width: 280px; font-size: 0.9rem;">
                    Please review the proposed rate and revenue-sharing terms. Accepting authorizes invoice generation for enrolled students and parent notifications.
                </p>
                <button class="btn btn-primary" onclick="openModal('acceptAgreementModal')">Accept & Authorize Agreement</button>
                <button class="btn btn-outline" style="color: #d97706; border-color: #fde68a;" onclick="openModal('clarifyAgreementModal')">Request Clarification</button>
                <button class="btn btn-outline" style="color: #dc2626; border-color: #fca5a5;" onclick="openModal('rejectAgreementModal')">Reject Rate</button>
            </div>
        <?php elseif ($agreement['status'] === 'amendment_requested'): ?>
            <div style="background: #fffbeb; border: 1px solid #fef3c7; padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.85rem; color: #92400e;">
                <strong>Amendment Requested:</strong> <?= e($agreement['clarification_requested'] ?? 'Awaiting platform review') ?>
            </div>
        <?php elseif (!empty($agreement['school_notes'])): ?>
            <div style="font-size: 0.85rem; color: #475569; background: #fff; border: 1px solid #e2e8f0; padding: 0.75rem 1rem; border-radius: 6px;">
                <strong>Management Notes:</strong> <?= e($agreement['school_notes']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Student Eligibility Governance & Audit Policy Alert -->
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: flex-start;">
    <div style="background: #dbeafe; color: #1e40af; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
    </div>
    <div style="font-size: 0.85rem; color: #475569; flex: 1;">
        <strong style="color: #0f172a;">Student Eligibility & Audit Principles:</strong>
        Under this commercial model, fees apply strictly to eligible enrolled students. Graduated, withdrawn, or transferred students are automatically excluded by enrollment status. New admissions and mid-term enrollments are billed idempotently when invoice generation is re-run. Approved scholarships and waivers can be applied using the <em>Adjust / Waive</em> tool with mandatory audit justification. Historical transactions and paid receipts are permanently preserved.
    </div>
</div>

<!-- Term Telemetry Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Invoices Issued</div>
            <div class="stat-value"><?= number_format($summary['total_invoices']) ?></div>
            <div class="stat-trend positive">
                <span><?= $summary['paid_count'] ?> Fully Settled (<?= $counts['count_partial'] ?> Partial)</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Amount Billed</div>
            <div class="stat-value"><?= money($summary['total_invoiced']) ?></div>
            <div class="stat-trend">
                <span>Gross pupil term charges</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Verified Collections</div>
            <div class="stat-value" style="color: #059669;"><?= money($summary['total_collected']) ?></div>
            <div class="stat-trend positive">
                <span><?= number_format($summary['collection_rate'], 1) ?>% collection rate</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">School Incentive Balance</div>
            <div class="stat-value" style="color: #2563eb;"><?= money($summary['incentive_balance']) ?></div>
            <div class="stat-trend positive">
                <a href="<?= url('school/incentives') ?>" style="color: #2563eb; text-decoration: underline;">Request Payout &rarr;</a>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
        </div>
    </div>
</div>

<!-- Published Parent Announcements -->
<?php if (!empty($notices)): ?>
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Broadcasted Parent Notices</h3>
        <button class="btn btn-outline btn-sm" onclick="openModal('dispatchNoticeModal')">+ Broadcast New Notice</button>
    </div>
    <div style="padding: 1.25rem;">
        <?php foreach ($notices as $nt): ?>
        <div style="border-left: 3px solid #2563eb; padding-left: 1rem; margin-bottom: 1rem; background: #f8fafc; padding-top: 0.75rem; padding-bottom: 0.75rem; border-radius: 0 6px 6px 0;">
            <div style="font-weight: 700; color: #1e3a8a;"><?= e($nt['title']) ?></div>
            <div style="color: #475569; font-size: 0.9rem; margin-top: 0.25rem;"><?= nl2br(e($nt['notice_body'])) ?></div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                Sent via <?= e($nt['sent_via']) ?> to <?= (int)$nt['recipient_count'] ?> registered parents &bull; <?= date('M j, Y g:i A', strtotime($nt['created_at'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Student Portal Invoices Card with Categorical Filter Tabs -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title">Student Portal Fee Invoices</h3>
            <p class="card-subtitle">Active charges, scholarship waivers, verified payments, and balance dues</p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="<?= url('school/term-portal-fee') ?>" class="btn <?= ($statusFilter === 'all') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                All (<?= (int)$counts['count_all'] ?>)
            </a>
            <a href="<?= url('school/term-portal-fee?status=paid') ?>" class="btn <?= ($statusFilter === 'paid') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Paid (<?= (int)$counts['count_paid'] ?>)
            </a>
            <a href="<?= url('school/term-portal-fee?status=partially_paid') ?>" class="btn <?= ($statusFilter === 'partially_paid') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Partial (<?= (int)$counts['count_partial'] ?>)
            </a>
            <a href="<?= url('school/term-portal-fee?status=unpaid') ?>" class="btn <?= ($statusFilter === 'unpaid') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Unpaid (<?= (int)$counts['count_unpaid'] ?>)
            </a>
            <a href="<?= url('school/term-portal-fee?status=waived') ?>" class="btn <?= ($statusFilter === 'waived') ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Waived (<?= (int)$counts['count_waived'] ?>)
            </a>
            <a href="<?= url('school/term-portal-fee?status=overdue') ?>" class="btn <?= ($statusFilter === 'overdue') ? 'btn-danger' : 'btn-outline' ?> btn-sm">
                Overdue (<?= (int)$counts['count_overdue'] ?>)
            </a>
            <a href="<?= url('school/term-portal-fee?status=disputed') ?>" class="btn <?= ($statusFilter === 'disputed') ? 'btn-warning' : 'btn-outline' ?> btn-sm">
                Disputed (<?= (int)$counts['count_disputed'] ?>)
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Student Name</th>
                    <th>Class Level & Arm</th>
                    <th>Gross Fee</th>
                    <th>Waiver / Scholarship</th>
                    <th>Amount Due</th>
                    <th>Amount Paid</th>
                    <th>Balance Due</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                <tr>
                    <td colspan="11" style="text-align: center; color: #64748b; padding: 2.5rem;">
                        No invoices found for the current filter.
                        <?php if ($counts['count_all'] === 0 && $agreement['status'] === 'accepted'): ?>
                            <br><br>
                            <form method="POST" action="<?= url('school/term-portal-fee/generate-invoices') ?>" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary btn-sm">Generate Student Invoices Now</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td>
                            <strong><?= e($inv['invoice_number']) ?></strong>
                            <div style="font-size: 0.7rem; color: #64748b;"><?= date('M j, Y', strtotime($inv['created_at'])) ?></div>
                        </td>
                        <td>
                            <strong><?= e($inv['first_name'] . ' ' . $inv['last_name']) ?></strong>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($inv['admission_number']) ?></div>
                        </td>
                        <td><?= e($inv['class_name'] . ' ' . $inv['arm_name']) ?></td>
                        <td><?= money((float)$inv['gross_amount']) ?></td>
                        <td>
                            <?php if ((float)$inv['discount_amount'] > 0): ?>
                                <span style="color: #059669; font-weight: 600;">-<?= money((float)$inv['discount_amount']) ?></span>
                                <div style="font-size: 0.7rem; color: #64748b;"><?= e($inv['waiver_reason'] ?? 'Discount') ?></div>
                            <?php else: ?>
                                <span style="color: #94a3b8;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight: 700; color: #1e3a8a;"><?= money((float)$inv['amount_due']) ?></td>
                        <td style="color: #059669; font-weight: 600;"><?= money((float)$inv['paid_amount']) ?></td>
                        <td style="font-weight: 700; color: <?= (float)$inv['balance_due'] > 0 ? '#dc2626' : '#64748b' ?>;">
                            <?= money((float)$inv['balance_due']) ?>
                        </td>
                        <td>
                            <span><?= e($inv['due_date']) ?></span>
                            <?php if (!empty($inv['is_overdue'])): ?>
                                <span class="badge badge-danger" style="display: block; margin-top: 0.2rem; font-size: 0.65rem;">OVERDUE</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($inv['status'] === 'paid'): ?>
                                <span class="badge badge-success">Paid</span>
                            <?php elseif ($inv['status'] === 'partially_paid'): ?>
                                <span class="badge badge-warning">Partial</span>
                            <?php elseif ($inv['status'] === 'waived'): ?>
                                <span class="badge badge-info">Waived</span>
                            <?php elseif ($inv['status'] === 'disputed'): ?>
                                <span class="badge badge-danger">Disputed</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.25rem;">
                                <?php if ($inv['status'] !== 'paid'): ?>
                                <button class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;"
                                    onclick="openAdjustModal(<?= (int)$inv['id'] ?>, '<?= e($inv['invoice_number']) ?>', '<?= e($inv['first_name'] . ' ' . $inv['last_name']) ?>', <?= (float)$inv['gross_amount'] ?>, <?= (float)$inv['balance_due'] ?>)">
                                    Adjust/Waive
                                </button>
                                <?php endif; ?>
                                <?php if ($inv['status'] !== 'disputed'): ?>
                                <button class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.2rem 0.5rem; color: #dc2626; border-color: #fca5a5;"
                                    onclick="openDisputeModal(<?= (int)$inv['id'] ?>, '<?= e($inv['invoice_number']) ?>', '<?= e($inv['first_name'] . ' ' . $inv['last_name']) ?>')">
                                    Dispute
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal 1: Accept Agreement -->
<div class="modal" id="acceptAgreementModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Accept Term Portal Fee Agreement</h3>
                <button class="modal-close" onclick="closeModal('acceptAgreementModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('school/term-portal-fee/' . (int)$agreement['id'] . '/accept') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p style="color: #334155; margin-bottom: 1rem;">
                        By accepting, you authorize the generation of student portal invoices at <strong><?= money((float)$agreement['proposed_fee_per_student']) ?></strong> per enrolled pupil for <strong><?= e($term['name'] ?? 'Current Term') ?></strong>.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Acceptance Notes / Resolution Reference</label>
                        <textarea name="school_notes" class="form-control" rows="2" placeholder="e.g. Approved in school board meeting on Oct 10, 2026"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('acceptAgreementModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm & Accept Agreement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Reject Agreement -->
<div class="modal" id="rejectAgreementModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Reject Portal Fee Arrangement</h3>
                <button class="modal-close" onclick="closeModal('rejectAgreementModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('school/term-portal-fee/' . (int)$agreement['id'] . '/reject') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Reason for Rejection *</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Explain why the proposed fee cannot be adopted." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('rejectAgreementModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Rejection Notice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Request Clarification / Amendment -->
<div class="modal" id="clarifyAgreementModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Request Clarification / Fee Adjustment</h3>
                <button class="modal-close" onclick="closeModal('clarifyAgreementModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('school/term-portal-fee/' . (int)$agreement['id'] . '/clarify') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p style="color: #475569; font-size: 0.9rem; margin-bottom: 1rem;">
                        Submit questions or negotiate customized institutional pricing with platform administrators before formal approval.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Clarification Notes / Amendment Request *</label>
                        <textarea name="clarification_notes" class="form-control" rows="4" placeholder="Detail your requested rate adjustment, student category exclusions, or billing clarifications." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('clarifyAgreementModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Clarification Query</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 4: Interactive Parent Invoice Preview -->
<div class="modal" id="previewInvoiceModal">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Sample Parent Portal Invoice Preview</h3>
                <button class="modal-close" onclick="closeModal('previewInvoiceModal')">&times;</button>
            </div>
            <div class="modal-body" style="background: #f8fafc; padding: 1.5rem;">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #2563eb; padding-bottom: 1rem; margin-bottom: 1rem;">
                        <div>
                            <h4 style="margin: 0; color: #1e3a8a; font-size: 1.15rem;"><?= e(TenantContext::getSchool()['name'] ?? 'School Tenant') ?></h4>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e(TenantContext::getSchool()['address'] ?? 'Official Student Billing Portal') ?></div>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge badge-info">DIGITAL PORTAL INVOICE</span>
                            <div style="font-size: 0.8rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">PINV-2026-DEMO</div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; font-size: 0.85rem;">
                        <div>
                            <span style="color: #64748b;">Billed To Student:</span>
                            <div style="font-weight: 700; color: #0f172a;"><?= e($sampleStudent['first_name'] ?? 'Sample') ?> <?= e($sampleStudent['last_name'] ?? 'Student') ?></div>
                            <div style="color: #64748b;">Adm No: <?= e($sampleStudent['admission_number'] ?? 'ADM-2026-001') ?></div>
                            <div style="color: #64748b;">Class: <?= e($sampleStudent['class_level_name'] ?? 'Primary') ?> <?= e($sampleStudent['class_arm_name'] ?? 'A') ?></div>
                        </div>
                        <div style="text-align: right;">
                            <span style="color: #64748b;">Term / Session:</span>
                            <div style="font-weight: 700; color: #0f172a;"><?= e($term['name'] ?? 'Current Term') ?></div>
                            <div style="color: #64748b;">Session <?= e($session['name'] ?? '2025/2026') ?></div>
                            <div style="color: #dc2626; font-weight: 600;">Due: <?= date('M j, Y', strtotime('+30 days')) ?></div>
                        </div>
                    </div>

                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; margin-bottom: 1rem;">
                        <thead>
                            <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">
                                <th style="padding: 0.5rem; text-align: left;">Item Description</th>
                                <th style="padding: 0.5rem; text-align: right;">Amount (NGN)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.6rem 0.5rem;">
                                    <strong>Term Student Digital Portal Access Fee</strong>
                                    <div style="font-size: 0.75rem; color: #64748b;">Real-time continuous assessment records, digital report sheets, and academic parent portal</div>
                                </td>
                                <td style="padding: 0.6rem 0.5rem; text-align: right; font-weight: 700;"><?= money((float)$agreement['proposed_fee_per_student']) ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td style="padding: 0.75rem 0.5rem; text-align: right; font-weight: 800; font-size: 0.95rem;">Total Amount Payable:</td>
                                <td style="padding: 0.75rem 0.5rem; text-align: right; font-weight: 800; font-size: 1.1rem; color: #1e3a8a;"><?= money((float)$agreement['proposed_fee_per_student']) ?></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div style="background: #eff6ff; border-radius: 6px; padding: 0.75rem; font-size: 0.8rem; color: #1e40af; text-align: center;">
                        💳 Parents can pay securely online via debit card, bank transfer, or USSD via the Parent Dashboard.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('previewInvoiceModal')">Close Preview</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 5: Authorized Billing Correction / Waiver -->
<div class="modal" id="adjustModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Authorized Billing Adjustment</h3>
                <button class="modal-close" onclick="closeModal('adjustModal')">&times;</button>
            </div>
            <form method="POST" id="adjustForm" action="">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div style="background: #f8fafc; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem;">
                        <div>Invoice: <strong id="adjInvNum"></strong></div>
                        <div>Student: <strong id="adjStuName"></strong></div>
                        <div>Gross Fee: <span id="adjGross"></span> &bull; Current Balance: <span id="adjBalance" style="color: #dc2626; font-weight: 700;"></span></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Adjustment Type *</label>
                        <select name="adjustment_type" class="form-control" id="adjTypeSelect" onchange="handleAdjTypeChange(this.value)">
                            <option value="scholarship_waiver">Full Scholarship Waiver (100% Discount)</option>
                            <option value="partial_scholarship">Partial Scholarship / Bursary</option>
                            <option value="sibling_discount">Approved Sibling Discount</option>
                            <option value="mid_term_proration">Mid-Term Admission Proration</option>
                            <option value="administrative_correction">Administrative Billing Correction</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Discount / Waiver Amount (₦) *</label>
                        <input type="number" step="50" name="discount_amount" id="adjDiscountAmount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Audit Explanation & Justification *</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Provide reason for adjustment (e.g. Approved Academic Merit Scholarship 2026)" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('adjustModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Billing Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 6: Dispute Invoice -->
<div class="modal" id="disputeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Flag Invoice as Disputed</h3>
                <button class="modal-close" onclick="closeModal('disputeModal')">&times;</button>
            </div>
            <form method="POST" id="disputeForm" action="">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p style="color: #475569; font-size: 0.9rem; margin-bottom: 1rem;">
                        You are recording a formal billing dispute for invoice <strong id="dispInvNum"></strong> (<span id="dispStuName"></span>).
                    </p>
                    <div class="form-group">
                        <label class="form-label">Dispute Reason & Justification *</label>
                        <textarea name="dispute_reason" class="form-control" rows="3" placeholder="Explain the dispute (e.g. Student transferred before start of term, duplicate registration)" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('disputeModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Dispute Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 7: Broadcast Notice to Parents -->
<div class="modal" id="dispatchNoticeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Broadcast Notice to Parents</h3>
                <button class="modal-close" onclick="closeModal('dispatchNoticeModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('school/term-portal-fee/publish-notice') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="agreement_id" value="<?= (int)$agreement['id'] ?>">
                <input type="hidden" name="term_id" value="<?= (int)$termId ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Notice Title *</label>
                        <input type="text" name="title" class="form-control" value="Official Notice: <?= e($term['name'] ?? '') ?> Student Portal Access Fee" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notice Announcement Body *</label>
                        <textarea name="notice_body" class="form-control" rows="5" required>Dear Parents and Guardians,

Please be informed that the approved student digital portal fee of <?= money((float)$agreement['proposed_fee_per_student']) ?> for <?= e($term['name'] ?? 'this term') ?> is now due.

This enables uninterrupted access to real-time continuous assessment marksheets, digital report cards, lesson schedules, and fee balance reconciliations. Payments can be completed online via card checkout on the parent portal.</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('dispatchNoticeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Publish Announcement to Parents</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentGrossFee = 0;

function openAdjustModal(id, invNum, stuName, gross, balance) {
    currentGrossFee = gross;
    document.getElementById('adjInvNum').textContent = invNum;
    document.getElementById('adjStuName').textContent = stuName;
    document.getElementById('adjGross').textContent = '₦' + gross.toLocaleString();
    document.getElementById('adjBalance').textContent = '₦' + balance.toLocaleString();
    document.getElementById('adjDiscountAmount').value = gross;
    document.getElementById('adjustForm').action = '<?= url("school/term-portal-fee/invoices/") ?>' + id + '/adjust';
    openModal('adjustModal');
}

function handleAdjTypeChange(type) {
    const input = document.getElementById('adjDiscountAmount');
    if (type === 'scholarship_waiver') {
        input.value = currentGrossFee;
    } else if (type === 'partial_scholarship' || type === 'sibling_discount') {
        input.value = Math.round(currentGrossFee * 0.5);
    }
}

function openDisputeModal(id, invNum, stuName) {
    document.getElementById('dispInvNum').textContent = invNum;
    document.getElementById('dispStuName').textContent = stuName;
    document.getElementById('disputeForm').action = '<?= url("school/term-portal-fee/invoices/") ?>' + id + '/dispute';
    openModal('disputeModal');
}
</script>
