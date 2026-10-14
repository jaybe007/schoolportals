<?php

declare(strict_types=1);

namespace EduSphere\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use EduSphere\Core\Database;
use RuntimeException;

class FinancialPdfService
{
    private static function getDompdfInstance(): Dompdf
    {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        return new Dompdf($options);
    }

    /**
     * Generate Official School Fee Receipt PDF
     */
    public static function generateReceiptPdf(int $schoolId, int $receiptId): Dompdf
    {
        $receipt = Database::fetch(
            "SELECT pr.*, 
                    fi.invoice_number, fi.total_amount AS invoice_total, fi.paid_amount AS invoice_paid, fi.balance_due,
                    s.first_name, s.middle_name, s.last_name, s.admission_number,
                    ca.name AS arm_name, cl.name AS class_name,
                    u_ver.name AS verified_by_name
             FROM payment_receipts pr
             JOIN fee_invoices fi ON fi.id = pr.invoice_id
             JOIN students s ON s.id = pr.student_id
             LEFT JOIN class_arms ca ON ca.id = s.class_arm_id
             LEFT JOIN class_levels cl ON cl.id = ca.class_level_id
             LEFT JOIN users u_ver ON u_ver.id = pr.verified_by
             WHERE pr.id = ? AND pr.school_id = ?",
            [$receiptId, $schoolId]
        );

        if (!$receipt) {
            throw new RuntimeException("Payment receipt not found or access denied.");
        }

        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);
        $html = self::buildReceiptHtml($school, $receipt);

