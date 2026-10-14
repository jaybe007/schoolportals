<?php

declare(strict_types=1);

namespace EduSphere\Core;

class View
{
    private static string $viewsPath = __DIR__ . '/../../views';

    public static function render(string $template, array $data = [], ?string $layout = 'tenant'): void
    {
        extract($data);

        $viewFile = self::$viewsPath . '/' . str_replace('.', '/', $template) . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View template not found: {$template} ({$viewFile})");
        }

        // Capture view output
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = self::$viewsPath . '/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }

        include $layoutFile;
    }
}
