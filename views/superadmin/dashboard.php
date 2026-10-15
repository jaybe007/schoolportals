<div class="page-header">
    <div>
        <h1 class="page-title">Platform Super Administrator Console</h1>
        <p class="page-subtitle">Central telemetry, school tenant management, SaaS metrics, and security governance</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <button class="btn btn-primary" onclick="openModal('onboardSchoolModal')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            <span>Onboard New School</span>
        </button>
    </div>
</div>

<!-- New Commercial Model Banner -->
<div style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: #ffffff; border-radius: 10px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);">
    <div>
        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #93c5fd; font-weight: 700;">Default Commercial Model</div>
        <h3 style="margin: 0.25rem 0 0.25rem 0; font-size: 1.25rem; font-weight: 800;">Per-Student Term Portal Fee & Revenue-Sharing Engine</h3>
        <p style="margin: 0; font-size: 0.875rem; color: #bfdbfe; max-width: 650px;">
            Replace fixed subscriptions with automated per-student term billing (₦3,000/student/term), 40% school incentives, 15% sales commissions, and verified platform collections.
        </p>
    </div>
    <a href="<?= url('superadmin/portal-billing') ?>" class="btn btn-primary" style="background: #ffffff; color: #1e3a8a; font-weight: 700; border: none; white-space: nowrap; padding: 0.6rem 1.25rem;">
        <span>Portal Billing & Revenue &rarr;</span>
    </a>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total School Tenants</div>
            <div class="stat-value"><?= number_format($totalSchools) ?></div>
            <div class="stat-trend positive">
                <span><?= $activeSchools ?> Active &bull; <?= $trialSchools ?> Trial</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14M21 7v14M6 21V7l6-4 6 4v14"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Platform Enrolled Students</div>
            <div class="stat-value"><?= number_format($totalStudents) ?></div>
            <div class="stat-trend positive">
                <span>Across all 100+ schools</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Subscription MRR</div>
            <div class="stat-value"><?= money($mrr) ?></div>
            <div class="stat-trend positive">
                <span>Total Collected: <?= money($totalRevenue) ?></span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Faculty & Staff Members</div>
            <div class="stat-value"><?= number_format($totalStaff) ?></div>
            <div class="stat-trend">
                <span>Verified active personnel</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Recent Schools Panel -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">Registered School Tenants</div>
            <a href="<?= url('superadmin/schools') ?>" class="btn btn-secondary btn-sm">View All Tenants</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>School Name</th>
                        <th>Tenant Domain</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentSchools as $s): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #0f172a;"><?= e($s['name']) ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;"><?= e($s['code']) ?> &bull; <?= e($s['state']) ?></div>
                            </td>
                            <td>
                                <code style="font-size: 0.8125rem; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?= e($s['primary_domain'] ?? 'edusphere.example') ?></code>
                            </td>
                            <td><?= e($s['plan_name'] ?? 'Custom') ?></td>
                            <td>
                                <span class="badge badge-<?= $s['status'] === 'active' ? 'success' : 'danger' ?>">
                                    <?= e($s['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url('school?school=' . $s['slug']) ?>" class="btn btn-secondary btn-sm" target="_blank">
                                    <span>Access Portal</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Health & Platform Integrity -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">System Health & Compliance</div>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.875rem;">NDPA 2023 Compliance</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Children privacy & audit logs active</div>
                    </div>
                    <span class="badge badge-success">Enforced</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.875rem;">Tenant Isolation Guard</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Zero cross-tenant SQL leaks</div>
                    </div>
                    <span class="badge badge-success">Protected</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.875rem;">Paystack Gateway API</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Server-side webhooks & idempotency</div>
                    </div>
                    <span class="badge badge-success">Connected</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.875rem;">Server Engine Stack</div>
                        <div style="font-size: 0.75rem; color: #64748b;">PHP 8.2 &bull; MariaDB 10.4 &bull; Dompdf 3.1</div>
                    </div>
                    <span class="badge badge-info">Optimal</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Rep Performance & Audit Log Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Sales Rep Team Performance -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Commercial Sales Rep Performance</div>
            <a href="<?= url('sales') ?>" class="btn btn-secondary btn-sm">Open CRM</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Representative</th>
                        <th>Territory</th>
                        <th>Leads</th>
                        <th>Closed</th>
                        <th>Approved Comm.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salesStats as $rep): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?= e($rep['rep_name']) ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;"><?= e($rep['rep_code']) ?></div>
                            </td>
                            <td><?= e($rep['territory']) ?></td>
                            <td><strong><?= (int)$rep['total_leads'] ?></strong></td>
                            <td><span class="badge badge-success"><?= (int)$rep['won_leads'] ?> Won</span></td>
                            <td style="font-weight: 600; color: #059669;"><?= money($rep['earned_commission']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Audit Logs -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Immutable Security Audit Trail</div>
            <a href="<?= url('superadmin/audit-logs') ?>" class="btn btn-secondary btn-sm">Full Trail</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                        <tr>
                            <td style="font-size: 0.75rem; color: #64748b;"><?= format_date($log['created_at'], 'd M H:i') ?></td>
                            <td style="font-weight: 500;"><?= e($log['user_name'] ?? 'System') ?></td>
                            <td><code style="font-size: 0.75rem;"><?= e($log['action']) ?></code></td>
                            <td style="font-size: 0.75rem;"><?= e($log['ip_address']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Onboard School Modal -->
<div id="onboardSchoolModal" class="modal-overlay">
    <div class="modal-container">
        <form method="POST" action="<?= url('superadmin/schools/create') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="card-title">Onboard New School Tenant</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('onboardSchoolModal')">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">School Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. St. Gregory Premier College" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Institutional Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="admin@stgregory.edu.ng" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+2348030000000">
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">School Type</label>
                        <select name="school_type" class="form-select">
                            <option value="combined">Combined (Nursery, Primary & Secondary)</option>
                            <option value="secondary">Secondary Only (JSS & SSS)</option>
                            <option value="primary">Primary Only</option>
                            <option value="nursery_primary">Nursery & Primary</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Commercial Model</label>
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 0.65rem 0.85rem; font-size: 0.8125rem; color: #166534; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                            <span>Per-Student Term Fee</span>
                            <span style="font-size: 0.75rem; background: #dcfce7; padding: 2px 8px; border-radius: 4px; border: 1px solid #86efac; color: #15803d;">₦3,000 / student</span>
                        </div>
                        <input type="hidden" name="portal_fee" value="3000">
                    </div>
                </div>
                <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); margin-top: 0.5rem; border: 1px solid #e2e8f0;">
                    <div style="font-weight: 600; font-size: 0.8125rem; color: #1e3a8a; margin-bottom: 0.5rem;">Initial School Head Administrator</div>
                    <div class="form-grid">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Admin Full Name</label>
                            <input type="text" name="admin_name" class="form-control" placeholder="Dr. Administrator">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Admin Login Email *</label>
                            <input type="email" name="admin_email" class="form-control" placeholder="headadmin@stgregory.edu.ng" required>
                        </div>
                    </div>
                    <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">Default initial password will be set to <code>password123</code> with mandatory rotation on first login.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('onboardSchoolModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Activate School Tenant</button>
            </div>
        </form>
    </div>
</div>
