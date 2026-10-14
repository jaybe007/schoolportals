<?php

declare(strict_types=1);

namespace EduSphere\Core;

/**
 * SecurityHeaders
 * 
 * Enforces production-grade HTTP security headers across every web request
 * to prevent Clickjacking, MIME-sniffing, XSS, and unauthorized framing.
 */
class SecurityHeaders
{
    public static function apply(): void
    {
        if (headers_sent()) {
            return;
        }

        // 1. Prevent Clickjacking (disallow framing from external origins)
        header('X-Frame-Options: SAMEORIGIN');

        // 2. Prevent MIME-type confusion / sniffing
        header('X-Content-Type-Options: nosniff');

        // 3. Enable legacy browser XSS auditor & block rendering if attack detected
        header('X-XSS-Protection: 1; mode=block');

        // 4. Protect Referrer leakage across domains
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // 5. Permissions Policy - disable camera, mic, and geolocation access unless explicitly needed
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(self)');

        // 6. Strict-Transport-Security (HSTS) when running over HTTPS
        $isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1'))
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        if ($isHttps) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // 7. Content-Security-Policy (CSP)
        // Whitelists self-hosted resources, fonts, and inline styles used by UI dashboard
        $csp = "default-src 'self' 'unsafe-inline' https:; " .
               "img-src 'self' data: https: blob:; " .
               "font-src 'self' https: data:; " .
               "frame-ancestors 'self'; " .
               "object-src 'none'; " .
               "base-uri 'self';";
        header("Content-Security-Policy: {$csp}");

        // 8. Prevent sensitive portal and exam data from being cached on shared/public machines
        if (Session::has('user_id')) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
        }
    }
}
