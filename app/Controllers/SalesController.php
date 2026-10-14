<?php

declare(strict_types=1);

namespace EduSphere\Controllers;

use EduSphere\Core\Database;
use EduSphere\Core\Request;
use EduSphere\Core\Response;
use EduSphere\Core\View;
use EduSphere\Core\Auth;
use EduSphere\Core\AuditLogger;
use EduSphere\Services\CommissionEngine;

class SalesController
{
    private function getCurrentRepId(): ?int
    {
        $userId = Auth::id();
        $rep = Database::fetch("SELECT id FROM sales_representatives WHERE user_id = ?", [$userId]);
        return $rep ? (int)$rep['id'] : null;
    }

    public function dashboard(Request $request): void
    {
        $userId = Auth::id();
        $isManager = Auth::hasRole('sales_manager', 'super_admin');
        $repId = $this->getCurrentRepId();

        if ($isManager || !$repId) {
            $totalLeads = (int)(Database::fetch("SELECT COUNT(*) AS c FROM sales_leads")['c'] ?? 0);
            $wonLeads = (int)(Database::fetch("SELECT COUNT(*) AS c FROM sales_leads WHERE stage = 'won'")['c'] ?? 0);
            $demosScheduled = (int)(Database::fetch("SELECT COUNT(*) AS c FROM sales_leads WHERE stage = 'demo_scheduled'")['c'] ?? 0);
            $approvedCommissions = (float)(Database::fetch("SELECT IFNULL(SUM(commission_amount), 0) AS s FROM sales_commissions WHERE status IN ('approved', 'paid')")['s'] ?? 0.0);
            $pendingCommissions = (float)(Database::fetch("SELECT IFNULL(SUM(commission_amount), 0) AS s FROM sales_commissions WHERE status = 'pending'")['s'] ?? 0.0);
        } else {
            $totalLeads = (int)(Database::fetch("SELECT COUNT(*) AS c FROM sales_leads WHERE rep_id = ?", [$repId])['c'] ?? 0);
            $wonLeads = (int)(Database::fetch("SELECT COUNT(*) AS c FROM sales_leads WHERE rep_id = ? AND stage = 'won'", [$repId])['c'] ?? 0);
            $demosScheduled = (int)(Database::fetch("SELECT COUNT(*) AS c FROM sales_leads WHERE rep_id = ? AND stage = 'demo_scheduled'", [$repId])['c'] ?? 0);
            $approvedCommissions = (float)(Database::fetch("SELECT IFNULL(SUM(commission_amount), 0) AS s FROM sales_commissions WHERE rep_id = ? AND status IN ('approved', 'paid')", [$repId])['s'] ?? 0.0);
            $pendingCommissions = (float)(Database::fetch("SELECT IFNULL(SUM(commission_amount), 0) AS s FROM sales_commissions WHERE rep_id = ? AND status = 'pending'", [$repId])['s'] ?? 0.0);
        }

        // Recent pipeline activities
        $activities = Database::query(
            "SELECT sa.*, sl.school_name, u.name AS rep_name
             FROM sales_activities sa
             JOIN sales_leads sl ON sl.id = sa.lead_id
             JOIN sales_representatives sr ON sr.id = sa.rep_id
             JOIN users u ON u.id = sr.user_id
             ORDER BY sa.id DESC LIMIT 6"
        );

        // Rep leaderboard
        $reps = Database::query(
            "SELECT sr.*, u.name AS rep_name,
                (SELECT COUNT(*) FROM sales_leads WHERE rep_id = sr.id) AS leads_count,
                (SELECT COUNT(*) FROM sales_leads WHERE rep_id = sr.id AND stage = 'won') AS won_count,
                (SELECT IFNULL(SUM(commission_amount), 0) FROM sales_commissions WHERE rep_id = sr.id AND status = 'paid') AS total_paid
             FROM sales_representatives sr
             JOIN users u ON u.id = sr.user_id"
        );

        $currentRep = $repId ? Database::fetch("SELECT * FROM sales_representatives WHERE id = ?", [$repId]) : null;

        View::render('sales.dashboard', [
            'title' => 'Sales CRM Command Center - EduSphere',
            'activeNav' => 'dashboard',
            'isManager' => $isManager,
            'currentRep' => $currentRep,
            'totalLeads' => $totalLeads,
            'wonLeads' => $wonLeads,
            'demosScheduled' => $demosScheduled,
            'approvedCommissions' => $approvedCommissions,
            'pendingCommissions' => $pendingCommissions,
            'activities' => $activities,
            'reps' => $reps,
        ], 'sales');
    }

