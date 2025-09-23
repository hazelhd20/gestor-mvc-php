<?php

use App\Core\Config;

function base_path(string $path = ''): string
{
    $base = dirname(__DIR__, 2);
    return $path !== ''
        ? $base . DIRECTORY_SEPARATOR . ltrim($path, '/\\')
        : $base;
}

function app_path(string $path = ''): string
{
    $base = base_path('app');
    return $path !== ''
        ? $base . DIRECTORY_SEPARATOR . ltrim($path, '/\\')
        : $base;
}

function view_path(string $path = ''): string
{
    $base = app_path('Views');
    return $path !== ''
        ? $base . DIRECTORY_SEPARATOR . ltrim($path, '/\\')
        : $base;
}

function base_url(): string
{
    $configured = Config::get('app.base_url');
    if ($configured !== '') {
        return $configured;
    }

    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($script));

    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        return '';
    }

    return rtrim($dir, '/');
}

function url(string $path = ''): string
{
    $base = base_url();

    if ($path === '' || $path === '/') {
        return $base !== '' ? $base : '/';
    }

    $suffix = '/' . ltrim($path, '/');

    return ($base !== '' ? $base : '') . $suffix;
}

function redirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function redirect_to(string $path): void
{
    redirect(url($path));
}

function redirect_back(string $fallback = '/'): void
{
    $target = $_SERVER['HTTP_REFERER'] ?? url($fallback);
    redirect($target);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
