<?php

declare(strict_types=1);

use App\Core\Config;
use Dotenv\Dotenv;

$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require $composerAutoload;
}

if (class_exists(Dotenv::class)) {
    $basePath = dirname(__DIR__);
    $dotenv = Dotenv::createImmutable($basePath);
    $dotenv->safeLoad();
}

require __DIR__ . '/Core/helpers.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $prefixLength = strlen($prefix);

    if (strncmp($class, $prefix, $prefixLength) !== 0) {
        return;
    }

    $relativeClass = substr($class, $prefixLength);
    $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

Config::load(require __DIR__ . '/../config/app.php');

date_default_timezone_set('UTC');
