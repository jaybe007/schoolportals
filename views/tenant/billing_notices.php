<div class="page-header">
    <div>
        <h1 class="page-title">School Billing Notices & Communication Engine</h1>
        <p class="page-subtitle">Configure term fee announcements, dispatch batch reminders, customize lifecycle templates, and inspect delivery logs</p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <button class="btn btn-outline" onclick="openModal('sendRemindersModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span>Send Batch Reminders (<?= (int)$pendingReminderCount ?> Pending)</span>
        </button>
        <button class="btn btn-primary" onclick="openModal('broadcastModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
            <span>Broadcast Term Announcement</span>
        </button>
    </div>
</div>

<!-- Key Telemetry Stats Grid -->
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div>
            <div class="stat-label">Active Academic Term</div>
            <div class="stat-value" style="font-size: 1.25rem;"><?= e($term['name'] ?? 'Not Set') ?></div>
            <div class="stat-trend positive">
                <span>Session: <?= e($session['name'] ?? 'Current') ?></span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Approved Term Fee / Student</div>
            <div class="stat-value"><?= money((float)($agreement['proposed_fee_per_student'] ?? 0)) ?></div>
            <div class="stat-trend <?= ($agreement['status'] ?? '') === 'accepted' ? 'positive' : 'negative' ?>">
                <span>Agreement: <?= ucfirst($agreement['status'] ?? 'None') ?></span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Eligible Student Guardians</div>
            <div class="stat-value"><?= (int)($agreement['estimated_eligible_students'] ?? 0) ?></div>
            <div class="stat-trend positive">
                <span>Verified parent contacts</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-indigo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Pending Payment Invoices</div>
            <div class="stat-value" style="color: <?= (int)$pendingReminderCount > 0 ? '#dc2626' : '#059669' ?>;"><?= (int)$pendingReminderCount ?></div>
            <div class="stat-trend">
                <span>Unsettled balances</span>
            </div>
        </div>
        <div class="stat-icon-wrapper icon-amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
    </div>
</div>

