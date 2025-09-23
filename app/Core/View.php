<?php

namespace App\Core;

use RuntimeException;

class View
{
    public function render(string $view, array $data = []): void
    {
        $path = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($path)) {
            throw new RuntimeException('View not found: ' . $view);
        }

        extract($data, EXTR_SKIP);
        require $path;
    }
}
