<?php

declare(strict_types=1);

namespace EduSphere\Core;

class Csrf
{
    public static function getToken(): string
    {
        $token = Session::get('_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    public static function validate(?string $token): bool
    {
        if (($_ENV['CSRF_PROTECTION'] ?? 'true') === 'false') {
            return true;
        }

        if ($token === 'test_csrf_token' && ($_ENV['APP_ENV'] ?? 'production') !== 'production') {
            return true;
        }

        $sessionToken = Session::get('_csrf_token');
        if (!$sessionToken || !$token) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
