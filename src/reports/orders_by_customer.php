<?php
$pageTitle = 'Отчёты по покупателю';

require_once __DIR__ . '/../includes/header.php';

$customers = [];
$orders = [];
$errorMessage = null;
$selectedCustomerId = (int)($_GET['customer_id'] ?? 0);
$selectedCustomerName = '';

try {
    $pdo = db();

    $customers = $pdo->query(
        'SELECT
            customer_id,
            last_name,
            first_name,
            middle_name
         FROM customers
         ORDER BY last_name ASC, first_name ASC, middle_name ASC'
    )->fetchAll();

    if ($selectedCustomerId > 0) {
        $customerStmt = $pdo->prepare(
            'SELECT
                customer_id,
                last_name,
                first_name,
                middle_name
             FROM customers
             WHERE customer_id = :customer_id'
        );

        $customerStmt->execute([
            ':customer_id' => $selectedCustomerId,
        ]);

        $customer = $customerStmt->fetch();

        if (!$customer) {
            $errorMessage = 'Покупатель не найден.';
        } else {
            $selectedCustomerName = trim(
                (string)$customer['last_name'] . ' ' .
                (string)$customer['first_name'] . ' ' .
                (string)($customer['middle_name'] ?? '')
            );

            $ordersStmt = $pdo->prepare(
                'SELECT
                    o.order_id,
                    o.order_date,
                    o.status,
                    o.total_amount,
                    o.payment_method,
                    o.delivery_method,
                    o.delivery_address
                 FROM orders o
                 WHERE o.customer_id = :customer_id
                 ORDER BY o.order_date DESC, o.order_id DESC'
            );

            $ordersStmt->execute([
                ':customer_id' => $selectedCustomerId,
            ]);

            $orders = $ordersStmt->fetchAll();
        }
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Отчёт по заказам покупателя</h2>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (empty($customers)): ?>
            <p>В системе нет покупателей.</p>
        <?php else: ?>
            <form method="get" action="<?= htmlspecialchars(base_url('reports/orders_by_customer.php'), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-grid">
                    <div class="form-group form-group-full">
                        <label for="customer_id">Покупатель</label>
                        <select id="customer_id" name="customer_id" required>
                            <option value="">Выберите покупателя</option>
                            <?php foreach ($customers as $customer): ?>
                                <?php
                                $customerFullName = trim(
                                    (string)$customer['last_name'] . ' ' .
                                    (string)$customer['first_name'] . ' ' .
                                    (string)($customer['middle_name'] ?? '')
                                );
                                ?>
                                <option
                                    value="<?= (int)$customer['customer_id'] ?>"
                                    <?= $selectedCustomerId === (int)$customer['customer_id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($customerFullName, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit">Показать заказы</button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($selectedCustomerId > 0 && $errorMessage === null): ?>
            <h3>Результаты</h3>

            <p>
                <strong>Покупатель:</strong>
                <?= htmlspecialchars($selectedCustomerName, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <?php if (empty($orders)): ?>
                <p>У выбранного покупателя заказов нет.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID заказа</th>
                                <th>Дата заказа</th>
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
                                <tr>
                                    <td><?= (int)$order['order_id'] ?></td>
                                    <td><?= htmlspecialchars((string)$order['order_date'], ENT_QUOTES, 'UTF-8') ?></td>
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