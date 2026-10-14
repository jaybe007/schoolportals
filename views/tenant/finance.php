<div class="page-header">
    <div>
        <h1 class="page-title">Bursary, Fees & Financial Accounting</h1>
        <p class="page-subtitle">Manage class tuition schedules, termly student billing invoices, and verified receipt records</p>
    </div>
</div>

<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">Student Fee Invoices</div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <button type="button" class="btn btn-primary btn-sm" onclick="openModal('createInvoiceModal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Issue Fee Invoice</span>
            </button>
            <input type="text" class="form-control" placeholder="Search invoices..." data-table-search="#invoicesTable" style="width: 220px;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="invoicesTable">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Total Billed</th>
                    <th>Amount Paid</th>
                    <th>Balance Due</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><code><?= e($inv['invoice_number']) ?></code></td>
                        <td>
                            <div style="font-weight: 700;"><?= e($inv['first_name'] . ' ' . $inv['last_name']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($inv['admission_number']) ?></div>
                        </td>
                        <td><?= e(($inv['class_name'] ?? '') . ' ' . ($inv['arm_name'] ?? '')) ?></td>
                        <td><strong><?= money($inv['total_amount']) ?></strong></td>
                        <td style="color: #059669; font-weight: 600;"><?= money($inv['paid_amount']) ?></td>
                        <td style="color: #dc2626; font-weight: 700;"><?= money($inv['balance_due']) ?></td>
                        <td>
                            <span class="badge badge-<?= $inv['status'] === 'paid' ? 'success' : ($inv['status'] === 'partially_paid' ? 'warning' : 'danger') ?>">
                                <?= e(str_replace('_', ' ', $inv['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.35rem; align-items: center;">
                                <?php if ($inv['status'] !== 'paid'): ?>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openPaymentModal(<?= $inv['id'] ?>, '<?= e($inv['first_name'] . ' ' . $inv['last_name']) ?>', '<?= e($inv['invoice_number']) ?>', <?= (float)$inv['balance_due'] ?>)">
                                        Record Payment
                                    </button>
                                <?php else: ?>
                                    <span class="badge badge-success">Fully Settled</span>
                                <?php endif; ?>
                                <a href="<?= url('school/finance/invoices/' . $inv['id'] . '/pdf') ?>" target="_blank" class="btn btn-secondary btn-sm" title="Download / Print Official Fee Invoice PDF">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    <span>Print Bill</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Verified Official Payment Receipts</div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Invoice No</th>
                    <th>Student Name</th>
                    <th>Amount Paid</th>
                    <th>Payment Method</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($receipts as $rc): ?>
                    <tr>
                        <td><code style="font-weight: bold; color: #1e3a8a;"><?= e($rc['receipt_number']) ?></code></td>
                        <td><code><?= e($rc['invoice_number']) ?></code></td>
                        <td>
                            <div style="font-weight: 600;"><?= e($rc['first_name'] . ' ' . $rc['last_name']) ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= e($rc['admission_number']) ?></div>
                        </td>
                        <td style="font-weight: 700; color: #059669; font-size: 0.9375rem;"><?= money($rc['amount_paid']) ?></td>
                        <td><span class="badge badge-info" style="text-transform: uppercase;"><?= e($rc['payment_method']) ?></span></td>
                        <td><?= format_date($rc['payment_date']) ?></td>
                        <td><span class="badge badge-success"><?= e($rc['status']) ?></span></td>
                        <td>
                            <a href="<?= url('school/finance/receipts/' . $rc['id'] . '/pdf') ?>" target="_blank" class="btn btn-secondary btn-sm" title="Download & Print Official PDF Receipt Slip">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                                <span>Print Receipt</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Record Payment Modal -->
<div id="paymentModal" class="modal-overlay">
    <div class="modal-container">
        <form method="POST" action="<?= url('school/finance') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="record_payment">
            <input type="hidden" name="invoice_id" id="modalInvoiceId">

            <div class="modal-header">
                <h3 class="card-title">Record Fee Payment</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('paymentModal')">✕</button>
            </div>
            <div class="modal-body">
                <div style="background: #f8fafc; padding: 0.85rem; border-radius: var(--radius-md); margin-bottom: 1rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.8125rem; color: #64748b;">Student: <strong id="modalStudentName" style="color: #0f172a;"></strong></div>
                    <div style="font-size: 0.8125rem; color: #64748b;">Invoice: <strong id="modalInvoiceNo" style="color: #0f172a;"></strong></div>
                    <div style="font-size: 0.875rem; color: #dc2626; margin-top: 0.25rem;">Outstanding Balance: <strong id="modalBalanceText"></strong></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Amount to Pay (₦) *</label>
                    <input type="number" step="100" min="100" name="amount_paid" id="modalAmountInput" class="form-control" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Payment Channel</label>
                        <select name="payment_method" class="form-select">
                            <option value="bank_transfer">Direct Bank Transfer</option>
                            <option value="paystack">Paystack Online Payment</option>
                            <option value="cash">Cash at Bursary</option>
                            <option value="pos">POS Terminal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bank Reference / Teller No</label>
                        <input type="text" name="payment_reference" class="form-control" placeholder="e.g. GTB-TXN-9912">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('paymentModal')">Cancel</button>
                <button type="submit" class="btn btn-success">Issue Verified Receipt</button>
            </div>
        </form>
    </div>
</div>

<!-- Create Invoice Modal -->
<div id="createInvoiceModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 540px;">
        <form method="POST" action="<?= url('school/finance/create-invoice') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="card-title">Issue New Student Fee Invoice</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('createInvoiceModal')">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select Enrolled Student *</label>
                    <select name="student_id" class="form-select" required>
                        <option value="">-- Choose Student --</option>
                        <?php foreach ($students as $stu): ?>
                            <option value="<?= $stu['id'] ?>">
                                <?= e($stu['first_name'] . ' ' . $stu['last_name'] . ' (' . $stu['admission_number'] . ' - ' . ($stu['class_name'] ?? '') . ' ' . ($stu['arm_name'] ?? '') . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fee Category</label>
                    <select name="fee_category_id" class="form-select">
                        <option value="">Tuition & Composite Term Bill</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Total Amount Billed (₦) *</label>
                        <input type="number" step="100" min="500" name="amount" class="form-control" placeholder="185000" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Due Date *</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createInvoiceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Generate Official Invoice</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal(invoiceId, studentName, invoiceNo, balance) {
    document.getElementById('modalInvoiceId').value = invoiceId;
    document.getElementById('modalStudentName').textContent = studentName;
    document.getElementById('modalInvoiceNo').textContent = invoiceNo;
    document.getElementById('modalBalanceText').textContent = '₦' + Number(balance).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalAmountInput').value = balance;
    openModal('paymentModal');
}
</script>
