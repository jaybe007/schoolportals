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

    public static function isSales(): bool
    {
        return in_array(self::role(), ['sales_rep', 'sales_manager'], true);
    }

    public static function isSchoolStaff(): bool
    {
        return in_array(self::role(), ['school_admin', 'principal', 'registrar', 'teacher', 'bursar'], true);
    }

    public static function hasRole(string ...$roles): bool
    {
        $current = self::role();
        return $current !== null && in_array($current, $roles, true);
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
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
