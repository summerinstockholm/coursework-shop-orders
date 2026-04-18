<?php
$pageTitle = 'Топ-10 товаров';

require_once __DIR__ . '/../includes/header.php';

$products = [];
$errorMessage = null;

try {
    $pdo = db();

    $stmt = $pdo->query(
        'SELECT
            p.product_id,
            p.product_name,
            c.category_name,
            m.manufacturer_name,
            SUM(oi.quantity) AS total_quantity,
            SUM(oi.line_total) AS total_sales_amount,
            COUNT(DISTINCT oi.order_id) AS orders_count
         FROM order_items oi
         INNER JOIN products p ON p.product_id = oi.product_id
         INNER JOIN categories c ON c.category_id = p.category_id
         INNER JOIN manufacturers m ON m.manufacturer_id = p.manufacturer_id
         GROUP BY
            p.product_id,
            p.product_name,
            c.category_name,
            m.manufacturer_name
         ORDER BY total_quantity DESC, p.product_id ASC
         LIMIT 10'
    );

    $products = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Топ-10 товаров по количеству в заказах</h2>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php elseif (empty($products)): ?>
            <p>Товары не найдены.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Место</th>
                            <th>ID товара</th>
                            <th>Название товара</th>
                            <th>Категория</th>
                            <th>Производитель</th>
                            <th>Общее количество</th>
                            <th>Сумма продаж</th>
                            <th>Количество заказов</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $index => $product): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= (int)$product['product_id'] ?></td>
                                <td><?= htmlspecialchars((string)$product['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$product['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$product['manufacturer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int)$product['total_quantity'] ?></td>
                                <td><?= number_format((float)$product['total_sales_amount'], 2, '.', ' ') ?></td>
                                <td><?= (int)$product['orders_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>