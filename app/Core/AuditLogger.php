<?php

declare(strict_types=1);

namespace EduSphere\Core;

class AuditLogger
{
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $newValues = null,
        ?array $oldValues = null,
        ?int $explicitSchoolId = null
    ): void {
        try {
            $schoolId = $explicitSchoolId ?? TenantContext::getSchoolId() ?? Auth::schoolId();
            $userId = Auth::id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

            Database::execute(
                "INSERT INTO audit_logs (school_id, user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $schoolId,
                    $userId,
                    $action,
                    $entityType,
                    $entityId,
                    $oldValues ? json_encode($oldValues, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
                    $newValues ? json_encode($newValues, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
                    $ip,
                    $ua
                ]
            );
        } catch (\Throwable $e) {
            // Never break application flow if audit logging encounters a database hiccup
            error_log("Audit logging failed: " . $e->getMessage());
        }
    }
}
