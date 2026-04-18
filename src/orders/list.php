<?php
$pageTitle = 'Заказы';

require_once __DIR__ . '/../includes/header.php';

$orders = [];
$errorMessage = null;

try {
    $pdo = db();

    $stmt = $pdo->query(
        'SELECT
            o.order_id,
            o.order_date,
            o.status,
            o.total_amount,
            o.payment_method,
            o.delivery_method,
            o.delivery_address,
            c.last_name,
            c.first_name,
            c.middle_name,
            GROUP_CONCAT(
                CONCAT(p.product_name, " × ", oi.quantity)
                ORDER BY p.product_name
                SEPARATOR "; "
            ) AS ordered_products
         FROM orders o
         INNER JOIN customers c ON c.customer_id = o.customer_id
         LEFT JOIN order_items oi ON oi.order_id = o.order_id
         LEFT JOIN products p ON p.product_id = oi.product_id
         GROUP BY
            o.order_id,
            o.order_date,
            o.status,
            o.total_amount,
            o.payment_method,
            o.delivery_method,
            o.delivery_address,
            c.last_name,
            c.first_name,
            c.middle_name
         ORDER BY o.order_id ASC'
    );

    $orders = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Список заказов</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('orders/create.php'), ENT_QUOTES, 'UTF-8') ?>">
                Добавить заказ
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong>
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php elseif (empty($orders)): ?>
            <p>Заказы не найдены.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата заказа</th>
                        <th>Покупатель</th>
                        <th>Товары</th>
                        <th>Статус</th>
                        <th>Сумма</th>
                        <th>Способ оплаты</th>
                        <th>Способ доставки</th>
                        <th>Адрес доставки</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $customerFullName = trim(
                            (string)$order['last_name'] . ' ' .
                            (string)$order['first_name'] . ' ' .
                            (string)($order['middle_name'] ?? '')
                        );
                        ?>
                        <tr>
                            <td><?= (int)$order['order_id'] ?></td>
                            <td><?= htmlspecialchars((string)$order['order_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($customerFullName, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($order['ordered_products'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$order['status'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= number_format((float)$order['total_amount'], 2, '.', ' ') ?></td>
                            <td><?= htmlspecialchars((string)$order['payment_method'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$order['delivery_method'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$order['delivery_address'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="<?= htmlspecialchars(base_url('orders/view.php?id=' . (int)$order['order_id']), ENT_QUOTES, 'UTF-8') ?>">
                                    Открыть
                                </a>
                                |
                                <a href="<?= htmlspecialchars(base_url('orders/edit.php?id=' . (int)$order['order_id']), ENT_QUOTES, 'UTF-8') ?>">
                                    Редактировать
                                </a>
                                |
                                <a
                                    href="<?= htmlspecialchars(base_url('orders/delete.php?id=' . (int)$order['order_id']), ENT_QUOTES, 'UTF-8') ?>"
                                    onclick="return confirm('Удалить заказ?');"
                                >
                                    Удалить
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>