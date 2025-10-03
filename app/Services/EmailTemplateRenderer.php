<?php

namespace App\Services;

use RuntimeException;

class EmailTemplateRenderer
{
    public function render(string $template, array $data = []): string
    {
        $path = view_path('emails/' . str_replace('.', '/', $template) . '.php');

        if (!file_exists($path)) {
            throw new RuntimeException('Plantilla de correo no encontrada: ' . $template);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}
