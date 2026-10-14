# EduSphere SaaS &mdash; Premium Multi-Tenant School Management Platform

**EduSphere** is an enterprise-grade, production-ready multi-tenant Software-as-a-Service (SaaS) school management and business administration engine engineered for nursery, primary, and secondary schools.

---

## 1. Key Architectural Capabilities

- **Strict Multi-Tenancy & Tenant Isolation**:
  - Central shared MySQL database with server-side tenant scoping (`school_id`).
  - Protection against cross-tenant data leakage and Insecure Direct Object References (IDOR).
  - Support for platform subdomains (e.g. `apex.edusphere.example`) and custom school domains (e.g. `apexschools.edu.ng`, `schoolname.sch.ng`).
  - Seamless URL and session tenant resolution in both production and local XAMPP environments (`/schoolportals/site/{slug}`, `?school={slug}`).

- **10 Granular Specialized Roles**:
  1. `super_admin`: Platform owner, SaaS metrics, tenant provisioning, subscription billing, and security audit logs.
  2. `school_admin`: School Head Administrator, institutional settings, faculty allocations.
  3. `principal`: Academic Director, result approvals, class promotions, executive commendations.
  4. `registrar`: Admissions officer, applicant review, student directory, registration numbers.
  5. `teacher`: Subject and class roll calls, continuous assessments (CA), examination marks entry.
  6. `bursar`: Tuition and fee structures, student billing invoices, payment receipt issuance, Paystack reconciliation.
  7. `student`: Student portal, personalized academic results, class schedules, fee invoice status.
  8. `parent`: Family guardian portal, ward performance tracking, attendance summaries, receipt downloads.
  9. `sales_rep`: Commercial representative CRM, prospect tracking, interactive Kanban pipeline, commission earnings.
  10. `sales_manager`: Platform commercial director, sales territory allocations, commission approval and payout approvals.

- **Continuous Assessment (CA) & Examination Engine**:
  - Configurable continuous assessment weightings: CA 1 (20) + CA 2 (20) + Terminal Exam (60) = 100%.
  - Standard WAEC/NECO distinction grading scales (A1, B2, B3, C4, C5, C6, D7, E8, F9).
  - Automated class ranking and position computation (`#1 of 35 students`).
  - Pixel-perfect, official printable PDF report cards generated server-side using **Dompdf**.

- **Complete Sales CRM & Incentive Commission Ledger**:
  - Full prospect pipeline with stages (*New Lead*, *Contacted*, *Qualified*, *Demo Scheduled*, *Proposal Sent*, *Negotiation*, *Won & Onboarded*, *Lost*).
  - Visual drag/stage interactive Kanban board.
  - Duplicate detection on phone, email, and institution name.
  - Multi-tier, percentage, and fixed commission calculation engine.
  - Traceable source subscription transactions.
  - Regulatory compliance rule: **Representatives can never approve their own commissions**.

- **Finance & Bursary Management**:
  - Tuition, development fund, and ICT lab fee structures by class level.
  - Sequential receipt numbering (`REC-2025-001`).
  - Integrated Paystack online verification with idempotency to prevent duplicate receipting.

- **Public-Facing Tenant Websites**:
  - Independent school public websites with CMS editor (`/site/{slug}`).
  - Online student admission application forms with automatic application tracking numbers (`APP-2025-0001`).

- **Security & Data Privacy (NDPA 2023 Compliant)**:
  - Immutable audit logs capturing administrative actions, IP addresses, and JSON state diffs.
  - Cryptographic CSRF tokens on all state-changing forms with `hash_equals` timing-attack defense.
  - Bcrypt password hashing (`PASSWORD_BCRYPT`).
  - Session rotation preventing session fixation.
  - Security headers (X-Frame-Options, X-Content-Type-Options, Strict-Transport-Security).

---

## 2. Technology Stack

