<?php
$pageTitle = 'Отчёты по статусу';

require_once __DIR__ . '/../includes/header.php';

$orders = [];
$errorMessage = null;
$selectedStatus = trim((string)($_GET['status'] ?? ''));

$statuses = [
    'новый',
    'собирается',
    'оплачен',
    'в доставке',
    'доставлен',
    'отменен',
];

try {
    if ($selectedStatus !== '') {
        if (!in_array($selectedStatus, $statuses, true)) {
            $errorMessage = 'Некорректный статус заказа.';
        } else {
            $pdo = db();

            $stmt = $pdo->prepare(
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
                    c.middle_name
                 FROM orders o
                 INNER JOIN customers c ON c.customer_id = o.customer_id
                 WHERE o.status = :status
                 ORDER BY o.order_date DESC, o.order_id DESC'
            );

            $stmt->execute([
                ':status' => $selectedStatus,
            ]);

            $orders = $stmt->fetchAll();
        }
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Отчёт по заказам по статусу</h2>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="get" action="<?= htmlspecialchars(base_url('reports/orders_by_status.php'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label for="status">Статус заказа</label>
                    <select id="status" name="status" required>
                        <option value="">Выберите статус</option>
                        <?php foreach ($statuses as $status): ?>
                            <option
                                value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"
                                <?= $selectedStatus === $status ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Показать заказы</button>
            </div>
        </form>

        <?php if ($selectedStatus !== '' && $errorMessage === null): ?>
            <h3>Результаты</h3>

            <p>
                <strong>Статус:</strong>
                <?= htmlspecialchars($selectedStatus, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <?php if (empty($orders)): ?>
                <p>Заказы с выбранным статусом не найдены.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID заказа</th>
                                <th>Дата заказа</th>
                                <th>Покупатель</th>
                                <th>Статус</th>
                                <th>Сумма</th>
                                <th>Способ оплаты</th>
                                <th>Способ доставки</th>
                                <th>Адрес доставки</th>
                                <th>Открыть</th>
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
                                    <td><?= htmlspecialchars((string)$order['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= number_format((float)$order['total_amount'], 2, '.', ' ') ?></td>
                                    <td><?= htmlspecialchars((string)$order['payment_method'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)$order['delivery_method'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)$order['delivery_address'], ENT_QUOTES, 'UTF-8') ?></td>
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
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>