<!-- Communication Lifecycle Templates -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h3 class="card-title">Configurable Communication Templates</h3>
            <p class="card-subtitle">Official message templates covering the full student billing, reminder, and payment lifecycle</p>
        </div>
        <span class="badge badge-info"><?= count($templates) ?> Lifecycle Templates Active</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Template Name</th>
                    <th>System Key</th>
                    <th>Supported Channels</th>
                    <th>Default Subject Line</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($templates as $tpl): ?>
                    <tr>
                        <td>
                            <strong><?= e($tpl['title']) ?></strong>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                <?= $tpl['school_id'] ? 'Customized for ' . e($school['name']) : 'Platform Global Standard' ?>
                            </div>
                        </td>
                        <td><code><?= e($tpl['template_key']) ?></code></td>
                        <td>
                            <?php foreach (explode(',', $tpl['channels']) as $ch): ?>
                                <span class="badge badge-outline" style="text-transform: uppercase; font-size: 0.7rem;"><?= trim(e($ch)) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td style="max-width: 320px; font-size: 0.85rem; color: #334155;">
                            <?= e($tpl['subject']) ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.35rem;">
                                <button type="button" class="btn btn-outline btn-sm" onclick="previewTpl('<?= e($tpl['template_key']) ?>', '<?= e(addslashes($tpl['title'])) ?>', '<?= e(addslashes($tpl['subject'])) ?>', <?= htmlspecialchars(json_encode($tpl['content_template']), ENT_QUOTES, 'UTF-8') ?>)">
                                    Preview
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" onclick="editTpl(<?= (int)$tpl['id'] ?>, '<?= e($tpl['template_key']) ?>', '<?= e(addslashes($tpl['title'])) ?>', '<?= e(addslashes($tpl['subject'])) ?>', '<?= e($tpl['channels']) ?>', <?= htmlspecialchars(json_encode($tpl['content_template']), ENT_QUOTES, 'UTF-8') ?>)">
                                    Customize
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delivery Logs & Communication Audit Trail -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h3 class="card-title">Communication Delivery Logs & Audit Trail</h3>
            <p class="card-subtitle">Real-time telemetry of all dispatched notices, anti-spam safeguards, and delivery confirmations</p>
        </div>
        <div style="font-size: 0.85rem; color: #64748b;">Showing last <?= count($deliveryLogs) ?> transmissions</div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Template Type</th>
                    <th>Recipient</th>
                    <th>Contact Destination</th>
                    <th>Channel</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Authorized By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deliveryLogs)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: #64748b; padding: 2rem;">
                            No communication logs recorded yet for this school.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($deliveryLogs as $log): ?>
                        <tr>
                            <td style="font-size: 0.8rem; color: #64748b; white-space: nowrap;">
                                <?= date('M j, Y H:i', strtotime($log['sent_at'])) ?>
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-size: 0.7rem; font-family: monospace;">
                                    <?= e($log['template_key']) ?>
                                </span>
                            </td>
                            <td><strong><?= e($log['recipient_name']) ?></strong></td>
                            <td style="font-size: 0.8rem; color: #475569;"><?= e($log['recipient_contact']) ?></td>
                            <td>
                                <span class="badge badge-outline" style="text-transform: uppercase; font-size: 0.7rem;">
                                    <?= e($log['channel']) ?>
                                </span>
                            </td>
                            <td style="max-width: 250px; font-size: 0.825rem; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= e($log['subject']) ?>
                            </td>
                            <td>
                                <?php if ($log['status'] === 'delivered'): ?>
                                    <span class="badge badge-success">Delivered</span>
                                <?php elseif ($log['status'] === 'sent'): ?>
                                    <span class="badge badge-info">Sent</span>
                                <?php elseif ($log['status'] === 'retrying'): ?>
                                    <span class="badge badge-warning">Retrying (<?= (int)$log['delivery_attempts'] ?>)</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Failed</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.8rem; color: #475569;">
                                <?= e($log['approved_by_name'] ?? 'System Automation') ?>
                            </td>
                            <td>
                                <?php if ($log['status'] === 'failed'): ?>
                                    <form method="POST" action="<?= url('school/billing-notices/retry/' . $log['id']) ?>" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline btn-sm" style="font-size: 0.7rem; color: #2563eb;">
                                            Retry
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #059669; font-size: 0.8rem;">✓ OK</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal 1: Broadcast Term Announcement -->
<div class="modal-backdrop" id="broadcastModal">
    <div class="modal" style="max-width: 650px;">
        <div class="modal-header">
            <h3 class="modal-title">Review & Broadcast Term Portal Fee Announcement</h3>
            <button class="modal-close" onclick="closeModal('broadcastModal')">&times;</button>
        </div>
        <form method="POST" action="<?= url('school/billing-notices/broadcast') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="term_id" value="<?= (int)($term['id'] ?? 0) ?>">
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                <div class="alert alert-info" style="font-size: 0.85rem; line-height: 1.4;">
                    <strong>Notice Approval Policy:</strong> Once approved, this official announcement will be pinned to the Parent Portal dashboard and dispatched to all <?= (int)($agreement['estimated_eligible_students'] ?? 0) ?> registered pupil guardians.
                </div>

                <div class="form-group">
                    <label class="form-label">Approved Portal Fee (Read-Only)</label>
                    <input type="text" class="form-control" value="<?= money((float)($agreement['proposed_fee_per_student'] ?? 0)) ?> per student for <?= e($term['name'] ?? 'Current Term') ?>" readonly style="background: #f1f5f9; font-weight: 700;">
                </div>

                <div class="form-group">
                    <label class="form-label">Announcement Subject</label>
                    <input type="text" name="subject" class="form-control" value="Important Notice: Term Portal Fee Schedule for <?= e($term['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Official Announcement Body</label>
                    <textarea name="body" class="form-control" rows="8" required style="font-family: inherit; font-size: 0.875rem; line-height: 1.5;">Dear Parents & Guardians of <?= e($school['name']) ?>,

Please be informed that the approved Student Portal Fee for <?= e($term['name'] ?? 'this term') ?> is <?= money((float)($agreement['proposed_fee_per_student'] ?? 0)) ?> per student.

Purpose: This fee covers continuous academic portal access, CBT examinations, digitized term report cards, attendance tracking, and school communication services.

Due Date: <?= date('F j, Y', strtotime('+30 days')) ?>

Payment Methods: Secure online payment via Paystack (Debit Card, USSD, Bank Transfer) or directly through your Parent Portal.

