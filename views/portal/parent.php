<div class="page-header">
    <div>
        <h1 class="page-title">Parent & Family Guardian Portal</h1>
        <p class="page-subtitle">Academic progress, attendance telemetry, terminal report cards, and communication with school</p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <?php if ($selectedWard): ?>
            <a href="<?= url('portal/parent/wards/' . $selectedWard['id'] . '/report-card') ?>" class="btn btn-primary" target="_blank" title="Download & Print Official PDF Terminal Report Card">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                <span>Download & Print <?= e($selectedWard['first_name']) ?>'s Report Card (PDF)</span>
            </a>
        <?php endif; ?>
        <button type="button" class="btn btn-outline" onclick="openModal('parentDashboardComposeModal')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            <span>+ Compose Message to School</span>
        </button>
    </div>
</div>

<!-- Quick Communication & Direct Messages Hub -->
<div class="card" style="margin-bottom: 1.5rem; background: #ffffff; border-left: 4px solid #2563eb; box-shadow: var(--shadow-sm);">
    <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: #2563eb;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0;">School-Parent Messages Desk</h3>
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.15rem;">
                    Private communication with class teachers, principal, and school management
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <?php if (($unreadMessagesCount ?? 0) > 0): ?>
                <span class="badge badge-warning" style="font-size: 0.8rem; padding: 0.4rem 0.75rem; font-weight: 700;">
                    <?= (int)$unreadMessagesCount ?> Unread Message<?= (int)$unreadMessagesCount > 1 ? 's' : '' ?>
                </span>
            <?php endif; ?>
            <button type="button" class="btn btn-primary btn-sm" onclick="openModal('parentDashboardComposeModal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Compose Message</span>
            </button>
            <a href="<?= url('portal/parent/messages') ?>" class="btn btn-outline btn-sm">
                <span>View Full Inbox</span>
            </a>
        </div>
    </div>
    <div class="card-body" style="padding: 1rem 1.25rem;">
        <?php if (empty($threads)): ?>
            <div style="text-align: center; padding: 1.5rem; color: #64748b;">
                <p style="margin: 0; font-size: 0.9rem;">No messages yet. Click <strong>Compose Message</strong> above to contact your child's teacher or school administration directly.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <?php foreach (array_slice($threads, 0, 3) as $th): ?>
                    <a href="<?= url('portal/parent/messages?thread_id=' . (int)$th['id']) ?>" 
                       style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; border-radius: 6px; background: <?= !empty($th['is_unread']) ? '#fffbeb' : '#f8fafc' ?>; border: 1px solid <?= !empty($th['is_unread']) ? '#fcd34d' : '#e2e8f0' ?>; text-decoration: none; color: inherit; transition: all 0.15s ease;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <?php if (!empty($th['is_unread'])): ?>
                                <span class="badge badge-warning" style="font-size: 0.65rem;">NEW</span>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 0.65rem;"><?= $th['sender_type'] === 'school' ? 'From School' : 'My Inquiry' ?></span>
                            <?php endif; ?>
                            <div>
                                <strong style="color: #0f172a; font-size: 0.9rem;"><?= e($th['subject']) ?></strong>
                                <div style="font-size: 0.75rem; color: #64748b;">
                                    From: <?= $th['sender_type'] === 'school' ? '🏫 ' . e($th['sender_name']) : 'You' ?>
                                    <?php if (!empty($th['student_first'])): ?>
                                        &bull; Regarding: <?= e($th['student_first'] . ' ' . $th['student_last']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 0.75rem; color: #64748b;"><?= date('M j, g:i A', strtotime($th['updated_at'])) ?></span>
                            <span style="color: #2563eb; font-size: 0.85rem; font-weight: 600;">Open &rarr;</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- General School Notice Board for Parents -->
<?php if (!empty($generalNotices)): ?>
<div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid #2563eb;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <h3 class="card-title" style="margin: 0; font-size: 1.1rem;">Institutional School Notice Board</h3>
        </div>
    </div>
    <div class="card-body" style="padding: 1rem 1.25rem;">
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($generalNotices as $gn): ?>
                <div style="padding: 1rem 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; border-left: 3px solid <?= $gn['priority'] === 'urgent' ? '#dc2626' : ($gn['priority'] === 'important' ? '#f59e0b' : '#3b82f6') ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.35rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <?php if ($gn['is_pinned']): ?><span class="badge badge-primary" style="font-size: 0.7rem;">📌 Pinned</span><?php endif; ?>
                            <span class="badge badge-info" style="font-size: 0.7rem; text-transform: uppercase;"><?= e($gn['category']) ?></span>
                            <strong style="color: #0f172a; font-size: 0.95rem;"><?= e($gn['title']) ?></strong>
                        </div>
                        <span style="font-size: 0.75rem; color: #64748b;"><?= date('M j, Y', strtotime($gn['published_at'])) ?></span>
                    </div>
                    <div style="font-size: 0.875rem; color: #334155; line-height: 1.5; white-space: pre-wrap;">
                        <?= e($gn['content']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Ward Switcher Tabs (If Family has multiple children) -->
<?php if (count($wards) > 1): ?>
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <?php foreach ($wards as $w): ?>
            <a href="<?= url('portal/parent?ward_id=' . $w['id']) ?>" class="btn btn-<?= $selectedWard && $selectedWard['id'] == $w['id'] ? 'primary' : 'secondary' ?>">
                Ward: <?= e($w['first_name'] . ' ' . $w['last_name']) ?> (<?= e($w['class_name'] ?? '') ?>)
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($selectedWard): ?>
    <!-- Ward details and Telemetry Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <div class="stat-label">Student Name & Class</div>
                <div class="stat-value" style="font-size: 1.25rem;">
                    <?= e($selectedWard['first_name'] . ' ' . $selectedWard['last_name']) ?>
                </div>
                <div class="stat-trend positive">
                    <?= e(($selectedWard['class_name'] ?? '') . ' ' . ($selectedWard['arm_name'] ?? '')) ?> &bull; <?= e($selectedWard['admission_number']) ?>
                </div>
            </div>
            <div class="stat-icon-wrapper icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <div class="stat-label">Term Performance</div>
                <div class="stat-value" style="color: #059669;">
                    <?= $summary ? number_format((float)$summary['average_score'], 1) . '%' : 'In Progress' ?>
                </div>
                <div class="stat-trend">Standing: <?= $summary ? '#' . $summary['position'] . ' of ' . $summary['total_students'] : 'Pending Final Review' ?></div>
            </div>
            <div class="stat-icon-wrapper icon-emerald">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <div class="stat-label">Attendance Record</div>
                <div class="stat-value" style="color: #7c3aed;">
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

        <div class="stat-card">
            <div>
                <div class="stat-label">Current Academic Term</div>
                <div class="stat-value" style="font-size: 1.15rem; color: #1e3a8a;">
                    <?= e($term['name'] ?? 'Current Term') ?>
                </div>
                <div class="stat-trend">Session: <?= e($session['name'] ?? '2025/2026') ?> &bull; Active</div>
            </div>
            <div class="stat-icon-wrapper icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
    </div>

    <!-- Ward Examination Scores -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <div class="card-title"><?= e($selectedWard['first_name']) ?>'s Published Examination Marks</div>
                <div class="card-subtitle">Official term performance &bull; Verified grade records</div>
            </div>
            <a href="<?= url('portal/parent/wards/' . $selectedWard['id'] . '/report-card') ?>" target="_blank" class="btn btn-primary btn-sm" title="Download & Print Official PDF Terminal Report Card">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                <span>Print / Download Report Card (PDF)</span>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>CA 1 (20)</th>
                        <th>CA 2 (20)</th>
                        <th>Exam (60)</th>
                        <th>Total (100)</th>
                        <th>Grade</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($scores)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 2rem;">
                                No published examination records available for this term.
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
    <!-- Official School Notice & Portal Fee Explanation -->
    <div class="card" style="margin-bottom: 1.5rem; background: #ffffff; border-left: 4px solid #3b82f6; box-shadow: var(--shadow-sm);">
        <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; padding: 0.85rem 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: #1e3a8a;">Official School Notice & Term Portal Fee Explanation</h3>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="openModal('portalFeePolicyModal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>Payment & Refund Policy</span>
            </button>
        </div>
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem; color: #334155; line-height: 1.5;">
                <strong>About the Term Portal Maintenance Fee:</strong> This term charge covers your child's 24/7 cloud marksheet generation, continuous assessment telemetry, online report card verification, and encrypted family messaging with teachers and administration.
            </p>
            <?php if (!empty($portalNotices)): ?>
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 0.75rem 1rem; margin-top: 0.75rem;">
                    <div style="font-weight: 700; color: #1e40af; font-size: 0.85rem; margin-bottom: 0.25rem;">
                        📢 <?= e($portalNotices[0]['title']) ?>
                    </div>
                    <div style="font-size: 0.825rem; color: #1e3a8a; line-height: 1.4;">
                        <?= nl2br(e($portalNotices[0]['notice_body'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section A: Term Portal Fee Invoices (Digital Marksheet & Cloud System Access) -->
    <div class="card" style="margin-bottom: 1.5rem; border-top: 3px solid #2563eb;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h3 class="card-title">Term Portal Fee Invoices</h3>
                <p class="card-subtitle">Continuous assessment access, digital marksheet generation, and portal maintenance fee</p>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                <a href="<?= url('portal/parent/statement/pdf') ?>" target="_blank" class="btn btn-outline btn-sm" title="Download Complete Multi-Ward Consolidated Family Statement">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Download Consolidated Statement (PDF)</span>
                </a>
                <?php if (!empty($familySummary) && (float)$familySummary['balance_due'] > 0): ?>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openConsolidatedPayModal(<?= (float)$familySummary['balance_due'] ?>, <?= (int)$familySummary['unsettled_count'] ?>)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <span>Consolidated Pay All (<?= money((float)$familySummary['balance_due']) ?>)</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($familySummary)): ?>
            <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1.25rem; display: flex; gap: 1.5rem; flex-wrap: wrap; font-size: 0.85rem;">
                <div>Total Wards: <strong><?= count($wards) ?></strong></div>
                <div>Total Invoiced: <strong style="color: #1e3a8a;"><?= money((float)$familySummary['total_invoiced']) ?></strong></div>
                <div>Total Verified Paid: <strong style="color: #059669;"><?= money((float)$familySummary['total_paid']) ?></strong></div>
                <div>Outstanding Balance: <strong style="color: <?= (float)$familySummary['balance_due'] > 0 ? '#dc2626' : '#059669' ?>;"><?= money((float)$familySummary['balance_due']) ?></strong></div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Pupil / Ward</th>
                        <th>Class & Arm</th>
                        <th>Gross Fee</th>
                        <th>Waiver / Scholarship</th>
                        <th>Amount Due</th>
                        <th>Paid</th>
                        <th>Balance Due</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($portalInvoices)): ?>
                        <tr>
                            <td colspan="11" style="text-align: center; color: #64748b; padding: 2rem;">
                                No portal fee invoices pending for your registered wards.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($portalInvoices as $pi): ?>
                            <tr>
                                <td>
                                    <strong><?= e($pi['invoice_number']) ?></strong>
                                    <?php if (!empty($pi['adjustment_reason'])): ?>
                                        <div style="font-size: 0.7rem; color: #0284c7;"><?= e($pi['adjustment_reason']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= e($pi['first_name'] . ' ' . $pi['last_name']) ?></strong>
                                    <div style="font-size: 0.75rem; color: #64748b;"><?= e($pi['admission_number']) ?></div>
                                </td>
                                <td><?= e($pi['class_name'] . ' ' . $pi['arm_name']) ?></td>
                                <td><?= money((float)$pi['gross_amount']) ?></td>
                                <td>
                                    <?php if ((float)$pi['discount_amount'] > 0): ?>
                                        <span style="color: #059669; font-weight: 600;">-<?= money((float)$pi['discount_amount']) ?></span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 700; color: #1e3a8a;"><?= money((float)$pi['amount_due']) ?></td>
                                <td style="color: #059669; font-weight: 600;"><?= money((float)$pi['paid_amount']) ?></td>
                                <td style="font-weight: 700; color: <?= (float)$pi['balance_due'] > 0 ? '#dc2626' : '#64748b' ?>;">
                                    <?= money((float)$pi['balance_due']) ?>
                                </td>
                                <td>
                                    <div style="font-size: 0.8rem; font-weight: 600; color: <?= strtotime($pi['due_date']) < time() && (float)$pi['balance_due'] > 0 ? '#dc2626' : '#475569' ?>;">
                                        <?= date('M j, Y', strtotime($pi['due_date'])) ?>
                                    </div>
                                    <?php if (strtotime($pi['due_date']) < time() && (float)$pi['balance_due'] > 0): ?>
                                        <span class="badge badge-danger" style="font-size: 0.65rem;">Overdue</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($pi['status'] === 'paid'): ?>
                                        <span class="badge badge-success">Paid</span>
                                    <?php elseif ($pi['status'] === 'partially_paid'): ?>
                                        <span class="badge badge-warning">Partial</span>
                                    <?php elseif ($pi['status'] === 'waived'): ?>
                                        <span class="badge badge-info">Waived</span>
                                    <?php elseif ($pi['status'] === 'disputed'): ?>
                                        <span class="badge badge-danger">Disputed</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem;">
                                        <?php if ((float)$pi['balance_due'] > 0 && $pi['status'] !== 'waived'): ?>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="openPayPortalModal(<?= (int)$pi['id'] ?>, '<?= e($pi['invoice_number']) ?>', '<?= e($pi['first_name'] . ' ' . $pi['last_name']) ?>', <?= (float)$pi['balance_due'] ?>)">
                                                Pay Online
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #059669; font-weight: 600; font-size: 0.8rem;">✓ Settled</span>
                                        <?php endif; ?>
                                        <?php if ($pi['status'] !== 'paid' && $pi['status'] !== 'disputed'): ?>
                                            <button type="button" class="btn btn-outline btn-sm" style="font-size: 0.75rem; color: #dc2626; border-color: #fca5a5;" onclick="openDisputePortalModal(<?= (int)$pi['id'] ?>, '<?= e($pi['invoice_number']) ?>')">
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

    <!-- Section A.1: Verified Portal Payment History & Official Electronic Receipts -->
    <div class="card" style="margin-bottom: 1.5rem; border-top: 3px solid #0891b2;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h3 class="card-title">Verified Payment History & Official Electronic Receipts</h3>
                <p class="card-subtitle">Authenticated transactions processed via Paystack gateway with tamper-proof receipt downloads</p>
            </div>
            <span class="badge badge-info"><?= count($portalPayments ?? []) ?> Payment Record(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Pupil / Ward</th>
                        <th>Invoice No</th>
                        <th>Payment Reference</th>
                        <th>Gateway & Channel</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($portalPayments)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 1.5rem;">
                                No verified payments recorded for your family account yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($portalPayments as $pp): ?>
                            <tr>
                                <td style="font-size: 0.8rem; color: #64748b; white-space: nowrap;">
                                    <?= date('M j, Y H:i', strtotime($pp['created_at'])) ?>
                                </td>
                                <td>
                                    <strong><?= e($pp['first_name'] . ' ' . $pp['last_name']) ?></strong>
                                    <div style="font-size: 0.75rem; color: #64748b;"><?= e($pp['admission_number']) ?></div>
                                </td>
                                <td><code><?= e($pp['invoice_number']) ?></code></td>
                                <td><strong><?= e($pp['payment_reference']) ?></strong></td>
                                <td>
                                    <span class="badge badge-outline" style="text-transform: uppercase; font-size: 0.7rem;">
                                        <?= e($pp['gateway']) ?> &bull; <?= e($pp['channel'] ?? 'card') ?>
                                    </span>
                                </td>
                                <td style="font-weight: 700; color: #059669;"><?= money((float)$pp['amount']) ?></td>
                                <td>
                                    <?php if ($pp['status'] === 'verified'): ?>
                                        <span class="badge badge-success">Verified Paid</span>
                                    <?php elseif ($pp['status'] === 'refunded'): ?>
                                        <span class="badge badge-danger">Refunded</span>
                                    <?php elseif ($pp['status'] === 'disputed'): ?>
                                        <span class="badge badge-warning">Disputed</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?= ucfirst(e($pp['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem; align-items: center;">
                                        <a href="<?= url('portal/portal-payments/' . $pp['id'] . '/pdf') ?>" target="_blank" class="btn btn-outline btn-sm" style="font-size: 0.75rem;" title="Download School-Branded Official PDF Receipt">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                                            <span>Receipt (PDF)</span>
                                        </a>
                                        <?php if ($pp['status'] === 'verified'): ?>
                                            <button type="button" class="btn btn-outline btn-sm" style="font-size: 0.75rem; color: #dc2626; border-color: #fca5a5;" onclick="openRequestRefund(<?= (int)$pp['id'] ?>, '<?= e($pp['payment_reference']) ?>', <?= (float)$pp['amount'] ?>)" title="Request Refund / Transaction Dispute">
                                                Refund
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

    <!-- Section B: School Fees & Tuition Invoices (Tuition, Development Levies, Books, Uniforms, Lab Fees) -->
    <div class="card" style="margin-bottom: 1.5rem; border-top: 3px solid #059669;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h3 class="card-title">School Tuition & Ancillary Invoices (<?= e($selectedWard['first_name']) ?>)</h3>
                <p class="card-subtitle">Official tuition, development levies, uniforms, and textbooks</p>
            </div>
            <span class="badge badge-success">School Bursary Account</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Fee Schedule / Description</th>
                        <th>Gross Amount</th>
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
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 2rem;">
                                No tuition fee invoices issued for this ward.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td><strong><?= e($inv['invoice_number']) ?></strong></td>
                                <td>Tuition, Laboratory, Books & Uniform Ancillaries</td>
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
                                    <div style="display: flex; gap: 0.35rem;">
                                        <?php if ((float)$inv['balance_due'] > 0): ?>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="openPaySchoolFeeModal(<?= (int)$inv['id'] ?>, '<?= e($inv['invoice_number']) ?>', <?= (float)$inv['balance_due'] ?>)">
                                                Pay Online
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?= url('invoices/' . (int)$inv['id'] . '/pdf') ?>" target="_blank" class="btn btn-outline btn-sm" title="Download Official A4 Bill PDF">
                                            PDF Bill
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section C: Verified Payment Receipts & Transaction History -->
    <?php if (!empty($portalPayments)): ?>
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Verified Payment Receipts & History</h3>
            <p class="card-subtitle">Official payment receipts and digital verification references</p>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction Ref</th>
                        <th>Pupil / Ward</th>
                        <th>Payment Purpose</th>
                        <th>Amount Paid</th>
                        <th>Method</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($portalPayments as $pp): ?>
                        <tr>
                            <td><strong><?= e($pp['payment_reference']) ?></strong></td>
                            <td><?= e($pp['first_name'] . ' ' . $pp['last_name']) ?></td>
                            <td>Portal Digital Fee (<?= e($pp['invoice_number']) ?>)</td>
                            <td style="font-weight: 700; color: #059669;"><?= money((float)$pp['amount']) ?></td>
                            <td><span class="badge badge-info"><?= strtoupper(e($pp['gateway'])) ?></span></td>
                            <td><?= date('M j, Y g:i A', strtotime($pp['created_at'])) ?></td>
                            <td><span class="badge badge-success">Verified</span></td>
                            <td>
                                <a href="<?= url('portal-payments/' . (int)$pp['id'] . '/pdf') ?>" target="_blank" class="btn btn-outline btn-sm">
                                    Print Receipt
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Modal: Pay Term Portal Fee -->
<div class="modal" id="payPortalModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Pay Term Portal Access Fee</h3>
                <button class="modal-close" onclick="closeModal('payPortalModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('portal/parent/pay-portal-fee') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="invoice_id" id="portalInvoiceId" value="">
                <div class="modal-body">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 1rem; margin-bottom: 1rem;">
                        <div>Invoice: <strong id="portalInvNum"></strong></div>
                        <div>Pupil: <strong id="portalStuName"></strong></div>
                        <div style="margin-top: 0.25rem;">Outstanding Balance: <strong id="portalBalanceDisplay" style="color: #dc2626; font-size: 1.1rem;"></strong></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount to Pay (NGN) *</label>
                        <input type="number" step="50" name="amount" id="portalPayAmount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Channel</label>
                        <select name="gateway" class="form-control">
                            <option value="paystack">Paystack Online Checkout (Debit Card / Bank Transfer / USSD)</option>
                            <option value="bank_transfer">Direct Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('payPortalModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Proceed to Secure Checkout</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Dispute Portal Fee -->
<div class="modal" id="disputePortalModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Submit Portal Fee Dispute / Inquiry</h3>
                <button class="modal-close" onclick="closeModal('disputePortalModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('portal/parent/dispute-portal-fee') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="invoice_id" id="disputeInvoiceId" value="">
                <div class="modal-body">
                    <p style="color: #475569; font-size: 0.9rem; margin-bottom: 1rem;">
                        Submit an official inquiry or correction request to the school administration regarding invoice <strong id="disputeInvNumDisplay"></strong>.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Reason for Dispute / Correction Request *</label>
                        <textarea name="dispute_reason" class="form-control" rows="3" placeholder="Explain the discrepancy (e.g. approved scholarship, fee already paid, student on bursary waiver)" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('disputePortalModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Dispute Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Pay School Fees / Tuition -->
<div class="modal" id="paySchoolFeeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Pay Tuition & Ancillary School Fees</h3>
                <button class="modal-close" onclick="closeModal('paySchoolFeeModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('portal/parent/pay-school-fee') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="invoice_id" id="schoolFeeInvoiceId" value="">
                <div class="modal-body">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 1rem; margin-bottom: 1rem;">
                        <div>Invoice: <strong id="schoolFeeInvNum"></strong></div>
                        <div style="margin-top: 0.25rem;">Outstanding Balance: <strong id="schoolFeeBalanceDisplay" style="color: #dc2626; font-size: 1.1rem;"></strong></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount to Pay (NGN) *</label>
                        <input type="number" step="50" name="amount" id="schoolFeePayAmount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Channel</label>
                        <select name="gateway" class="form-control">
                            <option value="paystack">Paystack Online Checkout (Debit Card / Bank Transfer / USSD)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('paySchoolFeeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Pay School Fees Online</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPayPortalModal(id, invNum, stuName, balance) {
    document.getElementById('portalInvoiceId').value = id;
    document.getElementById('portalInvNum').textContent = invNum;
    document.getElementById('portalStuName').textContent = stuName;
    document.getElementById('portalBalanceDisplay').textContent = '₦' + balance.toLocaleString();
    document.getElementById('portalPayAmount').value = balance;
    openModal('payPortalModal');
}

function openConsolidatedPayModal(totalBalance, count) {
    document.getElementById('consolidatedTotalDisplay').textContent = '₦' + totalBalance.toLocaleString();
    document.getElementById('consolidatedWardCountDisplay').textContent = count;
    document.getElementById('consolidatedPayAmount').value = totalBalance;
    openModal('consolidatedPayModal');
}

function openDisputePortalModal(id, invNum) {
    document.getElementById('disputeInvoiceId').value = id;
    document.getElementById('disputeInvNumDisplay').textContent = invNum;
    openModal('disputePortalModal');
}

function openPaySchoolFeeModal(id, invNum, balance) {
    document.getElementById('schoolFeeInvoiceId').value = id;
    document.getElementById('schoolFeeInvNum').textContent = invNum;
    document.getElementById('schoolFeeBalanceDisplay').textContent = '₦' + balance.toLocaleString();
    document.getElementById('schoolFeePayAmount').value = balance;
    openModal('paySchoolFeeModal');
}
</script>

<!-- Modal: Consolidated Multi-Ward Payment -->
<div class="modal" id="consolidatedPayModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Consolidated Family Portal Payment</h3>
                <button class="modal-close" onclick="closeModal('consolidatedPayModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('portal/parent/pay-consolidated-portal-fee') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 1rem; margin-bottom: 1rem;">
                        <div style="font-size: 0.85rem; color: #1e40af; margin-bottom: 0.25rem;">Consolidated Family Settlement</div>
                        <div>Unsettled Wards: <strong id="consolidatedWardCountDisplay"></strong> child invoice(s)</div>
                        <div style="margin-top: 0.25rem;">Total Outstanding Balance: <strong id="consolidatedTotalDisplay" style="color: #dc2626; font-size: 1.15rem;"></strong></div>
                    </div>
                    <p style="font-size: 0.85rem; color: #475569; margin-bottom: 1rem; line-height: 1.4;">
                        Your payment will be automatically and transparently allocated across your children's individual portal invoices, generating official verified receipts for each ward.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Payment Amount (NGN) *</label>
                        <input type="number" step="50" min="500" name="amount" id="consolidatedPayAmount" class="form-control" required>
                        <small style="color: #64748b;">Permitted partial payments accepted: Enter any amount to reduce family balance.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Channel</label>
                        <select name="gateway" class="form-control">
                            <option value="paystack">Paystack Online Checkout (Debit Card / Bank Transfer / USSD)</option>
                            <option value="bank_transfer">Direct Institutional Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('consolidatedPayModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Proceed to Consolidated Checkout</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: School & Platform Fee Payment, Refund and Dispute Policies -->
<div class="modal" id="portalFeePolicyModal">
    <div class="modal-dialog" style="max-width: 650px;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Official Billing & Payment Policies</h3>
                <button class="modal-close" onclick="closeModal('portalFeePolicyModal')">&times;</button>
            </div>
            <div class="modal-body" style="font-size: 0.875rem; color: #334155; line-height: 1.6;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e3a8a; margin-top: 0;">1. Purpose & Service Scope</h4>
                <p>The term portal fee is an approved institutional charge enabling continuous assessment recording, teacher-parent messaging, instantaneous examination result publishing, and digital report card verification across the academic term.</p>

                <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e3a8a;">2. Partial Payments & Settlement Rules</h4>
                <p>Parents may make partial payments in permitted instalments prior to the term due date. Terminal report cards and official marksheets unlock immediately once the active term balance is fully cleared.</p>

                <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e3a8a;">3. Pending vs. Verified Transactions</h4>
                <p>Only verified transactions recorded via our certified payment gateway or validated by school bursary officers grant active marksheet access. Transactions marked pending or under processing do not imply settled payment until receipt verification completes.</p>

                <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e3a8a;">4. Billing Disputes, Waivers & Corrections</h4>
                <p>If you believe an invoice contains an error (such as an unapplied scholarship, sibling discount, or dual charge), click the <strong>Dispute</strong> button beside the invoice. The school accounts office is notified immediately to investigate and apply authorized adjustments.</p>

                <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e3a8a;">5. Refund Policy</h4>
                <p>Approved overpayments or authorized administrative reversals are refunded to the payer's verified bank account following bursary confirmation within 5 to 7 business days.</p>

                <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e3a8a;">6. Data Protection & Privacy (Anti-IDOR)</h4>
                <p>Our platform strictly restricts pupil financial telemetry. Wards and their corresponding invoices are strictly private to confirmed, authorized biological or legal guardians.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="closeModal('portalFeePolicyModal')">I Understand</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Compose Message to School -->
<div class="modal" id="parentDashboardComposeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Compose Message to School</h3>
                <button class="modal-close" onclick="closeModal('parentDashboardComposeModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('portal/parent/messages/send') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Send To Recipient *</label>
                        <select name="recipient_id" class="form-control" required>
                            <option value="0">School Administration / General Desk</option>
                            <?php foreach ($staffMembers as $stf): ?>
                                <option value="<?= (int)$stf['id'] ?>">
                                    <?= e($stf['name']) ?> (<?= ucfirst(str_replace('_', ' ', $stf['role'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($wards)): ?>
                        <div class="form-group">
                            <label class="form-label">Regarding Ward / Pupil</label>
                            <select name="student_id" class="form-control">
                                <option value="">General Family Matter (All Wards)</option>
                                <?php foreach ($wards as $w): ?>
                                    <option value="<?= (int)$w['id'] ?>" <?= ($selectedWard && $selectedWard['id'] == $w['id']) ? 'selected' : '' ?>>
                                        <?= e($w['first_name'] . ' ' . $w['last_name']) ?> (<?= e($w['admission_number']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Subject *</label>
                        <input type="text" name="subject" class="form-control" placeholder="e.g. Inquiring on mid-term performance / Health update / Fee clarification" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Dear Teacher / School Administration,&#10;&#10;I am writing to..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('parentDashboardComposeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        <span>Send Message</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Request Payment Refund / Correction -->
<div class="modal" id="requestRefundModal">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Request Payment Refund / Billing Correction</h3>
                <button class="modal-close" onclick="closeModal('requestRefundModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('portal/parent/request-refund') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="payment_id" id="parentRefundPayId">
                <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                    <p style="font-size: 0.875rem; color: #334155; margin: 0;">
                        Submit a formal request for refund on transaction reference <strong id="parentRefundRefDisplay"></strong>.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Refund Amount (NGN) *</label>
                        <input type="number" step="0.01" name="amount" id="parentRefundAmount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Detailed Reason for Refund *</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why you are requesting a refund (e.g. duplicate charge, incorrect student billed, billing waiver approved)."></textarea>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; font-size: 0.8rem; color: #64748b;">
                        Refund requests are reviewed by the platform finance administration in compliance with school accounting guidelines. Authorized refunds are processed back through your original gateway payment channel.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('requestRefundModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Refund Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRequestRefund(payId, ref, amount) {
    document.getElementById('parentRefundPayId').value = payId;
    document.getElementById('parentRefundRefDisplay').textContent = ref;
    document.getElementById('parentRefundAmount').value = amount;
    openModal('requestRefundModal');
}
</script>
