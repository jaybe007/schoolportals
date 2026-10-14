<div class="page-header">
    <div>
        <h1 class="page-title">Sales Representative Commission Ledger</h1>
        <p class="page-subtitle">Traceable transaction attribution, multi-tiered commission computations, and manager approvals</p>
    </div>
</div>

<div class="card">
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

