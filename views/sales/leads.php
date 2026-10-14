<div class="page-header">
    <div>
        <h1 class="page-title">Prospective School Leads Directory</h1>
        <p class="page-subtitle">Territory outreach directory, contact logs, and prospect pipeline attribution</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <button type="button" class="btn btn-primary" onclick="openModal('addLeadModal')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            <span>Capture New Prospect</span>
        </button>
        <input type="text" class="form-control" placeholder="Search prospects..." data-table-search="#leadsTable" style="width: 240px;">
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table" id="leadsTable">
            <thead>
                <tr>
                    <th>School Name</th>
                    <th>Contact Person</th>
                    <th>Phone & Email</th>
                    <th>Est. Students</th>
                    <th>Current Stage</th>
                    <th>Assigned Rep</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $l): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;"><?= e($l['school_name']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($l['state'] ?? 'Nigeria') ?></div>
                        </td>
                        <td><?= e($l['contact_person']) ?></td>
                        <td>
                            <div><?= e($l['phone']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($l['email'] ?? '—') ?></div>
                        </td>
                        <td><strong><?= number_format((int)$l['estimated_students']) ?></strong></td>
                        <td>
                            <span class="badge badge-<?= $l['stage'] === 'won' ? 'success' : ($l['stage'] === 'lost' ? 'danger' : 'info') ?>">
                                <?= e(str_replace('_', ' ', $l['stage'])) ?>
                            </span>
                        </td>
                        <td><?= e($l['rep_name']) ?></td>
                        <td>
                            <a href="<?= url('sales/pipeline') ?>" class="btn btn-secondary btn-sm">View in Pipeline</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Lead Modal -->
<div id="addLeadModal" class="modal-overlay">
    <div class="modal-container">
        <form method="POST" action="<?= url('sales/leads') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="card-title">Add Prospective School</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('addLeadModal')">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">School Name *</label>
                    <input type="text" name="school_name" class="form-control" placeholder="e.g. Greenwood Academy" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Contact Person / Proprietor *</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="e.g. Chief O. Johnson" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telephone Number *</label>
                        <input type="text" name="phone" class="form-control" placeholder="+2348030000000" required>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="info@greenwood.edu.ng">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estimated Student Population</label>
                        <input type="number" name="estimated_students" class="form-control" value="350">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Pipeline Stage</label>
                    <select name="stage" class="form-select">
                        <option value="new_lead">New Lead</option>
                        <option value="contacted">Contacted</option>
                        <option value="qualified">Qualified</option>
                        <option value="demo_scheduled">Demo Scheduled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Discovery Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Key pain points, current software or manual bottlenecks..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addLeadModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Prospect</button>
            </div>
        </form>
    </div>
</div>
