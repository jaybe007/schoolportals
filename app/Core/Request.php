<?php

declare(strict_types=1);

namespace EduSphere\Core;

class Request
{
    private string $method;
    private string $path;
    private array $queryParams;
    private array $postData;
    private array $files;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        
        // Normalize path
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Strip subfolder if running in /schoolportals/
        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '/' && $scriptName !== '\\' && strpos($path, $scriptName) === 0) {
            $path = substr($path, strlen($scriptName));
        }

        $this->path = '/' . trim($path, '/');
        $this->queryParams = $_GET;
        $this->postData = $_POST;
        $this->files = $_FILES;

        // Support JSON payload
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $this->postData = array_merge($this->postData, $json);
            }
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function get(string $key, $default = null)
    {
        return $this->queryParams[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        return $this->postData[$key] ?? $default;
    }

    public function input(string $key, $default = null)
    {
        return $this->postData[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->queryParams, $this->postData);
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
