<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="page-title">School-Parent Messaging Desk</h1>
        <p class="page-subtitle">Direct, confidential communication with teachers, principal, and school administrative office</p>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
            <span class="badge badge-warning" style="font-size: 0.85rem; padding: 0.45rem 0.85rem; font-weight: 700;">
                <?= (int)$unreadCount ?> Unread Message<?= $unreadCount > 1 ? 's' : '' ?>
            </span>
        <?php endif; ?>
        <button class="btn btn-primary" onclick="openModal('parentComposeModal')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Compose Message</span>
        </button>
        <a href="<?= url('portal/parent') ?>" class="btn btn-secondary">
            &larr; Back to Dashboard
        </a>
    </div>
</div>

<!-- Mobile Tab Navigation if on small screens -->
<div style="display: grid; grid-template-columns: 340px 1fr; gap: 1.5rem; align-items: start;">
    <!-- Threads List (Left Column) -->
    <div class="card" style="padding: 0; overflow: hidden; box-shadow: var(--shadow-sm);">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0;">Inbox & Conversations</h3>
                <span class="badge badge-secondary" style="font-size: 0.75rem;"><?= count($threads) ?> total</span>
            </div>

            <!-- Quick Filter Tabs -->
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                <button type="button" class="btn btn-sm filter-tab active" onclick="filterMessages('all', this)" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;">
                    All (<?= count($threads) ?>)
                </button>
                <button type="button" class="btn btn-sm filter-tab" onclick="filterMessages('unread', this)" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;">
                    Unread (<?= (int)$unreadCount ?>)
                </button>
                <button type="button" class="btn btn-sm filter-tab" onclick="filterMessages('school', this)" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;">
                    From School
                </button>
                <button type="button" class="btn btn-sm filter-tab" onclick="filterMessages('parent', this)" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;">
                    My Inquiries
                </button>
            </div>
        </div>

        <!-- Conversation Threads List -->
        <div id="threadListContainer" style="max-height: 620px; overflow-y: auto;">
            <?php if (empty($threads)): ?>
                <div style="text-align: center; padding: 3.5rem 1.5rem; color: #64748b;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: #94a3b8;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <strong style="color: #334155; display: block; margin-bottom: 0.25rem;">No Conversations Yet</strong>
                    <p style="font-size: 0.85rem; margin-bottom: 1rem;">Have a question or update for your child's teacher?</p>
                    <button class="btn btn-primary btn-sm" onclick="openModal('parentComposeModal')">
                        + Compose New Message
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($threads as $th): 
                    $isSelected = ($activeThread && (int)$activeThread['id'] === (int)$th['id']);
                    $isUnread = !empty($th['is_unread']);
                    $senderType = $th['sender_type'];
                ?>
                    <a href="<?= url('portal/parent/messages?thread_id=' . (int)$th['id']) ?>" 
                       class="thread-card-item"
                       data-unread="<?= $isUnread ? '1' : '0' ?>"
                       data-type="<?= e($senderType) ?>"
                       style="display: block; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; text-decoration: none; background: <?= $isSelected ? '#eff6ff' : ($isUnread ? '#fffdf5' : '#ffffff') ?>; border-left: 4px solid <?= $isSelected ? '#2563eb' : ($isUnread ? '#f59e0b' : 'transparent') ?>; transition: all 0.15s ease;">
                        
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.25rem;">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <?php if ($isUnread): ?>
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;" title="Unread Message"></span>
                                <?php endif; ?>
                                <strong style="font-size: 0.9rem; color: <?= $isUnread ? '#0f172a' : '#334155' ?>; font-weight: <?= $isUnread ? '700' : '600' ?>;">
                                    <?= $th['sender_type'] === 'school' ? '🏫 ' . e($th['sender_name']) : 'You' ?>
                                </strong>
                            </div>
                            <span style="font-size: 0.7rem; color: #64748b; font-weight: 500;"><?= date('M j', strtotime($th['updated_at'])) ?></span>
                        </div>

                        <div style="font-size: 0.85rem; font-weight: <?= $isUnread ? '700' : '600' ?>; color: <?= $isUnread ? '#1e3a8a' : '#2563eb' ?>; margin-bottom: 0.35rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= e($th['subject']) ?>
                        </div>

                        <?php if (!empty($th['student_first'])): ?>
                            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.4rem;">
                                Regarding: <strong style="color: #475569;"><?= e($th['student_first'] . ' ' . $th['student_last']) ?></strong>
                            </div>
                        <?php endif; ?>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; gap: 0.35rem; align-items: center;">
                                <?php if ($isUnread): ?>
                                    <span class="badge badge-warning" style="font-size: 0.65rem; font-weight: 700;">NEW</span>
                                <?php endif; ?>
                                <span class="badge <?= $th['sender_type'] === 'school' ? 'badge-primary' : 'badge-secondary' ?>" style="font-size: 0.65rem;">
                                    <?= $th['sender_type'] === 'school' ? 'School Desk' : 'My Inquiry' ?>
                                </span>
                            </div>
                            <?php if ((int)$th['reply_count'] > 0): ?>
                                <span style="font-size: 0.7rem; color: #64748b; font-weight: 600;">
                                    <?= (int)$th['reply_count'] ?> <?= (int)$th['reply_count'] === 1 ? 'reply' : 'replies' ?>
                                </span>
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
            <div class="card" style="text-align: center; padding: 4.5rem 1.5rem; color: #64748b;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; color: #2563eb;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3 style="font-size: 1.15rem; color: #1e293b; margin-bottom: 0.4rem; font-weight: 700;">Select a Conversation</h3>
                <p style="font-size: 0.9rem; max-width: 440px; margin: 0 auto 1.5rem; line-height: 1.5;">
                    Choose a thread from the left or start a brand new inquiry to communicate directly with school management or teachers.
                </p>
                <button class="btn btn-primary" onclick="openModal('parentComposeModal')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>+ Compose Message to School</span>
                </button>
            </div>
        <?php else: ?>
            <div class="card" style="margin-bottom: 1.5rem; box-shadow: var(--shadow-sm); overflow: hidden; padding: 0;">
                <!-- Conversation Header -->
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1.25rem 1.5rem;">
                    <div>
                        <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.35rem;">
                            <span class="badge <?= $activeThread['sender_type'] === 'school' ? 'badge-primary' : 'badge-secondary' ?>" style="font-size: 0.7rem;">
                                <?= $activeThread['sender_type'] === 'school' ? 'From School' : 'My Family Inquiry' ?>
                            </span>
                            <?php if (!empty($activeThread['student_first'])): ?>
                                <span class="badge badge-info" style="font-size: 0.7rem;">
                                    Pupil: <?= e($activeThread['student_first'] . ' ' . $activeThread['student_last']) ?>
                                </span>
                            <?php endif; ?>
                            <span style="font-size: 0.75rem; color: #64748b; margin-left: auto;">
                                Started <?= date('M j, Y g:i A', strtotime($activeThread['created_at'])) ?>
                            </span>
                        </div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0; line-height: 1.4;">
                            <?= e($activeThread['subject']) ?>
                        </h2>
                    </div>
                </div>

                <!-- Messages Stream -->
                <div class="card-body" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem; max-height: 520px; overflow-y: auto; background: #ffffff;">
                    <!-- Initial Message -->
                    <div style="padding: 1.25rem 1.5rem; border-radius: 8px; background: <?= $activeThread['sender_type'] === 'school' ? '#eff6ff' : '#f8fafc' ?>; border: 1px solid <?= $activeThread['sender_type'] === 'school' ? '#bfdbfe' : '#e2e8f0' ?>; <?= $activeThread['sender_type'] === 'parent' ? 'margin-left: 1rem;' : 'margin-right: 1rem;' ?>">
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem;">
                            <div>
                                <strong style="font-size: 0.95rem; color: #1e293b;"><?= $activeThread['sender_type'] === 'school' ? '🏫 ' . e($activeThread['sender_name']) : 'You (Parent)' ?></strong>
                                <span class="badge <?= $activeThread['sender_type'] === 'school' ? 'badge-primary' : 'badge-secondary' ?>" style="font-size: 0.65rem; margin-left: 0.35rem;">
                                    <?= $activeThread['sender_type'] === 'school' ? 'School Official' : 'Parent' ?>
                                </span>
                            </div>
                            <span style="font-size: 0.75rem; color: #64748b;"><?= date('M j, Y g:i A', strtotime($activeThread['created_at'])) ?></span>
                        </div>
                        <div style="font-size: 0.95rem; color: #334155; line-height: 1.6; white-space: pre-wrap;">
                            <?= e($activeThread['message']) ?>
                        </div>
                    </div>

                    <!-- Conversation Replies -->
                    <?php foreach ($replies as $rep): ?>
                        <div style="padding: 1.25rem 1.5rem; border-radius: 8px; background: <?= $rep['sender_type'] === 'school' ? '#eff6ff' : '#f8fafc' ?>; border: 1px solid <?= $rep['sender_type'] === 'school' ? '#bfdbfe' : '#e2e8f0' ?>; <?= $rep['sender_type'] === 'parent' ? 'margin-left: 2rem;' : 'margin-right: 2rem;' ?>">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem;">
                                <div>
                                    <strong style="font-size: 0.95rem; color: #1e293b;"><?= $rep['sender_type'] === 'school' ? '🏫 ' . e($rep['sender_name']) : 'You (Parent)' ?></strong>
                                    <span class="badge <?= $rep['sender_type'] === 'school' ? 'badge-primary' : 'badge-secondary' ?>" style="font-size: 0.65rem; margin-left: 0.35rem;">
                                        <?= $rep['sender_type'] === 'school' ? ucfirst(str_replace('_', ' ', $rep['sender_role'] ?? 'Staff')) : 'Parent' ?>
                                    </span>
                                </div>
                                <span style="font-size: 0.75rem; color: #64748b;"><?= date('M j, Y g:i A', strtotime($rep['created_at'])) ?></span>
                            </div>
                            <div style="font-size: 0.95rem; color: #334155; line-height: 1.6; white-space: pre-wrap;">
                                <?= e($rep['message']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Reply Composer Box -->
                <div style="padding: 1.25rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                    <form method="POST" action="<?= url('portal/parent/messages/' . (int)$activeThread['id'] . '/reply') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <label class="form-label" style="font-size: 0.85rem; font-weight: 600;">Type Reply to School</label>
                            <textarea name="message" class="form-control" rows="3" placeholder="Write your response here..." required style="background: #ffffff;"></textarea>
                        </div>
                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                <span>Send Reply</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Compose New Message to School -->
<div class="modal" id="parentComposeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Compose Message to School</h3>
                <button class="modal-close" onclick="closeModal('parentComposeModal')">&times;</button>
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
                            <label class="form-label">Regarding Child / Ward</label>
                            <select name="student_id" class="form-control">
                                <option value="">General Family Inquiry (All Children)</option>
                                <?php foreach ($wards as $w): ?>
                                    <option value="<?= (int)$w['id'] ?>">
                                        <?= e($w['first_name'] . ' ' . $w['last_name']) ?> (<?= e($w['admission_number']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Subject *</label>
                        <input type="text" name="subject" class="form-control" placeholder="e.g. Sick Leave Notification / Homework Question / Meeting Request" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Dear School Administration / Form Teacher,&#10;&#10;I would like to inform you that..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('parentComposeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        <span>Send Message</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterMessages(type, btn) {
    document.querySelectorAll('.filter-tab').forEach(function(b) {
        b.classList.remove('active');
        b.classList.remove('btn-primary');
        b.classList.add('btn-secondary');
    });
    btn.classList.add('active');
    btn.classList.remove('btn-secondary');
    btn.classList.add('btn-primary');

    var items = document.querySelectorAll('.thread-card-item');
    items.forEach(function(item) {
        if (type === 'all') {
            item.style.display = 'block';
        } else if (type === 'unread') {
            item.style.display = (item.getAttribute('data-unread') === '1') ? 'block' : 'none';
        } else if (type === 'school') {
            item.style.display = (item.getAttribute('data-type') === 'school') ? 'block' : 'none';
        } else if (type === 'parent') {
            item.style.display = (item.getAttribute('data-type') === 'parent') ? 'block' : 'none';
        }
    });
}
</script>
