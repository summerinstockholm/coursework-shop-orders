<?php
$pageTitle = 'Товары';

require_once __DIR__ . '/../includes/header.php';

$products = [];
$errorMessage = null;

try {
    $pdo = db();

    $stmt = $pdo->query(
        'SELECT
            p.product_id,
            p.product_name,
            p.description,
            p.price,
            p.stock_qty,
            c.category_name,
            m.manufacturer_name,
            w.warehouse_name
         FROM products p
         INNER JOIN categories c ON c.category_id = p.category_id
         INNER JOIN manufacturers m ON m.manufacturer_id = p.manufacturer_id
         INNER JOIN warehouses w ON w.warehouse_id = p.warehouse_id
         ORDER BY p.product_id ASC'
    );

    $products = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Список товаров</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('products/create.php'), ENT_QUOTES, 'UTF-8') ?>">
                Добавить товар
            </a>
        </p>

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
                            <th>ID</th>
                            <th>Название</th>
                            <th>Описание</th>
                            <th>Цена</th>
                            <th>Остаток</th>
                            <th>Категория</th>
                            <th>Производитель</th>
                            <th>Склад</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= (int)$product['product_id'] ?></td>
                                <td><?= htmlspecialchars((string)$product['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($product['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= number_format((float)$product['price'], 2, '.', ' ') ?></td>
                                <td><?= (int)$product['stock_qty'] ?></td>
                                <td><?= htmlspecialchars((string)$product['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$product['manufacturer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$product['warehouse_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="actions-cell">
                                    <a href="<?= htmlspecialchars(base_url('products/edit.php?id=' . (int)$product['product_id']), ENT_QUOTES, 'UTF-8') ?>">
                                        Редактировать
                                    </a>
                                    |
                                    <a
                                        href="<?= htmlspecialchars(base_url('products/delete.php?id=' . (int)$product['product_id']), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="return confirm('Удалить товар?');"
                                    >
                                        Удалить
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