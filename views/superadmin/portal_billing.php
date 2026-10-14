<div class="page-header">
    <div>
        <h1 class="page-title">Portal Billing & Revenue Management</h1>
        <p class="page-subtitle">Per-student term billing, multi-tenant revenue allocations, school incentives, and verified payout governance</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <button class="btn btn-primary" onclick="openModal('createPricingModal')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            <span>+ Configure Pricing Policy</span>
        </button>
    </div>
</div>

<!-- Primary Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Eligible Students</div>
            <div class="stat-value"><?= number_format($stats['eligible_students']) ?></div>
            <div class="stat-trend positive">
                <span><?= number_format($stats['billed_students']) ?> billed this term</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Total Invoiced</div>
            <div class="stat-value"><?= money($stats['total_invoiced']) ?></div>
            <div class="stat-trend">
                <span><?= number_format($stats['total_invoices']) ?> active invoices</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Verified Collections</div>
            <div class="stat-value"><?= money($stats['total_collected']) ?></div>
            <div class="stat-trend positive">
                <span><?= $stats['collection_rate'] ?>% collection rate</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Outstanding Balances</div>
            <div class="stat-value"><?= money($stats['total_outstanding']) ?></div>
            <div class="stat-trend negative">
                <span>Unsettled parent charges</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
        </div>
    </div>
</div>

<!-- Secondary Revenue Slices Grid -->
<div class="stats-grid" style="margin-top: 1rem;">
    <div class="stat-card">
        <div>
            <div class="stat-label">Platform Share (45%)</div>
            <div class="stat-value" style="color: #2563eb;"><?= money($stats['platform_revenue']) ?></div>
            <div class="stat-trend positive"><span>Retained SaaS revenue</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">School Incentives (40%)</div>
            <div class="stat-value" style="color: #059669;"><?= money($stats['school_incentives']) ?></div>
            <div class="stat-trend"><span>Accrued to partner schools</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Sales Commissions</div>
            <div class="stat-value" style="color: #7c3aed;"><?= money($stats['sales_commissions']) ?></div>
            <div class="stat-trend"><span>Attributed reps ledger</span></div>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Pending Payouts</div>
            <div class="stat-value" style="color: #d97706;"><?= money($stats['payouts_pending']) ?></div>
            <div class="stat-trend"><span>Awaiting Super Admin release</span></div>
        </div>
    </div>
</div>

<!-- School Collection Performance Breakdown -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">School-by-School Collection Performance</h3>
            <p class="card-subtitle">Real-time enrollment, billed amounts, verified collections, and agreement status</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>School Institution</th>
                    <th>Eligible Students</th>
                    <th>Invoices Billed</th>
                    <th>Total Invoiced</th>
                    <th>Verified Collections</th>
                    <th>Outstanding Due</th>
                    <th>Agreement Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schoolBreakdown as $sch): ?>
                <tr>
                    <td>
                        <strong><?= e($sch['name']) ?></strong>
                        <div style="font-size: 0.75rem; color: #64748b;"><?= e($sch['code']) ?> &bull; /site/<?= e($sch['slug']) ?></div>
                    </td>
                    <td><span class="badge badge-info"><?= (int)$sch['eligible_students'] ?> Enrolled</span></td>
                    <td><?= (int)$sch['billed_invoices'] ?></td>
                    <td><?= money((float)$sch['total_invoiced']) ?></td>
                    <td style="color: #059669; font-weight: 600;"><?= money((float)$sch['total_collected']) ?></td>
                    <td style="color: <?= (float)$sch['total_balance'] > 0 ? '#dc2626' : '#64748b' ?>; font-weight: 600;">
                        <?= money((float)$sch['total_balance']) ?>
                    </td>
                    <td>
                        <?php if ($sch['agreement_status'] === 'accepted'): ?>
                            <span class="badge badge-success">Accepted</span>
                        <?php elseif ($sch['agreement_status'] === 'proposed'): ?>
                            <span class="badge badge-warning">Proposed</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Pending Setup</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= url('school/term-portal-fee') ?>" class="btn btn-outline btn-sm">Inspect Portal</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Active Pricing Policies & Revenue Sharing Rules -->
