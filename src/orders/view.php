<?php
$pageTitle = 'Просмотр заказа';

require_once __DIR__ . '/../includes/db.php';

$orderId = (int)($_GET['id'] ?? 0);

$errorMessage = null;
$order = null;
$orderItems = [];
$payment = null;
$delivery = null;

if ($orderId <= 0) {
    $errorMessage = 'Некорректный идентификатор заказа.';
} else {
    try {
        $pdo = db();

        $orderStmt = $pdo->prepare(
            'SELECT
                o.order_id,
                o.order_date,
                o.status,
                o.total_amount,
                o.payment_method,
                o.delivery_method,
                o.delivery_address,
                c.customer_id,
                c.last_name,
                c.first_name,
                c.middle_name,
                c.phone,
                c.email,
                c.city,
                c.street,
                c.house,
                c.apartment,
                c.postal_code
             FROM orders o
             INNER JOIN customers c ON c.customer_id = o.customer_id
             WHERE o.order_id = :order_id'
        );

        $orderStmt->execute([
            ':order_id' => $orderId,
        ]);

        $order = $orderStmt->fetch();

        if (!$order) {
            $errorMessage = 'Заказ не найден.';
        } else {
            $itemsStmt = $pdo->prepare(
                'SELECT
                    oi.order_item_id,
                    oi.quantity,
                    oi.unit_price,
                    oi.line_total,
                    p.product_id,
                    p.product_name,
                    c.category_name,
                    m.manufacturer_name
                 FROM order_items oi
                 INNER JOIN products p ON p.product_id = oi.product_id
                 INNER JOIN categories c ON c.category_id = p.category_id
                 INNER JOIN manufacturers m ON m.manufacturer_id = p.manufacturer_id
                 WHERE oi.order_id = :order_id
                 ORDER BY oi.order_item_id ASC'
            );

            $itemsStmt->execute([
                ':order_id' => $orderId,
            ]);

            $orderItems = $itemsStmt->fetchAll();

            $paymentStmt = $pdo->prepare(
                'SELECT
                    payment_id,
                    payment_date,
                    payment_amount,
                    payment_status,
                    payment_type
                 FROM payments
                 WHERE order_id = :order_id'
            );

            $paymentStmt->execute([
                ':order_id' => $orderId,
            ]);

            $payment = $paymentStmt->fetch();

            $deliveryStmt = $pdo->prepare(
                'SELECT
                    delivery_id,
                    carrier_name,
                    ship_date,
                    estimated_delivery_date,
                    actual_delivery_date,
                    delivery_status
                 FROM deliveries
                 WHERE order_id = :order_id'
            );

            $deliveryStmt->execute([
                ':order_id' => $orderId,
            ]);

            $delivery = $deliveryStmt->fetch();
        }
    } catch (Throwable $e) {
        $errorMessage = $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Просмотр заказа</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('orders/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                ← Вернуться к списку заказов
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong>
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php else: ?>
            <?php
            $customerFullName = trim(
                (string)$order['last_name'] . ' ' .
                (string)$order['first_name'] . ' ' .
                (string)($order['middle_name'] ?? '')
            );

            $customerAddress = trim(
                (string)$order['city'] . ', ' .
                (string)$order['street'] . ', д. ' .
                (string)$order['house'] .
                (
                    ($order['apartment'] ?? '') !== ''
                        ? ', кв. ' . (string)$order['apartment']
                        : ''
                )
            );
            ?>

            <div class="card">
                <h3>Основная информация</h3>
                <table>
                    <tbody>
                        <tr>
                            <th>ID заказа</th>
                            <td><?= (int)$order['order_id'] ?></td>
                        </tr>
                        <tr>
                            <th>Дата заказа</th>
                            <td><?= htmlspecialchars((string)$order['order_date'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <th>Статус</th>
                            <td><?= htmlspecialchars((string)$order['status'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <th>Сумма</th>
                            <td><?= number_format((float)$order['total_amount'], 2, '.', ' ') ?></td>
                        </tr>
                        <tr>
                            <th>Способ оплаты</th>
                            <td><?= htmlspecialchars((string)$order['payment_method'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <th>Способ доставки</th>
                            <td><?= htmlspecialchars((string)$order['delivery_method'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <th>Адрес доставки</th>
                            <td><?= htmlspecialchars((string)$order['delivery_address'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Покупатель</h3>
                <table>
                    <tbody>
                        <tr>
                            <th>ID покупателя</th>
                            <td><?= (int)$order['customer_id'] ?></td>
                        </tr>
                        <tr>
                            <th>ФИО</th>
                            <td><?= htmlspecialchars($customerFullName, ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <th>Телефон</th>
                            <td><?= htmlspecialchars((string)$order['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= htmlspecialchars((string)($order['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <th>Адрес</th>
                            <td><?= htmlspecialchars($customerAddress, ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <th>Индекс</th>
                            <td><?= htmlspecialchars((string)$order['postal_code'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Состав заказа</h3>

                <p>
                    <a class="btn" href="<?= htmlspecialchars(base_url('orders/add_item.php?order_id=' . (int)$order['order_id']), ENT_QUOTES, 'UTF-8') ?>">
                        Добавить позицию
                    </a>
                </p>

                <?php if (empty($orderItems)): ?>
                    <p>Позиции заказа пока отсутствуют.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID позиции</th>
                                <th>ID товара</th>
                                <th>Название товара</th>
                                <th>Категория</th>
                                <th>Производитель</th>
                                <th>Количество</th>
                                <th>Цена за единицу</th>
                                <th>Сумма позиции</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td><?= (int)$item['order_item_id'] ?></td>
                                    <td><?= (int)$item['product_id'] ?></td>
                                    <td><?= htmlspecialchars((string)$item['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)$item['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)$item['manufacturer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int)$item['quantity'] ?></td>
                                    <td><?= number_format((float)$item['unit_price'], 2, '.', ' ') ?></td>
                                    <td><?= number_format((float)$item['line_total'], 2, '.', ' ') ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars(base_url('orders/edit_item.php?id=' . (int)$item['order_item_id']), ENT_QUOTES, 'UTF-8') ?>">
                                            Редактировать
                                        </a>
                                        |
                                        <a
                                            href="<?= htmlspecialchars(base_url('orders/delete_item.php?id=' . (int)$item['order_item_id']), ENT_QUOTES, 'UTF-8') ?>"
                                            onclick="return confirm('Удалить позицию заказа?');"
                                        >
                                            Удалить
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Оплата</h3>

                <?php if (!$payment): ?>
                    <p>Информация об оплате отсутствует.</p>
                <?php else: ?>
                    <table>
                        <tbody>
                            <tr>
                                <th>ID оплаты</th>
                                <td><?= (int)$payment['payment_id'] ?></td>
                            </tr>
                            <tr>
                                <th>Дата оплаты</th>
                                <td><?= htmlspecialchars((string)($payment['payment_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <th>Сумма оплаты</th>
                                <td><?= number_format((float)$payment['payment_amount'], 2, '.', ' ') ?></td>
                            </tr>
                            <tr>
                                <th>Статус оплаты</th>
                                <td><?= htmlspecialchars((string)$payment['payment_status'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <th>Тип оплаты</th>
                                <td><?= htmlspecialchars((string)$payment['payment_type'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Доставка</h3>

                <?php if (!$delivery): ?>
                    <p>Информация о доставке отсутствует.</p>
                <?php else: ?>
                    <table>
                        <tbody>
                            <tr>
                                <th>ID доставки</th>
                                <td><?= (int)$delivery['delivery_id'] ?></td>
                            </tr>
                            <tr>
                                <th>Служба доставки</th>
                                <td><?= htmlspecialchars((string)$delivery['carrier_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <th>Дата отправки</th>
                                <td><?= htmlspecialchars((string)($delivery['ship_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <th>Плановая дата доставки</th>
                                <td><?= htmlspecialchars((string)($delivery['estimated_delivery_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <th>Фактическая дата доставки</th>
                                <td><?= htmlspecialchars((string)($delivery['actual_delivery_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <th>Статус доставки</th>
                                <td><?= htmlspecialchars((string)$delivery['delivery_status'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a class="btn" href="<?= htmlspecialchars(base_url('orders/edit.php?id=' . (int)$order['order_id']), ENT_QUOTES, 'UTF-8') ?>">
                    Редактировать заказ
                </a>
                <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('orders/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                    Назад к списку
                </a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>