    public function pipeline(Request $request): void
    {
        $isManager = Auth::hasRole('sales_manager', 'super_admin');
        $repId = $this->getCurrentRepId();

        if ($isManager || !$repId) {
            $leads = Database::query(
                "SELECT sl.*, u.name AS rep_name
                 FROM sales_leads sl
                 JOIN sales_representatives sr ON sr.id = sl.rep_id
                 JOIN users u ON u.id = sr.user_id
                 ORDER BY sl.id DESC"
            );
        } else {
            $leads = Database::query(
                "SELECT sl.*, u.name AS rep_name
                 FROM sales_leads sl
                 JOIN sales_representatives sr ON sr.id = sl.rep_id
                 JOIN users u ON u.id = sr.user_id
                 WHERE sl.rep_id = ?
                 ORDER BY sl.id DESC",
                [$repId]
            );
        }

        $stages = [
            'new_lead' => 'New Leads',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'demo_scheduled' => 'Demo Scheduled',
            'proposal_sent' => 'Proposal Sent',
            'negotiation' => 'Negotiation',
            'won' => 'Won & Onboarded',
            'lost' => 'Lost',
        ];

        $pipelineData = [];
        foreach ($stages as $k => $label) {
            $pipelineData[$k] = [];
        }

        foreach ($leads as $l) {
            $stage = $l['stage'] ?? 'new_lead';
            if (isset($pipelineData[$stage])) {
                $pipelineData[$stage][] = $l;
            }
        }

        View::render('sales.pipeline', [
            'title' => 'Visual Kanban Sales Pipeline - EduSphere',
            'activeNav' => 'pipeline',
            'stages' => $stages,
            'pipelineData' => $pipelineData,
            'isManager' => $isManager,
        ], 'sales');
    }

    public function leads(Request $request): void
    {
        $isManager = Auth::hasRole('sales_manager', 'super_admin');
        $repId = $this->getCurrentRepId();

        if ($request->isPost()) {
            $schoolName = trim((string)$request->post('school_name', ''));
            $contact = trim((string)$request->post('contact_person', ''));
            $phone = trim((string)$request->post('phone', ''));
            $email = trim((string)$request->post('email', ''));
            $students = (int)$request->post('estimated_students', 300);
            $stage = (string)$request->post('stage', 'new_lead');
            $notes = trim((string)$request->post('notes', ''));

            // Duplicate detection check
            $existing = Database::fetch(
                "SELECT id, school_name FROM sales_leads WHERE (phone = ? OR email = ? OR school_name = ?) LIMIT 1",
                [$phone, $email, $schoolName]
            );

            if ($existing) {
                Response::redirect('/sales/leads', "Notice: Potential duplicate lead detected with existing prospect '{$existing['school_name']}'.", 'warning');
            }

            $assignedRep = $repId ?? (int)Database::fetch("SELECT id FROM sales_representatives LIMIT 1")['id'];

            Database::execute(
                "INSERT INTO sales_leads (rep_id, school_name, contact_person, phone, email, estimated_students, stage, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$assignedRep, $schoolName, $contact, $phone, $email, $students, $stage, $notes]
            );
            $newLeadId = (int)Database::lastInsertId();

            Database::execute(
                "INSERT INTO sales_activities (lead_id, rep_id, activity_type, summary, completed_at)
                 VALUES (?, ?, 'stage_change', ?, NOW())",
                [$newLeadId, $assignedRep, "Lead created in stage " . $stage]
            );

            AuditLogger::log('sales_lead_created', 'sales_lead', $newLeadId, ['school_name' => $schoolName]);
            Response::redirect('/sales/leads', "Prospect '{$schoolName}' added to CRM pipeline!", 'success');
        }

        if ($isManager || !$repId) {
            $leads = Database::query(
                "SELECT sl.*, u.name AS rep_name
                 FROM sales_leads sl
                 JOIN sales_representatives sr ON sr.id = sl.rep_id
                 JOIN users u ON u.id = sr.user_id
                 ORDER BY sl.id DESC"
            );
        } else {
            $leads = Database::query(
                "SELECT sl.*, u.name AS rep_name
                 FROM sales_leads sl
                 JOIN sales_representatives sr ON sr.id = sl.rep_id
                 JOIN users u ON u.id = sr.user_id
                 WHERE sl.rep_id = ?
                 ORDER BY sl.id DESC",
                [$repId]
            );
        }

        View::render('sales.leads', [
            'title' => 'Prospective Schools Directory - EduSphere',
            'activeNav' => 'leads',
            'leads' => $leads,
            'isManager' => $isManager,
        ], 'sales');
    }