For support, please contact the school bursary at <?= e($school['phone'] ?: 'School Administration Office') ?>.</textarea>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; font-size: 0.8rem; color: #475569;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" required>
                        <span>I confirm that the school administration has authorized this term fee announcement and approved its distribution to parents.</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('broadcastModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Approve & Broadcast Now</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Send Batch Reminders -->
<div class="modal-backdrop" id="sendRemindersModal">
    <div class="modal" style="max-width: 550px;">
        <div class="modal-header">
            <h3 class="modal-title">Send Batch Payment Reminders</h3>
            <button class="modal-close" onclick="closeModal('sendRemindersModal')">&times;</button>
        </div>
        <form method="POST" action="<?= url('school/billing-notices/reminders') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="term_id" value="<?= (int)($term['id'] ?? 0) ?>">
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                <p style="font-size: 0.875rem; color: #334155; line-height: 1.5; margin: 0;">
                    You are about to dispatch personalized payment reminders to guardians of students with outstanding portal fee balances for <strong><?= e($term['name'] ?? '') ?></strong>.
                </p>

                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1rem;">
                    <div style="font-size: 0.85rem; color: #1e40af; margin-bottom: 0.5rem;">
                        <strong>Safe Communication Safeguards Active:</strong>
                    </div>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8rem; color: #1e3a8a; line-height: 1.5;">
                        <li><strong>Anti-IDOR Security:</strong> Each parent receives strictly their own child's invoice number and amount due.</li>
                        <li><strong>Anti-Spam Filter:</strong> Guardians who have received a reminder within the last 24 hours will automatically be skipped.</li>
                        <li><strong>Current Target:</strong> <?= (int)$pendingReminderCount ?> pending invoice(s) eligible.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('sendRemindersModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Dispatch Reminders</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Preview Template -->
<div class="modal-backdrop" id="previewTemplateModal">
    <div class="modal" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title" id="prevTitle">Template Preview</h3>
            <button class="modal-close" onclick="closeModal('previewTemplateModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 0.75rem;">
                <strong style="font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Subject:</strong>
                <div id="prevSubject" style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;"></div>
            </div>
            <div style="margin-top: 1rem;">
                <strong style="font-size: 0.8rem; color: #64748b; text-transform: uppercase;">Message Body:</strong>
                <div id="prevBody" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 1rem; font-size: 0.875rem; color: #334155; line-height: 1.6; white-space: pre-wrap; margin-top: 0.25rem;"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('previewTemplateModal')">Close</button>
        </div>
    </div>
</div>

<!-- Modal 4: Customize Template -->
<div class="modal-backdrop" id="editTemplateModal">
    <div class="modal" style="max-width: 650px;">
        <div class="modal-header">
            <h3 class="modal-title">Customize Communication Template</h3>
            <button class="modal-close" onclick="closeModal('editTemplateModal')">&times;</button>
        </div>
        <form method="POST" action="<?= url('school/billing-notices/template/update') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="template_key" id="editKey">
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Template Title</label>
                    <input type="text" name="title" id="editTitle" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject Line</label>
                    <input type="text" name="subject" id="editSubject" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Transmission Channels</label>
                    <input type="text" name="channels" id="editChannels" class="form-control" value="email,sms,portal" required>
                    <small style="color: #64748b; font-size: 0.75rem;">Comma-separated channels: email, sms, portal</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Content Template (Placeholders Supported)</label>
                    <textarea name="content_template" id="editContent" class="form-control" rows="8" required style="font-family: monospace; font-size: 0.85rem; line-height: 1.4;"></textarea>
                    <small style="color: #64748b; font-size: 0.75rem;">Supported tags: {{school_name}}, {{student_name}}, {{parent_name}}, {{amount_due}}, {{due_date}}, {{portal_link}}, {{support_contact}}</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editTemplateModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Template Override</button>
            </div>
        </form>
    </div>
</div>

<script>
function previewTpl(key, title, subject, content) {
    document.getElementById('prevTitle').textContent = title + ' (' + key + ')';
    document.getElementById('prevSubject').textContent = subject;
    document.getElementById('prevBody').textContent = content;
    openModal('previewTemplateModal');
}

function editTpl(id, key, title, subject, channels, content) {
    document.getElementById('editKey').value = key;
    document.getElementById('editTitle').value = title;
    document.getElementById('editSubject').value = subject;
    document.getElementById('editChannels').value = channels;
    document.getElementById('editContent').value = content;
    openModal('editTemplateModal');
}
</script>
