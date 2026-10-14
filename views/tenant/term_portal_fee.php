<div class="page-header">
    <div>
        <h1 class="page-title">Term Portal Fee Management</h1>
        <p class="page-subtitle"><?= e($term['name'] ?? 'Current Term') ?> &bull; Session <?= e($session['name'] ?? '2025/2026') ?></p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <?php if ($agreement['status'] === 'accepted'): ?>
            <form method="POST" action="<?= url('school/term-portal-fee/generate-invoices') ?>" style="display: inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/></svg>
                    <span>Generate Student Invoices</span>
                </button>
            </form>
            <button class="btn btn-outline" onclick="openModal('dispatchNoticeModal')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Notify Parents</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Term Agreement Review Card -->
<div class="card" style="border-left: 4px solid <?= $agreement['status'] === 'accepted' ? '#059669' : ($agreement['status'] === 'rejected' ? '#dc2626' : '#2563eb') ?>; margin-bottom: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Portal Fee Term Agreement</h3>
            <p class="card-subtitle">Governed by Policy: <strong><?= e($agreement['policy_code']) ?></strong></p>
        </div>
        <div>
            <?php if ($agreement['status'] === 'accepted'): ?>
                <span class="badge badge-success" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                    ✓ Agreement Accepted (<?= date('M j, Y', strtotime($agreement['accepted_at'] ?? 'now')) ?>)
                </span>
            <?php elseif ($agreement['status'] === 'proposed'): ?>
                <span class="badge badge-warning" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                    Action Required: Proposed Fee Arrangement
                </span>
            <?php else: ?>
                <span class="badge badge-danger">Rejected / Adjustment Requested</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; background: #f8fafc; padding: 1.25rem; border-radius: 8px; margin-bottom: 1.25rem;">
            <div>
                <div style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Proposed Fee / Student</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #1e3a8a;"><?= money((float)$agreement['proposed_fee_per_student']) ?></div>
                <div style="font-size: 0.75rem; color: #64748b;"><?= ucfirst(e($agreement['fee_payer'])) ?> Payer Model</div>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Eligible Students</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #0f172a;"><?= (int)$agreement['estimated_eligible_students'] ?></div>
                <div style="font-size: 0.75rem; color: #64748b;">Active enrolled cohort</div>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Estimated Term Bill</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #7c3aed;">
                    <?= money((float)$agreement['proposed_fee_per_student'] * (int)$agreement['estimated_eligible_students']) ?>
                </div>
                <div style="font-size: 0.75rem; color: #64748b;">Parent-billed total</div>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 600;">School Incentive Share</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #059669;"><?= $agreement['school_incentive_percentage'] ?>%</div>
                <div style="font-size: 0.75rem; color: #64748b;">Accrued upon verification</div>
            </div>
        </div>

        <?php if ($agreement['status'] === 'proposed'): ?>
            <div style="display: flex; gap: 1rem; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                <p style="margin: 0; color: #475569; flex: 1;">
                    Review the term rate and revenue share above. Accepting allows you to generate parent invoices and broadcast official notices.
                </p>
                <button class="btn btn-primary" onclick="openModal('acceptAgreementModal')">Accept & Approve Agreement</button>
                <button class="btn btn-outline" style="color: #dc2626; border-color: #fca5a5;" onclick="openModal('rejectAgreementModal')">Reject / Request Rate Change</button>
            </div>
        <?php elseif (!empty($agreement['school_notes'])): ?>
            <div style="font-size: 0.85rem; color: #475569; background: #fff; border: 1px solid #e2e8f0; padding: 0.75rem 1rem; border-radius: 6px;">
                <strong>Management Notes:</strong> <?= e($agreement['school_notes']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Term Telemetry Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Invoices Issued</div>
            <div class="stat-value"><?= number_format($summary['total_invoices']) ?></div>
            <div class="stat-trend positive">
                <span><?= $summary['paid_count'] ?> Fully Settled</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Billed</div>
            <div class="stat-value"><?= money($summary['total_invoiced']) ?></div>
            <div class="stat-trend">
                <span>Gross student charges</span>
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
                <span><?= $summary['collection_rate'] ?>% collection rate</span>
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
        <button class="btn btn-outline btn-sm" onclick="openModal('dispatchNoticeModal')">+ Send New Notice</button>
    </div>
    <div style="padding: 1.25rem;">
        <?php foreach ($notices as $nt): ?>
        <div style="border-left: 3px solid #2563eb; padding-left: 1rem; margin-bottom: 1rem;">
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

<!-- Student Portal Invoices Table -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Student Portal Fee Invoices</h3>
            <p class="card-subtitle">Active charges, scholarship waivers, verified payments, and balance dues</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Student Name</th>
                    <th>Class Arm</th>
                    <th>Gross Fee</th>
                    <th>Waiver / Scholarship</th>
                    <th>Amount Due</th>
                    <th>Amount Paid</th>
                    <th>Balance Due</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                <tr>
                    <td colspan="10" style="text-align: center; color: #64748b; padding: 2.5rem;">
                        No student portal invoices generated yet for this term. Click <strong>Generate Student Invoices</strong> above to create them.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><strong><?= e($inv['invoice_number']) ?></strong></td>
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
                        <td><?= e($inv['due_date']) ?></td>
                        <td>
                            <?php if ($inv['status'] === 'paid'): ?>
                                <span class="badge badge-success">Paid</span>
                            <?php elseif ($inv['status'] === 'partially_paid'): ?>
                                <span class="badge badge-warning">Partial</span>
                            <?php elseif ($inv['status'] === 'waived'): ?>
                                <span class="badge badge-info">Waived</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Unpaid</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Accept Agreement -->
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

<!-- Modal: Reject Agreement -->
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
                        <label class="form-label">Reason for Rejection / Adjustment Request *</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Explain why the proposed fee cannot be adopted or request a customized rate." required></textarea>
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

<!-- Modal: Dispatch Notice to Parents -->
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
