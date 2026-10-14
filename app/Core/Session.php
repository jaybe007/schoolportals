<?php

declare(strict_types=1);

namespace EduSphere\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (php_sapi_name() === 'cli') {
            self::$started = true;
            return;
        }

        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $lifetime = (int)($_ENV['SESSION_LIFETIME'] ?? 7200);

        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_samesite', 'Lax');

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
        self::$started = true;

        // Session expiration / rotation check
        if (!isset($_SESSION['_session_created'])) {
            $_SESSION['_session_created'] = time();
        } elseif (time() - $_SESSION['_session_created'] > 1800) {
            // Regenerate session ID every 30 minutes to prevent fixation
            session_regenerate_id(true);
            $_SESSION['_session_created'] = time();
        }
    }

    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, $value = null)
    {
        self::start();
        if ($value === null) {
            $msg = $_SESSION['_flash'][$key] ?? null;
            unset($_SESSION['_flash'][$key]);
            return $msg;
        }

        $_SESSION['_flash'][$key] = $value;
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        self::$started = false;
    }
}