    public function moveLeadStage(Request $request, string $leadId): void
    {
        $id = (int)$leadId;
        $newStage = (string)$request->post('stage', 'contacted');

        $lead = Database::fetch("SELECT * FROM sales_leads WHERE id = ?", [$id]);
        if (!$lead) {
            Response::redirect('/sales/pipeline', 'Lead not found.', 'danger');
        }

        Database::execute("UPDATE sales_leads SET stage = ?, updated_at = NOW() WHERE id = ?", [$newStage, $id]);

        $repId = $this->getCurrentRepId() ?? (int)$lead['rep_id'];
        Database::execute(
            "INSERT INTO sales_activities (lead_id, rep_id, activity_type, summary, completed_at)
             VALUES (?, ?, 'stage_change', ?, NOW())",
            [$id, $repId, "Stage updated from " . $lead['stage'] . " to " . $newStage]
        );

        AuditLogger::log('lead_stage_updated', 'sales_lead', $id, ['from' => $lead['stage'], 'to' => $newStage]);
        Response::redirect('/sales/pipeline', "Deal stage updated to " . ucfirst(str_replace('_', ' ', $newStage)), 'success');
    }

    public function commissions(Request $request): void
    {
        $isManager = Auth::hasRole('sales_manager', 'super_admin');
        $repId = $this->getCurrentRepId();

        if ($isManager || !$repId) {
            $commissions = Database::query(
                "SELECT sc.*, u.name AS rep_name, sr.rep_code, sch.name AS school_name, sub.invoice_number,
                    u_app.name AS approver_name
                 FROM sales_commissions sc
                 JOIN sales_representatives sr ON sr.id = sc.rep_id
                 JOIN users u ON u.id = sr.user_id
                 JOIN schools sch ON sch.id = sc.school_id
                 LEFT JOIN subscriptions sub ON sub.id = sc.subscription_id
                 LEFT JOIN users u_app ON u_app.id = sc.approved_by
                 ORDER BY sc.id DESC"
            );

            $portalCommissions = Database::query(
                "SELECT pcl.*, u.name AS rep_name, sr.rep_code, sch.name AS school_name, t.name AS term_name,
                    fa.distributable_amount, u_app.name AS approver_name
                 FROM portal_commission_ledger pcl
                 JOIN sales_representatives sr ON sr.id = pcl.rep_id
                 JOIN users u ON u.id = sr.user_id
                 JOIN schools sch ON sch.id = pcl.school_id
                 JOIN academic_terms t ON t.id = pcl.term_id
                 JOIN financial_allocations fa ON fa.id = pcl.allocation_id
                 LEFT JOIN users u_app ON u_app.id = pcl.approved_by
                 ORDER BY pcl.id DESC"
            );
        } else {
            $commissions = Database::query(
                "SELECT sc.*, u.name AS rep_name, sr.rep_code, sch.name AS school_name, sub.invoice_number,
                    u_app.name AS approver_name
                 FROM sales_commissions sc
                 JOIN sales_representatives sr ON sr.id = sc.rep_id
                 JOIN users u ON u.id = sr.user_id
                 JOIN schools sch ON sch.id = sc.school_id
                 LEFT JOIN subscriptions sub ON sub.id = sc.subscription_id
                 LEFT JOIN users u_app ON u_app.id = sc.approved_by
                 WHERE sc.rep_id = ?
                 ORDER BY sc.id DESC",
                [$repId]
            );

            $portalCommissions = Database::query(
                "SELECT pcl.*, u.name AS rep_name, sr.rep_code, sch.name AS school_name, t.name AS term_name,
                    fa.distributable_amount, u_app.name AS approver_name
                 FROM portal_commission_ledger pcl
                 JOIN sales_representatives sr ON sr.id = pcl.rep_id
                 JOIN users u ON u.id = sr.user_id
                 JOIN schools sch ON sch.id = pcl.school_id
                 JOIN academic_terms t ON t.id = pcl.term_id
                 JOIN financial_allocations fa ON fa.id = pcl.allocation_id
                 LEFT JOIN users u_app ON u_app.id = pcl.approved_by
                 WHERE pcl.rep_id = ?
                 ORDER BY pcl.id DESC",
                [$repId]
            );
        }

        View::render('sales.commissions', [
            'title' => 'Commission Ledger - EduSphere',
            'activeNav' => 'commissions',
            'commissions' => $commissions,
            'portalCommissions' => $portalCommissions,
            'isManager' => $isManager,
        ], 'sales');
    }

