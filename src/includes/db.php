<?php

$configPath = __DIR__ . '/../config/config.local.php';

if (!file_exists($configPath)) {
    $configPath = __DIR__ . '/../config/config.example.php';
}

$config = require $configPath;

if (!is_array($config) || !isset($config['db'], $config['app'])) {
    throw new RuntimeException('Некорректный файл конфигурации.');
}

function app_config(): array
{
    global $config;
    return $config;
}

function base_url(string $path = ''): string
{
    $config = app_config();
    $base = rtrim($config['app']['base_url'] ?? '', '/');

    if ($path === '') {
        return $base;
    }

    return $base . '/' . ltrim($path, '/');
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = app_config();
    $db = $config['db'];

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $db['host'],
        $db['port'],
        $db['name'],
        $db['charset']
    );

    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}