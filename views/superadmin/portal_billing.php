<div class="page-header">
    <div>
        <h1 class="page-title">Portal Billing & Revenue Management</h1>
        <p class="page-subtitle">Central per-student term fee configuration, multi-tenant revenue allocations, school incentives, and audit reconciliations</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <button class="btn btn-primary" onclick="openModal('createPricingModal')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            <span>+ Configure Pricing Policy</span>
        </button>
    </div>
</div>

<!-- Primary Platform Telemetry Grid -->
<div class="stats-grid">
    <div class="stat-card" style="border-top: 3px solid #2563eb;">
        <div>
            <div class="stat-label">Current Portal Fee</div>
            <div class="stat-value" style="color: #1e3a8a;"><?= money($stats['current_default_fee']) ?></div>
            <div class="stat-trend positive">
                <span>Per student per academic term (<?= e($stats['currency']) ?>)</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
        </div>
    </div>

    <div class="stat-card" style="border-top: 3px solid #6366f1;">
        <div>
            <div class="stat-label">Eligible Students</div>
            <div class="stat-value"><?= number_format($stats['eligible_students']) ?></div>
            <div class="stat-trend positive">
                <span><?= number_format($stats['billed_students']) ?> billed for current term</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
    </div>

    <div class="stat-card" style="border-top: 3px solid #059669;">
        <div>
            <div class="stat-label">Total Invoiced</div>
            <div class="stat-value"><?= money($stats['total_invoiced']) ?></div>
            <div class="stat-trend">
                <span><?= number_format($stats['total_invoices']) ?> invoices generated</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
        </div>
    </div>

    <div class="stat-card" style="border-top: 3px solid #10b981;">
        <div>
            <div class="stat-label">Verified Collections</div>
            <div class="stat-value" style="color: #059669;"><?= money($stats['total_collected']) ?></div>
            <div class="stat-trend positive">
                <span><?= number_format($stats['collection_rate'], 1) ?>% platform collection rate</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
    </div>
</div>

<!-- Secondary Balances & Distributions Row -->
<div class="stats-grid" style="margin-top: 1rem;">
    <div class="stat-card">
        <div>
            <div class="stat-label">Outstanding Parent Balances</div>
            <div class="stat-value" style="color: #dc2626;"><?= money($stats['total_outstanding']) ?></div>
            <div class="stat-trend negative"><span>Unsettled charges</span></div>
        </div>
        <div class="stat-icon-wrapper icon-amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Platform Revenue (Retained)</div>
            <div class="stat-value" style="color: #2563eb;"><?= money($stats['platform_revenue']) ?></div>
            <div class="stat-trend positive"><span>Accrued platform share</span></div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">School Incentives Accrued</div>
            <div class="stat-value" style="color: #059669;"><?= money($stats['school_incentives']) ?></div>
            <div class="stat-trend"><span>Earned by partner schools</span></div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14M21 7v14M6 21V7l6-4 6 4v14"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Sales Commissions Accrued</div>
            <div class="stat-value" style="color: #7c3aed;"><?= money($stats['sales_commissions']) ?></div>
            <div class="stat-trend"><span>Attributed sales partners</span></div>
        </div>
        <div class="stat-icon-wrapper icon-purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
    </div>
</div>

