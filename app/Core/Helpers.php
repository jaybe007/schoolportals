<?php

declare(strict_types=1);

use EduSphere\Core\Auth;
use EduSphere\Core\Csrf;
use EduSphere\Core\Session;
use EduSphere\Core\TenantContext;

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('money')) {
    function money($amount): string
    {
        $num = (float)($amount ?? 0);
        return '₦' . number_format($num, 2);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '/schoolportals', '/');
        $path = ltrim($path, '/');
        return $path === '' ? $base : $base . '/' . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('public/assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::getToken();
    }
}

if (!function_exists('flash')) {
    function flash(string $key)
    {
        return Session::flash($key);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('current_school')) {
    function current_school(): ?array
    {
        return TenantContext::getSchool();
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd M Y'): string
    {
        if (!$date) return '—';
        return date($format, strtotime($date));
    }
}
