<?php
$pageTitle = 'Добавить заказ';

$errorMessage = null;

$formData = [
    'order_date' => date('Y-m-d\TH:i'),
    'status' => 'новый',
    'customer_id' => '',
    'payment_method' => '',
    'delivery_method' => '',
    'delivery_address' => '',
    'product_id' => '',
    'quantity' => '1',
];

$statuses = [
    'новый',
    'собирается',
    'оплачен',
    'в доставке',
    'доставлен',
    'отменен',
];

$paymentMethods = [
    'банковская карта',
    'СБП',
    'банковский перевод',
    'наличными',
];

$deliveryMethods = [
    'курьер',
    'ПВЗ',
    'самовывоз',
];

$customers = [];
$products = [];

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = db();

    $customers = $pdo->query(
        'SELECT
            customer_id,
            last_name,
            first_name,
            middle_name,
            city,
            street,
            house,
            apartment
         FROM customers
         ORDER BY last_name ASC, first_name ASC, middle_name ASC'
    )->fetchAll();

    $products = $pdo->query(
        'SELECT
            p.product_id,
            p.product_name,
            p.price,
            p.stock_qty,
            w.warehouse_name
         FROM products p
         INNER JOIN warehouses w ON w.warehouse_id = p.warehouse_id
         WHERE p.stock_qty > 0
         ORDER BY p.product_name ASC, w.warehouse_name ASC'
    )->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($formData as $key => $value) {
            $formData[$key] = trim((string)($_POST[$key] ?? ''));
        }

        $requiredFields = [
            'order_date' => 'Дата заказа',
            'status' => 'Статус',
            'customer_id' => 'Покупатель',
            'payment_method' => 'Способ оплаты',
            'delivery_method' => 'Способ доставки',
            'delivery_address' => 'Адрес доставки',
            'product_id' => 'Товар',
            'quantity' => 'Количество',
        ];

        foreach ($requiredFields as $field => $label) {
            if ($formData[$field] === '') {
                $errorMessage = "Поле «{$label}» обязательно для заполнения.";
                break;
            }
        }

        if ($errorMessage === null && !in_array($formData['status'], $statuses, true)) {
            $errorMessage = 'Некорректный статус заказа.';
        }

        if ($errorMessage === null && !in_array($formData['payment_method'], $paymentMethods, true)) {
            $errorMessage = 'Некорректный способ оплаты.';
        }

        if ($errorMessage === null && !in_array($formData['delivery_method'], $deliveryMethods, true)) {
            $errorMessage = 'Некорректный способ доставки.';
        }

        if ($errorMessage === null && !ctype_digit($formData['customer_id'])) {
            $errorMessage = 'Некорректный покупатель.';
        }

        if ($errorMessage === null && !ctype_digit($formData['product_id'])) {
            $errorMessage = 'Некорректный товар.';
        }

        if ($errorMessage === null && (!ctype_digit($formData['quantity']) || (int)$formData['quantity'] <= 0)) {
            $errorMessage = 'Количество должно быть положительным целым числом.';
        }

        if ($errorMessage === null) {
            $orderDate = DateTime::createFromFormat('Y-m-d\TH:i', $formData['order_date']);
            if (!$orderDate || $orderDate->format('Y-m-d\TH:i') !== $formData['order_date']) {
                $errorMessage = 'Некорректная дата заказа.';
            }
        }

        if ($errorMessage === null) {
            $customerCheckStmt = $pdo->prepare(
                'SELECT customer_id
                 FROM customers
                 WHERE customer_id = :customer_id'
            );

            $customerCheckStmt->execute([
                ':customer_id' => (int)$formData['customer_id'],
            ]);

            if (!$customerCheckStmt->fetch()) {
                $errorMessage = 'Выбранный покупатель не найден.';
            }
        }

        if ($errorMessage === null) {
            try {
                $pdo->beginTransaction();

                $productStmt = $pdo->prepare(
                    'SELECT
                        product_id,
                        product_name,
                        price,
                        stock_qty
                     FROM products
                     WHERE product_id = :product_id
                     FOR UPDATE'
                );

                $productStmt->execute([
                    ':product_id' => (int)$formData['product_id'],
                ]);

                $product = $productStmt->fetch();

                if (!$product) {
                    $errorMessage = 'Выбранный товар не найден.';
                    $pdo->rollBack();
                } else {
                    $requestedQty = (int)$formData['quantity'];
                    $availableQty = (int)$product['stock_qty'];

                    if ($availableQty < $requestedQty) {
                        $errorMessage = 'Недостаточно товара на складе для оформления заказа.';
                        $pdo->rollBack();
                    } else {
                        $unitPrice = (float)$product['price'];
                        $lineTotal = $unitPrice * $requestedQty;
                        $orderDateSql = str_replace('T', ' ', $formData['order_date']);

                        $insertOrderStmt = $pdo->prepare(
                            'INSERT INTO orders
                            (
                                order_date,
                                status,
                                total_amount,
                                customer_id,
                                payment_method,
                                delivery_method,
                                delivery_address
                            )
                            VALUES
                            (
                                :order_date,
                                :status,
                                :total_amount,
                                :customer_id,
                                :payment_method,
                                :delivery_method,
                                :delivery_address
                            )'
                        );

                        $insertOrderStmt->execute([
                            ':order_date' => $orderDateSql,
                            ':status' => $formData['status'],
                            ':total_amount' => $lineTotal,
                            ':customer_id' => (int)$formData['customer_id'],
                            ':payment_method' => $formData['payment_method'],
                            ':delivery_method' => $formData['delivery_method'],
                            ':delivery_address' => $formData['delivery_address'],
                        ]);

                        $orderId = (int)$pdo->lastInsertId();

                        $insertItemStmt = $pdo->prepare(
                            'INSERT INTO order_items
                            (
                                order_id,
                                product_id,
                                quantity,
                                unit_price,
                                line_total
                            )
                            VALUES
                            (
                                :order_id,
                                :product_id,
                                :quantity,
                                :unit_price,
                                :line_total
                            )'
                        );

                        $insertItemStmt->execute([
                            ':order_id' => $orderId,
                            ':product_id' => (int)$product['product_id'],
                            ':quantity' => $requestedQty,
                            ':unit_price' => $unitPrice,
                            ':line_total' => $lineTotal,
                        ]);

                        $updateStockStmt = $pdo->prepare(
                            'UPDATE products
                             SET stock_qty = stock_qty - :quantity
                             WHERE product_id = :product_id'
                        );

                        $updateStockStmt->execute([
                            ':quantity' => $requestedQty,
                            ':product_id' => (int)$product['product_id'],
                        ]);

                        $pdo->commit();

                        header('Location: ' . base_url('orders/view.php?id=' . $orderId));
                        exit;
                    }
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errorMessage = 'Не удалось создать заказ.';
            }
        }
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Добавить заказ</h2>

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
        <?php endif; ?>

        <?php if (empty($customers)): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> Невозможно создать заказ, пока в системе нет ни одного покупателя.
            </div>
        <?php elseif (empty($products)): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> Невозможно создать заказ, пока в системе нет товаров с положительным остатком.
            </div>
        <?php else: ?>
            <form method="post" action="<?= htmlspecialchars(base_url('orders/create.php'), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="order_date">Дата заказа *</label>
                        <input
                            type="datetime-local"
                            id="order_date"
                            name="order_date"
                            value="<?= htmlspecialchars($formData['order_date'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="status">Статус *</label>
                        <select id="status" name="status" required>
                            <option value="">Выберите статус</option>
                            <?php foreach ($statuses as $status): ?>
                                <option
                                    value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $formData['status'] === $status ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group form-group-full">
                        <label for="customer_id">Покупатель *</label>
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
                                    <?= $formData['customer_id'] === (string)$customer['customer_id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($customerFullName, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Способ оплаты *</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Выберите способ оплаты</option>
                            <?php foreach ($paymentMethods as $paymentMethod): ?>
                                <option
                                    value="<?= htmlspecialchars($paymentMethod, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $formData['payment_method'] === $paymentMethod ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($paymentMethod, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="delivery_method">Способ доставки *</label>
                        <select id="delivery_method" name="delivery_method" required>
                            <option value="">Выберите способ доставки</option>
                            <?php foreach ($deliveryMethods as $deliveryMethod): ?>
                                <option
                                    value="<?= htmlspecialchars($deliveryMethod, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $formData['delivery_method'] === $deliveryMethod ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($deliveryMethod, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group form-group-full">
                        <label for="delivery_address">Адрес доставки *</label>
                        <input
                            type="text"
                            id="delivery_address"
                            name="delivery_address"
                            value="<?= htmlspecialchars($formData['delivery_address'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="form-group form-group-full">
                        <label for="product_id">Товар *</label>
                        <select id="product_id" name="product_id" required>
                            <option value="">Выберите товар</option>
                            <?php foreach ($products as $product): ?>
                                <?php
                                $productText = (string)$product['product_name']
                                    . ' — ' . (string)$product['warehouse_name']
                                    . ' — остаток: ' . (int)$product['stock_qty']
                                    . ' — цена: ' . number_format((float)$product['price'], 2, '.', ' ');
                                ?>
                                <option
                                    value="<?= (int)$product['product_id'] ?>"
                                    <?= $formData['product_id'] === (string)$product['product_id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($productText, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Количество *</label>
                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            min="1"
                            step="1"
                            value="<?= htmlspecialchars($formData['quantity'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit">Сохранить</button>
                    <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('orders/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                        Отмена
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>