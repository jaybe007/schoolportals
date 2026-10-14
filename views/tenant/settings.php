<div class="page-header">
    <div>
        <h1 class="page-title">School Profile & Institutional Settings</h1>
        <p class="page-subtitle">Configure school identity, official domains, contact points, and academic grading benchmarks</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Profile & Identity -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Institutional Identity</div>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('school/settings') ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label">Official School Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= e($school['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">School Motto / Slogan</label>
                    <input type="text" name="motto" class="form-control" value="<?= e($school['motto'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Administrative Telephone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($school['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Campus Physical Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= e($school['address'] ?? '') ?></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 1.25rem;">
                    <button type="submit" class="btn btn-primary">Save Profile Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div>
        <!-- Verified Domains -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <div class="card-title">Configured Domains & Hostnames</div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Domain Host</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($domains as $d): ?>
                            <tr>
                                <td>
                                    <code style="font-weight: 600; color: #1e3a8a;"><?= e($d['domain']) ?></code>
                                </td>
                                <td><?= $d['is_custom'] ? 'Custom Domain (.edu.ng)' : 'SaaS Subdomain' ?></td>
                                <td><span class="badge badge-success">Active & Verified</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grading Scales -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Configured Terminal Grading Benchmarks</div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Range</th>
                            <th>Points</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $g): ?>
                            <tr>
                                <td><strong style="color: #1e3a8a;"><?= e($g['grade_name']) ?></strong></td>
                                <td><?= (float)$g['min_score'] ?>% &ndash; <?= (float)$g['max_score'] ?>%</td>
                                <td><?= (float)$g['grade_point'] ?></td>
                                <td><?= e($g['remark']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