    public function approveCommission(Request $request, string $id): void
    {
        if (!Auth::hasRole('sales_manager', 'super_admin')) {
            Response::redirect('/sales/commissions', 'Unauthorized. Only sales managers can approve commissions.', 'danger');
        }

        try {
            CommissionEngine::approveCommission((int)$id, Auth::id());
            Response::redirect('/sales/commissions', 'Commission approved successfully.', 'success');
        } catch (\Throwable $e) {
            Response::redirect('/sales/commissions', $e->getMessage(), 'danger');
        }
    }

    public function payCommission(Request $request, string $id): void
    {
        if (!Auth::hasRole('sales_manager', 'super_admin')) {
            Response::redirect('/sales/commissions', 'Unauthorized.', 'danger');
        }

        $payoutRef = 'PAYOUT-BNK-' . date('Ymd') . '-' . rand(1000, 9999);
        try {
            CommissionEngine::markPaid((int)$id, $payoutRef);
            Response::redirect('/sales/commissions', "Commission marked paid with payout reference {$payoutRef}!", 'success');
        } catch (\Throwable $e) {
            Response::redirect('/sales/commissions', $e->getMessage(), 'danger');
        }
    }

    public function approvePortalCommission(Request $request, string $id): void
    {
        if (!Auth::hasRole('sales_manager', 'super_admin')) {
            Response::redirect('/sales/commissions', 'Unauthorized. Only sales managers can approve commissions.', 'danger');
        }

        $comm = Database::fetch("SELECT * FROM portal_commission_ledger WHERE id = ?", [(int)$id]);
        if (!$comm || $comm['status'] !== 'pending') {
            Response::redirect('/sales/commissions', 'Commission not found or not in pending state.', 'danger');
        }

        // Compliance: check anti-self-approval
        $rep = Database::fetch("SELECT * FROM sales_representatives WHERE id = ?", [(int)$comm['rep_id']]);
        if ($rep && (int)$rep['user_id'] === Auth::id()) {
            Response::redirect('/sales/commissions', 'Compliance violation: Representatives cannot approve their own commissions.', 'danger');
        }

        Database::execute(
            "UPDATE portal_commission_ledger SET status = 'approved', approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ?",
            [Auth::id(), (int)$id]
        );

        AuditLogger::log('portal_commission_approved', 'portal_commission_ledger', (int)$id, [
            'approved_by' => Auth::id(),
            'amount' => $comm['commission_amount'],
        ]);

        Response::redirect('/sales/commissions', 'Portal term fee commission approved successfully.', 'success');
    }

    public function payPortalCommission(Request $request, string $id): void
    {
        if (!Auth::hasRole('sales_manager', 'super_admin')) {
            Response::redirect('/sales/commissions', 'Unauthorized.', 'danger');
        }

        $payoutRef = 'PAYOUT-REP-' . date('Ymd') . '-' . rand(1000, 9999);
        Database::execute(
            "UPDATE portal_commission_ledger SET status = 'paid', paid_at = NOW(), payout_reference = ?, updated_at = NOW() WHERE id = ?",
            [$payoutRef, (int)$id]
        );

        AuditLogger::log('portal_commission_paid', 'portal_commission_ledger', (int)$id, [
            'payout_reference' => $payoutRef,
        ]);

        Response::redirect('/sales/commissions', "Portal commission marked paid with reference {$payoutRef}!", 'success');
    }
}
