# EduSphere SaaS &mdash; Complete End-to-End User & Operator Manual

Welcome to **EduSphere SaaS**, a comprehensive multi-tenant School Management Software-as-a-Service platform engineered for nursery, primary, and secondary schools.

This manual covers the complete lifecycle of the platform &mdash; from installation and initial tenant provisioning to classroom operations, financial management, examination ranking, report card compilation, and family portal access.

---

## Table of Contents

1. [System Architecture & Multi-Tenancy](#1-system-architecture--multi-tenancy)
2. [Prerequisites & Initial Setup](#2-prerequisites--initial-setup)
3. [Master Credentials & Demo Accounts Directory](#3-master-credentials--demo-accounts-directory)
4. [Super Administrator Platform Operations (HQ)](#4-super-administrator-platform-operations-hq)
5. [Commercial Sales CRM & Commission Engine](#5-commercial-sales-crm--commission-engine)
6. [School Administrative Setup & Academic Governance](#6-school-administrative-setup--academic-governance)
7. [Admissions & Student Enrollment Lifecycle](#7-admissions--student-enrollment-lifecycle)
8. [Faculty & Staff Management](#8-faculty--staff-management)
9. [Daily Classroom Attendance Register](#9-daily-classroom-attendance-register)
10. [Assessments, WAEC 9-Point Grading & PDF Report Cards](#10-assessments-waec-9-point-grading--pdf-report-cards)
11. [School Bursary, Fee Invoicing & Payment Verification](#11-school-bursary-fee-invoicing--payment-verification)
12. [Institutional Notice Board Management](#12-institutional-notice-board-management)
13. [Direct Two-Way School-Parent Messaging Desk](#13-direct-two-way-school-parent-messaging-desk)
14. [Public School Website & Integrated CMS](#14-public-school-website--integrated-cms)
15. [Student Portal Operations & Online Fee Payments](#15-student-portal-operations--online-fee-payments)
16. [Parent / Guardian Family Portal & Direct Messaging](#16-parent--guardian-family-portal--direct-messaging)
17. [Security, NDPA 2023 Compliance & Cross-Tenant Guards](#17-security-ndpa-2023-compliance--cross-tenant-guards)
18. [Verification & Automated Test Suite Execution](#18-verification--automated-test-suite-execution)

---

## 1. System Architecture & Multi-Tenancy

EduSphere operates on an isolated multi-tenant architecture designed to host hundreds of schools on a single unified deployment while guaranteeing strict data boundaries:

- **Tenant Resolution Modes:**
  1. **Path/Slug Routing (Default / Localhost):** `http://localhost/schoolportals/site/{slug}` or `/school` with session tenant context.
  2. **Subdomain Routing:** `apex.edusphere.example` automatically binds to School ID `1`.
  3. **Custom Domain Routing:** `portal.myschool.edu.ng` resolves to the verified tenant via `tenant_domains`.
- **Tenant Data Isolation:** Every query scoped to tenant data automatically binds `school_id = ?`. Cross-tenant URL tampering (IDOR attacks) returns **HTTP 403 Forbidden** and triggers an immutable security audit event.
- **Role-Based Access Control (RBAC):** Ten distinct roles enforce separation of duties across central administration, school leadership, teaching faculty, bursary, sales, students, and parents.

---

## 2. Prerequisites & Initial Setup

### System Requirements
- **Web Server:** Apache 2.4+ (with `mod_rewrite` enabled) or Nginx
- **PHP Version:** PHP 8.2 or higher (Extensions required: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `curl`)
- **Database:** MySQL 8.0+ or MariaDB 10.5+
- **Dependency Manager:** Composer

### Installation Steps

1. **Clone/Place Codebase:**
   Ensure project resides in `c:/xampp/htdocs/schoolportals`.

2. **Environment Configuration:**
   Copy `.env.example` to `.env` and verify database parameters:
   ```env
   APP_NAME="EduSphere SaaS"
   APP_ENV=development
   APP_DEBUG=true
   APP_URL="http://localhost/schoolportals"
   APP_TIMEZONE="Africa/Lagos"

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=edusphere_saas
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Install Dependencies:**
   ```bash
   composer install
   ```

4. **Initialize Database & Seed Realistic Demo Data:**
   Run the database schema migration and seeder:
   ```bash
   C:\xampp\php\php.exe database/migrate.php
   C:\xampp\php\php.exe database/seeders/DatabaseSeeder.php
   ```

---

## 3. Master Credentials & Demo Accounts Directory

All seeded demo accounts share the uniform default password: **`password123`**

### Platform Super Administration & Commercial Sales
| Role | Name | Email Address | Dashboard URL |
|---|---|---|---|
| **Super Admin** | Dr. Marcus Adebayo | `superadmin@edusphere.example` | `/superadmin` |
| **Sales Manager** | Victoria Eke | `salesmanager@edusphere.example` | `/sales` |
| **Sales Rep** | Tunde Bakare | `salesrep@edusphere.example` | `/sales` |

### School 1: Apex Crown College (Combined Secondary School, ID: 1)
| Role | Name | Email Address | Dashboard URL |
|---|---|---|---|
| **School Admin** | Olawale Sanusi | `admin@apexcollege.edu.ng` | `/school` |
| **Principal** | Mrs. Folake Folorunsho | `principal@apexcollege.edu.ng` | `/school` |
| **Registrar** | Mr. Godwin Okon | `registrar@apexcollege.edu.ng` | `/school/admissions` |
| **Teacher** | Mr. Chidi Nwosu | `teacher@apexcollege.edu.ng` | `/school/attendance` |
| **Bursar** | Mrs. Zainab Aliyu | `bursar@apexcollege.edu.ng` | `/school/finance` |
| **Student** | Chinedu Okafor | `student@apexcollege.edu.ng` | `/portal/student` |
| **Parent** | Chief Emeka Okafor | `parent@apexcollege.edu.ng` | `/portal/parent` |

### School 2: Horizon International Academy (Nursery & Primary School, ID: 2)
| Role | Name | Email Address | Dashboard URL |
|---|---|---|---|
| **School Admin** | Hajiya Amina Bello | `admin@horizonacademy.sch.ng` | `/school` |
| **Teacher** | Mr. Emmanuel Damilola | `teacher@horizonacademy.sch.ng` | `/school/attendance` |
| **Student** | Zainab Garba | `student@horizonacademy.sch.ng` | `/portal/student` |
| **Parent** | Alhaji Musa Garba | `parent@horizonacademy.sch.ng` | `/portal/parent` |

> **Pro Tip:** In local testing, you can instantly log in to any account by visiting:  
> `http://localhost/schoolportals/demo-login?email={EMAIL}`

---

## 4. Super Administrator Platform Operations (HQ)

The Super Administrator portal manages the SaaS business, tenant schools, and security logs.

### Accessing the Portal
1. Navigate to `/login` and sign in with `superadmin@edusphere.example`.
2. The dashboard displays platform Monthly Recurring Revenue (MRR), total active school institutions, enrolled student count, and payment transaction volume.

### Provisioning a New School Tenant
1. Click **Schools Directory** in the navigation bar (`/superadmin/schools`).
2. Click the **+ Onboard New School** button.
3. Fill in the required details:
   - **School Full Name** (e.g., `Prestige International College`)
   - **Unique URL Slug** (e.g., `prestige`)
   - **School Motto & Institution Category** (Nursery, Primary, Secondary, or Combined)
   - **Subscription Plan** (Standard, Premium, or Multi-Campus)
   - **Principal/Administrator Contact Email & Phone**
4. Click **Create School Tenant**. The platform provisions tenant records, assigns the subscription plan, and creates the default school administrator.

### Mapping a Custom Apex Domain
1. In the Schools Directory (`/superadmin/schools`), locate the school card.
2. Click the **Custom Domain** button.
3. Enter the fully qualified domain (e.g., `portal.prestigecollege.edu.ng`).
4. Click **Save Domain Mapping**. The platform maps the hostname to the tenant ID.

### Portal Billing & Revenue Management (Per-Student Term Model)
1. Navigate to **Portal Billing & Revenue** (`/superadmin/portal-billing`).
2. **Central Telemetry Dashboard:** View total eligible students, billed students, total invoiced, verified collections, collection rate (%), platform revenue (45%), school incentives accrued (40%), and sales commissions (15%).
3. **Configuring Portal Pricing Policies:**
   - Click **+ Configure Pricing Policy**.
   - Input Policy Code (e.g. `TERM-FEE-2026`), Fee per student (default: `₦3,000.00`), Currency (`NGN`), and select Global Default or specific school scope.
   - Click **Publish Pricing Policy**. Existing historical agreements remain intact.
4. **Authorizing School Incentive Payouts:**
   - In the Payout Requests table, review pending bank disbursement requests submitted by schools.
   - Click **Approve & Disburse**, confirm bank details, enter NIP wire reference (e.g. `DISB-NIP-20261013-001`), and submit.
   - The system automatically debits the school's ledger balance and updates transaction status.

### Inspecting NDPA 2023 Audit Trails
1. Navigate to **Audit Logs** (`/superadmin/audit-logs`).
2. Review immutable audit logs documenting all critical platform events: login successes, student enrollments, exam approvals, financial disbursements, and cross-tenant access attempts.

---

## 5. Commercial Sales CRM & Commission Engine

The built-in Sales CRM drives school acquisitions and automates field representative commissions.

### 1. Sales Partner Self-Registration & Prospective School Onboarding
External representatives can self-register at `/become-a-sales-rep` (or `/sales/register`):
1. **Commercial Partner Agreement (v1.0):** Rep reviews transparent earning terms (**15.0% initial term**, **7.5% recurring**), anti-collusion rules, NDPR student data protection guidelines, and accepts three compliance checkboxes.
2. **Representative Identity & Banking:** Submits name, email, phone, territory, password, and Nigerian commercial bank account details (NUBAN) for automated commission disbursements.
3. **Initial School Dossier:** Submits their first prospect school (school name, category, location, decision maker, and estimated pupil population).
4. **Auto-Provisioning:** The system atomically creates the `sales_rep` user, generates an immutable rep referral code (`REP-TER-XXXX`), archives the digitally signed agreement snapshot with IP audit trail in `sales_partner_agreements`, automatically logs the prospect in `sales_leads`, and signs the rep into `/sales`.

### 2. Managing Leads & The Visual Kanban Pipeline
1. Log in as Sales Rep (`salesrep@edusphere.example`) or Sales Manager (`salesmanager@edusphere.example`).
2. Navigate to **CRM Pipeline** (`/sales/pipeline`).
3. To add a lead, click **+ Add Lead** in the **Prospective Directory** (`/sales/leads`), entering school name, contact person, phone, estimated pupil count, and deal stage.
4. Move deals through stages:
   `New Lead` &rarr; `Contacted` &rarr; `Demo Scheduled` &rarr; `Proposal Sent` &rarr; `Negotiation` &rarr; `Won / Active`

### Commission Calculation & Anti-Collusion Approval
1. **Dual Commission Streams:**
   - **Subscription Plan Sales:** Fixed percentage based on package tier.
   - **Term Portal Fee Collections:** Direct field representatives earn **15.0%** on initial academic term portal fees collected from their onboarded schools, and **7.5%** on subsequent recurring terms.
2. When a parent pays a portal fee invoice, financial allocation engines automatically credit the assigned sales representative's commission ledger (`/sales/commissions`).
3. **Strict Rule of Anti-Collusion Compliance:** A sales representative cannot approve their own commission. Attempting to approve one's own commission triggers an immediate compliance exception: `"Compliance Violation: You cannot approve your own sales commission"`.
4. The **Sales Manager** reviews the ledger, verifies the transaction reference and mathematical split, and clicks **Approve Commission**.
5. Once paid out via bank wire, click **Mark Paid** to assign an immutable bank disbursement reference (e.g. `DISB-COMM-NIP-20261013-001`).

---

## 6. School Administrative Setup & Academic Governance

When a school begins operations, the School Admin sets up academic governance in `/school/academics`.

### 1. Creating Academic Sessions
1. Navigate to **Academic Structure** (`/school/academics`).
2. Click **+ Add Session**.
3. Specify session name (e.g., `2025/2026`), start date, end date, and mark **Set as Current Session**.
4. Click **Create Session**.

### 2. Creating Academic Terms
1. In the terms section of `/school/academics`, review or configure the three standard terms: First Term, Second Term, and Third Term.

### 3. Setting Up Class Levels & Class Arms
1. **Class Levels:** Verify or add educational tiers (e.g., `JSS 1`, `JSS 2`, `SSS 1`, `Primary 1`).
2. **Class Arms:** Click **+ Add Class Arm**.
   - Select Class Level (e.g., `JSS 1`)
   - Arm Name (e.g., `Gold`, `Diamond`, `Emerald`)
   - Maximum Capacity (e.g., `35` pupils)
   - Assign Class Teacher from the faculty roster
3. Click **Create Arm**.

### 4. Configuring Subject Catalog
1. Click **+ Add Subject** in `/school/academics`.
2. Input Subject Code (e.g., `MTH`, `ENG`, `PHY`), Subject Title, and Category (`Core` or `Elective`).
3. Click **Create Subject**.

### 5. Term Portal Fee Governance & School Incentive Program
Schools collect portal fees per student each academic term and earn a guaranteed **40% School Incentive** on every verified payment.
1. Navigate to **Term Portal Fee** (`/school/term-portal-fee`).
2. **Reviewing Fee Agreement:**
   - Review current term pricing agreement (e.g. `₦3,000.00 / student` for Apex Crown College, or negotiated rate).
   - Click **Accept Agreement** or reject if terms are pending renegotiation with the Super Admin.
3. **Broadcasting Parent Billing Notice:**
   - Click **Broadcast Notice to Parents**.
   - Input Notice Title, formal payment instructions, and optional penalty/grace period dates.
   - Click **Publish Notice**. The announcement displays prominently across all parent dashboards.
4. **Idempotent Invoice Generation:**
   - Once student enrollment is stabilized, click **Generate Term Invoices**.
   - The engine automatically issues itemized term portal fee invoices (e.g. `PTL-INV-20261-0001`) for all actively enrolled students.
   - **Zero Duplicate Billing Guarantee:** Re-clicking the generation button safely scans for unbilled students and skips already-invoiced pupils using an SHA-256 cryptographic idempotency hash.
5. **School Incentive Balance & Bank Payout Requests:**
   - Navigate to **Incentive Ledger** (`/school/incentives`).
   - Track total term collections, accrued 40% incentive earnings, pending payouts, and net withdrawable balance.
   - Click **Request Incentive Payout**, specify the withdrawal amount (e.g., `₦50,000.00`), provide corporate bank account details (Bank Name, Account Number, Account Name), and submit.
   - Once authorized by the platform Super Admin, funds are disbursed via direct bank transfer and the ledger is reconciled with a wire transfer reference.

---

## 7. Admissions & Student Enrollment Lifecycle

EduSphere provides two distinct pathways for student intake:

### Pathway A: Online Public Entrance Application (Self-Service)
1. Prospective parents visit `http://localhost/schoolportals/site/{slug}/apply`.
2. Fill out student biodata, date of birth, previous school attended, parent/guardian contact, and target entry class.
3. Submit the application. A unique registration code is generated.
4. School Registrar navigates to **Admissions Desk** (`/school/admissions`).
5. Review applicant profile and click **Admit Student**.
6. Select the assigned Class Arm. The system automatically enrolls the pupil and generates their unique admission number (e.g., `APX/2025/008`).

### Pathway B: Direct Manual Student Enrollment
1. School Admin navigates to **Students Directory** (`/school/students`).
2. Click **+ Enroll New Student**.
3. Fill in:
   - First, Middle, and Last Name
   - Date of Birth & Gender
   - Blood Group & Genotype (AA, AS, SS)
   - Target Class Arm (e.g., `JSS 1 Gold`)
   - Parent / Guardian Name, Email, and Phone Number
4. Click **Enroll Student**. The system persists the pupil record, creates a linked parent profile, and assigns the next sequential admission number.

---

## 8. Faculty & Staff Management

Manage teaching staff and administrative personnel in `/school/staff`.

1. Navigate to **Faculty & Staff** (`/school/staff`).
2. Click **+ Onboard Staff Member**.
3. Provide:
   - Full Name & Email Address
   - Phone Number & Residential Address
   - Department (Administration, Sciences, Arts, Bursary)
   - Designation (e.g., `Senior Mathematics Tutor`, `Bursar`)
   - Educational Qualifications (e.g., `B.Sc Ed Mathematics`)
   - Monthly Base Salary
   - System Portal Role (`teacher`, `bursar`, `registrar`, `principal`)
4. Click **Onboard Staff**. The system creates the portal user account and issues a staff ID (e.g., `APX-EMP-006`).

---

## 9. Daily Classroom Attendance Register

Class teachers track pupil presence daily in `/school/attendance`.

1. Log in as a teacher or school admin.
2. Navigate to **Daily Attendance** (`/school/attendance`).
3. Select the **Class Arm** (e.g., `JSS 1 Gold`) and the **Date**.
4. The system loads the roster of all enrolled students in that class arm.
5. For each student, select their attendance status:
   - `Present` (Default)
   - `Absent`
   - `Late`
   - `Excused`
6. Click **Save Attendance Register**.
7. The system updates student attendance aggregates, which feed directly into terminal report cards and parent portal summaries.

---

## 10. Assessments, WAEC 9-Point Grading & PDF Report Cards

EduSphere incorporates continuous assessment and terminal examination grading aligned with standard educational benchmarks.

### Continuous Assessment (CA) Structure
The terminal grade is composed of:
$$\text{Total Score (100\%)} = \text{CA 1 (20\%)} + \text{CA 2 (20\%)} + \text{Terminal Examination (60\%)}$$

### WAEC 9-Point Grading Scale
| Total Score Range | Grade | Description | Grade Point |
|---|---|---|---|
| **75.00% &ndash; 100.00%** | **A1** | Distinction | 4.00 |
| **70.00% &ndash; 74.99%** | **B2** | Very Good | 3.50 |
| **65.00% &ndash; 69.99%** | **B3** | Good | 3.00 |
| **60.00% &ndash; 64.99%** | **C4** | Credit | 2.50 |
| **55.00% &ndash; 59.99%** | **C5** | Credit | 2.00 |
| **50.00% &ndash; 54.99%** | **C6** | Credit | 1.50 |
| **45.00% &ndash; 49.99%** | **D7** | Pass | 1.00 |
| **40.00% &ndash; 44.99%** | **E8** | Pass | 0.50 |
| **0.00% &ndash; 39.99%** | **F9** | Fail | 0.00 |

### Entering Class Marksheets
1. Navigate to **Examinations & CA** (`/school/exams`).
2. Select the **Class Arm** and the **Subject** (e.g., `JSS 1 Gold` & `Mathematics`).
3. Input CA 1 (out of 20), CA 2 (out of 20), and Exam marks (out of 60).
4. Click **Save & Compute Marksheet**.
5. The system computes total scores, applies letter grades, assigns performance remarks, and updates class position rankings (`#1 of 35`).

### Compiling & Downloading Official PDF Report Cards
1. From the Students Directory (`/school/students`), click **View Report Card** or navigate directly to `/school/students/{id}/report-card`.
2. The system renders a branded report card compiled via **Dompdf**, featuring:
   - Official School Header, Crest & Motto
   - Pupil Biodata, Admission Number, Class & Term
   - Complete Subject-by-Subject Score Breakdown (CA1, CA2, Exam, Total, Grade, Class Average, Position in Subject)
   - Aggregate Term Average & Overall Class Position
   - Attendance Summary (Days Present / Total Sessions)
   - Form Teacher & Principal Digital Endorsements

---

## 11. School Bursary, Fee Invoicing & Payment Verification

Manage school tuition fees, billing, and reconciliations in `/school/finance`.

### 1. Issuing Student Tuition Invoices
1. Navigate to **Bursary & Accounts** (`/school/finance`).
2. Click **+ Issue Student Invoice**.
3. Select the Student, Class Arm, and specify:
   - Tuition Fee Amount
   - Ancillary Fees (Development Levy, ICT/Science Lab, Uniforms, Books)
   - Due Date
4. Click **Generate Invoice**. An invoice is created (e.g., `INV-2025-001`) with balance due.

### 2. Manual Payment Recording (Cash, POS, Direct Bank Transfer)
1. In `/school/finance`, locate the student's pending invoice.
2. Click **Record Payment**.
3. Enter amount paid, payment method (`Bank Transfer`, `Cash`, `POS`, `Cheque`), and transaction reference.
4. Click **Submit Payment Receipt**.
5. The system decrements the invoice balance due (marking it `partially_paid` or `paid`) and issues an official receipt (`REC-2025-001`).

### 3. Online Payments via Paystack
1. In the Student or Parent Portal, users can view outstanding invoices and click **Pay Online via Paystack**.
2. Upon completing checkout, the Paystack verification endpoint confirms transaction authenticity and decrements invoice balances in real time.

### 4. Printing & Downloading Official PDF Bills & Receipt Slips
1. **Official Fee Bill (Invoice PDF):** In `/school/finance`, click **Print Bill** next to any student fee invoice. Generates a formal A4 invoice with composite fee schedules, payment history, and school bank remittance instructions.
2. **Official Payment Receipt (Receipt PDF):** In the **Verified Official Payment Receipts** table, click **Print Receipt**. Generates a formal bursary voucher featuring school letterhead, amount in figures & words, verification watermark stamp, and authorized cashier signature line.

---

## 12. Institutional Notice Board Management

The **School Notice Board** (`/school/notices`) provides a centralized circular and announcement dispatch system with audience targeting, priority visual alerts, and automated feed integration.

### Publishing an Announcement
1. Navigate to **Notice Board** (`/school/notices`) from the school sidebar.
2. Click **+ Publish Institutional Notice**.
3. Fill out the notice composition form:
   - **Notice Title:** e.g., *"Resumption Guidelines for 2nd Term 2025/2026 Academic Session"*.
   - **Category:** Select from *General Bulletin*, *Academic & Examinations*, *Holiday & Mid-Term Break*, *Campus Events & Visiting Day*, *Inter-House Sports*, *PTA & Board Meeting*, or *Urgent Circular*.
   - **Priority:** *Normal* (Blue border), *Important* (Amber border), or *Urgent* (Red border).
   - **Target Audience:**
     - **All School Community (`all`):** Broadcasts to school staff, all student dashboards, and all parent portals.
     - **Parents Only (`parents`):** Displayed exclusively on Parent Family Dashboards.
     - **Students Only (`students`):** Displayed exclusively on Student Portals.
     - **Staff Faculty Only (`staff`):** Visible only within internal faculty administration desks.
   - **Pin to Top:** Keeps the circular permanently pinned to the top of the notice board regardless of publication date.
   - **Expiry Date:** Optional cutoff date after which the announcement archives automatically.
4. Click **Publish Institutional Notice**.

---

## 13. Direct Two-Way School-Parent Messaging Desk

The **Parent Communication Desk** (`/school/messages`) provides private, threaded, 1-on-1 messaging between school leadership/teachers and parents of enrolled students, replacing uncoordinated WhatsApp chats and SMS fees.

### School Administration Dispatches
1. Navigate to **Parent Messages** (`/school/messages`) in the faculty menu.
2. Click **+ Send Message to Parent**.
3. Select the target parent (linked with student name and admission number), enter the subject (e.g., *"Academic Commendation"*, *"Medical Consultation"*, or *"Homework Guidance"*), write the message, and click **Dispatch Message**.
4. The message appears instantly in the parent's portal with an unread badge counter.

### Parent Replies & Thread History
- In the communication console, click any active conversation thread on the left pane to view full chronological conversation history with sender role badges.
- Type in the reply editor and click **Send Reply to Parent** to maintain an unbroken audit trail.

---

## 14. Public School Website & Integrated CMS

Each tenant school has a dedicated public website showcasing their academic identity.

### Viewing the Public Site
- Visit `http://localhost/schoolportals/site/{slug}` (e.g., `http://localhost/schoolportals/site/apex`).
- Public pages available:
  - **Home:** Hero banner, value propositions, key statistics (e.g., "98.4% WAEC Pass Rate").
  - **About:** School history, leadership message, vision & core values.
  - **Academics:** Educational philosophy, curriculum overview, facilities.
  - **Admissions:** Entry guidelines, tuition schedules, application portal link.
  - **Contact:** Campus map, phone, email, and interactive inquiry form.

### Editing Content via the CMS
1. Log in as School Admin and navigate to **Website CMS** (`/school/website-cms`).
2. Modify hero headline, sub-headline, welcome message, contact phone, or campus address.
3. Click **Publish Website Changes** to update the live public site.

---

## 15. Student Portal Operations & Online Fee Payments

Empower learners to monitor their personal academic journey and settle dues online.

1. Sign in with student credentials (e.g., `student@apexcollege.edu.ng`).
2. **Dashboard Features:**
   - **Official Notice Board:** Top bulletin widget showing institutional notices targeted to `students` and `all`.
   - **Personal Attendance Rate:** Visual gauge of term attendance percentage.
   - **Academic Marksheet:** Published scores across all enrolled subjects.
   - **Terminal Performance Summary:** Term average, class rank, and faculty appraisal remarks.
   - **Report Card Download:** 1-click download of the official terminal report card PDF.
3. **Tuition & School Fee Online Settlement:**
   - Review pending invoices in the **School Fees & Tuition Invoices** table.
   - Click **Pay Online** next to any outstanding invoice.
   - Enter payment amount (supports partial or full settlement) and confirm Paystack simulated or live card checkout.
   - Invoice balance decrements instantly in real-time, and an official **Verified Payment Receipt** (`REC-...`) is generated.
4. **Digital Portal Access Fee Payment:**
   - Review the **Digital Portal Access & Cloud Fee** card.
   - Click **Pay Online** to settle the per-student term cloud access charge.
   - Automatically executes the 3-way revenue sharing split allocating platform and school incentive balances.
5. **Verified Payment Receipts & Printable Slip Ledger:**
   - Complete table of all verified bursary receipts and Paystack transaction references.
   - Click **Print Receipt** next to any payment to download or print an official PDF receipt slip featuring school letterhead, amount in words, and verification watermark.

---

## 16. Parent / Guardian Family Portal & Direct Messaging

Enable parents to supervise multiple wards and communicate directly with school authorities from a single unified family account.

1. Sign in with parent credentials (e.g., `parent@apexcollege.edu.ng`).
2. **Family Dashboard Features:**
   - **Direct School Communication Desk Banner:** Quick-action banner indicating unread messages count with 1-click navigation to `/portal/parent/messages`.
   - **Institutional Notice Board:** Live announcements feed for parents and general school community notices.
   - **Ward Selector:** Toggle between all children linked to this parent across classes.
   - **Ward Profile & Roster:** View class arm, assigned form tutor, and attendance rate.
   - **Term Examination Results:** Complete subject grade ledger with teacher remarks.
   - **Tuition & Ancillary Invoices:** Outstanding tuition balances and school bursary receipts.
   - **Official Report Card Download:** Instant access to Dompdf terminal report card.
3. **Two-Way Direct Messaging Desk (`/portal/parent/messages`):**
   - Click **+ Send Message to School** to open a new private inquiry regarding any of their linked children.
   - View ongoing communication threads with teachers, bursar, or principal.
   - Reply directly to school dispatches in real-time.
4. **Per-Student Term Portal Fee Payments:**
   - Located in the dedicated **Term Portal Maintenance Fees** section on the parent dashboard.
   - Lists each enrolled child, their active term invoice number (e.g. `PTL-INV-20261-0001`), invoice status (`unpaid`, `partially_paid`, `paid`), and balance due.
   - Click **Pay Online via Paystack** to open the embedded Paystack modal.
   - Enter payment amount (supports full or installment partial payments) and complete payment.
   - Upon confirmation, invoice status updates immediately, generating a verifiable payment receipt (`PTL-REC-...`).
5. **Submitting & Tracking Billing Disputes:**
   - If an invoice is disputed (e.g., duplicate charge or scholarship waiver), click **Dispute Fee**.
   - Input the reason and detailed dispute explanation, then submit.
   - The invoice transitions to `disputed` status with notice delivered to the school bursary desk for administrative review.

---

## 17. Security, NDPA 2023 Compliance & Cross-Tenant Guards

EduSphere implements comprehensive cybersecurity safeguards:

1. **Anti-IDOR Cross-Tenant Guard:**
   - Any attempt by a user from School A to query or mutate records from School B (such as downloading another school's report card, posting attendance, or viewing invoices) is rejected with **HTTP 403 Forbidden**.
2. **Immutable NDPA 2023 Audit Logs:**
   - Security incidents, administrative overrides, and data access requests are recorded in `audit_logs` with timestamp, actor user ID, client IP address, and metadata.
3. **Cross-Site Request Forgery (CSRF) Guard:**
   - All 43 POST forms include cryptographic CSRF tokens (`csrf_field()`), validated on submission.
4. **100% Prepared Statement Parameterization:**
   - All database queries use PDO prepared statements with `?` placeholders, eliminating SQL injection vectors.
5. **Cross-Portal RBAC Guards:**
   - Students attempting to enter administrative or parent portals are blocked with **HTTP 403 Forbidden**.

---

## 18. Verification & Automated Test Suite Execution

Validate platform health and compliance using the automated test suite:

### 1. Comprehensive Codebase Health & Oversight Scan
Scans PHP syntax across all 76 application files, 43 CSRF forms, portal isolation guards, navigation link integrity, SQL parameterization, database constraints, and live portal authentication:
```bash
C:\xampp\php\php.exe tests/codebase_health_check.php
```
*Expected Result:* `HEALTH CHECK SCAN SUMMARY: 14 Checks Run, 0 Issues Found`

### 2. Multi-Tenant Isolation & Anti-IDOR Security Suite
Verifies cross-tenant access barriers between Apex Crown College and Horizon Academy:
```bash
C:\xampp\php\php.exe tests/test_cross_tenant_security.php
```
*Expected Result:* `SECURITY TEST RESULTS: 21 PASSED, 0 FAILED`

### 3. Deep Endpoint & View Integrity Audit
Verifies HTTP 200 responses across all 26 public, administrative, and portal views:
```bash
C:\xampp\php\php.exe tests/test_all_endpoints.php
```
*Expected Result:* `AUDIT SUCCESS: All 26 endpoints returned HTTP 200 OK without errors!`

### 4. Per-Student Term Portal Billing & Revenue Sharing Engine Suite
Tests cryptographic SHA-256 idempotency deduplication, parent payment allocation, exact penny reconciliation, school incentive payout approvals, withdrawable balance protections, and anti-collusion commission barriers:
```bash
C:\xampp\php\php.exe tests/test_portal_billing.php
```
*Expected Result:* `PORTAL BILLING TEST RESULTS: 30 PASSED, 0 FAILED`

### 5. Institutional Notices, Direct Messaging & Student Fee Payments Suite
Tests notice board audience filtering, 2-way threaded parent-school messaging, student online tuition payment via Paystack, per-student portal access fee settlement, and anti-IDOR payment isolation:
```bash
C:\xampp\php\php.exe tests/test_notices_and_messaging.php
```
*Expected Result:* `TEST RESULTS: 27 PASSED, 0 FAILED`

---

*EduSphere SaaS &mdash; Next-Generation Multi-Tenant School Administration Engine*

