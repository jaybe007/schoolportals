<?php

declare(strict_types=1);

namespace EduSphere\Core;

class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $url, ?string $flashMessage = null, string $flashType = 'success'): void
    {
        if ($flashMessage !== null) {
            Session::flash($flashType, $flashMessage);
        }

        // Base prefix adjustment if url is relative and begins with /
        $baseUrl = rtrim($_ENV['APP_URL'] ?? '/schoolportals', '/');
        if (str_starts_with($url, '/')) {
            // Check if already prefixed
            if (!str_starts_with($url, $baseUrl)) {
                $url = $baseUrl . $url;
            }
        }

        header("Location: {$url}");
        exit;
    }

    public static function back(?string $flashMessage = null, string $flashType = 'info'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        self::redirect($referer, $flashMessage, $flashType);
    }

    public static function download(string $filePath, ?string $downloadName = null): void
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            die("File not found");
        }

        $downloadName = $downloadName ?? basename($filePath);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public static function forbidden(?string $message = 'Access Denied: You do not have permission to access or modify records belonging to another school tenant.'): void
    {
        http_response_code(403);
        View::render('errors.403', ['message' => $message], 'auth');
        exit;
    }

    public static function notFound(?string $message = 'Resource not found'): void
    {
        http_response_code(404);
        echo "<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>" . htmlspecialchars($message ?? 'Not found') . "</p></body></html>";
        exit;
    }
}
