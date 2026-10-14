<div class="page-header">
    <div>
        <h1 class="page-title">Library Catalog & Physical Asset Inventory</h1>
        <p class="page-subtitle">Track library textbook loans, educational equipment, science laboratory apparatus, and store assets</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Library Books -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Library Books Catalog</div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title & Author</th>
                        <th>Category</th>
                        <th>Available</th>
                        <th>Shelf Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $b): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;"><?= e($b['title']) ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;"><?= e($b['author']) ?> &bull; ISBN: <?= e($b['isbn'] ?? 'N/A') ?></div>
                            </td>
                            <td><span class="badge badge-info"><?= e($b['category']) ?></span></td>
                            <td>
                                <strong><?= $b['available_copies'] ?></strong> / <?= $b['total_copies'] ?> copies
                            </td>
                            <td><code><?= e($b['shelf_location'] ?? 'General') ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inventory Items -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">School Asset Inventory & Lab Supplies</div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item Code & Name</th>
                        <th>Category</th>
                        <th>Quantity in Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory as $inv): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;"><?= e($inv['name']) ?></div>
                                <div style="font-size: 0.75rem; color: #64748b; font-family: monospace;"><?= e($inv['item_code']) ?></div>
                            </td>
                            <td><?= e($inv['category']) ?></td>
                            <td>
                                <strong><?= $inv['quantity'] ?> <?= e($inv['unit']) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-<?= $inv['status'] === 'in_stock' ? 'success' : 'warning' ?>">
                                    <?= e(str_replace('_', ' ', $inv['status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
