<?php
require_once __DIR__ . '/db.php';

$appName = app_config()['app']['name'] ?? 'Приложение';

$menuItems = [
    ['label' => 'Главная', 'url' => 'index.php'],
    ['label' => 'Покупатели', 'url' => 'customers/list.php'],
    ['label' => 'Категории', 'url' => 'categories/list.php'],
    ['label' => 'Производители', 'url' => 'manufacturers/list.php'],
    ['label' => 'Склады', 'url' => 'warehouses/list.php'],
    ['label' => 'Товары', 'url' => 'products/list.php'],
    ['label' => 'Заказы', 'url' => 'orders/list.php'],
    ['label' => 'Оплаты', 'url' => 'payments/list.php'],
    ['label' => 'Доставки', 'url' => 'deliveries/list.php'],
    ['label' => 'Отчёты по покупателю', 'url' => 'reports/orders_by_customer.php'],
    ['label' => 'Отчёты по периоду', 'url' => 'reports/orders_by_period.php'],
    ['label' => 'Отчёты по статусу', 'url' => 'reports/orders_by_status.php'],
    ['label' => 'Топ-10 заказов', 'url' => 'reports/top_orders.php'],
    ['label' => 'Топ-10 товаров', 'url' => 'reports/top_products.php'],
];
?>

<header>
    <h1><?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></h1>

    <nav>
        <?php foreach ($menuItems as $item): ?>
            <a href="<?= htmlspecialchars(base_url($item['url']), ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </nav>
</header>