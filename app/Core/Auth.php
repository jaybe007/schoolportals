<?php

declare(strict_types=1);

namespace EduSphere\Core;

class Auth
{
    private static ?array $cachedUser = null;

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        $id = Session::get('user_id');
        return $id ? (int)$id : null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $id = self::id();
        $user = Database::fetch("SELECT * FROM users WHERE id = ? AND is_active = 1", [$id]);
        if ($user) {
            unset($user['password_hash']);
            self::$cachedUser = $user;
        } else {
            self::logout();
        }

        return self::$cachedUser;
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user['role'] ?? null;
    }

    public static function schoolId(): ?int
    {
        $user = self::user();
        return isset($user['school_id']) && $user['school_id'] !== null ? (int)$user['school_id'] : null;
    }

    public static function isSuperAdmin(): bool
    {
        return self::role() === 'super_admin';
    }

    public static function isFinanceAdmin(): bool
    {
        return in_array(self::role(), ['super_admin', 'finance_admin'], true);
    }

    public static function isPlatformAdmin(): bool
    {
        return in_array(self::role(), ['super_admin', 'finance_admin'], true);
    }

    public static function isSales(): bool
    {
        return in_array(self::role(), ['sales_rep', 'sales_manager'], true);
    }

    public static function isSalesManager(): bool
    {
        return in_array(self::role(), ['sales_manager', 'super_admin'], true);
    }

    public static function isSchoolStaff(): bool
    {
        return in_array(self::role(), ['school_admin', 'school_owner', 'principal', 'registrar', 'teacher', 'bursar'], true);
    }

    public static function isSchoolAdmin(): bool
    {
        return in_array(self::role(), ['school_admin', 'school_owner', 'principal', 'bursar'], true);
    }

    public static function isBursar(): bool
    {
        return in_array(self::role(), ['bursar', 'school_admin', 'school_owner'], true);
    }

    public static function canApproveFinancials(): bool
    {
        return in_array(self::role(), ['super_admin', 'finance_admin'], true);
    }

    public static function hasRole(string ...$roles): bool
    {
        $current = self::role();
        if ($current === 'super_admin') {
            return true; // Super Admin can execute any action across the entire platform
        }
        return $current !== null && in_array($current, $roles, true);
    }

    /**
     * Check if user has specific permission (Super Admin has root access to all actions)
     */
    public static function can(string $permission, ?int $userId = null): bool
    {
        if ($userId === null) {
            $userId = self::id();
        }
        if (!$userId) {
            return false;
        }

        $userRole = ($userId === self::id()) ? self::role() : (Database::fetch("SELECT role FROM users WHERE id = ?", [$userId])['role'] ?? null);
        if ($userRole === 'super_admin') {
            return true; // Super Admin has omnipotent root access
        }

        // Check custom user override permissions (granted = 1 or 0)
        $userOverride = Database::fetch(
            "SELECT up.granted 
             FROM user_permissions up
             JOIN permissions p ON p.id = up.permission_id
             WHERE up.user_id = ? AND p.slug = ?",
            [$userId, $permission]
        );

        if ($userOverride !== false && $userOverride !== null) {
            return (bool)$userOverride['granted'];
        }

        // Fallback to role-based default permissions
        $hasRolePerm = Database::fetch(
            "SELECT 1 
             FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role = ? AND p.slug = ?",
            [$userRole, $permission]
        );

        return !empty($hasRolePerm);
    }

    public static function hasPermission(string $permission, ?int $userId = null): bool
    {
        return self::can($permission, $userId);
    }

    /**
     * Get all active permissions for a user
     */
    public static function getUserPermissions(?int $userId = null): array
    {
        if ($userId === null) {
            $userId = self::id();
        }
        if (!$userId) {
            return [];
        }

        $userRole = ($userId === self::id()) ? self::role() : (Database::fetch("SELECT role FROM users WHERE id = ?", [$userId])['role'] ?? null);
        if ($userRole === 'super_admin') {
            return Database::query("SELECT slug, display_name, category FROM permissions ORDER BY category, slug");
        }

        return Database::query(
            "SELECT p.id, p.slug, p.display_name, p.category,
                    COALESCE(up.granted, 1) AS is_granted
             FROM permissions p
             LEFT JOIN role_permissions rp ON rp.permission_id = p.id AND rp.role = ?
             LEFT JOIN user_permissions up ON up.permission_id = p.id AND up.user_id = ?
             WHERE (rp.id IS NOT NULL AND (up.granted IS NULL OR up.granted = 1))
                OR (up.granted = 1)
             ORDER BY p.category, p.slug",
            [$userRole, $userId]
        );
    }

    /**
     * Assign or update role and school for a user
     */
    public static function assignRole(int $userId, string $role, ?int $schoolId = null): bool
    {
        Database::execute(
            "UPDATE users SET role = ?, school_id = ?, updated_at = NOW() WHERE id = ?",
            [$role, $schoolId, $userId]
        );

        if ($userId === self::id()) {
            self::$cachedUser = null;
        }

        return true;
    }

    /**
     * Synchronize custom permissions for an administrator
     */
    public static function syncPermissions(int $userId, array $permissionIds, ?int $grantedBy = null): bool
    {
        Database::transaction(function($pdo) use ($userId, $permissionIds, $grantedBy) {
            Database::execute("DELETE FROM user_permissions WHERE user_id = ?", [$userId]);
            foreach ($permissionIds as $permId) {
                $pId = (int)$permId;
                if ($pId > 0) {
                    Database::execute(
                        "INSERT INTO user_permissions (user_id, permission_id, granted, granted_by, created_at)
                         VALUES (?, ?, 1, ?, NOW())",
                        [$userId, $pId, $grantedBy]
                    );
                }
            }
        });

        return true;
    }

    public static function login(array $user): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        Session::set('user_id', $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_school_id', $user['school_id']);

        Database::execute("UPDATE users SET last_login_at = NOW() WHERE id = ?", [$user['id']]);

        self::$cachedUser = null;
    }

    public static function logout(): void
    {
        self::$cachedUser = null;
        Session::remove('user_id');
        Session::remove('user_role');
        Session::remove('user_name');
        Session::remove('user_email');
        Session::remove('user_school_id');
        Session::destroy();
    }
}
