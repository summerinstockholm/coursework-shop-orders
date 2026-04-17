<?php
require_once __DIR__ . '/../includes/db.php';

function recalculateOrderTotal(PDO $pdo, int $orderId): void
{
    $sumStmt = $pdo->prepare(
        'SELECT COALESCE(SUM(line_total), 0)
         FROM order_items
         WHERE order_id = :order_id'
    );

    $sumStmt->execute([
        ':order_id' => $orderId,
    ]);

    $totalAmount = (float)$sumStmt->fetchColumn();

    $updateStmt = $pdo->prepare(
        'UPDATE orders
         SET total_amount = :total_amount
         WHERE order_id = :order_id'
    );

    $updateStmt->execute([
        ':total_amount' => $totalAmount,
        ':order_id' => $orderId,
    ]);
}

$pageTitle = 'Редактировать позицию заказа';

$orderItemId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$errorMessage = null;
$orderItem = null;
$order = null;
$products = [];

$formData = [
    'product_id' => '',
    'quantity' => '',
];

if ($orderItemId <= 0) {
    $errorMessage = 'Некорректный идентификатор позиции заказа.';
} else {
    try {
        $pdo = db();

        $itemStmt = $pdo->prepare(
            'SELECT
                oi.order_item_id,
                oi.order_id,
                oi.product_id,
                oi.quantity
             FROM order_items oi
             WHERE oi.order_item_id = :order_item_id'
        );

        $itemStmt->execute([
            ':order_item_id' => $orderItemId,
        ]);

        $orderItem = $itemStmt->fetch();

        if (!$orderItem) {
            $errorMessage = 'Позиция заказа не найдена.';
        } else {
            $orderStmt = $pdo->prepare(
                'SELECT
                    o.order_id,
                    o.order_date,
                    c.last_name,
                    c.first_name,
                    c.middle_name
                 FROM orders o
                 INNER JOIN customers c ON c.customer_id = o.customer_id
                 WHERE o.order_id = :order_id'
            );

            $orderStmt->execute([
                ':order_id' => (int)$orderItem['order_id'],
            ]);

            $order = $orderStmt->fetch();

            $productsStmt = $pdo->prepare(
                'SELECT
                    p.product_id,
                    p.product_name,
                    p.price,
                    c.category_name,
                    m.manufacturer_name
                 FROM products p
                 INNER JOIN categories c ON c.category_id = p.category_id
                 INNER JOIN manufacturers m ON m.manufacturer_id = p.manufacturer_id
                 WHERE p.product_id = :current_product_id
                    OR NOT EXISTS (
                        SELECT 1
                        FROM order_items oi
                        WHERE oi.order_id = :order_id
                          AND oi.product_id = p.product_id
                    )
                 ORDER BY p.product_name ASC'
            );

            $productsStmt->execute([
                ':current_product_id' => (int)$orderItem['product_id'],
                ':order_id' => (int)$orderItem['order_id'],
            ]);

            $products = $productsStmt->fetchAll();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                foreach ($formData as $key => $value) {
                    $formData[$key] = trim((string)($_POST[$key] ?? ''));
                }

                if ($formData['product_id'] === '') {
                    $errorMessage = 'Поле «Товар» обязательно для заполнения.';
                }

                if ($errorMessage === null && !ctype_digit($formData['product_id'])) {
                    $errorMessage = 'Некорректный товар.';
                }

                if ($errorMessage === null && $formData['quantity'] === '') {
                    $errorMessage = 'Поле «Количество» обязательно для заполнения.';
                }

                if ($errorMessage === null && (!ctype_digit($formData['quantity']) || (int)$formData['quantity'] <= 0)) {
                    $errorMessage = 'Количество должно быть положительным целым числом.';
                }

                if ($errorMessage === null) {
                    $productStmt = $pdo->prepare(
                        'SELECT
                            p.product_id,
                            p.price
                         FROM products p
                         WHERE p.product_id = :product_id
                           AND (
                                p.product_id = :current_product_id
                                OR NOT EXISTS (
                                    SELECT 1
                                    FROM order_items oi
                                    WHERE oi.order_id = :order_id
                                      AND oi.product_id = p.product_id
                                      AND oi.order_item_id <> :order_item_id
                                )
                           )'
                    );

                    $productStmt->execute([
                        ':product_id' => (int)$formData['product_id'],
                        ':current_product_id' => (int)$orderItem['product_id'],
                        ':order_id' => (int)$orderItem['order_id'],
                        ':order_item_id' => $orderItemId,
                    ]);

                    $product = $productStmt->fetch();

                    if (!$product) {
                        $errorMessage = 'Выбранный товар не найден или уже добавлен в этот заказ.';
                    } else {
                        $quantity = (int)$formData['quantity'];
                        $unitPrice = (float)$product['price'];
                        $lineTotal = $quantity * $unitPrice;

                        $updateStmt = $pdo->prepare(
                            'UPDATE order_items
                             SET
                                product_id = :product_id,
                                quantity = :quantity,
                                unit_price = :unit_price,
                                line_total = :line_total
                             WHERE order_item_id = :order_item_id'
                        );

                        $updateStmt->execute([
                            ':product_id' => (int)$formData['product_id'],
                            ':quantity' => $quantity,
                            ':unit_price' => $unitPrice,
                            ':line_total' => $lineTotal,
                            ':order_item_id' => $orderItemId,
                        ]);

                        recalculateOrderTotal($pdo, (int)$orderItem['order_id']);

                        header('Location: ' . base_url('orders/view.php?id=' . (int)$orderItem['order_id']));
                        exit;
                    }
                }
            } else {
                $formData['product_id'] = (string)$orderItem['product_id'];
                $formData['quantity'] = (string)$orderItem['quantity'];
            }
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
        <h2>Редактировать позицию заказа</h2>

        <?php
        $backOrderId = $orderItem !== null ? (int)$orderItem['order_id'] : 0;
        ?>

        <p>
            <a href="<?= htmlspecialchars(base_url('orders/view.php?id=' . $backOrderId), ENT_QUOTES, 'UTF-8') ?>">
                ← Вернуться к заказу
            </a>
        </p>

        <?php if ($order !== null): ?>
            <?php
            $customerFullName = trim(
                (string)$order['last_name'] . ' ' .
                (string)$order['first_name'] . ' ' .
                (string)($order['middle_name'] ?? '')
            );
            ?>
            <p>
                Заказ:
                <strong>#<?= (int)$order['order_id'] ?></strong>,
                покупатель:
                <strong><?= htmlspecialchars($customerFullName, ENT_QUOTES, 'UTF-8') ?></strong>,
                дата:
                <strong><?= htmlspecialchars((string)$order['order_date'], ENT_QUOTES, 'UTF-8') ?></strong>
            </p>
        <?php endif; ?>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($orderItem !== null && !empty($products) && ($errorMessage === null || $_SERVER['REQUEST_METHOD'] === 'POST')): ?>
            <form method="post" action="<?= htmlspecialchars(base_url('orders/edit_item.php?id=' . $orderItemId), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" value="<?= (int)$orderItemId ?>">

                <div class="form-grid">
                    <div class="form-group form-group-full">
                        <label for="product_id">Товар *</label>
                        <select id="product_id" name="product_id" required>
                            <option value="">Выберите товар</option>
                            <?php foreach ($products as $product): ?>
                                <?php
                                $optionText = (string)$product['product_name']
                                    . ' — ' . (string)$product['category_name']
                                    . ' — ' . (string)$product['manufacturer_name']
                                    . ' — ' . number_format((float)$product['price'], 2, '.', ' ');
                                ?>
                                <option
                                    value="<?= (int)$product['product_id'] ?>"
                                    <?= $formData['product_id'] === (string)$product['product_id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($optionText, ENT_QUOTES, 'UTF-8') ?>
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
                    <button type="submit">Сохранить изменения</button>
                    <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('orders/view.php?id=' . $backOrderId), ENT_QUOTES, 'UTF-8') ?>">
                        Отмена
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>