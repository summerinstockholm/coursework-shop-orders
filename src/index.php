<?php
$pageTitle = 'Главная';

require_once __DIR__ . '/includes/header.php';

$dbStatus = 'Подключение к базе данных успешно установлено.';
$dbError = null;

$stats = [
    'customers' => 0,
    'categories' => 0,
    'manufacturers' => 0,
    'warehouses' => 0,
    'products' => 0,
    'orders' => 0,
    'payments' => 0,
    'deliveries' => 0,
];

try {
    $pdo = db();

    foreach (array_keys($stats) as $tableName) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM {$tableName}");
        $stats[$tableName] = (int)$stmt->fetchColumn();
    }
} catch (Throwable $e) {
    $dbStatus = 'Не удалось получить данные из базы.';
    $dbError = $e->getMessage();
}

require_once __DIR__ . '/includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Система управления заказами интернет-магазина</h2>
        <p>
            Это главная страница учебного веб-приложения для работы с покупателями,
            товарами, заказами, оплатами, доставками и отчетами.
        </p>
    </section>

    <section class="card">
        <h2>Статус подключения к БД</h2>
        <p><?= htmlspecialchars($dbStatus, ENT_QUOTES, 'UTF-8') ?></p>

        <?php if ($dbError !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Краткая статистика</h2>

        <table>
            <thead>
                <tr>
                    <th>Раздел</th>
                    <th>Количество записей</th>
                    <th>Открыть</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Покупатели</td>
                    <td><?= (int)$stats['customers'] ?></td>
                    <td><a href="<?= htmlspecialchars(base_url('customers/list.php'), ENT_QUOTES, 'UTF-8') ?>">Открыть</a></td>
                </tr>
                <tr>
                    <td>Категории</td>
                    <td><?= (int)$stats['categories'] ?></td>
                    <td><a href="<?= htmlspecialchars(base_url('categories/list.php'), ENT_QUOTES, 'UTF-8') ?>">Открыть</a></td>
                </tr>
                <tr>
                    <td>Производители</td>
                    <td><?= (int)$stats['manufacturers'] ?></td>
                    <td><a href="<?= htmlspecialchars(base_url('manufacturers/list.php'), ENT_QUOTES, 'UTF-8') ?>">Открыть</a></td>
                </tr>
                <tr>
                    <td>Склады</td>
                    <td><?= (int)$stats['warehouses'] ?></td>
                    <td><a href="<?= htmlspecialchars(base_url('warehouses/list.php'), ENT_QUOTES, 'UTF-8') ?>">Открыть</a></td>
                </tr>
                <tr>
                    <td>Товары</td>
                    <td><?= (int)$stats['products'] ?></td>
                    <td><a href="<?= htmlspecialchars(base_url('products/list.php'), ENT_QUOTES, 'UTF-8') ?>">Открыть</a></td>
                </tr>
                <tr>
                    <td>Заказы</td>
                    <td><?= (int)$stats['orders'] ?></td>
                    <td><a href="<?= htmlspecialchars(base_url('orders/list.php'), ENT_QUOTES, 'UTF-8') ?>">Открыть</a></td>
                </tr>
                <tr>
                    <td>Оплаты</td>
                    <td><?= (int)$stats['payments'] ?></td>
                    <td><a href="<?= htmlspecialchars(base_url('payments/list.php'), ENT_QUOTES, 'UTF-8') ?>">Открыть</a></td>
                </tr>
                <tr>
                    <td>Доставки</td>
                    <td><?= (int)$stats['deliveries'] ?></td>
                    <td><a href="<?= htmlspecialchars(base_url('deliveries/list.php'), ENT_QUOTES, 'UTF-8') ?>">Открыть</a></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="card">
        <h2>Основные разделы</h2>

        <div class="form-actions">
            <a class="btn" href="<?= htmlspecialchars(base_url('customers/list.php'), ENT_QUOTES, 'UTF-8') ?>">Покупатели</a>
            <a class="btn" href="<?= htmlspecialchars(base_url('products/list.php'), ENT_QUOTES, 'UTF-8') ?>">Товары</a>
            <a class="btn" href="<?= htmlspecialchars(base_url('orders/list.php'), ENT_QUOTES, 'UTF-8') ?>">Заказы</a>
            <a class="btn" href="<?= htmlspecialchars(base_url('payments/list.php'), ENT_QUOTES, 'UTF-8') ?>">Оплаты</a>
            <a class="btn" href="<?= htmlspecialchars(base_url('deliveries/list.php'), ENT_QUOTES, 'UTF-8') ?>">Доставки</a>
        </div>
    </section>

    <section class="card">
        <h2>Отчеты</h2>

        <ul>
            <li>
                <a href="<?= htmlspecialchars(base_url('reports/orders_by_customer.php'), ENT_QUOTES, 'UTF-8') ?>">
                    Заказы по покупателю
                </a>
            </li>
            <li>
                <a href="<?= htmlspecialchars(base_url('reports/orders_by_period.php'), ENT_QUOTES, 'UTF-8') ?>">
                    Заказы за период
                </a>
            </li>
            <li>
                <a href="<?= htmlspecialchars(base_url('reports/orders_by_status.php'), ENT_QUOTES, 'UTF-8') ?>">
                    Заказы по статусу
                </a>
            </li>
            <li>
                <a href="<?= htmlspecialchars(base_url('reports/top_orders.php'), ENT_QUOTES, 'UTF-8') ?>">
                    Топ-10 заказов по сумме
                </a>
            </li>
            <li>
                <a href="<?= htmlspecialchars(base_url('reports/top_products.php'), ENT_QUOTES, 'UTF-8') ?>">
                    Топ-10 товаров по количеству в заказах
                </a>
            </li>
        </ul>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>