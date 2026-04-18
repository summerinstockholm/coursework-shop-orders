<?php
$pageTitle = 'Топ-10 заказов';

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
            c.last_name,
            c.first_name,
            c.middle_name
         FROM orders o
         INNER JOIN customers c ON c.customer_id = o.customer_id
         ORDER BY o.total_amount DESC, o.order_id ASC
         LIMIT 10'
    );

    $orders = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Топ-10 заказов по сумме</h2>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php elseif (empty($orders)): ?>
            <p>Заказы не найдены.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Место</th>
                            <th>ID заказа</th>
                            <th>Дата заказа</th>
                            <th>Покупатель</th>
                            <th>Статус</th>
                            <th>Сумма</th>
                            <th>Способ оплаты</th>
                            <th>Способ доставки</th>
                            <th>Открыть</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $index => $order): ?>
                            <?php
                            $customerFullName = trim(
                                (string)$order['last_name'] . ' ' .
                                (string)$order['first_name'] . ' ' .
                                (string)($order['middle_name'] ?? '')
                            );
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= (int)$order['order_id'] ?></td>
                                <td><?= htmlspecialchars((string)$order['order_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($customerFullName, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$order['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= number_format((float)$order['total_amount'], 2, '.', ' ') ?></td>
                                <td><?= htmlspecialchars((string)$order['payment_method'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$order['delivery_method'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="actions-cell">
                                    <a href="<?= htmlspecialchars(base_url('orders/view.php?id=' . (int)$order['order_id']), ENT_QUOTES, 'UTF-8') ?>">
                                        Открыть
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>