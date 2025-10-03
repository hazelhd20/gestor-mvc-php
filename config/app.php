<?php

return [
    'app' => [
        'name' => 'Gestor de Titulacion',
        'base_url' => rtrim(getenv('APP_URL') ?: '', '/'),
    ],
    'db' => [
        'driver' => getenv('DB_DRIVER') ?: 'sqlite',
        'sqlite' => [
            'database' => __DIR__ . '/../storage/database.sqlite',
        ],
        'mysql' => [
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => getenv('DB_PORT') ?: '3306',
            'database' => getenv('DB_DATABASE') ?: 'gestor',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
        ],
    ],
    'mail' => [
        'host' => getenv('MAIL_HOST') ?: 'mail.hazelhd.com',
        'port' => (int) (getenv('MAIL_PORT') ?: 465),
        'username' => getenv('MAIL_USERNAME') ?: 'no-reply@hazelhd.com',
        'password' => getenv('MAIL_PASSWORD') ?: 'esDECczn*HkOZe-Y',
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'ssl',
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@hazelhd.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'Gestor de Titulacion',
    ],
];
