<style>
    /* Override auth container width for rich multi-step partner onboarding */
    .auth-box {
        max-width: 980px !important;
        border-radius: 1rem !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45) !important;
    }
    .auth-box-header {
        background: linear-gradient(135deg, #09122c 0%, #172554 50%, #1e3a8a 100%) !important;
        padding: 2.25rem 2rem !important;
        border-bottom: 3px solid #10b981 !important;
    }
    .agreement-container {
        max-height: 240px;
        overflow-y: auto;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        padding: 1.25rem;
        font-size: 0.8125rem;
        line-height: 1.6;
        color: #334155;
        margin-bottom: 1rem;
    }
    .agreement-container::-webkit-scrollbar {
        width: 6px;
    }
    .agreement-container::-webkit-scrollbar-thumb {
        background-color: #94a3b8;
        border-radius: 3px;
    }
    .step-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .section-card:hover {
        border-color: #93c5fd;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.05);
    }
    .calc-banner {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 1px solid #a7f3d0;
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    .form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
    @media (max-width: 768px) {
        .form-grid-2, .form-grid-3 {
            grid-template-columns: 1fr;
        }
    }
</style>

<div style="margin-bottom: 1.75rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.5rem;">
        <div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                Sales Partner Registration & School Onboarding
            </h2>
            <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
                Become an authorized EduSphere field partner, earn guaranteed recurring commissions, and onboard your first school.
            </p>
        </div>
        <div>
            <a href="<?= url('login') ?>" class="btn btn-secondary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                <span>Back to Sign In</span>
            </a>
        </div>
    </div>
</div>

<form method="POST" action="<?= url('become-a-sales-rep') ?>" id="salesRepRegisterForm">
    <?= csrf_field() ?>

    <!-- Live Commission Earnings Simulator Banner -->
    <div class="calc-banner">
        <div style="background: #10b981; color: white; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
        </div>
        <div style="flex: 1;">
            <div style="font-weight: 700; color: #065f46; font-size: 0.9375rem;">
                Dual Revenue Earning Structure
            </div>
            <div style="color: #047857; font-size: 0.8125rem; margin-top: 0.2rem; line-height: 1.5;">
                Earn <strong>15.0% commission</strong> on your school's initial academic term / subscription, and <strong>7.5% recurring commission</strong> on every subsequent academic term. Direct bank disbursements with verified audit trails.
            </div>
        </div>
        <div style="text-align: right; border-left: 1px solid #a7f3d0; padding-left: 1rem;" class="calc-metrics">
            <div style="font-size: 0.7rem; text-transform: uppercase; font-weight: 700; color: #059669;">Est. Initial Payout (300 pupils)</div>
            <div style="font-size: 1.25rem; font-weight: 800; color: #065f46;" id="estCommDisplay">₦67,500</div>
        </div>
    </div>

    <!-- ================= SECTION 1: LEGAL PARTNER AGREEMENT ================= -->
    <div class="section-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="step-pill" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                    Phase 1
                </span>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0;">
                    Commercial Sales Partner Agreement
                </h3>
            </div>
            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Agreement v1.0 &bull; Read & Accept</span>
        </div>

        <div class="agreement-container">
            <h4 style="font-weight: 700; margin-top: 0; color: #0f172a;">1. APPOINTMENT & ENGAGEMENT SCOPE</h4>
            <p>By registering, you are appointed as an independent EduSphere Commercial Sales Representative. You are authorized to introduce, present, and onboard educational institutions (nursery, primary, and secondary schools) to the EduSphere Cloud School Management & Portal Platform.</p>

            <h4 style="font-weight: 700; color: #0f172a;">2. COMMISSION EARNING & DUAL REVENUE COMPENSATION</h4>
            <p>EduSphere guarantees verified commercial partners two robust revenue streams:</p>
            <ul>
                <li><strong>Initial Term Portal Fee / Subscription:</strong> Fifteen percent (15.0%) of the gross collections received from your registered school for their inaugural term or annual subscription package.</li>
                <li><strong>Recurring Term Royalty:</strong> Seven and a half percent (7.5%) recurring commission on all subsequent academic terms for as long as the school remains an active tenant on EduSphere.</li>
                <li><strong>Calculation Basis:</strong> Calculated automatically by the Commission Engine upon successful reconciliation of student portal fees or school licensing invoices.</li>
            </ul>

            <h4 style="font-weight: 700; color: #0f172a;">3. ANTI-COLLUSION & GOVERNANCE COMPLIANCE</h4>
            <p>EduSphere adheres strictly to automated compliance governance. Sales representatives are prohibited from self-approving commissions. All payouts must be audited and verified by a Sales Manager or Central Super Administrator. Any fraudulent prospect filings or collusion with school bursars will lead to immediate forfeiture of pending balances and account termination.</p>

            <h4 style="font-weight: 700; color: #0f172a;">4. STUDENT & PARENT DATA PRIVACY (NDPR COMPLIANCE)</h4>
            <p>Representatives shall not collect, extract, or retain unauthorized personal student or parent contact data. All school submissions must reflect genuine administrative decision-makers (Proprietor, Principal, or Bursar).</p>

            <h4 style="font-weight: 700; color: #0f172a;">5. DIRECT SETTLEMENT & BANK DISBURSEMENT</h4>
            <p>Commissions are disbursed electronically via NIP bank wire to the Nigerian commercial bank account provided during this registration. Partner is solely responsible for ensuring the account name matches their registered legal identity.</p>
        </div>

        <!-- Agreement Checkboxes -->
        <div style="background: #f1f5f9; border-radius: 0.5rem; padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
            <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer; font-size: 0.8125rem; color: #1e293b;">
                <input type="checkbox" name="agree_terms" value="1" required style="margin-top: 0.2rem; width: 16px; height: 16px; accent-color: #2563eb;">
                <span><strong>I have read, understood, and accept</strong> the EduSphere Commercial Sales Partner Agreement (v1.0) and agree to abide by its commission policies and terms.</span>
            </label>

            <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer; font-size: 0.8125rem; color: #1e293b;">
                <input type="checkbox" name="agree_conduct" value="1" required style="margin-top: 0.2rem; width: 16px; height: 16px; accent-color: #2563eb;">
                <span><strong>I certify that the prospective school</strong> details I am providing below represent authentic, actively operating educational institutions directly contacted or solicited by me.</span>
            </label>

            <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer; font-size: 0.8125rem; color: #1e293b;">
                <input type="checkbox" name="agree_payout" value="1" required style="margin-top: 0.2rem; width: 16px; height: 16px; accent-color: #2563eb;">
                <span><strong>I confirm that my settlement bank account</strong> details below are valid and correspond to my legal identity for receiving automated electronic commission payouts.</span>
            </label>
        </div>
    </div>

    <!-- ================= SECTION 2: REPRESENTATIVE IDENTITY & BANKING ================= -->
    <div class="section-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="step-pill" style="background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8;">
                    Phase 2
                </span>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0;">
                    Sales Representative Identity & Payout Account
                </h3>
            </div>
            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Personal & Banking Profile</span>
        </div>

        <div class="form-grid-3">
            <div class="form-group">
                <label class="form-label">Full Name <span style="color: #ef4444;">*</span></label>
                <input type="text" name="rep_name" class="form-control" placeholder="e.g. Babatunde Adeyemi" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address (Login Username) <span style="color: #ef4444;">*</span></label>
                <input type="email" name="rep_email" class="form-control" placeholder="babatunde@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Phone / WhatsApp Number <span style="color: #ef4444;">*</span></label>
                <input type="tel" name="rep_phone" class="form-control" placeholder="0803 123 4567" required>
            </div>
        </div>

        <div class="form-grid-3" style="margin-top: 0.5rem;">
            <div class="form-group">
                <label class="form-label">Primary Operating Territory / State <span style="color: #ef4444;">*</span></label>
                <select name="territory" class="form-control" required>
                    <option value="">Select Territory</option>
                    <option value="Lagos (Ikeja & Mainland)">Lagos (Ikeja & Mainland)</option>
                    <option value="Lagos (Island & Lekki)">Lagos (Island & Lekki)</option>
                    <option value="Abuja FCT">Abuja FCT</option>
                    <option value="Oyo (Ibadan & Environs)">Oyo (Ibadan & Environs)</option>
                    <option value="Ogun (Abeokuta & Ota)">Ogun (Abeokuta & Ota)</option>
                    <option value="Rivers (Port Harcourt)">Rivers (Port Harcourt)</option>
                    <option value="Kano State">Kano State</option>
                    <option value="Kaduna State">Kaduna State</option>
                    <option value="Enugu State">Enugu State</option>
                    <option value="Delta State (Warri & Asaba)">Delta State (Warri & Asaba)</option>
                    <option value="Edo (Benin City)">Edo (Benin City)</option>
                    <option value="Other Regional State">Other Regional State</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Create CRM Password <span style="color: #ef4444;">*</span></label>
                <input type="password" name="rep_password" class="form-control" placeholder="At least 6 characters" minlength="6" required>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password <span style="color: #ef4444;">*</span></label>
                <input type="password" name="rep_password_confirm" class="form-control" placeholder="Re-enter password" minlength="6" required>
            </div>
        </div>

        <!-- Bank Details Sub-Box -->
        <div style="margin-top: 1rem; padding: 1rem; background: #faf5ff; border: 1px dashed #d8b4fe; border-radius: 0.5rem;">
            <div style="font-weight: 700; color: #6b21a8; font-size: 0.8125rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                <span>Settlement Bank Account for Commission Payouts (Direct NIP Transfer)</span>
            </div>

            <div class="form-grid-3">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.75rem;">Bank Name <span style="color: #ef4444;">*</span></label>
                    <select name="bank_name" class="form-control" required>
                        <option value="">Select Bank</option>
                        <option value="Access Bank">Access Bank</option>
                        <option value="GTBank (Guaranty Trust)">GTBank (Guaranty Trust)</option>
                        <option value="Zenith Bank">Zenith Bank</option>
                        <option value="First Bank of Nigeria">First Bank of Nigeria</option>
                        <option value="United Bank for Africa (UBA)">United Bank for Africa (UBA)</option>
                        <option value="Fidelity Bank">Fidelity Bank</option>
                        <option value="FCMB">FCMB</option>
                        <option value="Stanbic IBTC">Stanbic IBTC</option>
                        <option value="Union Bank">Union Bank</option>
                        <option value="Sterling Bank">Sterling Bank</option>
                        <option value="Wema Bank">Wema Bank</option>
                        <option value="Kuda Bank">Kuda Bank</option>
                        <option value="Moniepoint Microfinance Bank">Moniepoint MFB</option>
                        <option value="OPay Digital Services">OPay</option>
                        <option value="Palmpay">Palmpay</option>
                        <option value="Other Commercial Bank">Other Commercial Bank</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.75rem;">NUBAN Account Number (10 digits) <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="account_number" class="form-control" placeholder="0123456789" maxlength="10" pattern="\d{10}" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.75rem;">Account Holder Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="account_name" class="form-control" placeholder="e.g. Babatunde Adeyemi" required>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= SECTION 3: PROSPECTIVE SCHOOL DOSSIER ================= -->
    <div class="section-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="step-pill" style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;">
                    Phase 3
                </span>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0;">
                    Initial Prospective School You Are Bringing Onboard
                </h3>
            </div>
            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">First CRM Lead Dossier</span>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">School Official Name <span style="color: #ef4444;">*</span></label>
                <input type="text" name="school_name" class="form-control" placeholder="e.g. Corona Crest Academy" required>
            </div>

            <div class="form-group">
                <label class="form-label">School Category <span style="color: #ef4444;">*</span></label>
                <select name="school_type" class="form-control" required>
                    <option value="combined">Combined (Nursery, Primary & Secondary)</option>
                    <option value="secondary">Senior / Junior Secondary School</option>
                    <option value="primary">Nursery & Primary School</option>
                </select>
            </div>
        </div>

        <div class="form-grid-3" style="margin-top: 0.5rem;">
            <div class="form-group">
                <label class="form-label">School Location State <span style="color: #ef4444;">*</span></label>
                <input type="text" name="school_state" class="form-control" placeholder="e.g. Lagos" required>
            </div>

            <div class="form-group">
                <label class="form-label">School Address / Town</label>
                <input type="text" name="school_address" class="form-control" placeholder="e.g. 14 Admiralty Way, Lekki Phase 1">
            </div>

            <div class="form-group">
                <label class="form-label">Estimated Student Population <span style="color: #ef4444;">*</span></label>
                <input type="number" name="estimated_students" id="estimatedStudentsInput" class="form-control" value="300" min="20" max="10000" required>
            </div>
        </div>

        <div class="form-grid-3" style="margin-top: 0.5rem;">
            <div class="form-group">
                <label class="form-label">Key Contact Person / Decision Maker <span style="color: #ef4444;">*</span></label>
                <input type="text" name="contact_person" class="form-control" placeholder="e.g. Mrs. Funke Adeleke (Proprietress)" required>
            </div>

            <div class="form-group">
                <label class="form-label">Decision Maker Phone <span style="color: #ef4444;">*</span></label>
                <input type="tel" name="school_phone" class="form-control" placeholder="0802 987 6543" required>
            </div>

            <div class="form-group">
                <label class="form-label">School Official Email</label>
                <input type="email" name="school_email" class="form-control" placeholder="info@coronacrest.example">
            </div>
        </div>

        <div class="form-grid-2" style="margin-top: 0.5rem;">
            <div class="form-group">
                <label class="form-label">Current School Management System</label>
                <select name="current_system" class="form-control">
                    <option value="Paper report cards & manual registers">Paper report cards & manual registers</option>
                    <option value="Excel spreadsheets & manual typing">Excel spreadsheets & manual typing</option>
                    <option value="Legacy offline desktop software">Legacy offline desktop software</option>
                    <option value="Competitor web portal (Dissatisfied)">Competitor web portal (Dissatisfied)</option>
                    <option value="Brand new school setup">Brand new school setup</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Discovery Notes & Key Requirements</label>
                <input type="text" name="notes" class="form-control" placeholder="e.g. Wants automated scratch cards and terminal PDF report generation">
            </div>
        </div>
    </div>

    <!-- Submit Action Button -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; padding-top: 0.5rem;">
        <div style="font-size: 0.8125rem; color: #64748b;">
            🔒 By clicking register, your digital signature and IP address will be recorded on the partner agreement.
        </div>
        <button type="submit" class="btn btn-primary" style="padding: 0.85rem 2rem; font-size: 0.9375rem; font-weight: 700; background: linear-gradient(135deg, #10b981, #059669); border: none;">
            <span>Complete Registration & Launch CRM Pipeline</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
    </div>
</form>

<script>
    // Dynamic commission preview calculator
    const studentsInput = document.getElementById('estimatedStudentsInput');
    const display = document.getElementById('estCommDisplay');

    function updatePreview() {
        if (!studentsInput || !display) return;
        const count = parseInt(studentsInput.value, 10) || 0;
        // Assume standard ₦1,500 portal fee per pupil per term
        // 15% commission = ₦225 per pupil
        const estAmount = Math.round(count * 1500 * 0.15);
        display.textContent = '₦' + estAmount.toLocaleString();
    }

    if (studentsInput) {
        studentsInput.addEventListener('input', updatePreview);
        updatePreview();
    }
</script>
