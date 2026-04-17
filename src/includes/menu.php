<?php
require_once __DIR__ . '/db.php';
?>

<header>
    <h1><?= htmlspecialchars(app_config()['app']['name'] ?? 'Приложение', ENT_QUOTES, 'UTF-8') ?></h1>

    <nav>
        <a href="<?= htmlspecialchars(base_url('index.php'), ENT_QUOTES, 'UTF-8') ?>">Главная</a>

        <a href="<?= htmlspecialchars(base_url('customers/list.php'), ENT_QUOTES, 'UTF-8') ?>">Покупатели</a>
        <a href="<?= htmlspecialchars(base_url('categories/list.php'), ENT_QUOTES, 'UTF-8') ?>">Категории</a>
        <a href="<?= htmlspecialchars(base_url('manufacturers/list.php'), ENT_QUOTES, 'UTF-8') ?>">Производители</a>
        <a href="<?= htmlspecialchars(base_url('warehouses/list.php'), ENT_QUOTES, 'UTF-8') ?>">Склады</a>
        <a href="<?= htmlspecialchars(base_url('products/list.php'), ENT_QUOTES, 'UTF-8') ?>">Товары</a>

        <a href="<?= htmlspecialchars(base_url('orders/list.php'), ENT_QUOTES, 'UTF-8') ?>">Заказы</a>
        <a href="<?= htmlspecialchars(base_url('payments/list.php'), ENT_QUOTES, 'UTF-8') ?>">Оплаты</a>
        <a href="<?= htmlspecialchars(base_url('deliveries/list.php'), ENT_QUOTES, 'UTF-8') ?>">Доставки</a>

        <a href="<?= htmlspecialchars(base_url('reports/orders_by_customer.php'), ENT_QUOTES, 'UTF-8') ?>">Отчёты по покупателю</a>
        <a href="<?= htmlspecialchars(base_url('reports/orders_by_period.php'), ENT_QUOTES, 'UTF-8') ?>">Отчёты по периоду</a>
        <a href="<?= htmlspecialchars(base_url('reports/orders_by_status.php'), ENT_QUOTES, 'UTF-8') ?>">Отчёты по статусу</a>
        <a href="<?= htmlspecialchars(base_url('reports/top_orders.php'), ENT_QUOTES, 'UTF-8') ?>">Топ-10 заказов</a>
        <a href="<?= htmlspecialchars(base_url('reports/top_products.php'), ENT_QUOTES, 'UTF-8') ?>">Топ-10 товаров</a>
    </nav>
</header>