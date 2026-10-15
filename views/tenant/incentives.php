<div class="page-header">
    <div>
        <h1 class="page-title">School Incentives & Revenue Share</h1>
        <p class="page-subtitle">Track accrued school incentives from portal fee collections and manage bank disbursements</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= url('school/incentives/export') ?>" class="btn btn-outline" title="Download Complete Double-Entry Ledger CSV">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Export Ledger (CSV)</span>
        </a>
        <button class="btn btn-primary" onclick="openModal('requestPayoutModal')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
            <span>Request Bank Payout</span>
        </button>
    </div>
</div>

<!-- Incentive Financial Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Verified Collections</div>
            <div class="stat-value"><?= money($summary['total_collected']) ?></div>
            <div class="stat-trend positive">
                <span>From <?= $summary['paid_count'] ?> settled portal invoices</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Accrued Incentives</div>
            <div class="stat-value" style="color: #059669;"><?= money($summary['incentive_accrued']) ?></div>
            <div class="stat-trend positive">
                <span>School 25% agreed allocation</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Disbursed Payouts</div>
            <div class="stat-value" style="color: #7c3aed;"><?= money($summary['incentive_paid_out']) ?></div>
            <div class="stat-trend">
                <span>Transferred to school bank</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Available Payout Balance</div>
            <div class="stat-value" style="color: #2563eb;"><?= money($summary['incentive_balance']) ?></div>
            <div class="stat-trend positive">
                <span>Ready for disbursement</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
    </div>
</div>

<!-- Term-by-Term Incentive Performance Breakdown -->
<?php if (!empty($termPerformance)): ?>
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Term-by-Term Incentive Performance</h3>
            <p class="card-subtitle">Historical term collections and school incentive accruals</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Academic Session & Term</th>
                    <th>Invoices Billed</th>
                    <th>Invoices Paid</th>
                    <th>Total Collections</th>
                    <th>School Agreed Share</th>
                    <th>Incentive Accrued</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($termPerformance as $tp): ?>
                <tr>
                    <td><strong><?= e($tp['session_name']) ?> &bull; <?= e($tp['term_name']) ?></strong></td>
                    <td><?= (int)$tp['total_invoices'] ?> pupils</td>
                    <td><span class="badge badge-success"><?= (int)$tp['paid_invoices'] ?> Settled</span></td>
                    <td style="font-weight: 700; color: #1e3a8a;"><?= money((float)$tp['total_collected']) ?></td>
                    <td><span class="badge badge-info">40.0% Allocation</span></td>
                    <td style="font-weight: 700; color: #059669; font-size: 1rem;"><?= money((float)$tp['accrued_incentive']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Bank Payout Requests Table -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">School Bank Payout Requests</h3>
            <p class="card-subtitle">Official disbursement records processed by Super Admin HQ</p>
        </div>
        <button class="btn btn-outline btn-sm" onclick="openModal('requestPayoutModal')">+ New Request</button>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Request Code</th>
                    <th>Amount</th>
                    <th>Beneficiary Bank Account</th>
                    <th>Date Requested</th>
                    <th>Status</th>
                    <th>Disbursement Reference</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payoutRequests)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 2rem;">No payout requests submitted yet.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($payoutRequests as $pr): ?>
                    <tr>
                        <td><strong><?= e($pr['request_code']) ?></strong></td>
                        <td style="font-weight: 700; color: #059669;"><?= money((float)$pr['requested_amount']) ?></td>
                        <td>
                            <div><?= e($pr['bank_name']) ?> &bull; <?= e($pr['account_number']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($pr['account_name']) ?></div>
                        </td>
                        <td><?= date('M j, Y g:i A', strtotime($pr['created_at'])) ?></td>
                        <td>
                            <?php if ($pr['status'] === 'paid'): ?>
                                <span class="badge badge-success">Disbursed</span>
                            <?php elseif ($pr['status'] === 'pending'): ?>
                                <span class="badge badge-warning">Awaiting Approval</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><?= ucfirst(e($pr['status'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($pr['disbursement_reference'])): ?>
                                <span style="font-family: monospace; font-weight: 600; color: #1e3a8a;"><?= e($pr['disbursement_reference']) ?></span>
                            <?php else: ?>
                                <span style="color: #94a3b8;">Pending HQ wire</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Historical Incentive Double-Entry Ledger -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Incentive Accrual & Payout Ledger</h3>
            <p class="card-subtitle">Transparent double-entry transaction record of every accrual, adjustment, and disbursement</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Type</th>
                    <th>Notes</th>
                    <th>Amount</th>
                    <th>Running Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ledger)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 2rem;">No incentive transactions recorded yet.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($ledger as $entry): ?>
                    <tr>
                        <td><?= date('M j, Y g:i A', strtotime($entry['created_at'])) ?></td>
                        <td><strong><?= e($entry['reference']) ?></strong></td>
                        <td>
                            <?php if ($entry['entry_type'] === 'accrual'): ?>
                                <span class="badge badge-success">Accrual</span>
                            <?php elseif ($entry['entry_type'] === 'payout'): ?>
                                <span class="badge badge-purple">Payout</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><?= ucfirst(e($entry['entry_type'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($entry['notes'] ?? '—') ?></td>
                        <td style="font-weight: 700; color: <?= (float)$entry['amount'] >= 0 ? '#059669' : '#dc2626' ?>;">
                            <?= (float)$entry['amount'] >= 0 ? '+' : '' ?><?= money((float)$entry['amount']) ?>
                        </td>
                        <td style="font-weight: 700; color: #1e3a8a;"><?= money((float)$entry['running_balance']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Request Payout -->
<div class="modal" id="requestPayoutModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Request Incentive Bank Payout</h3>
                <button class="modal-close" onclick="closeModal('requestPayoutModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('school/incentives/request-payout') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p style="color: #475569; margin-bottom: 1rem;">
                        Available incentive balance for disbursement: <strong style="color: #059669; font-size: 1.1rem;"><?= money($summary['incentive_balance']) ?></strong>
                    </p>
                    <div class="form-group">
                        <label class="form-label">Payout Amount (NGN) *</label>
                        <input type="number" step="0.01" max="<?= $summary['incentive_balance'] ?>" name="amount" class="form-control" value="<?= min(50000.0, $summary['incentive_balance']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bank Name *</label>
                        <input type="text" name="bank_name" class="form-control" placeholder="e.g. Zenith Bank, Access Bank, GTBank" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Account Number *</label>
                            <input type="text" name="account_number" class="form-control" placeholder="10-digit NUBAN" maxlength="10" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Name *</label>
                            <input type="text" name="account_name" class="form-control" placeholder="Official School Account Name" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('requestPayoutModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" <?= $summary['incentive_balance'] <= 0 ? 'disabled' : '' ?>>Submit Disbursement Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
