<?php

declare(strict_types=1);

namespace EduSphere\Core;

/**
 * RateLimiter
 * 
 * High-performance, standalone rate limiter to mitigate brute-force
 * attacks, credential stuffing, and Denial of Service attempts.
 * Uses persistent file locks in the system temp directory so it functions
 * reliably without requiring external dependencies (like Redis/Memcached).
 */
class RateLimiter
{
    private static function getStoragePath(string $key): string
    {
        $hash = hash('sha256', $key);
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'edusphere_ratelimit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        return $dir . DIRECTORY_SEPARATOR . $hash . '.json';
    }

    /**
     * Determine if a given key has exceeded its maximum attempts within decay seconds.
     */
    public static function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $file = self::getStoragePath($key);
        if (!file_exists($file)) {
            return false;
        }

        $raw = @file_get_contents($file);
        if (!$raw) {
            return false;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['attempts'])) {
            return false;
        }

        $now = time();
        $recentAttempts = array_filter(
            $data['attempts'],
            fn($timestamp) => ($now - (int)$timestamp) < $decaySeconds
        );

        return count($recentAttempts) >= $maxAttempts;
    }

    /**
     * Increment the counter for a given key.
     */
    public static function hit(string $key, int $decaySeconds = 900): int
    {
        $file = self::getStoragePath($key);
        $data = ['attempts' => []];

        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            if ($raw) {
                $existing = json_decode($raw, true);
                if (is_array($existing) && isset($existing['attempts']) && is_array($existing['attempts'])) {
                    $data = $existing;
                }
            }
        }

        $now = time();
        // Remove attempts that are older than decaySeconds
        $data['attempts'] = array_values(array_filter(
            $data['attempts'],
            fn($timestamp) => ($now - (int)$timestamp) < $decaySeconds
        ));

        // Add current timestamp
        $data['attempts'][] = $now;

        @file_put_contents($file, json_encode($data), LOCK_EX);

        return count($data['attempts']);
    }

    /**
     * Reset the rate limit counter for a given key upon successful action.
     */
    public static function clear(string $key): void
    {
        $file = self::getStoragePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Get the number of seconds until a key is released from lockout.
     */
    public static function availableIn(string $key, int $decaySeconds): int
    {
        $file = self::getStoragePath($key);
        if (!file_exists($file)) {
            return 0;
        }

        $raw = @file_get_contents($file);
        if (!$raw) {
            return 0;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['attempts'])) {
            return 0;
        }

        $oldestValid = min($data['attempts']);
        $elapsed = time() - (int)$oldestValid;
        return max(1, $decaySeconds - $elapsed);
    }
}
