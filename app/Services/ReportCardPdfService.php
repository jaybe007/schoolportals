<?php

declare(strict_types=1);

namespace EduSphere\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use EduSphere\Core\Database;

class ReportCardPdfService
{
    public static function generateReportCard(int $schoolId, int $studentId, int $sessionId = 0, int $termId = 0): Dompdf
    {
        $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$schoolId]);
        $student = Database::fetch(
            "SELECT s.*, ca.name AS arm_name, cl.name AS class_name 
             FROM students s
             LEFT JOIN class_arms ca ON ca.id = s.class_arm_id
             LEFT JOIN class_levels cl ON cl.id = ca.class_level_id
             WHERE s.id = ? AND s.school_id = ?",
            [$studentId, $schoolId]
        );

        if (!$student) {
            throw new \RuntimeException("Student record not found or does not belong to school.");
        }

        // 1. Resolve Session if not provided
        if ($sessionId <= 0) {
            $sess = Database::fetch("SELECT id FROM academic_sessions WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId])
                ?: Database::fetch("SELECT session_id AS id FROM exam_scores WHERE student_id = ? ORDER BY id DESC LIMIT 1", [$studentId])
                ?: Database::fetch("SELECT id FROM academic_sessions WHERE school_id = ? ORDER BY id DESC LIMIT 1", [$schoolId]);
            $sessionId = (int)($sess['id'] ?? 0);
        }

        // 2. Resolve Term if not provided
        if ($termId <= 0) {
            $t = Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? AND is_current = 1 LIMIT 1", [$schoolId])
                ?: Database::fetch("SELECT term_id AS id FROM exam_scores WHERE student_id = ? AND session_id = ? ORDER BY id DESC LIMIT 1", [$studentId, $sessionId])
                ?: Database::fetch("SELECT id FROM academic_terms WHERE school_id = ? ORDER BY id DESC LIMIT 1", [$schoolId]);
            $termId = (int)($t['id'] ?? 0);
        }

        $session = Database::fetch("SELECT * FROM academic_sessions WHERE id = ?", [$sessionId])
            ?: ['name' => 'Current Academic Session'];
        $term = Database::fetch("SELECT * FROM academic_terms WHERE id = ?", [$termId])
            ?: ['name' => 'Current Term'];

        $summary = Database::fetch(
            "SELECT * FROM student_term_summaries WHERE school_id = ? AND session_id = ? AND term_id = ? AND student_id = ?",
            [$schoolId, $sessionId, $termId, $studentId]
        );

        $scores = Database::query(
            "SELECT es.*, sub.name AS subject_name, sub.code AS subject_code
             FROM exam_scores es
             JOIN subjects sub ON sub.id = es.subject_id
             WHERE es.student_id = ? AND (es.session_id = ? OR ? = 0) AND (es.term_id = ? OR ? = 0)
             ORDER BY sub.name ASC",
            [$studentId, $sessionId, $sessionId, $termId, $termId]
        );

        // Auto-compute summary if summary row is not yet generated in DB
        if (!$summary && !empty($scores)) {
            $total = 0.0;
            foreach ($scores as $sc) {
                $total += (float)$sc['total_score'];
            }
            $count = count($scores);
            $avg = $count > 0 ? round($total / $count, 1) : 0.0;
            $summary = [
                'total_score' => $total,
                'average_score' => $avg,
                'position' => '—',
                'total_students' => '—',
                'teacher_remark' => ($avg >= 70 ? 'Outstanding academic excellence and discipline.' : ($avg >= 50 ? 'Good effort this term. Strive for higher marks.' : 'Requires closer attention and focused remedial study.')),
                'principal_remark' => ($avg >= 70 ? 'Commendable performance. Promoted with distinction.' : ($avg >= 50 ? 'Satisfactory progress. Approved.' : 'Needs significant improvement next term.')),
            ];
        }

        $attendanceStats = Database::fetch(
            "SELECT 
                COUNT(*) AS total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_days
             FROM attendance 
             WHERE student_id = ? AND (session_id = ? OR ? = 0) AND (term_id = ? OR ? = 0)",
            [$studentId, $sessionId, $sessionId, $termId, $termId]
        ) ?: ['total_days' => 0, 'present_days' => 0, 'absent_days' => 0];

        $html = self::buildHtmlTemplate($school, $student, $session, $term, $summary, $scores, $attendanceStats);

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }

    private static function buildHtmlTemplate(
        array $school,
        array $student,
        array $session,
        array $term,
        ?array $summary,
        array $scores,
        array $attendance
    ): string {
        $schoolName = htmlspecialchars($school['name']);
        $schoolMotto = htmlspecialchars($school['motto'] ?? 'Excellence in Education');
        $schoolAddress = htmlspecialchars($school['address'] ?? '');
        $schoolPhone = htmlspecialchars($school['phone'] ?? '');

        $studentName = htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']);
        $admNo = htmlspecialchars($student['admission_number']);
        $className = htmlspecialchars(($student['class_name'] ?? '') . ' ' . ($student['arm_name'] ?? ''));
        $gender = ucfirst($student['gender']);
        $sessionName = htmlspecialchars($session['name'] ?? '');
        $termName = htmlspecialchars($term['name'] ?? '');

        $totalDays = (int)($attendance['total_days'] ?? 0);
        $presentDays = (int)($attendance['present_days'] ?? 0);
        $attPercent = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) . '%' : 'N/A';

        $totalScore = $summary['total_score'] ?? 0;
        $avgScore = $summary['average_score'] ?? 0;
        $pos = $summary['position'] ?? '—';
        $outOf = $summary['total_students'] ?? '—';
        $teacherRemark = htmlspecialchars($summary['teacher_remark'] ?? 'Good performance. Keep striving for greater heights.');
        $principalRemark = htmlspecialchars($summary['principal_remark'] ?? 'Commendable result. Maintain this diligence next term.');

        $rowsHtml = '';
        foreach ($scores as $s) {
            $sub = htmlspecialchars($s['subject_name']);
            $ca1 = number_format((float)$s['ca1_score'], 1);
            $ca2 = number_format((float)$s['ca2_score'], 1);
            $exam = number_format((float)$s['exam_score'], 1);
            $tot = number_format((float)$s['total_score'], 1);
            $grd = htmlspecialchars($s['grade'] ?? '—');
            $rem = htmlspecialchars($s['remark'] ?? '—');

            $rowsHtml .= "
                <tr>
                    <td style='text-align: left; padding: 6px 8px; border-bottom: 1px solid #e2e8f0;'>{$sub}</td>
                    <td style='padding: 6px 8px; border-bottom: 1px solid #e2e8f0;'>{$ca1}</td>
                    <td style='padding: 6px 8px; border-bottom: 1px solid #e2e8f0;'>{$ca2}</td>
                    <td style='padding: 6px 8px; border-bottom: 1px solid #e2e8f0;'>{$exam}</td>
                    <td style='padding: 6px 8px; border-bottom: 1px solid #e2e8f0; font-weight: bold;'>{$tot}</td>
                    <td style='padding: 6px 8px; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #1e3a8a;'>{$grd}</td>
                    <td style='padding: 6px 8px; border-bottom: 1px solid #e2e8f0; text-align: left;'>{$rem}</td>
                </tr>
            ";
        }

        if (empty($scores)) {
            $rowsHtml = "<tr><td colspan='7' style='padding: 15px; color: #64748b;'>No approved examination scores found for this term.</td></tr>";
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Official Terminal Report Card</title>
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 15px; }
    .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 12px; margin-bottom: 14px; }
    .school-name { font-size: 20px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin-bottom: 4px; }
    .school-motto { font-style: italic; color: #475569; font-size: 11px; margin-bottom: 4px; }
    .school-meta { font-size: 10px; color: #64748b; }
    .title-banner { background-color: #1e3a8a; color: #ffffff; text-align: center; font-weight: bold; padding: 5px; font-size: 12px; margin-bottom: 14px; letter-spacing: 1px; border-radius: 3px; }
    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .info-table td { padding: 4px 6px; font-size: 11px; }
    .info-label { font-weight: bold; color: #334155; width: 18%; }
    .info-val { color: #0f172a; width: 32%; border-bottom: 1px dotted #cbd5e1; }
    .score-table { width: 100%; border-collapse: collapse; text-align: center; margin-bottom: 14px; }
    .score-table th { background-color: #f1f5f9; color: #1e293b; padding: 7px 6px; font-size: 10px; border-top: 1px solid #cbd5e1; border-bottom: 2px solid #94a3b8; text-transform: uppercase; }
    .summary-grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .summary-box { border: 1px solid #cbd5e1; border-radius: 4px; padding: 8px; background-color: #f8fafc; }
    .remark-box { border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 10px; margin-bottom: 10px; background-color: #fafafa; }
    .remark-label { font-weight: bold; color: #1e3a8a; font-size: 10px; margin-bottom: 3px; }
    .signatures { width: 100%; margin-top: 25px; }
    .sig-line { border-top: 1px solid #475569; width: 85%; margin-top: 35px; text-align: center; padding-top: 4px; font-size: 10px; font-weight: bold; }
    .footer { text-align: center; font-size: 9px; color: #94a3b8; margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 6px; }
</style>
</head>
<body>

<div class="header">
    <div class="school-name">{$schoolName}</div>
    <div class="school-motto">"{$schoolMotto}"</div>
    <div class="school-meta">{$schoolAddress} | Tel: {$schoolPhone}</div>
</div>

<div class="title-banner">OFFICIAL STUDENT TERMINAL PROGRESS REPORT</div>

<table class="info-table">
    <tr>
        <td class="info-label">Student Name:</td>
        <td class="info-val"><strong>{$studentName}</strong></td>
        <td class="info-label">Academic Session:</td>
        <td class="info-val">{$sessionName}</td>
    </tr>
    <tr>
        <td class="info-label">Admission No:</td>
        <td class="info-val">{$admNo}</td>
        <td class="info-label">Term:</td>
        <td class="info-val">{$termName}</td>
    </tr>
    <tr>
        <td class="info-label">Class & Arm:</td>
        <td class="info-val">{$className}</td>
        <td class="info-label">Gender:</td>
        <td class="info-val">{$gender}</td>
    </tr>
    <tr>
        <td class="info-label">Attendance:</td>
        <td class="info-val">{$presentDays} of {$totalDays} days ({$attPercent})</td>
        <td class="info-label">Class Position:</td>
        <td class="info-val"><strong>{$pos}</strong> of {$outOf} students</td>
    </tr>
</table>

<table class="score-table">
    <thead>
        <tr>
            <th style="text-align: left;">Subject</th>
            <th style="width: 10%;">CA 1 (20)</th>
            <th style="width: 10%;">CA 2 (20)</th>
            <th style="width: 10%;">Exam (60)</th>
            <th style="width: 12%;">Total (100)</th>
            <th style="width: 10%;">Grade</th>
            <th style="width: 20%; text-align: left;">Teacher Remark</th>
        </tr>
    </thead>
    <tbody>
        {$rowsHtml}
    </tbody>
</table>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
    <tr>
        <td style="width: 32%; padding-right: 8px;">
            <div class="summary-box">
                <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Total Score</div>
                <div style="font-size: 16px; font-weight: bold; color: #0f172a;">{$totalScore}</div>
            </div>
        </td>
        <td style="width: 32%; padding-right: 8px;">
            <div class="summary-box">
                <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Term Average</div>
                <div style="font-size: 16px; font-weight: bold; color: #1e3a8a;">{$avgScore}%</div>
            </div>
        </td>
        <td style="width: 36%;">
            <div class="summary-box">
                <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Class Standing</div>
                <div style="font-size: 16px; font-weight: bold; color: #059669;">Position: {$pos} / {$outOf}</div>
            </div>
        </td>
    </tr>
</table>

<div class="remark-box">
    <div class="remark-label">CLASS TEACHER'S GENERAL APPRAISAL:</div>
    <div style="font-size: 10.5px; color: #1e293b;">{$teacherRemark}</div>
</div>

<div class="remark-box">
    <div class="remark-label">PRINCIPAL / HEAD OF SCHOOL REMARKS:</div>
    <div style="font-size: 10.5px; color: #1e293b;">{$principalRemark}</div>
</div>

<table class="signatures">
    <tr>
        <td style="width: 50%; text-align: center;">
            <div class="sig-line">Class Teacher's Signature & Date</div>
        </td>
        <td style="width: 50%; text-align: center;">
            <div class="sig-line">Principal's Signature & Official Stamp</div>
        </td>
    </tr>
</table>

<div class="footer">
    Generated securely by EduSphere SaaS School Platform &bull; Document Verification Code: EDU-{$student['id']}-{$session['id']}-{$term['id']} &bull; Page 1 of 1
</div>

</body>
</html>
HTML;
    }
}