- **Backend**: PHP 8.2+ / PHP 8.4+ Modular Monolith (PSR-4 autoloading).
- **Database**: MySQL / MariaDB 10.4+ with InnoDB foreign keys and indexes.
- **Frontend**: HTML5, ES6+ JavaScript, Custom Handcrafted Responsive CSS Design System (`public/assets/css/edusphere.css`).
- **PDF Generation**: Dompdf 3.1+.
- **Payment Verification**: Paystack API with sandbox simulator.

---

## 3. Installation and Setup Instructions

### Prerequisites
- PHP 8.2 or later installed (with `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `gd`, `openssl`).
- MySQL / MariaDB server running on port `3306`.
- Composer 2.x or `composer.phar`.
- Apache / XAMPP or PHP built-in CLI server.

### Steps

1. **Clone or navigate to the project directory**:
   ```bash
   cd c:\xampp\htdocs\schoolportals
   ```

2. **Install Composer Dependencies**:
   ```bash
   php composer.phar install
   ```

3. **Configure Environment Variables**:
   Copy `.env.example` to `.env` and verify database credentials:
   ```env
   APP_NAME="EduSphere SaaS"
   APP_ENV=development
   APP_URL="http://localhost/schoolportals"
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=edusphere_saas
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Execute Database Migrations**:
   Run the migration runner:
   ```bash
   php database/migrate.php
   ```

5. **Seed the Database with Production-Grade Demo Data**:
   ```bash
   php database/seeders/DatabaseSeeder.php
   ```

6. **Run Automated Acceptance & Endpoint Tests**:
   ```bash
   php tests/test_workflows.php
   php tests/test_all_endpoints.php
   ```

---

## 4. Default Demonstration Accounts

All demo accounts use the standard password: `password123`.

| Role | Email Address | Portal / Responsibility |
|---|---|---|
| **Super Admin** | `superadmin@edusphere.example` | Central SaaS HQ Console (`/superadmin`) |
| **Sales Manager** | `salesmanager@edusphere.example` | Commercial Pipeline & Commission Approvals (`/sales`) |
| **Sales Rep** | `salesrep@edusphere.example` | Territory Lead Acquisition & Kanban Board (`/sales`) |
| **School Admin** | `admin@apexcollege.edu.ng` | Apex Crown College Administration (`/school`) |
| **Principal** | `principal@apexcollege.edu.ng` | Result Approval & Academic Directorship (`/school/exams`) |
| **Registrar** | `registrar@apexcollege.edu.ng` | Admissions & Student Enrollment (`/school/admissions`) |
| **Teacher** | `teacher@apexcollege.edu.ng` | Classroom Attendance & Score Sheet Entry (`/school/exams`) |
| **Bursar** | `bursar@apexcollege.edu.ng` | Fee Invoices & Payment Receipts (`/school/finance`) |
| **Student** | `student@apexcollege.edu.ng` | Student Personal Portal (`/portal/student`) |
| **Parent** | `parent@apexcollege.edu.ng` | Family Ward Portal (`/portal/parent`) |

*Tip: A 1-click Quick Demo Switcher is available directly on the login page at `http://localhost/schoolportals/login`.*

---

## 5. Main Application Routes

- **Authentication**: `http://localhost/schoolportals/login`
- **Super Admin HQ Console**: `http://localhost/schoolportals/superadmin`
- **Tenant School Portal**: `http://localhost/schoolportals/school`
- **Examinations & PDF Reports**: `http://localhost/schoolportals/school/exams`
- **Sales CRM & Kanban Pipeline**: `http://localhost/schoolportals/sales/pipeline`
- **Sales Commission Ledger**: `http://localhost/schoolportals/sales/commissions`
- **Student Dashboard**: `http://localhost/schoolportals/portal/student`
- **Parent Ward Dashboard**: `http://localhost/schoolportals/portal/parent`
- **Apex Crown College Public Website**: `http://localhost/schoolportals/site/apex`
- **Online Admission Form**: `http://localhost/schoolportals/site/apex/apply`
