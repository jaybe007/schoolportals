<div class="page-header">
    <div>
        <h1 class="page-title">School Notice Board</h1>
        <p class="page-subtitle">Publish and manage institutional announcements across students, parents, and faculty</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <button class="btn btn-primary" onclick="openModal('createNoticeModal')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Publish Notice</span>
        </button>
    </div>
</div>

<!-- Filters Bar -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
            <span style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-right: 0.5rem;">Category:</span>
            <?php
            $categories = ['' => 'All', 'academic' => 'Academic', 'holiday' => 'Holidays', 'event' => 'Events', 'sports' => 'Sports', 'pta' => 'PTA', 'urgent' => 'Urgent'];
            foreach ($categories as $catKey => $catLabel):
                $isActive = ($selectedCategory ?? '') === $catKey;
            ?>
                <a href="<?= url('school/notices' . ($catKey ? '?category=' . $catKey : '')) ?>" 
                   class="badge <?= $isActive ? 'badge-primary' : 'badge-secondary' ?>" 
                   style="text-decoration: none; padding: 0.45rem 0.85rem; font-size: 0.8rem;">
                    <?= $catLabel ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Audience:</span>
            <select class="form-control" style="width: auto; padding: 0.35rem 0.75rem; font-size: 0.85rem;" onchange="location.href='<?= url('school/notices') ?>?audience=' + this.value">
                <option value="">All Audiences</option>
                <option value="all" <?= ($selectedAudience ?? '') === 'all' ? 'selected' : '' ?>>Whole School (All)</option>
                <option value="parents" <?= ($selectedAudience ?? '') === 'parents' ? 'selected' : '' ?>>Parents Only</option>
                <option value="students" <?= ($selectedAudience ?? '') === 'students' ? 'selected' : '' ?>>Students Only</option>
                <option value="staff" <?= ($selectedAudience ?? '') === 'staff' ? 'selected' : '' ?>>Staff Only</option>
            </select>
        </div>
    </div>
</div>

<!-- Notice Board Feed -->
<div style="display: flex; flex-direction: column; gap: 1.25rem;">
    <?php if (empty($notices)): ?>
        <div class="card" style="text-align: center; padding: 3rem 1.5rem; color: #64748b;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin: 0 auto 1rem;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <h3 style="font-size: 1.1rem; color: #1e293b; margin-bottom: 0.25rem;">No Notices Published</h3>
            <p style="font-size: 0.875rem;">Click the "+ Publish Notice" button above to broadcast announcements to students and parents.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notices as $n): ?>
            <div class="card" style="border-left: 4px solid <?= $n['priority'] === 'urgent' ? '#dc2626' : ($n['priority'] === 'important' ? '#f59e0b' : ($n['is_pinned'] ? '#2563eb' : '#e2e8f0')) ?>;">
                <div class="card-header" style="padding-bottom: 0.75rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                            <?php if ($n['is_pinned']): ?>
                                <span class="badge badge-primary" style="font-size: 0.75rem;">📌 Pinned</span>
                            <?php endif; ?>
                            <span class="badge badge-info" style="text-transform: uppercase; font-size: 0.7rem;"><?= e($n['category']) ?></span>
                            <?php if ($n['priority'] === 'urgent'): ?>
                                <span class="badge badge-danger" style="font-size: 0.7rem;">URGENT</span>
                            <?php elseif ($n['priority'] === 'important'): ?>
                                <span class="badge badge-warning" style="font-size: 0.7rem;">IMPORTANT</span>
                            <?php endif; ?>
                            <span class="badge badge-secondary" style="font-size: 0.7rem;">Audience: <?= ucfirst(e($n['target_audience'])) ?></span>
                        </div>
                        <h3 class="card-title" style="font-size: 1.2rem;"><?= e($n['title']) ?></h3>
                        <p class="card-subtitle" style="font-size: 0.8rem; color: #64748b;">
                            Posted by <strong><?= e($n['author_name']) ?></strong> &bull; <?= date('M j, Y g:i A', strtotime($n['published_at'])) ?>
                            <?php if (!empty($n['expires_at'])): ?>
                                &bull; <span style="color: #dc2626;">Expires: <?= date('M j, Y', strtotime($n['expires_at'])) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <form method="POST" action="<?= url('school/notices/' . (int)$n['id'] . '/delete') ?>" onsubmit="return confirm('Are you sure you want to delete this notice?');" style="display: inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline btn-sm" style="color: #dc2626; border-color: #fca5a5;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                <span>Delete</span>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body" style="padding-top: 0.5rem;">
                    <div style="font-size: 0.925rem; color: #334155; line-height: 1.6; white-space: pre-wrap; background: #f8fafc; padding: 1.25rem; border-radius: 8px;">
                        <?= e($n['content']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal: Publish Notice -->
<div class="modal" id="createNoticeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Publish Institutional Notice</h3>
                <button class="modal-close" onclick="closeModal('createNoticeModal')">&times;</button>
            </div>
            <form method="POST" action="<?= url('school/notices/create') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Notice Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Resumption Guidelines for 2nd Term 2025/2026 Session" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control">
                                <option value="general">General Bulletin</option>
                                <option value="academic">Academic & Examinations</option>
                                <option value="holiday">Holiday & Mid-Term Break</option>
                                <option value="event">Campus Events & Visiting Day</option>
                                <option value="sports">Inter-House Sports</option>
                                <option value="pta">PTA & Board Meeting</option>
                                <option value="urgent">Urgent Circular</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-control">
                                <option value="normal">Normal</option>
                                <option value="important">Important (Yellow Border)</option>
                                <option value="urgent">Urgent (Red Border)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Target Audience</label>
                            <select name="target_audience" class="form-control">
                                <option value="all">Whole School (All)</option>
                                <option value="parents">Parents Only</option>
                                <option value="students">Students Only</option>
                                <option value="staff">Staff / Faculty Only</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Expiry Date (Optional)</label>
                            <input type="date" name="expires_at" class="form-control">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                            <input type="checkbox" name="is_pinned" value="1" style="width: 1.1rem; height: 1.1rem;">
                            <strong>Pin this notice to top of Notice Board</strong>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notice Announcement Body *</label>
                        <textarea name="content" class="form-control" rows="6" placeholder="Provide full details of the announcement..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createNoticeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Publish to Notice Board</button>
                </div>
            </form>
        </div>
    </div>
</div>