<div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
    <!-- Pricing Policies -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Portal Pricing Policies</h3>
                <p class="card-subtitle">Global defaults and approved school-specific rates</p>
            </div>
            <button class="btn btn-outline btn-sm" onclick="openModal('createPricingModal')">+ New Rate</button>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Policy Code</th>
                        <th>Scope</th>
                        <th>Fee / Student</th>
                        <th>Payer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricingPolicies as $pol): ?>
                    <tr>
                        <td>
                            <strong><?= e($pol['policy_code']) ?></strong>
                            <div style="font-size: 0.75rem; color: #64748b;">Eff: <?= e($pol['effective_date']) ?></div>
                        </td>
                        <td><?= e($pol['school_name'] ?? 'Global Default (All Schools)') ?></td>
                        <td style="font-weight: 700; color: #1e3a8a;"><?= money((float)$pol['fee_per_student']) ?></td>
                        <td><?= ucfirst(e($pol['fee_payer'])) ?></td>
                        <td><span class="badge badge-success"><?= ucfirst(e($pol['status'])) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Revenue Sharing Rules -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Revenue-Sharing Model</h3>
                <p class="card-subtitle">Approved platform, school & rep distribution</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rule Code</th>
                        <th>Basis</th>
                        <th>Platform</th>
                        <th>School</th>
                        <th>Sales Rep</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revPolicies as $rp): ?>
                    <tr>
                        <td><strong><?= e($rp['policy_code']) ?></strong></td>
                        <td><span class="badge badge-info"><?= str_replace('_', ' ', e($rp['allocation_basis'])) ?></span></td>
                        <td style="color: #2563eb; font-weight: 600;"><?= $rp['platform_percentage'] ?>%</td>
                        <td style="color: #059669; font-weight: 600;"><?= $rp['school_incentive_percentage'] ?>%</td>
                        <td>
                            <div><?= $rp['sales_rep_initial_rate'] ?>% <span style="font-size: 0.7rem; color: #64748b;">(Init)</span></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= $rp['sales_rep_recurring_rate'] ?>% (Renew)</div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- School Incentive Payout Requests -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">School Incentive Payout Requests</h3>
            <p class="card-subtitle">Pending and completed bank transfers to participating schools</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Request Code</th>
                    <th>School</th>
                    <th>Amount Requested</th>
                    <th>Bank Details</th>
                    <th>Requested By</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payoutRequests)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; padding: 2rem;">No school payout requests currently pending.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($payoutRequests as $pr): ?>
                    <tr>
                        <td><strong><?= e($pr['request_code']) ?></strong></td>
                        <td><?= e($pr['school_name']) ?></td>
                        <td style="font-weight: 700; color: #059669;"><?= money((float)$pr['requested_amount']) ?></td>
                        <td>
                            <div><?= e($pr['bank_name']) ?> &bull; <?= e($pr['account_number']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($pr['account_name']) ?></div>
                        </td>
                        <td><?= e($pr['requester_name']) ?></td>
                        <td>
                            <?php if ($pr['status'] === 'paid'): ?>
                                <span class="badge badge-success">Disbursed (<?= e($pr['disbursement_reference']) ?>)</span>
                            <?php elseif ($pr['status'] === 'pending'): ?>
                                <span class="badge badge-warning">Pending Approval</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><?= ucfirst(e($pr['status'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($pr['status'] === 'pending'): ?>
                            <button class="btn btn-primary btn-sm" onclick="openPayoutModal(<?= (int)$pr['id'] ?>, '<?= e($pr['school_name']) ?>', '<?= money((float)$pr['requested_amount']) ?>')">
                                Approve & Disburse
                            </button>
                            <?php else: ?>
                            <span style="font-size: 0.8rem; color: #64748b;">Completed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Financial Allocations Ledger -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Real-Time Revenue Allocation Ledger</h3>
            <p class="card-subtitle">Penny-reconciled distribution of every verified portal collection</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Payment Ref</th>
                    <th>School</th>
                    <th>Gross Paid</th>
                    <th>Gateway Fee</th>
                    <th>Distributable</th>
                    <th>Platform (45%)</th>
                    <th>School Inc. (40%)</th>
                    <th>Sales Rep</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentAllocations)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; color: #64748b; padding: 2rem;">No verified payment allocations recorded yet.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($recentAllocations as $al): ?>
                    <tr>
                        <td>
                            <strong><?= e($al['payment_reference']) ?></strong>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= ucfirst(e($al['gateway'])) ?> &bull; <?= e($al['payer_name'] ?? 'Parent') ?></div>
                        </td>
                        <td><?= e($al['school_name']) ?></td>
                        <td style="font-weight: 600;"><?= money((float)$al['gross_amount']) ?></td>
                        <td style="color: #64748b;"><?= money((float)$al['gateway_fee']) ?></td>
                        <td style="font-weight: 700; color: #1e3a8a;"><?= money((float)$al['distributable_amount']) ?></td>
                        <td style="color: #2563eb; font-weight: 600;"><?= money((float)$al['platform_amount']) ?></td>
                        <td style="color: #059669; font-weight: 600;"><?= money((float)$al['school_incentive_amount']) ?></td>
                        <td>
                            <div><?= money((float)$al['sales_rep_commission_amount']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($al['rep_name'] ?? 'Unassigned') ?> (<?= e($al['commission_tier']) ?>)</div>
                        </td>
                        <td><span class="badge badge-success"><?= ucfirst(e($al['status'])) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal 1: Configure Pricing Policy -->
<div class="modal" id="createPricingModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Configure Portal Pricing Policy</h3>
                <button class="modal-close" onclick="closeModal('createPricingModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('superadmin/portal-billing/pricing') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Policy Code *</label>
                        <input type="text" name="policy_code" class="form-control" placeholder="e.g. TERM-FEE-2026-PRIMARY" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Fee per Student (NGN) *</label>
                            <input type="number" step="50" name="fee_per_student" class="form-control" value="3000" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fee Payer Model *</label>
                            <select name="fee_payer" class="form-control">
                                <option value="parent">Billed to Parents</option>
                                <option value="school_sponsored">School Sponsored</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">School Scope</label>
                        <select name="school_id" class="form-control">
                            <option value="">Global Default (All Schools)</option>
                            <?php foreach ($schools as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?> (School-Specific Rate)</option>
                            <?php endforeach; ?>
                        </select>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Select specific school for negotiated custom rates.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Effective Date *</label>
                        <input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Policy Notes / Justification</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Approved by platform board for 2025/2026 session."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createPricingModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Publish Pricing Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Approve School Payout -->
<div class="modal" id="payoutModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Approve School Incentive Disbursement</h3>
                <button class="modal-close" onclick="closeModal('payoutModal')">&times;</button>
            </div>
            <form method="POST" id="payoutForm" action="">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p style="color: #475569; margin-bottom: 1rem;">
                        You are approving an incentive disbursement of <strong id="payoutAmountDisplay" style="color: #059669;"></strong> to <strong id="payoutSchoolDisplay"></strong>.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Bank Disbursement Reference *</label>
                        <input type="text" name="disbursement_reference" class="form-control" value="DISB-NIP-<?= date('Ymd') ?>-<?= rand(1000, 9999) ?>" required>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">NIP session reference from central bank wire.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('payoutModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Authorize & Record Payout</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPayoutModal(id, schoolName, amountStr) {
    document.getElementById('payoutSchoolDisplay').textContent = schoolName;
    document.getElementById('payoutAmountDisplay').textContent = amountStr;
    document.getElementById('payoutForm').action = '<?= url("superadmin/portal-billing/payouts/") ?>' + id + '/approve';
    openModal('payoutModal');
}
</script>
