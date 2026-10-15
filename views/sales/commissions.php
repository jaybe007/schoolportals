<div class="page-header">
    <div>
        <h1 class="page-title">Sales Representative Commission Ledger</h1>
        <p class="page-subtitle">Traceable transaction attribution, multi-tiered commission computations, and manager approvals</p>
    </div>
</div>

<!-- Commission KPI Telemetry Grid -->
<?php if (!empty($telemetry)): ?>
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Attributed Schools</div>
            <div class="stat-value"><?= (int)$telemetry['total_schools'] ?></div>
            <div class="stat-trend positive">
                <span><?= (int)$telemetry['billed_students'] ?> pupils billed across portfolio</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Verified Collections</div>
            <div class="stat-value" style="color: #059669;"><?= money((float)$telemetry['verified_collections']) ?></div>
            <div class="stat-trend positive">
                <span>Cash collections basis</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Initial Commissions (15%)</div>
            <div class="stat-value" style="color: #2563eb;"><?= money((float)$telemetry['initial_commissions']) ?></div>
            <div class="stat-trend">
                <span>Terms 1–3 acquisition</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-indigo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Recurring Renewals (7.5%)</div>
            <div class="stat-value" style="color: #7c3aed;"><?= money((float)$telemetry['recurring_commissions']) ?></div>
            <div class="stat-trend positive">
                <span>Terms 4+ renewal yield</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
        </div>
    </div>
</div>

<div class="stats-grid" style="margin-top: 1rem;">
    <div class="stat-card">
        <div>
            <div class="stat-label">Pending Approval</div>
            <div class="stat-value" style="color: #d97706;"><?= money((float)$telemetry['pending_commissions']) ?></div>
            <div class="stat-trend">
                <span>Awaiting manager sign-off</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Approved Commissions</div>
            <div class="stat-value" style="color: #0284c7;"><?= money((float)$telemetry['approved_commissions']) ?></div>
            <div class="stat-trend positive">
                <span>Queued for disbursement</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Paid Out</div>
            <div class="stat-value" style="color: #059669;"><?= money((float)$telemetry['paid_commissions']) ?></div>
            <div class="stat-trend positive">
                <span>Wire reference confirmed</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Reversals & Adjustments</div>
            <div class="stat-value" style="color: #dc2626;"><?= money((float)$telemetry['reversed_commissions']) ?></div>
            <div class="stat-trend">
                <span>Symmetrical refund debit</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-rose">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Attributed Schools Directory -->