<!-- Detailed Reconciliation Micro-Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-top: 1rem;">
    <div class="stat-card" style="padding: 1rem;">
        <div class="stat-label" style="font-size: 0.75rem;">Payment Gateway Fees</div>
        <div style="font-size: 1.25rem; font-weight: 700; color: #64748b;"><?= money($stats['gateway_fees']) ?></div>
        <div style="font-size: 0.7rem; color: #94a3b8;">Deducted by processor</div>
    </div>
    <div class="stat-card" style="padding: 1rem;">
        <div class="stat-label" style="font-size: 0.75rem;">Refunds & Reversals</div>
        <div style="font-size: 1.25rem; font-weight: 700; color: <?= $stats['refunds_and_reversals'] > 0 ? '#dc2626' : '#64748b' ?>;">
            <?= money($stats['refunds_and_reversals']) ?>
        </div>
        <div style="font-size: 0.7rem; color: #94a3b8;">Reconciled payment reversals</div>
    </div>
    <div class="stat-card" style="padding: 1rem;">
        <div class="stat-label" style="font-size: 0.75rem;">Net Distributable Revenue</div>
        <div style="font-size: 1.25rem; font-weight: 700; color: #0284c7;"><?= money($stats['net_distributable']) ?></div>
        <div style="font-size: 0.7rem; color: #94a3b8;">Net verified collections</div>
    </div>
    <div class="stat-card" style="padding: 1rem;">
        <div class="stat-label" style="font-size: 0.75rem;">Awaiting Payout Approval</div>
        <div style="font-size: 1.25rem; font-weight: 700; color: #d97706;"><?= money($stats['payouts_pending']) ?></div>
        <div style="font-size: 0.7rem; color: #94a3b8;">Pending bank disbursement</div>
    </div>
    <div class="stat-card" style="padding: 1rem;">
        <div class="stat-label" style="font-size: 0.75rem;">Completed School Payouts</div>
        <div style="font-size: 1.25rem; font-weight: 700; color: #059669;"><?= money($stats['payouts_completed']) ?></div>
        <div style="font-size: 0.7rem; color: #94a3b8;">Disbursed to school accounts</div>
    </div>
</div>

