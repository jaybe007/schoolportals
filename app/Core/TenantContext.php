<?php

declare(strict_types=1);

namespace EduSphere\Core;

use RuntimeException;

class TenantContext
{
    private static ?array $currentSchool = null;
    private static ?int $explicitSchoolId = null;

    public static function setExplicitSchoolId(?int $schoolId): void
    {
        self::$explicitSchoolId = $schoolId;
        self::$currentSchool = null;
    }

    public static function clear(): void
    {
        self::$currentSchool = null;
        self::$explicitSchoolId = null;
    }

    public static function resolveSchool(?string $identifier = null): ?array
    {
        if (self::$currentSchool !== null && $identifier === null) {
            return self::$currentSchool;
        }

        // 1. If explicit ID set by super admin
        if (self::$explicitSchoolId !== null) {
            $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [self::$explicitSchoolId]);
            if ($school) {
                self::$currentSchool = $school;
                return $school;
            }
        }

        // 2. If logged in and bound to a school
        $userSchoolId = Auth::schoolId();
        if ($userSchoolId !== null) {
            $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [$userSchoolId]);
            if ($school) {
                self::$currentSchool = $school;
                return $school;
            }
        }

        // 3. If identifier passed (id or slug)
        if ($identifier !== null) {
            if (is_numeric($identifier)) {
                $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [(int)$identifier]);
            } else {
                $school = Database::fetch("SELECT * FROM schools WHERE slug = ? OR code = ?", [$identifier, $identifier]);
            }
            if ($school) {
                self::$currentSchool = $school;
                return $school;
            }
        }

        // 4. Request query or session tenant override
        $reqTenant = $_GET['school'] ?? $_GET['tenant'] ?? Session::get('active_tenant_slug');
        if ($reqTenant) {
            if (is_numeric($reqTenant)) {
                $school = Database::fetch("SELECT * FROM schools WHERE id = ?", [(int)$reqTenant]);
            } else {
                $school = Database::fetch("SELECT * FROM schools WHERE slug = ? OR code = ?", [$reqTenant, $reqTenant]);
            }
            if ($school) {
                self::$currentSchool = $school;
                return $school;
            }
        }

        // 5. Hostname / Domain resolution (e.g. custom domain apex.edu.ng or apex.edusphere.example)
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host) {
            $cleanHost = strtolower(explode(':', $host)[0]);
            $domainRecord = Database::fetch(
                "SELECT s.* FROM schools s 
                 JOIN tenant_domains td ON td.school_id = s.id 
                 WHERE td.domain = ? AND td.status = 'active' AND td.is_verified = 1",
                [$cleanHost]
            );
            if ($domainRecord) {
                self::$currentSchool = $domainRecord;
                return $domainRecord;
            }
        }

        return null;
    }

    public static function getSchool(): ?array
    {
        return self::resolveSchool();
    }

    public static function getSchoolId(): ?int
    {
        $school = self::getSchool();
        return $school ? (int)$school['id'] : null;
    }

    /**
     * Enforces strict multi-tenant boundary.
     * Prevents Insecure Direct Object References (IDOR) and cross-tenant leakage.
     */
    public static function assertTenantScope(int $resourceSchoolId): void
    {
        if (Auth::isSuperAdmin()) {
            return; // Super admin can inspect tenants
        }

        $userSchoolId = Auth::schoolId();
        if ($userSchoolId === null || $userSchoolId !== $resourceSchoolId) {
            AuditLogger::log('unauthorized_cross_tenant_attempt', 'school', $resourceSchoolId, [
                'attempted_school_id' => $resourceSchoolId,
                'user_school_id' => $userSchoolId,
            ]);
            http_response_code(403);
            die("Access Denied: Cross-tenant data access is strictly prohibited.");
        }
    }

    public static function checkSubscriptionActive(): bool
    {
        $school = self::getSchool();
        if (!$school) {
            return true;
        }

        // Check if school is suspended or archived
        if (in_array($school['status'], ['suspended', 'archived'], true)) {
            return false;
        }

        if ($school['subscription_expires_at']) {
            $expiry = strtotime($school['subscription_expires_at']);
            // Allow 7-day grace period
            if (time() > ($expiry + (7 * 86400))) {
                return false;
            }
        }

        return true;
    }
}