        $dompdf = self::getDompdfInstance();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }

    /**
     * Generate Official School Fee Invoice PDF
     */
    public static function generateInvoicePdf(int $schoolId, int $invoiceId): Dompdf
    {
        $invoice = Database::fetch(
            "SELECT fi.*,
                    s.first_name, s.middle_name, s.last_name, s.admission_number,
                    ca.name AS arm_name, cl.name AS class_name,
                    u_p.name AS parent_name, u_p.phone AS parent_phone,
                    ses.name AS session_name, t.name AS term_name
             FROM fee_invoices fi
             JOIN students s ON s.id = fi.student_id
             LEFT JOIN class_arms ca ON ca.id = s.class_arm_id
             LEFT JOIN class_levels cl ON cl.id = ca.class_level_id
             LEFT JOIN parents p ON p.id = s.parent_id
             LEFT JOIN users u_p ON u_p.id = p.user_id
             LEFT JOIN academic_sessions ses ON ses.id = fi.session_id
             LEFT JOIN academic_terms t ON t.id = fi.term_id
             WHERE fi.id = ? AND fi.school_id = ?",
            [$invoiceId, $schoolId]
        );

        if (!$invoice) {
            throw new RuntimeException("Fee invoice not found or access denied.");
        }

        // Fetch associated receipts for this invoice
        $receipts = Database::query(
            "SELECT * FROM payment_receipts WHERE invoice_id = ? AND school_id = ? ORDER BY payment_date ASC",
            [$invoiceId, $schoolId]
        );

        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);
        $html = self::buildInvoiceHtml($school, $invoice, $receipts);

        $dompdf = self::getDompdfInstance();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }

    /**
     * Generate Official Term Portal Fee Payment Receipt PDF
     */
    public static function generatePortalPaymentReceiptPdf(int $schoolId, int $paymentId): Dompdf
    {
        $payment = Database::fetch(
            "SELECT pp.*,
                    pi.invoice_number, pi.gross_amount, pi.amount_due, pi.balance_due,
                    s.first_name, s.middle_name, s.last_name, s.admission_number,
                    ca.name AS arm_name, cl.name AS class_name,
                    ses.name AS session_name, t.name AS term_name
             FROM portal_payments pp
             JOIN portal_invoices pi ON pi.id = pp.invoice_id
             JOIN students s ON s.id = pp.student_id
             LEFT JOIN class_arms ca ON ca.id = s.class_arm_id
             LEFT JOIN class_levels cl ON cl.id = ca.class_level_id
             LEFT JOIN academic_sessions ses ON ses.id = pi.session_id
             LEFT JOIN academic_terms t ON t.id = pi.term_id
             WHERE pp.id = ? AND pp.school_id = ?",
            [$paymentId, $schoolId]
        );

        if (!$payment) {
            throw new RuntimeException("Portal payment receipt not found or access denied.");
        }

        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);
        $html = self::buildPortalReceiptHtml($school, $payment);

        $dompdf = self::getDompdfInstance();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }

    private static function buildReceiptHtml(array $school, array $r): string
    {
        $schoolName = htmlspecialchars($school['name']);
        $schoolMotto = htmlspecialchars($school['motto'] ?? 'Knowledge, Character & Excellence');
        $schoolAddress = htmlspecialchars($school['address'] ?? 'Lagos, Nigeria');
        $schoolPhone = htmlspecialchars($school['phone'] ?? '+234 800 EDUSPHERE');
        $schoolEmail = htmlspecialchars($school['email'] ?? 'bursar@edusphere.example');

        $studentName = htmlspecialchars($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'] . ' ' : '') . $r['last_name']);
        $admNo = htmlspecialchars($r['admission_number']);
        $className = htmlspecialchars(($r['class_name'] ?? '') . ' ' . ($r['arm_name'] ?? ''));

        $receiptNo = htmlspecialchars($r['receipt_number']);
        $invoiceNo = htmlspecialchars($r['invoice_number']);
        $paymentDate = date('F j, Y', strtotime($r['payment_date']));
        $paymentMethod = strtoupper(htmlspecialchars(str_replace('_', ' ', $r['payment_method'])));
        $paymentRef = htmlspecialchars($r['payment_reference'] ?? $r['transaction_id'] ?? 'VERIFIED_NIP');
        $verifiedBy = htmlspecialchars($r['verified_by_name'] ?? 'Bursary Officer');

        $amountPaid = number_format((float)$r['amount_paid'], 2);
        $invoiceTotal = number_format((float)$r['invoice_total'], 2);
        $balanceDue = number_format((float)$r['balance_due'], 2);
        $words = self::convertNumberToWords((float)$r['amount_paid']);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Official Payment Receipt - {$receiptNo}</title>
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 25px; }
    .receipt-box { border: 2px solid #0f172a; border-radius: 6px; padding: 20px; position: relative; background: #ffffff; }
    .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 15px; }
    .school-name { font-size: 20px; font-weight: bold; color: #0f172a; text-transform: uppercase; margin-bottom: 3px; letter-spacing: 0.5px; }
    .school-motto { font-style: italic; color: #475569; font-size: 11px; margin-bottom: 4px; }
    .school-meta { font-size: 10px; color: #64748b; }
    .receipt-badge-bar { background: #0f172a; color: #ffffff; padding: 6px 12px; display: table; width: 100%; box-sizing: border-box; margin-bottom: 15px; border-radius: 3px; }
    .receipt-badge-title { display: table-cell; font-size: 13px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
    .receipt-badge-no { display: table-cell; text-align: right; font-size: 13px; font-weight: bold; color: #38bdf8; font-family: monospace; }
    .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .grid-table td { padding: 5px 8px; font-size: 11px; vertical-align: top; }
    .label { font-weight: bold; color: #475569; width: 22%; }
    .val { color: #0f172a; }
    .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
    .items-table th { background: #f1f5f9; color: #0f172a; font-weight: bold; padding: 8px; text-align: left; font-size: 10px; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; }
    .items-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
    .total-box { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 10px; margin-bottom: 15px; }
    .stamp-container { margin-top: 25px; display: table; width: 100%; }
    .stamp-cell { display: table-cell; width: 50%; vertical-align: bottom; }
    .sign-line { border-top: 1px solid #475569; width: 200px; margin-top: 40px; padding-top: 4px; font-size: 10px; color: #475569; }
    .paid-stamp { border: 3px dashed #16a34a; color: #16a34a; font-size: 18px; font-weight: 800; padding: 6px 16px; display: inline-block; text-transform: uppercase; border-radius: 6px; letter-spacing: 2px; transform: rotate(-5deg); }
    .footer-note { text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px dashed #cbd5e1; padding-top: 8px; margin-top: 20px; }
</style>
</head>
<body>

<div class="receipt-box">
    <!-- Header -->
    <div class="header">
        <div class="school-name">{$schoolName}</div>
        <div class="school-motto">&ldquo;{$schoolMotto}&rdquo;</div>
        <div class="school-meta">{$schoolAddress} &bull; Tel: {$schoolPhone} &bull; Email: {$schoolEmail}</div>
    </div>

    <!-- Title Bar -->
    <div class="receipt-badge-bar">
        <div class="receipt-badge-title">Official Bursary Payment Receipt</div>
        <div class="receipt-badge-no">{$receiptNo}</div>
    </div>

    <!-- Details Grid -->
    <table class="grid-table">
        <tr>
            <td class="label">Pupil Name:</td>
            <td class="val"><strong>{$studentName}</strong></td>
            <td class="label">Payment Date:</td>
            <td class="val"><strong>{$paymentDate}</strong></td>
        </tr>
        <tr>
            <td class="label">Admission No:</td>
            <td class="val"><code>{$admNo}</code></td>
            <td class="label">Invoice Ref:</td>
            <td class="val"><code>{$invoiceNo}</code></td>
        </tr>
        <tr>
            <td class="label">Class / Arm:</td>
            <td class="val">{$className}</td>
            <td class="label">Payment Channel:</td>
            <td class="val"><span style="font-weight: bold;">{$paymentMethod}</span></td>
        </tr>
        <tr>
            <td class="label">Txn / Bank Ref:</td>
            <td class="val" colspan="3"><code style="color: #2563eb;">{$paymentRef}</code></td>
        </tr>
    </table>

    <!-- Ledger Summary -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Total Invoice</th>
                <th style="text-align: right;">Amount Paid Now</th>
                <th style="text-align: right;">Balance Outstanding</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>School Tuition & Terminal Levies</strong> (Ref: {$invoiceNo})</td>
                <td style="text-align: right;">₦{$invoiceTotal}</td>
                <td style="text-align: right; font-weight: bold; color: #16a34a; font-size: 13px;">₦{$amountPaid}</td>
                <td style="text-align: right; font-weight: bold; color: #dc2626;">₦{$balanceDue}</td>
            </tr>
        </tbody>
    </table>

    <!-- Amount in Words Box -->
    <div class="total-box">
        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Amount in Words:</div>
        <div style="font-size: 12px; font-weight: bold; color: #0f172a; font-style: italic;">
            {$words}
        </div>
    </div>

    <!-- Stamps & Signatures -->
    <div class="stamp-container">
        <div class="stamp-cell">
            <div class="paid-stamp">VERIFIED &bull; PAID</div>
        </div>
        <div class="stamp-cell" style="text-align: right;">
            <div style="display: inline-block; text-align: left;">
                <div class="sign-line">
                    Authorized Cashier / Bursar Signature<br>
                    <strong>{$verifiedBy}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-note">
        This is an official computer-generated receipt from EduSphere Cloud Bursary System. Valid without manual alterations.
    </div>
</div>

</body>
</html>
HTML;
    }

    private static function buildInvoiceHtml(array $school, array $inv, array $receipts): string
    {
        $schoolName = htmlspecialchars($school['name']);
        $schoolMotto = htmlspecialchars($school['motto'] ?? 'Knowledge, Character & Excellence');
        $schoolAddress = htmlspecialchars($school['address'] ?? 'Lagos, Nigeria');
        $schoolPhone = htmlspecialchars($school['phone'] ?? '+234 800 EDUSPHERE');
        $schoolEmail = htmlspecialchars($school['email'] ?? 'bursar@edusphere.example');

        $studentName = htmlspecialchars($inv['first_name'] . ' ' . ($inv['middle_name'] ? $inv['middle_name'] . ' ' : '') . $inv['last_name']);
        $admNo = htmlspecialchars($inv['admission_number']);
        $className = htmlspecialchars(($inv['class_name'] ?? '') . ' ' . ($inv['arm_name'] ?? ''));
        $parentName = htmlspecialchars($inv['parent_name'] ?? 'Parent / Guardian');
        $parentPhone = htmlspecialchars($inv['parent_phone'] ?? 'N/A');

        $invoiceNo = htmlspecialchars($inv['invoice_number']);
        $sessionName = htmlspecialchars($inv['session_name'] ?? '');
        $termName = htmlspecialchars($inv['term_name'] ?? '');
        $issueDate = date('F j, Y', strtotime($inv['created_at']));
        $dueDate = !empty($inv['due_date']) ? date('F j, Y', strtotime($inv['due_date'])) : 'Prior to Term Resumption';

        $totalAmount = number_format((float)$inv['total_amount'], 2);
        $paidAmount = number_format((float)$inv['paid_amount'], 2);
        $balanceDue = number_format((float)$inv['balance_due'], 2);
        $status = strtoupper(str_replace('_', ' ', $inv['status']));

        $receiptRows = '';
        foreach ($receipts as $rc) {
            $rcDate = date('M j, Y', strtotime($rc['payment_date']));
            $rcAmt = number_format((float)$rc['amount_paid'], 2);
            $rcNo = htmlspecialchars($rc['receipt_number']);
            $rcMethod = strtoupper(htmlspecialchars($rc['payment_method']));
            $receiptRows .= "<tr>
                <td><code>{$rcNo}</code></td>
                <td>{$rcDate}</td>
                <td>{$rcMethod}</td>
                <td style='text-align: right; font-weight: bold; color: #16a34a;'>₦{$rcAmt}</td>
            </tr>";
        }

        if (empty($receipts)) {
            $receiptRows = "<tr><td colspan='4' style='color: #64748b; text-align: center; padding: 10px;'>No verified payments recorded against this invoice yet.</td></tr>";
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Fee Bill & Invoice - {$invoiceNo}</title>
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 25px; }
    .invoice-box { border: 2px solid #1e3a8a; border-radius: 6px; padding: 20px; background: #ffffff; }
    .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 12px; margin-bottom: 15px; }
    .school-name { font-size: 20px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin-bottom: 3px; }
    .school-motto { font-style: italic; color: #475569; font-size: 11px; margin-bottom: 4px; }
    .school-meta { font-size: 10px; color: #64748b; }
    .invoice-badge-bar { background: #1e3a8a; color: #ffffff; padding: 6px 12px; display: table; width: 100%; box-sizing: border-box; margin-bottom: 15px; border-radius: 3px; }
    .invoice-badge-title { display: table-cell; font-size: 13px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
    .invoice-badge-no { display: table-cell; text-align: right; font-size: 13px; font-weight: bold; color: #93c5fd; font-family: monospace; }
    .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .grid-table td { padding: 4px 6px; font-size: 11px; vertical-align: top; }
    .label { font-weight: bold; color: #475569; width: 22%; }
    .val { color: #0f172a; }
    .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .table th { background: #f8fafc; color: #1e293b; font-weight: bold; padding: 8px; text-align: left; font-size: 10px; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; }
    .table td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
    .summary-card { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 12px; margin-bottom: 15px; }
    .summary-table { width: 100%; border-collapse: collapse; }
    .summary-table td { padding: 4px 6px; font-size: 11px; }
    .bank-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 12px; margin-top: 15px; }
    .footer-note { text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px dashed #cbd5e1; padding-top: 8px; margin-top: 20px; }
</style>
</head>
<body>

<div class="invoice-box">
    <div class="header">
        <div class="school-name">{$schoolName}</div>
        <div class="school-motto">&ldquo;{$schoolMotto}&rdquo;</div>
        <div class="school-meta">{$schoolAddress} &bull; Tel: {$schoolPhone} &bull; Email: {$schoolEmail}</div>
    </div>

    <div class="invoice-badge-bar">
        <div class="invoice-badge-title">Terminal Fee Invoice & Schedule</div>
        <div class="invoice-badge-no">{$invoiceNo}</div>
    </div>

    <table class="grid-table">
        <tr>
            <td class="label">Pupil Name:</td>
            <td class="val"><strong>{$studentName}</strong></td>
            <td class="label">Academic Term:</td>
            <td class="val">{$termName} ({$sessionName})</td>
        </tr>
        <tr>
            <td class="label">Admission No:</td>
            <td class="val"><code>{$admNo}</code></td>
            <td class="label">Class / Arm:</td>
            <td class="val">{$className}</td>
        </tr>
        <tr>
            <td class="label">Parent / Guardian:</td>
            <td class="val">{$parentName} ({$parentPhone})</td>
            <td class="label">Due Date:</td>
            <td class="val"><strong style="color: #dc2626;">{$dueDate}</strong></td>
        </tr>
    </table>

    <!-- Financial Breakdown -->
    <table class="table">
        <thead>
            <tr>
                <th>Item Description</th>
                <th style="text-align: right;">Billed Amount (NGN)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Composite Terminal Tuition & Facilities Fee</strong><br>
                    <span style="font-size: 9px; color: #64748b;">Includes curriculum tuition, sports, ICT laboratory access, and continuous assessment materials</span>
                </td>
                <td style="text-align: right; font-weight: bold; font-size: 12px;">₦{$totalAmount}</td>
            </tr>
        </tbody>
    </table>

    <!-- Ledger Totals -->
    <div class="summary-card">
        <table class="summary-table">
            <tr>
                <td style="font-weight: bold;">Gross Invoice Total:</td>
                <td style="text-align: right; font-weight: bold;">₦{$totalAmount}</td>
            </tr>
            <tr>
                <td style="color: #059669; font-weight: bold;">Total Verified Payments Received:</td>
                <td style="text-align: right; color: #059669; font-weight: bold;">- ₦{$paidAmount}</td>
            </tr>
            <tr style="border-top: 2px solid #0f172a;">
                <td style="font-size: 14px; font-weight: 800; color: #dc2626; padding-top: 8px;">Net Balance Due:</td>
                <td style="text-align: right; font-size: 16px; font-weight: 800; color: #dc2626; padding-top: 8px;">₦{$balanceDue}</td>
            </tr>
        </table>
    </div>

    <!-- Verified Payment Receipts Section -->
    <div style="font-weight: bold; color: #1e3a8a; margin-bottom: 6px; font-size: 11px;">Payment History & Verified Receipts:</div>
    <table class="table">
        <thead>
            <tr>
                <th>Receipt No</th>
                <th>Payment Date</th>
                <th>Channel</th>
                <th style="text-align: right;">Amount (NGN)</th>
            </tr>
        </thead>
        <tbody>
            {$receiptRows}
        </tbody>
    </table>

    <!-- Direct Bank Wire Settlement Details -->
    <div class="bank-box">
        <div style="font-weight: bold; color: #1e40af; font-size: 11px; margin-bottom: 4px;">Payment Settlement Instructions:</div>
        <div style="font-size: 10px; color: #1e3a8a; line-height: 1.5;">
            Parents may settle this invoice via online card payment on the EduSphere Parent Portal, or via direct bank transfer.<br>
            Please reference Pupil Admission Number (<strong>{$admNo}</strong>) or Invoice (<strong>{$invoiceNo}</strong>) in transaction remarks.
        </div>
    </div>

    <div class="footer-note">
        Official invoice generated from EduSphere Cloud Bursary. For financial disputes, contact the school bursary desk.
    </div>
</div>

</body>
</html>
HTML;
    }

    private static function buildPortalReceiptHtml(array $school, array $pp): string
    {
        $schoolName = htmlspecialchars($school['name']);
        $schoolMotto = htmlspecialchars($school['motto'] ?? 'Knowledge, Character & Excellence');
        $schoolAddress = htmlspecialchars($school['address'] ?? 'Lagos, Nigeria');
        $schoolPhone = htmlspecialchars($school['phone'] ?? '+234 800 EDUSPHERE');

        $studentName = htmlspecialchars($pp['first_name'] . ' ' . ($pp['middle_name'] ? $pp['middle_name'] . ' ' : '') . $pp['last_name']);
        $admNo = htmlspecialchars($pp['admission_number']);
        $className = htmlspecialchars(($pp['class_name'] ?? '') . ' ' . ($pp['arm_name'] ?? ''));

        $ref = htmlspecialchars($pp['payment_reference']);
        $invoiceNo = htmlspecialchars($pp['invoice_number']);
        $termName = htmlspecialchars($pp['term_name'] ?? 'Current Term');
        $sessionName = htmlspecialchars($pp['session_name'] ?? '');
        $paymentDate = date('F j, Y g:i A', strtotime($pp['verified_at'] ?? $pp['created_at']));
        $gateway = strtoupper(htmlspecialchars($pp['gateway']));

        $amount = number_format((float)$pp['amount'], 2);
        $words = self::convertNumberToWords((float)$pp['amount']);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Portal Access Receipt - {$ref}</title>
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 25px; }
    .receipt-box { border: 2px solid #059669; border-radius: 6px; padding: 20px; background: #ffffff; }
    .header { text-align: center; border-bottom: 2px solid #059669; padding-bottom: 12px; margin-bottom: 15px; }
    .school-name { font-size: 20px; font-weight: bold; color: #065f46; text-transform: uppercase; margin-bottom: 3px; }
    .school-motto { font-style: italic; color: #475569; font-size: 11px; margin-bottom: 4px; }
    .school-meta { font-size: 10px; color: #64748b; }
    .badge-bar { background: #059669; color: #ffffff; padding: 6px 12px; display: table; width: 100%; box-sizing: border-box; margin-bottom: 15px; border-radius: 3px; }
    .badge-title { display: table-cell; font-size: 13px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
    .badge-no { display: table-cell; text-align: right; font-size: 13px; font-weight: bold; color: #a7f3d0; font-family: monospace; }
    .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .grid-table td { padding: 5px 8px; font-size: 11px; vertical-align: top; }
    .label { font-weight: bold; color: #475569; width: 22%; }
    .val { color: #0f172a; }
    .amount-box { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px; padding: 14px; text-align: center; margin-bottom: 15px; }
    .amount-val { font-size: 24px; font-weight: 800; color: #047857; margin-top: 4px; }
    .stamp-container { margin-top: 20px; display: table; width: 100%; }
    .stamp-cell { display: table-cell; width: 50%; vertical-align: bottom; }
    .paid-stamp { border: 3px dashed #059669; color: #059669; font-size: 16px; font-weight: 800; padding: 6px 16px; display: inline-block; text-transform: uppercase; border-radius: 6px; letter-spacing: 2px; }
    .footer-note { text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px dashed #cbd5e1; padding-top: 8px; margin-top: 20px; }
</style>
</head>
<body>

<div class="receipt-box">
    <div class="header">
        <div class="school-name">{$schoolName}</div>
        <div class="school-motto">&ldquo;{$schoolMotto}&rdquo;</div>
        <div class="school-meta">{$schoolAddress} &bull; Tel: {$schoolPhone}</div>
    </div>

    <div class="badge-bar">
        <div class="badge-title">Electronic Portal Fee Access Receipt</div>
        <div class="badge-no">{$ref}</div>
    </div>

    <table class="grid-table">
        <tr>
            <td class="label">Pupil Name:</td>
            <td class="val"><strong>{$studentName}</strong></td>
            <td class="label">Verified At:</td>
            <td class="val">{$paymentDate}</td>
        </tr>
        <tr>
            <td class="label">Admission No:</td>
            <td class="val"><code>{$admNo}</code></td>
            <td class="label">Payment Gateway:</td>
            <td class="val"><span style="font-weight: bold;">{$gateway}</span></td>
        </tr>
        <tr>
            <td class="label">Class / Arm:</td>
            <td class="val">{$className}</td>
            <td class="label">Academic Term:</td>
            <td class="val">{$termName} ({$sessionName})</td>
        </tr>
        <tr>
            <td class="label">Invoice Ref:</td>
            <td class="val"><code>{$invoiceNo}</code></td>
            <td class="label">Transaction Ref:</td>
            <td class="val"><code style="color: #059669;">{$ref}</code></td>
        </tr>
    </table>

    <div class="amount-box">
        <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #065f46;">Total Verified Portal Access Fee:</div>
        <div class="amount-val">₦{$amount}</div>
        <div style="font-size: 11px; color: #047857; margin-top: 4px; font-style: italic;">{$words}</div>
    </div>

    <div class="stamp-container">
        <div class="stamp-cell">
            <div class="paid-stamp">ACCESS ACTIVATED &bull; VERIFIED</div>
        </div>
        <div class="stamp-cell" style="text-align: right; color: #64748b; font-size: 10px;">
            Settlement Verified by Central EduSphere Financial Allocation Engine<br>
            Parent / Ward Term Portal Access Granted
        </div>
    </div>

    <div class="footer-note">
        Instant electronic payment receipt. Authenticated via EduSphere Multi-Tenant Financial Allocation Engine.
    </div>
</div>

</body>
</html>
HTML;
    }

    /**
     * Helper to convert numeric Naira amounts into english words
     */
    public static function convertNumberToWords(float $number): string
    {
        $integer = (int)floor($number);
        $fraction = (int)round(($number - $integer) * 100);

        $words = self::integerToWords($integer) . ' Naira';
        if ($fraction > 0) {
            $words .= ' and ' . self::integerToWords($fraction) . ' Kobo';
        }
        return ucwords($words) . ' Only';
    }

    private static function integerToWords(int $num): string
    {
        if ($num === 0) {
            return 'zero';
        }

        $units = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
            'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

        if ($num < 20) {
            return $units[$num];
        }
        if ($num < 100) {
            return $tens[(int)($num / 10)] . (($num % 10 !== 0) ? ' ' . $units[$num % 10] : '');
        }
        if ($num < 1000) {
            return $units[(int)($num / 100)] . ' hundred' . (($num % 100 !== 0) ? ' and ' . self::integerToWords($num % 100) : '');
        }
        if ($num < 1000000) {
            return self::integerToWords((int)($num / 1000)) . ' thousand' . (($num % 1000 !== 0) ? ' ' . self::integerToWords($num % 1000) : '');
        }
        if ($num < 1000000000) {
            return self::integerToWords((int)($num / 1000000)) . ' million' . (($num % 1000000 !== 0) ? ' ' . self::integerToWords($num % 1000000) : '');
        }

        return (string)$num;
    }
}