<!-- Term-by-Term Financial Trends (Real DB Records) -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Term-by-Term Financial Trends</h3>
            <p class="card-subtitle">Real database historical trends distinguishing invoiced charges, collections, platform revenue, and accrued incentives</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Academic Period</th>
                    <th>Students Billed</th>
                    <th>Invoices</th>
                    <th>Total Invoiced</th>
                    <th>Verified Collections</th>
                    <th>Collection Rate</th>
                    <th>Platform Retained</th>
                    <th>School Incentives</th>
                    <th>Sales Commissions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($termTrends)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; color: #64748b; padding: 2rem;">No term billing history recorded yet.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($termTrends as $tr): ?>
                    <?php 
                        $invTotal = (float)$tr['total_invoiced'];
                        $colTotal = (float)$tr['total_collected'];
                        $colRate = $invTotal > 0 ? round(($colTotal / $invTotal) * 100, 1) : 0.0;
                    ?>
                    <tr>
                        <td>
                            <strong><?= e($tr['term_name']) ?></strong>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($tr['session_name']) ?></div>
                        </td>
                        <td><span class="badge badge-info"><?= number_format((int)$tr['students_billed']) ?> Students</span></td>
                        <td><?= number_format((int)$tr['total_invoices']) ?></td>
                        <td style="font-weight: 600;"><?= money($invTotal) ?></td>
                        <td style="font-weight: 700; color: #059669;"><?= money($colTotal) ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="flex: 1; min-width: 60px; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                    <div style="width: <?= min(100, $colRate) ?>%; height: 100%; background: #10b981;"></div>
                                </div>
                                <span style="font-size: 0.75rem; font-weight: 600;"><?= $colRate ?>%</span>
                            </div>
                        </td>
                        <td style="color: #2563eb; font-weight: 600;"><?= money((float)$tr['platform_revenue']) ?></td>
                        <td style="color: #059669; font-weight: 600;"><?= money((float)$tr['school_incentives']) ?></td>
                        <td style="color: #7c3aed; font-weight: 600;"><?= money((float)$tr['sales_commissions']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- School-by-School Collection Performance -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">School-by-School Collection Performance</h3>
            <p class="card-subtitle">Active student enrollment, billing progress, verified collections, and agreement status per participating tenant</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>School Institution</th>
                    <th>Eligible Cohort</th>
                    <th>Invoices Billed</th>
                    <th>Total Invoiced</th>
                    <th>Verified Collections</th>
                    <th>Outstanding Due</th>
                    <th>Incentives Accrued</th>
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
                    <td style="color: #059669; font-weight: 600;"><?= money((float)($sch['incentives_accrued'] ?? 0)) ?></td>
                    <td>
                        <?php if ($sch['agreement_status'] === 'accepted'): ?>
                            <span class="badge badge-success">Accepted & Active</span>
                        <?php elseif ($sch['agreement_status'] === 'proposed'): ?>
                            <span class="badge badge-warning">Proposed</span>
                        <?php elseif ($sch['agreement_status'] === 'amendment_requested'): ?>
                            <span class="badge badge-info">Clarification Requested</span>
                        <?php elseif ($sch['agreement_status'] === 'rejected'): ?>
                            <span class="badge badge-danger">Rejected</span>
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
<div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
    <!-- Pricing Policies -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Portal Pricing Policies</h3>
                <p class="card-subtitle">Global defaults and approved school-specific rates with audit governance</p>
            </div>
            <button class="btn btn-outline btn-sm" onclick="openModal('createPricingModal')">+ Configure Policy</button>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Policy Code</th>
                        <th>Scope & Session</th>
                        <th>Fee / Student</th>
                        <th>Payer Model</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricingPolicies as $pol): ?>
                    <tr>
                        <td>
                            <strong><?= e($pol['policy_code']) ?></strong>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                Eff: <?= e($pol['effective_date']) ?> <?= !empty($pol['expiry_date']) ? 'to ' . e($pol['expiry_date']) : '' ?>
                            </div>
                        </td>
                        <td>
                            <div><?= e($pol['school_name'] ?? 'Global Default (All Schools)') ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                <?= e($pol['session_name'] ?? 'All Sessions') ?> &bull; <?= e($pol['term_name'] ?? 'All Terms') ?>
                            </div>
                        </td>
                        <td style="font-weight: 700; color: #1e3a8a;">
                            <?= money((float)$pol['fee_per_student']) ?>
                            <?php if (!empty($pol['tax_treatment']) && $pol['tax_treatment'] === 'tax_exclusive'): ?>
                                <span style="font-size: 0.7rem; color: #d97706;">(+<?= $pol['tax_rate'] ?>% Tax)</span>
                            <?php endif; ?>
                        </td>
                        <td><?= ucfirst(str_replace('_', ' ', e($pol['fee_payer']))) ?></td>
                        <td>
                            <?php if ($pol['status'] === 'active'): ?>
                                <span class="badge badge-success">Active</span>
                            <?php elseif ($pol['status'] === 'approved'): ?>
                                <span class="badge badge-info">Approved</span>
                            <?php elseif ($pol['status'] === 'pending_approval'): ?>
                                <span class="badge badge-warning">Pending Approval</span>
                            <?php elseif ($pol['status'] === 'draft'): ?>
                                <span class="badge badge-secondary">Draft</span>
                            <?php else: ?>
                                <span class="badge badge-danger"><?= ucfirst(e($pol['status'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($pol['status'] === 'pending_approval'): ?>
                                <form method="POST" action="<?= url('superadmin/portal-billing/pricing/' . (int)$pol['id'] . '/status') ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn btn-outline btn-sm" style="color: #059669; border-color: #a7f3d0;">Approve & Activate</button>
                                </form>
                            <?php elseif ($pol['status'] === 'active' && !empty($pol['school_id'])): ?>
                                <form method="POST" action="<?= url('superadmin/portal-billing/pricing/' . (int)$pol['id'] . '/status') ?>" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="archived">
                                    <button type="submit" class="btn btn-outline btn-sm" style="color: #64748b;">Archive</button>
                                </form>
                            <?php else: ?>
                                <span style="font-size: 0.75rem; color: #64748b;">Standard</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Revenue Sharing Rules -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h3 class="card-title">Revenue-Sharing Model</h3>
                <p class="card-subtitle">Approved platform, school & sales commission formula</p>
            </div>
            <button class="btn btn-outline btn-sm" onclick="openModal('createRevenueModal')">+ Configure Revenue Policy</button>
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
                        <td>
                            <strong><?= e($rp['policy_code']) ?></strong>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($rp['school_name'] ?? 'Global Policy') ?></div>
                        </td>
                        <td><span class="badge badge-info"><?= str_replace('_', ' ', e($rp['allocation_basis'])) ?></span></td>
                        <td style="color: #2563eb; font-weight: 600;"><?= $rp['platform_percentage'] ?>%</td>
                        <td style="color: #059669; font-weight: 600;"><?= $rp['school_incentive_percentage'] ?>%</td>
                        <td>
                            <div><?= $rp['sales_rep_initial_rate'] ?>% <span style="font-size: 0.7rem; color: #64748b;">(Initial)</span></div>
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
            <p class="card-subtitle">Central disbursement queue for approved school incentives</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Request Code</th>
                    <th>School Institution</th>
                    <th>Amount Requested</th>
                    <th>Bank Settlement Details</th>
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

<!-- Recent Financial Allocations Ledger (Reconciled Penny Integrity) -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Real-Time Revenue Allocation Ledger</h3>
            <p class="card-subtitle">Traceable double-entry allocation of every verified student portal payment</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Payment Reference</th>
                    <th>School Tenant</th>
                    <th>Gross Paid</th>
                    <th>Gateway Fee</th>
                    <th>Distributable</th>
                    <th>Platform Share</th>
                    <th>School Incentive</th>
                    <th>Sales Rep Commission</th>
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
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($al['rep_name'] ?? 'Direct Platform') ?> (<?= e($al['commission_tier']) ?>)</div>
                        </td>
                        <td><span class="badge badge-success"><?= ucfirst(e($al['status'])) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Gateway Settlement Reconciliations & Discrepancy Analysis -->
<div class="card" style="margin-top: 1.5rem; border-top: 3px solid #0891b2;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h3 class="card-title">Gateway Settlement Reconciliation Ledger</h3>
            <p class="card-subtitle">Reconciles third-party gateway payouts (Paystack) against internal verified payment records and tracks fee discrepancies</p>
        </div>
        <button class="btn btn-primary btn-sm" onclick="openModal('reconcileModal')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
            <span>Run Settlement Reconciliation</span>
        </button>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Batch ID</th>
                    <th>Settlement Date</th>
                    <th>Transactions</th>
                    <th>Gateway Gross</th>
                    <th>Gateway Fee</th>
                    <th>Net Settled</th>
                    <th>Internal Matched</th>
                    <th>Discrepancy</th>
                    <th>Status</th>
                    <th>Reconciled By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($settlementReconciliations)): ?>
                    <tr>
                        <td colspan="11" style="text-align: center; color: #64748b; padding: 2rem;">No gateway settlement reconciliation records found. Click above to reconcile current collections.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($settlementReconciliations as $sr): ?>
                    <tr>
                        <td><code><?= e($sr['settlement_batch_id']) ?></code></td>
                        <td><?= date('M j, Y', strtotime($sr['settlement_date'])) ?></td>
                        <td><span class="badge badge-info"><?= (int)$sr['total_transactions'] ?> txns</span></td>
                        <td><?= money((float)$sr['gross_amount']) ?></td>
                        <td style="color: #64748b;"><?= money((float)$sr['gateway_fee']) ?></td>
                        <td style="font-weight: 700; color: #0f172a;"><?= money((float)$sr['net_settled_amount']) ?></td>
                        <td style="color: #059669; font-weight: 600;"><?= money((float)$sr['internal_matched_amount']) ?></td>
                        <td style="font-weight: 700; color: <?= (float)$sr['discrepancy_amount'] > 0.05 ? '#dc2626' : '#059669' ?>;">
                            <?= money((float)$sr['discrepancy_amount']) ?>
                        </td>
                        <td>
                            <?php if ($sr['reconciliation_status'] === 'matched'): ?>
                                <span class="badge badge-success">Fully Reconciled</span>
                            <?php elseif ($sr['reconciliation_status'] === 'manual_resolved'): ?>
                                <span class="badge badge-info">Manual Resolved</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Discrepancy Found</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 0.8rem; color: #475569;"><?= e($sr['reconciled_by_name'] ?? 'System Automation') ?></td>
                        <td>
                            <?php if ($sr['reconciliation_status'] === 'discrepancy_found'): ?>
                                <button type="button" class="btn btn-outline btn-sm" style="font-size: 0.75rem; color: #0284c7;" onclick="openResolveDiscrepancy(<?= (int)$sr['id'] ?>, '<?= e($sr['settlement_batch_id']) ?>', '<?= money((float)$sr['discrepancy_amount']) ?>')">
                                    Resolve
                                </button>
                            <?php else: ?>
                                <span style="color: #059669; font-size: 0.8rem;">✓ Clean</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Authorized Refunds and Reversals Workflow -->
<div class="card" style="margin-top: 1.5rem; border-top: 3px solid #e11d48;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h3 class="card-title">Authorized Refunds & Symmetrical Ledger Reversals</h3>
            <p class="card-subtitle">Review parent or school refund requests, execute gateway refunds, and symmetrically adjust financial ledgers</p>
        </div>
        <span class="badge badge-danger"><?= count(array_filter($refundRequests, fn($r) => $r['status'] === 'requested')) ?> Pending Review</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Refund Ref</th>
                    <th>School Institution</th>
                    <th>Invoice No</th>
                    <th>Payment Ref</th>
                    <th>Refund Amount</th>
                    <th>Justification</th>
                    <th>Requested By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($refundRequests)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: #64748b; padding: 2rem;">No refund requests recorded.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($refundRequests as $rf): ?>
                    <tr>
                        <td><code><?= e($rf['refund_reference']) ?></code></td>
                        <td><strong><?= e($rf['school_name']) ?></strong></td>
                        <td><?= e($rf['invoice_number']) ?></td>
                        <td><?= e($rf['payment_reference']) ?></td>
                        <td style="font-weight: 700; color: #dc2626;"><?= money((float)$rf['amount']) ?></td>
                        <td style="max-width: 200px; font-size: 0.8rem; color: #334155;"><?= e($rf['reason']) ?></td>
                        <td style="font-size: 0.8rem;"><?= e($rf['requested_by_name']) ?></td>
                        <td>
                            <?php if ($rf['status'] === 'processed'): ?>
                                <span class="badge badge-success">Processed & Reversed</span>
                            <?php elseif ($rf['status'] === 'approved'): ?>
                                <span class="badge badge-info">Approved</span>
                            <?php elseif ($rf['status'] === 'rejected'): ?>
                                <span class="badge badge-danger">Rejected</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending Review</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($rf['status'] === 'requested'): ?>
                                <div style="display: flex; gap: 0.35rem;">
                                    <button type="button" class="btn btn-primary btn-sm" style="font-size: 0.75rem;" onclick="openApproveRefund(<?= (int)$rf['id'] ?>, '<?= e($rf['refund_reference']) ?>', '<?= money((float)$rf['amount']) ?>')">
                                        Approve
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" style="font-size: 0.75rem; color: #dc2626; border-color: #fca5a5;" onclick="openRejectRefund(<?= (int)$rf['id'] ?>, '<?= e($rf['refund_reference']) ?>')">
                                        Reject
                                    </button>
                                </div>
                            <?php else: ?>
                                <span style="font-size: 0.8rem; color: #64748b;">Closed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Raw Payment Gateway Webhook Events Log -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h3 class="card-title">Payment Gateway Webhook & Server Event Log</h3>
            <p class="card-subtitle">Zero-trust cryptographic audit log of raw incoming webhook payloads, signature checks, and idempotency status</p>
        </div>
        <span class="badge badge-info"><?= count($gatewayEvents) ?> Recent Events</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Gateway</th>
                    <th>Event Type</th>
                    <th>Transaction Reference</th>
                    <th>Origin IP</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($gatewayEvents)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #64748b; padding: 2rem;">No webhook events logged yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($gatewayEvents as $ge): ?>
                    <tr>
                        <td style="font-size: 0.8rem; color: #64748b;"><?= date('M j, Y H:i:s', strtotime($ge['created_at'])) ?></td>
                        <td><span class="badge badge-outline" style="text-transform: uppercase;"><?= e($ge['gateway']) ?></span></td>
                        <td><code><?= e($ge['event_type']) ?></code></td>
                        <td><strong><?= e($ge['reference']) ?></strong></td>
                        <td style="font-size: 0.8rem; color: #475569;"><?= e($ge['ip_address'] ?? 'Webhook Server') ?></td>
                        <td>
                            <?php if ($ge['status'] === 'processed'): ?>
                                <span class="badge badge-success">Processed & Allocated</span>
                            <?php elseif ($ge['status'] === 'received'): ?>
                                <span class="badge badge-warning">Received</span>
                            <?php elseif ($ge['status'] === 'ignored'): ?>
                                <span class="badge badge-secondary">Ignored</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Failed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal 1: Comprehensive Portal Pricing Form -->
<div class="modal" id="createPricingModal">
    <div class="modal-dialog" style="max-width: 680px;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title">Configure Portal Pricing Policy</h3>
                    <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Authorizes term portal fees, school overrides, and scholarship/tax policies</p>
                </div>
                <button class="modal-close" onclick="closeModal('createPricingModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('superadmin/portal-billing/pricing') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Policy Identifier Code *</label>
                            <input type="text" name="policy_code" class="form-control" placeholder="e.g. TERM-FEE-2026-DEFAULT" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Currency *</label>
                            <select name="currency" class="form-control">
                                <option value="NGN" selected>NGN (Nigerian Naira - ₦)</option>
                                <option value="USD">USD (US Dollar - $)</option>
                                <option value="GBP">GBP (British Pound - £)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Fee per Student per Term *</label>
                            <input type="number" step="50" name="fee_per_student" class="form-control" value="3000" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Commercial Payer Model *</label>
                            <select name="fee_payer" class="form-control">
                                <option value="parent">Billed to Parents / Guardians</option>
                                <option value="school_sponsored">School-Sponsored Fee (Zero Parent Charge)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Target School Scope</label>
                        <select name="school_id" class="form-control">
                            <option value="">Global Default Policy (All Participating Schools)</option>
                            <?php foreach ($schools as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?> (School-Specific Negotiated Rate)</option>
                            <?php endforeach; ?>
                        </select>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Select a specific school to configure customized or discounted institutional arrangements.</div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Effective Academic Session</label>
                            <select name="session_id" class="form-control">
                                <option value="">All / Current Session</option>
                                <?php foreach ($sessions as $ses): ?>
                                <option value="<?= (int)$ses['id'] ?>"><?= e($ses['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Effective Academic Term</label>
                            <select name="term_id" class="form-control">
                                <option value="">All Terms in Session</option>
                                <?php foreach ($terms as $t): ?>
                                <option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Eligible School Types</label>
                            <select name="eligible_school_types" class="form-control">
                                <option value="all">All School Levels</option>
                                <option value="nursery_primary">Nursery & Primary Only</option>
                                <option value="secondary">Secondary Only (JSS/SSS)</option>
                                <option value="comprehensive">Comprehensive K-12</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Eligible Student Categories</label>
                            <select name="eligible_student_categories" class="form-control">
                                <option value="all">All Active Enrolled Students</option>
                                <option value="day_only">Day Students Only</option>
                                <option value="boarder_only">Boarding Pupils Only</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Minimum Fee Floor (₦)</label>
                            <input type="number" step="50" name="min_fee" class="form-control" placeholder="Optional minimum threshold">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Maximum Fee Cap (₦)</label>
                            <input type="number" step="50" name="max_fee" class="form-control" placeholder="Optional maximum cap">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Tax Calculation Model</label>
                            <select name="tax_treatment" class="form-control">
                                <option value="tax_inclusive">Tax Inclusive (Included in Portal Fee)</option>
                                <option value="tax_exclusive">Tax Exclusive (Calculated Separately)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Applicable Tax Rate (%)</label>
                            <input type="number" step="0.1" name="tax_rate" class="form-control" value="0.00" placeholder="e.g. 7.5 for VAT">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Effective Date *</label>
                            <input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Discount & Scholarship Rules</label>
                            <input type="text" name="discount_policy" class="form-control" placeholder="e.g. Sibling discount 25%, Staff ward 50%">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Approved Exemptions & Waivers</label>
                            <input type="text" name="exemptions_policy" class="form-control" placeholder="e.g. Indigent scholarship waiver">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Initial Policy Status *</label>
                        <select name="status" class="form-control">
                            <option value="active" selected>Active (Immediately Effective for Term Agreements)</option>
                            <option value="approved">Approved (Awaiting Launch Date)</option>
                            <option value="pending_approval">Pending Approval</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes, Justification & Audit Trail</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Governing board approval notes or special contract terms."></textarea>
                    </div>

                    <!-- Explicit Authorization Notice -->
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 0.75rem 1rem; margin-top: 1rem;">
                        <label style="display: flex; gap: 0.5rem; align-items: flex-start; cursor: pointer; margin: 0; font-size: 0.85rem; color: #1e3a8a;">
                            <input type="checkbox" name="confirm_authorization" value="1" required style="margin-top: 0.2rem;">
                            <span>
                                <strong>Explicit Confirmation & Authorization Required:</strong> I authorize the publication of this portal fee pricing policy. I acknowledge that changing default pricing does not alter historical invoices or previously agreed term charges.
                            </span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createPricingModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm & Publish Pricing Policy</button>
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
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">NIP session reference or wire confirmation code.</div>
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

<!-- Modal 3: Configure Revenue Sharing Policy -->
<div class="modal" id="createRevenueModal">
    <div class="modal-dialog" style="max-width: 650px;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Configure Revenue Sharing Policy</h3>
                <button class="modal-close" onclick="closeModal('createRevenueModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('superadmin/portal-billing/revenue-policy') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Policy Code *</label>
                            <input type="text" name="policy_code" class="form-control" placeholder="e.g. REV-GLOBAL-2026 or REV-APEX-CUSTOM" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Scope / School Arrangement *</label>
                            <select name="school_id" class="form-control">
                                <option value="">Global Default Policy (All Schools)</option>
                                <?php foreach ($schools as $sch): ?>
                                    <option value="<?= (int)$sch['id'] ?>">School-Specific: <?= e($sch['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Distribution Basis *</label>
                            <select name="allocation_basis" class="form-control">
                                <option value="net_after_gateway" selected>Net After Gateway Fees (Recommended)</option>
                                <option value="gross">Gross Verified Payment</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Allocation Method *</label>
                            <select name="allocation_method" class="form-control">
                                <option value="percentage" selected>Percentage Split</option>
                                <option value="fixed_per_student">Fixed Amount per Student</option>
                                <option value="tiered">Tiered by Collection Volume</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Platform Share (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="platform_percentage" class="form-control" value="45.00" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">School Incentive (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="school_incentive_percentage" class="form-control" value="40.00" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Sales Rep Initial Rate (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="sales_rep_initial_rate" class="form-control" value="15.00" required>
                            <small style="color: #64748b;">Terms 1–3 acquisition commission.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sales Rep Recurring Rate (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="sales_rep_recurring_rate" class="form-control" value="7.50" required>
                            <small style="color: #64748b;">Terms 4+ renewal commission.</small>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Gateway Fee Bearer</label>
                            <select name="gateway_fee_bearer" class="form-control">
                                <option value="platform" selected>Platform (Absorbed by Platform Share)</option>
                                <option value="school">School (Deducted from School Share)</option>
                                <option value="split">Split Proportionately</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Min Collection Threshold (NGN)</label>
                            <input type="number" step="1000" min="0" name="min_collection_threshold" class="form-control" value="0.00">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes, Justification & Contract Version</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Approved revenue-sharing contract notes or special negotiated terms."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createRevenueModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Revenue Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 4: Run Gateway Settlement Reconciliation -->
<div class="modal" id="reconcileModal">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title">Run Gateway Settlement Reconciliation</h3>
                    <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Reconciles gateway settlement reports against verified internal payments</p>
                </div>
                <button class="modal-close" onclick="closeModal('reconcileModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('superadmin/portal-billing/reconcile') ?>">
                <?= csrf_field() ?>
                <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Payment Gateway *</label>
                        <select name="gateway" class="form-control">
                            <option value="paystack" selected>Paystack Settlement Ledger</option>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d', strtotime('-30 days')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date *</label>
                            <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; font-size: 0.8rem; color: #475569;">
                        The reconciliation engine matches transaction references, compares gateway processing fees against internal deductions, and flags any un-reconciled discrepancies.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('reconcileModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Execute Reconciliation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 5: Resolve Settlement Discrepancy -->
<div class="modal" id="resolveDiscrepancyModal">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Resolve Settlement Discrepancy</h3>
                <button class="modal-close" onclick="closeModal('resolveDiscrepancyModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('superadmin/portal-billing/reconcile/resolve') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="reconciliation_id" id="resolveReconId">
                <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                    <p style="font-size: 0.875rem; color: #334155; margin: 0;">
                        Resolving discrepancy for batch <code id="resolveBatchDisplay"></code> (<strong id="resolveDiscrepancyDisplay" style="color: #dc2626;"></strong>).
                    </p>
                    <div class="form-group">
                        <label class="form-label">Resolution Audit Notes & Justification *</label>
                        <textarea name="notes" class="form-control" rows="3" required placeholder="State bank settlement timing, FX adjustment, or gateway fee reconciliation details."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('resolveDiscrepancyModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Manual Resolution</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 6: Approve Portal Refund -->
<div class="modal" id="approveRefundModal">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Approve & Execute Portal Refund</h3>
                <button class="modal-close" onclick="closeModal('approveRefundModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('superadmin/portal-billing/refund/approve') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="refund_id" id="approveRefundId">
                <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="alert alert-warning" style="font-size: 0.85rem; line-height: 1.4;">
                        <strong>Symmetrical Accounting Reversal:</strong> Approving refund <strong id="approveRefundRefDisplay"></strong> (<strong id="approveRefundAmountDisplay"></strong>) will trigger gateway payout reversal, debit the school incentive ledger, cancel sales commission, and restore pupil invoice balance due.
                    </div>
                    <div class="form-group">
                        <label class="form-label">Approval Notes / Authorization Code</label>
                        <input type="text" name="notes" class="form-control" value="Approved by authorized platform compliance officer">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('approveRefundModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Execute Refund & Reversal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 7: Reject Portal Refund -->
<div class="modal" id="rejectRefundModal">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Reject Portal Refund Request</h3>
                <button class="modal-close" onclick="closeModal('rejectRefundModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('superadmin/portal-billing/refund/reject') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="refund_id" id="rejectRefundId">
                <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                    <p style="font-size: 0.875rem; color: #334155; margin: 0;">
                        Rejecting refund request <strong id="rejectRefundRefDisplay"></strong>.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Rejection Reason *</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why the refund request was denied (e.g. services rendered, invalid proof)."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('rejectRefundModal')">Cancel</button>
                    <button type="submit" class="btn btn-outline" style="color: #dc2626; border-color: #fca5a5;">Reject Request</button>
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

function openResolveDiscrepancy(id, batchId, discrepancyStr) {
    document.getElementById('resolveReconId').value = id;
    document.getElementById('resolveBatchDisplay').textContent = batchId;
    document.getElementById('resolveDiscrepancyDisplay').textContent = discrepancyStr;
    openModal('resolveDiscrepancyModal');
}

function openApproveRefund(id, ref, amountStr) {
    document.getElementById('approveRefundId').value = id;
    document.getElementById('approveRefundRefDisplay').textContent = ref;
    document.getElementById('approveRefundAmountDisplay').textContent = amountStr;
    openModal('approveRefundModal');
}

function openRejectRefund(id, ref) {
    document.getElementById('rejectRefundId').value = id;
    document.getElementById('rejectRefundRefDisplay').textContent = ref;
    openModal('rejectRefundModal');
}
</script>
