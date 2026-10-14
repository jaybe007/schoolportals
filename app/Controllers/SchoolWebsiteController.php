<?php

declare(strict_types=1);

namespace EduSphere\Controllers;

use EduSphere\Core\Database;
use EduSphere\Core\Request;
use EduSphere\Core\Response;
use EduSphere\Core\View;
use EduSphere\Core\TenantContext;
use EduSphere\Core\AuditLogger;

class SchoolWebsiteController
{
    private function resolveSchoolBySlug(string $slug): array
    {
        $school = Database::fetch("SELECT * FROM schools WHERE slug = ? OR code = ?", [$slug, $slug]);
        if (!$school) {
            http_response_code(404);
            die("School tenant '{$slug}' not found.");
        }
        TenantContext::setExplicitSchoolId((int)$school['id']);
        return $school;
    }

    public function page(Request $request, string $slug, string $page = 'home'): void
    {
        $school = $this->resolveSchoolBySlug($slug);
        $pageRecord = Database::fetch("SELECT * FROM school_website_pages WHERE school_id = ? AND page_slug = ?", [$school['id'], $page]);

        if (!$pageRecord) {
            $pageRecord = [
                'title' => ucfirst($page) . ' - ' . $school['name'],
                'banner_title' => $school['name'],
                'banner_subtitle' => $school['motto'] ?? '',
                'content' => "Welcome to the {$page} page of {$school['name']}.",
            ];
        }

        View::render('website.page', [
            'title' => $pageRecord['title'],
            'school' => $school,
            'page' => $pageRecord,
            'currentSlug' => $slug,
            'currentPage' => $page,
        ], 'website');
    }

    public function showApplyForm(Request $request, string $slug): void
    {
        $school = $this->resolveSchoolBySlug($slug);
        $classLevels = Database::query("SELECT * FROM class_levels WHERE school_id = ? ORDER BY rank_order ASC", [$school['id']]);

        View::render('website.apply', [
            'title' => 'Online Admission Application - ' . $school['name'],
            'school' => $school,
            'classLevels' => $classLevels,
            'currentSlug' => $slug,
        ], 'website');
    }

    public function submitApplication(Request $request, string $slug): void
    {
        $school = $this->resolveSchoolBySlug($slug);

        $firstName = trim((string)$request->post('applicant_first_name', ''));
        $lastName = trim((string)$request->post('applicant_last_name', ''));
        $gender = (string)$request->post('applicant_gender', 'male');
        $dob = (string)$request->post('date_of_birth', '');
        $classLevelId = (int)$request->post('applied_class_level_id', 0);
        $parentName = trim((string)$request->post('parent_name', ''));
        $parentEmail = trim((string)$request->post('parent_email', ''));
        $parentPhone = trim((string)$request->post('parent_phone', ''));
        $prevSchool = trim((string)$request->post('previous_school', ''));

        if (empty($firstName) || empty($lastName) || empty($parentEmail) || empty($parentPhone) || !$classLevelId) {
            Response::redirect("/site/{$slug}/apply", 'Please fill all required application fields.', 'danger');
        }

        $appCount = (int)Database::fetch("SELECT COUNT(*) AS c FROM admissions WHERE school_id = ?", [$school['id']])['c'] + 1;
        $appNo = sprintf('APP-%s-%04d', date('Y'), $appCount);

        Database::execute(
            "INSERT INTO admissions 
             (school_id, application_number, applicant_first_name, applicant_last_name, applicant_gender, date_of_birth, applied_class_level_id, parent_name, parent_email, parent_phone, previous_school, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
            [$school['id'], $appNo, $firstName, $lastName, $gender, $dob, $classLevelId, $parentName, $parentEmail, $parentPhone, $prevSchool]
        );

        AuditLogger::log('online_admission_applied', 'admission', (int)Database::lastInsertId(), [
            'application_number' => $appNo,
            'school_id' => $school['id'],
        ]);

        Response::redirect("/site/{$slug}/admissions", "Application submitted successfully! Your application reference number is {$appNo}. We will reach out shortly.", 'success');
    }
}
