<div class="page-header">
    <div>
        <h1 class="page-title">Commercial Sales CRM & Partnerships Hub</h1>
        <p class="page-subtitle">Prospect acquisition, school demonstrations, pipeline velocity, and commission earnings</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= url('sales/pipeline') ?>" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg>
            <span>Open Kanban Pipeline</span>
        </a>
    </div>
</div>

<!-- Partner Referral & Settlement Account Banner -->
<?php if (!empty($currentRep)): ?>
<div class="card" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border: 1px solid #334155; margin-bottom: 1.5rem; padding: 1.25rem 1.5rem; border-radius: 0.75rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge" style="background: #10b981; color: #ffffff; font-weight: 700; font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 9999px;">
                    Active Partner &bull; <?= e($currentRep['rep_code']) ?>
                </span>
                <span style="color: #94a3b8; font-size: 0.8125rem;">Territory: <strong><?= e($currentRep['territory']) ?></strong></span>
            </div>
            <div style="font-size: 1.125rem; font-weight: 800; color: #f8fafc;">
                Welcome, <?= e(auth_user()['name']) ?>!
            </div>
            <div style="font-size: 0.8125rem; color: #cbd5e1; margin-top: 0.25rem;">
                Dual Royalty: <strong><?= $currentRep['commission_rate'] ?>% initial term</strong> &bull; <strong>7.5% recurring</strong> &bull; Settlement: <strong><?= e($currentRep['bank_name'] ?? 'Not set') ?> (<?= e($currentRep['account_number'] ?? 'N/A') ?> - <?= e($currentRep['account_name'] ?? '') ?>)</strong>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <a href="<?= url('sales/leads') ?>" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.12); color: #ffffff; border: 1px solid rgba(255,255,255,0.2);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                <span>+ Add School Prospect</span>
            </a>
            <button type="button" onclick="navigator.clipboard.writeText('<?= url('become-a-sales-rep') ?>?ref=<?= urlencode($currentRep['rep_code']) ?>'); alert('Referral link copied to clipboard!');" class="btn btn-sm" style="background: #10b981; color: #ffffff; font-weight: 700; border: none; padding: 0.45rem 0.85rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                <span>Copy Partner Link</span>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Prospects in CRM</div>
            <div class="stat-value"><?= number_format($totalLeads) ?></div>
            <div class="stat-trend positive">Active territory outreach</div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Won & Enrolled Schools</div>
            <div class="stat-value" style="color: #059669;"><?= number_format($wonLeads) ?></div>
            <div class="stat-trend positive">Closed subscriptions</div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Live Demos Scheduled</div>
            <div class="stat-value"><?= number_format($demosScheduled) ?></div>
            <div class="stat-trend">Academic committee presentations</div>
        </div>
        <div class="stat-icon-wrapper icon-amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Approved Commissions</div>
            <div class="stat-value" style="color: #1e3a8a;"><?= money($approvedCommissions) ?></div>
            <div class="stat-trend">Pending: <?= money($pendingCommissions) ?></div>
        </div>
        <div class="stat-icon-wrapper icon-purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Sales Team Leaderboard -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Commercial Team Territory Rankings</div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rep Name & Code</th>
                        <th>Territory</th>
                        <th>Target</th>
                        <th>Closed Deals</th>
                        <th>Paid Comm.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reps as $r): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;"><?= e($r['rep_name']) ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;"><?= e($r['rep_code']) ?></div>
                            </td>
                            <td><?= e($r['territory']) ?></td>
                            <td><?= money($r['monthly_target']) ?></td>
                            <td><span class="badge badge-success"><?= $r['won_count'] ?> Schools</span></td>
                            <td style="font-weight: 700; color: #059669;"><?= money($r['total_paid']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Deal Activities -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Recent Client Activities & Discovery Logs</div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>School</th>
                        <th>Type</th>
                        <th>Summary</th>
                        <th>Rep</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $act): ?>
                        <tr>
                            <td><strong><?= e($act['school_name']) ?></strong></td>
                            <td><span class="badge badge-info" style="text-transform: uppercase;"><?= e($act['activity_type']) ?></span></td>
                            <td style="font-size: 0.8125rem;"><?= e($act['summary']) ?></td>
                            <td style="font-size: 0.8125rem; color: #64748b;"><?= e($act['rep_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
