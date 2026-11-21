<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    private string $viewsPath;

    public function __construct(?string $viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?? dirname(__DIR__, 2) . '/public/views';
    }

    public function render(string $template, array $variables = [], ?string $layout = 'layout'): string
    {
        $templateFile = $this->resolveTemplatePath($template);

        $content = $this->renderFile($templateFile, $variables);

        if ($layout === null) {
            return $content;
        }

        $layoutFile = $this->resolveTemplatePath($layout);
        $layoutVariables = array_merge($variables, ['content' => $content]);

        return $this->renderFile($layoutFile, $layoutVariables);
    }

    public function renderPartial(string $partial, array $variables = []): string
    {
        $partialFile = $this->resolveTemplatePath($partial);
        return $this->renderFile($partialFile, $variables);
    }

    private function renderFile(string $filePath, array $variables): string
    {
        extract($variables, EXTR_SKIP);

        ob_start();
        try {
            require $filePath;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }

    private function resolveTemplatePath(string $template): string
    {
        $file = $this->viewsPath . '/' . trim($template, '/') . '.html.php';

        if (!is_file($file)) {
            throw new RuntimeException("View template not found: [{$file}].");
        }

        return $file;
    }
}