<?php if (!empty($attributedSchools)): ?>
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Attributed Schools Portfolio</h3>
            <p class="card-subtitle">Active institutions mapped to representative commercial ownership</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>School Institution</th>
                    <th>Representative</th>
                    <th>Enrolled Pupils</th>
                    <th>Billed Pupils</th>
                    <th>Verified Collections</th>
                    <th>Commissions Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attributedSchools as $as): ?>
                <tr>
                    <td>
                        <strong><?= e($as['school_name']) ?></strong>
                        <div style="font-size: 0.75rem; color: #64748b;">Code: <?= e($as['school_code']) ?></div>
                    </td>
                    <td><?= e($as['rep_name']) ?></td>
                    <td><?= (int)$as['enrolled_students'] ?> pupils</td>
                    <td><strong><?= (int)$as['billed_students'] ?> pupils</strong></td>
                    <td style="font-weight: 700; color: #059669;"><?= money((float)$as['verified_collections']) ?></td>
                    <td style="font-weight: 700; color: #1e3a8a;"><?= money((float)$as['total_paid_comm']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Subscription Commissions</h3>
            <p class="card-subtitle">Legacy subscription fee commissions</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Ledger ID</th>
                    <th>Representative</th>
                    <th>Acquired School</th>
                    <th>Source Transaction</th>
                    <th>Rate</th>
                    <th>Commission</th>
                    <th>Status</th>
                    <th>Approver / Payout</th>
                    <?php if ($isManager): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($commissions)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: #64748b; padding: 2rem;">
                            No commission records found in the ledger.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($commissions as $comm): ?>
                    <tr>
                        <td><code>#COMM-<?= str_pad((string)$comm['id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;"><?= e($comm['rep_name']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($comm['rep_code']) ?></div>
                        </td>
                        <td>
                            <strong><?= e($comm['school_name']) ?></strong>
                            <div style="font-size: 0.75rem; color: #64748b;">Sub Inv: <?= e($comm['invoice_number'] ?? '—') ?></div>
                        </td>
                        <td><?= money($comm['transaction_amount']) ?></td>
                        <td><?= (float)$comm['commission_rate'] ?>%</td>
                        <td>
                            <strong style="color: #059669; font-size: 1rem;"><?= money($comm['commission_amount']) ?></strong>
                        </td>
                        <td>
                            <span class="badge badge-<?= $comm['status'] === 'paid' ? 'success' : ($comm['status'] === 'approved' ? 'info' : 'warning') ?>">
                                <?= e($comm['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($comm['status'] === 'paid'): ?>
                                <div style="font-size: 0.75rem; color: #059669; font-weight: 600;">Paid: <?= format_date($comm['paid_at']) ?></div>
                                <div style="font-size: 0.75rem; font-family: monospace; color: #64748b;"><?= e($comm['payout_reference']) ?></div>
                            <?php elseif ($comm['status'] === 'approved'): ?>
                                <div style="font-size: 0.75rem; color: #1e3a8a;">Approved by <?= e($comm['approver_name'] ?? 'Manager') ?></div>
                            <?php else: ?>
                                <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">Awaiting manager review</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($isManager): ?>
                            <td>
                                <?php if ($comm['status'] === 'pending'): ?>
                                    <form method="POST" action="<?= url('sales/commissions/' . $comm['id'] . '/approve') ?>" style="display: inline;" onsubmit="return confirm('Approve this verified commission payout?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                    </form>
                                <?php elseif ($comm['status'] === 'approved'): ?>
                                    <form method="POST" action="<?= url('sales/commissions/' . $comm['id'] . '/pay') ?>" style="display: inline;" onsubmit="return confirm('Record bank disbursement for this commission?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-success btn-sm">Disburse Payout</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: #64748b;">Completed</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Per-Student Portal Fee Commissions -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Per-Student Term Portal Fee Commissions</h3>
            <p class="card-subtitle">Recurring and initial commissions calculated on verified student portal collections</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Ledger ID</th>
                    <th>Representative</th>
                    <th>School Institution</th>
                    <th>Academic Term</th>
                    <th>Distributable Basis</th>
                    <th>Rate Applied</th>
                    <th>Commission</th>
                    <th>Tier</th>
                    <th>Status</th>
                    <th>Approval / Payout Ref</th>
                    <?php if ($isManager): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($portalCommissions)): ?>
                    <tr>
                        <td colspan="11" style="text-align: center; color: #64748b; padding: 2rem;">
                            No portal fee commissions recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($portalCommissions as $pc): ?>
                    <tr>
                        <td><code>#PCOM-<?= str_pad((string)$pc['id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;"><?= e($pc['rep_name']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($pc['rep_code']) ?></div>
                        </td>
                        <td><strong><?= e($pc['school_name']) ?></strong></td>
                        <td><?= e($pc['term_name']) ?></td>
                        <td><?= money((float)$pc['distributable_amount']) ?></td>
                        <td><?= (float)$pc['rate_applied'] ?>%</td>
                        <td style="font-weight: 700; color: #059669; font-size: 1rem;">
                            <?= money((float)$pc['commission_amount']) ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= $pc['commission_tier'] === 'initial' ? 'info' : 'purple' ?>">
                                <?= ucfirst(e($pc['commission_tier'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= $pc['status'] === 'paid' ? 'success' : ($pc['status'] === 'approved' ? 'info' : 'warning') ?>">
                                <?= ucfirst(e($pc['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($pc['status'] === 'paid'): ?>
                                <div style="font-size: 0.75rem; color: #059669; font-weight: 600;">Paid: <?= format_date($pc['paid_at']) ?></div>
                                <div style="font-size: 0.75rem; font-family: monospace; color: #64748b;"><?= e($pc['payout_reference']) ?></div>
                            <?php elseif ($pc['status'] === 'approved'): ?>
                                <div style="font-size: 0.75rem; color: #1e3a8a;">Approved by <?= e($pc['approver_name'] ?? 'Manager') ?></div>
                            <?php else: ?>
                                <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">Awaiting manager review</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($isManager): ?>
                            <td>
                                <?php if ($pc['status'] === 'pending'): ?>
                                    <form method="POST" action="<?= url('sales/portal-commissions/' . $pc['id'] . '/approve') ?>" style="display: inline;" onsubmit="return confirm('Approve this verified portal fee commission?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                    </form>
                                <?php elseif ($pc['status'] === 'approved'): ?>
                                    <form method="POST" action="<?= url('sales/portal-commissions/' . $pc['id'] . '/pay') ?>" style="display: inline;" onsubmit="return confirm('Record payout reference for this commission?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-success btn-sm">Record Payout</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: #64748b;">Completed</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

