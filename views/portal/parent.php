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
<?php endif; ?>

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
