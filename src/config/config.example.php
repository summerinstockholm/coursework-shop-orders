<?php

return [
    'app' => [
        'base_url' => getenv('APP_BASE_URL') ?: '',
        'name' => getenv('APP_NAME') ?: 'Система управления заказами интернет-магазина',
    ],

    'db' => [
        'host' => getenv('DB_HOST') ?: 'db',
        'port' => (int)(getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'shop_orders',
        'user' => getenv('DB_USER') ?: 'shop_user',
        'pass' => getenv('DB_PASSWORD') ?: '',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    ],
];