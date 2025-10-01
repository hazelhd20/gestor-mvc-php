<?php

declare(strict_types=1);

if (!function_exists('dashboard_component')) {
    function dashboard_component(string $component, array $data = []): void
    {
        $basePath = __DIR__ . '/components/';
        $path = $basePath . str_replace('.', '/', $component) . '.php';

        if (!file_exists($path)) {
            throw new \RuntimeException('Dashboard component not found: ' . $component);
        }

        extract($data, EXTR_SKIP);
        require $path;
    }
}

if (!function_exists('dashboard_layout')) {
    function dashboard_layout(string $layout, array $data = []): void
    {
        dashboard_component('layout/' . $layout, $data);
    }
}

if (!function_exists('dashboard_section')) {
    function dashboard_section(string $section, array $data = []): void
    {
        dashboard_component('sections/' . $section, $data);
    }
}

if (!function_exists('dashboard_modal')) {
    function dashboard_modal(string $modal, array $data = []): void
    {
        dashboard_component('modals/' . $modal, $data);
    }
}

if (!function_exists('render_dashboard_page')) {
    function render_dashboard_page(array $data): void
    {
        dashboard_layout('page', $data);
    }
}
