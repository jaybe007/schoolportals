<div class="page-header">
    <div>
        <h1 class="page-title">Nigeria Data Protection Act (NDPA) 2023 Audit Trail</h1>
        <p class="page-subtitle">Immutable chronological logging of all privileged administrative, financial, and security actions</p>
    </div>
    <div>
        <input type="text" class="form-control" placeholder="Search audit logs..." data-table-search="#auditTable" style="width: 280px;">
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table" id="auditTable">
            <thead>
                <tr>
                    <th>Timestamp (WAT)</th>
                    <th>User Initiator</th>
                    <th>Tenant Scope</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Payload Diffs</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $l): ?>
                    <tr>
                        <td style="font-size: 0.8125rem; font-family: monospace; color: #475569;">
                            <?= format_date($l['created_at'], 'Y-m-d H:i:s') ?>
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?= e($l['user_name'] ?? 'System Process') ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($l['user_email'] ?? '') ?></div>
                        </td>
                        <td><?= e($l['school_name'] ?? 'Platform HQ') ?></td>
                        <td>
                            <span class="badge badge-info"><?= e($l['action']) ?></span>
                        </td>
                        <td style="font-size: 0.8125rem; font-family: monospace;">
                            <?= e($l['entity_type'] ?? '—') ?> #<?= e($l['entity_id'] ?? '—') ?>
                        </td>
                        <td style="font-size: 0.75rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: monospace; color: #475569;">
                            <?= e($l['new_values'] ?? '—') ?>
                        </td>
                        <td style="font-size: 0.8125rem; font-family: monospace;"><?= e($l['ip_address']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
