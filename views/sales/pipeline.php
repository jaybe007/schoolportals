<div class="page-header">
    <div>
        <h1 class="page-title">Interactive Visual Kanban Pipeline</h1>
        <p class="page-subtitle">Track school acquisition milestones from initial outreach through proposal and verified onboarding</p>
    </div>
    <div>
        <a href="<?= url('sales/leads') ?>" class="btn btn-secondary">
            <span>List View</span>
        </a>
    </div>
</div>

<!-- Kanban Board Container -->
<div class="kanban-board">
    <?php foreach ($stages as $stageKey => $stageTitle): ?>
        <?php $cards = $pipelineData[$stageKey] ?? []; ?>
        <div class="kanban-column">
            <div class="kanban-col-header">
                <div><?= e($stageTitle) ?></div>
                <span class="badge badge-neutral"><?= count($cards) ?></span>
            </div>
            <div class="kanban-cards">
                <?php if (empty($cards)): ?>
                    <div style="font-size: 0.75rem; color: #94a3b8; text-align: center; padding: 1.5rem 0.5rem; border: 1px dashed #cbd5e1; border-radius: var(--radius-md);">
                        No deals in this stage
                    </div>
                <?php endif; ?>
                <?php foreach ($cards as $lead): ?>
                    <div class="kanban-card">
                        <div class="kanban-card-title"><?= e($lead['school_name']) ?></div>
                        <div class="kanban-card-meta">
                            <div>👤 <?= e($lead['contact_person']) ?></div>
                            <div>📞 <?= e($lead['phone']) ?></div>
                            <div>🎓 Est. Students: <strong><?= (int)$lead['estimated_students'] ?></strong></div>
                            <?php if ($isManager): ?>
                                <div style="color: #1e3a8a; font-weight: 600;">Rep: <?= e($lead['rep_name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Stage advance selector -->
                        <div style="margin-top: 0.75rem; padding-top: 0.5rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                            <form method="POST" action="<?= url('sales/leads/' . $lead['id'] . '/move-stage') ?>" style="display: flex; gap: 0.25rem; width: 100%;">
                                <?= csrf_field() ?>
                                <select name="stage" class="form-select" onchange="this.form.submit()" style="font-size: 0.75rem; padding: 0.2rem 0.4rem; height: auto;">
                                    <?php foreach ($stages as $sk => $st): ?>
                                        <option value="<?= $sk ?>" <?= $sk === $stageKey ? 'selected' : '' ?>>
                                            Move to: <?= e($st) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
