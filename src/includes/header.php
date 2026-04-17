<?php
require_once __DIR__ . '/db.php';

$pageTitle = $pageTitle ?? (app_config()['app']['name'] ?? 'Приложение');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(base_url('assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>