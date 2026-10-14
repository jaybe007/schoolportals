<div class="page-header">
    <div>
        <h1 class="page-title">Parent Communication Desk</h1>
        <p class="page-subtitle">Direct, private, two-way messaging with parents regarding individual student progress, health, and conduct</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <button class="btn btn-primary" onclick="openModal('composeMessageModal')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Send Message to Parent</span>
        </button>
    </div>
</div>

<div style="display: grid; grid-template-columns: 340px 1fr; gap: 1.5rem; align-items: start;">
    <!-- Threads List (Left Column) -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <h3 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0;">Parent Conversations</h3>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;"><?= count($threads) ?> active discussion threads</div>
        </div>
        <div style="max-height: 650px; overflow-y: auto;">
            <?php if (empty($threads)): ?>
                <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin: 0 auto 0.75rem;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <p style="font-size: 0.85rem; margin: 0;">No parent messages yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($threads as $th): 
                    $isSelected = ($activeThread && (int)$activeThread['id'] === (int)$th['id']);
                ?>
                    <a href="<?= url('school/messages?thread_id=' . (int)$th['id']) ?>" 
                       style="display: block; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; text-decoration: none; background: <?= $isSelected ? '#eff6ff' : '#ffffff' ?>; border-left: 3px solid <?= $isSelected ? '#2563eb' : 'transparent' ?>; transition: background 0.15s ease;">
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.25rem;">
                            <strong style="font-size: 0.9rem; color: #1e293b;">
                                <?= e($th['father_name'] ?: ($th['mother_name'] ?: 'Parent')) ?>
                            </strong>
                            <span style="font-size: 0.7rem; color: #64748b;"><?= date('M j', strtotime($th['updated_at'])) ?></span>
                        </div>
                        <div style="font-size: 0.8rem; font-weight: 600; color: #2563eb; margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= e($th['subject']) ?>
                        </div>
                        <?php if (!empty($th['student_first'])): ?>
                            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.35rem;">
                                Pupil: <strong><?= e($th['student_first'] . ' ' . $th['student_last']) ?></strong> (<?= e($th['admission_number']) ?>)
                            </div>
                        <?php endif; ?>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="badge <?= $th['sender_type'] === 'parent' ? 'badge-warning' : 'badge-secondary' ?>" style="font-size: 0.65rem;">
                                <?= $th['sender_type'] === 'parent' ? 'Parent Inquiry' : 'School Sent' ?>
                            </span>
                            <?php if ((int)$th['reply_count'] > 0): ?>
                                <span style="font-size: 0.7rem; color: #64748b; font-weight: 600;"><?= (int)$th['reply_count'] ?> replies</span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Conversation View (Right Column) -->
    <div>
        <?php if (!$activeThread): ?>
            <div class="card" style="text-align: center; padding: 4rem 1.5rem; color: #64748b;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin: 0 auto 1rem;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <h3 style="font-size: 1.1rem; color: #1e293b; margin-bottom: 0.25rem;">Select a Conversation</h3>
                <p style="font-size: 0.875rem;">Choose a conversation thread from the left or click "+ Send Message to Parent" to initiate a private conversation.</p>
            </div>
        <?php else: ?>
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #2563eb; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
                            Direct Conversation Thread #<?= (int)$activeThread['id'] ?>
                        </div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0;"><?= e($activeThread['subject']) ?></h2>
                        <div style="font-size: 0.85rem; color: #475569; margin-top: 0.35rem;">
                            Parent: <strong><?= e($activeThread['father_name'] ?: ($activeThread['mother_name'] ?: 'Parent')) ?></strong>
                            <?php if (!empty($activeThread['father_phone'])): ?>
                                &bull; Phone: <?= e($activeThread['father_phone']) ?>
                            <?php endif; ?>
                            <?php if (!empty($activeThread['student_first'])): ?>
                                &bull; Pupil: <strong><?= e($activeThread['student_first'] . ' ' . $activeThread['student_last']) ?></strong> (<?= e($activeThread['admission_number']) ?>)
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; max-height: 500px; overflow-y: auto; background: #ffffff;">
                    <!-- Initial Message -->
                    <div style="padding: 1.25rem; border-radius: 8px; background: <?= $activeThread['sender_type'] === 'parent' ? '#fef3c7' : '#eff6ff' ?>; border: 1px solid <?= $activeThread['sender_type'] === 'parent' ? '#fde68a' : '#bfdbfe' ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem;">
                            <div>
                                <strong style="font-size: 0.9rem; color: #1e293b;"><?= e($activeThread['sender_name']) ?></strong>
                                <span class="badge <?= $activeThread['sender_type'] === 'parent' ? 'badge-warning' : 'badge-primary' ?>" style="font-size: 0.65rem; margin-left: 0.35rem;">
                                    <?= $activeThread['sender_type'] === 'parent' ? 'Parent' : 'School Official' ?>
                                </span>
                            </div>
                            <span style="font-size: 0.75rem; color: #64748b;"><?= date('M j, Y g:i A', strtotime($activeThread['created_at'])) ?></span>
                        </div>
                        <div style="font-size: 0.925rem; color: #334155; line-height: 1.6; white-space: pre-wrap;">
                            <?= e($activeThread['message']) ?>
                        </div>
                    </div>

                    <!-- Replies -->
                    <?php foreach ($replies as $rep): ?>
                        <div style="padding: 1.25rem; border-radius: 8px; background: <?= $rep['sender_type'] === 'parent' ? '#fef3c7' : '#eff6ff' ?>; border: 1px solid <?= $rep['sender_type'] === 'parent' ? '#fde68a' : '#bfdbfe' ?>; margin-left: <?= $rep['sender_type'] === 'school' ? '1.5rem' : '0' ?>; margin-right: <?= $rep['sender_type'] === 'parent' ? '1.5rem' : '0' ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem;">
                                <div>
                                    <strong style="font-size: 0.9rem; color: #1e293b;"><?= e($rep['sender_name']) ?></strong>
                                    <span class="badge <?= $rep['sender_type'] === 'parent' ? 'badge-warning' : 'badge-primary' ?>" style="font-size: 0.65rem; margin-left: 0.35rem;">
                                        <?= $rep['sender_type'] === 'parent' ? 'Parent' : 'School Official' ?>
                                    </span>
                                </div>
                                <span style="font-size: 0.75rem; color: #64748b;"><?= date('M j, Y g:i A', strtotime($rep['created_at'])) ?></span>
                            </div>
                            <div style="font-size: 0.925rem; color: #334155; line-height: 1.6; white-space: pre-wrap;">
                                <?= e($rep['message']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Reply Composer -->
                <div style="padding: 1.25rem; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                    <form method="POST" action="<?= url('school/messages/' . (int)$activeThread['id'] . '/reply') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label class="form-label" style="font-size: 0.85rem;">Post Reply to Parent</label>
                            <textarea name="message" class="form-control" rows="3" placeholder="Type your response to this parent..." required></textarea>
                        </div>
                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                <span>Send Reply</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Compose New Direct Message to Parent -->
<div class="modal" id="composeMessageModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Send Direct Message to Parent</h3>
                <button class="modal-close" onclick="closeModal('composeMessageModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('school/messages/send') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Recipient Parent & Pupil *</label>
                        <select name="parent_id" id="composeParentSelect" class="form-control" required onchange="updateSelectedStudent(this)">
                            <option value="">Select Parent...</option>
                            <?php foreach ($parents as $p): ?>
                                <option value="<?= (int)$p['id'] ?>" data-student="<?= (int)$p['student_id'] ?>">
                                    <?= e($p['father_name'] ?: ($p['mother_name'] ?: 'Parent')) ?> &bull; Pupil: <?= e($p['student_first'] . ' ' . $p['student_last']) ?> (<?= e($p['admission_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="student_id" id="composeStudentId" value="">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subject *</label>
                        <input type="text" name="subject" class="form-control" placeholder="e.g. Academic Performance Follow-up / Health Notice" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Personal Message Body *</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Dear Parent, we would like to bring to your attention..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('composeMessageModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Deliver Message to Parent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateSelectedStudent(selectElem) {
    var selectedOption = selectElem.options[selectElem.selectedIndex];
    var studentId = selectedOption.getAttribute('data-student');
    document.getElementById('composeStudentId').value = studentId || '';
}
</script>
