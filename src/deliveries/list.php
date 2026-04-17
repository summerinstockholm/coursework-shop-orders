<?php
$pageTitle = 'Доставки';

require_once __DIR__ . '/../includes/header.php';

$deliveries = [];
$errorMessage = null;

try {
    $pdo = db();

    $stmt = $pdo->query(
        'SELECT
            d.delivery_id,
            d.order_id,
            d.carrier_name,
            d.ship_date,
            d.estimated_delivery_date,
            d.actual_delivery_date,
            d.delivery_status,
            o.order_date,
            c.last_name,
            c.first_name,
            c.middle_name
         FROM deliveries d
         INNER JOIN orders o ON o.order_id = d.order_id
         INNER JOIN customers c ON c.customer_id = o.customer_id
         ORDER BY d.delivery_id ASC'
    );

    $deliveries = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Список доставок</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('deliveries/create.php'), ENT_QUOTES, 'UTF-8') ?>">
                Добавить доставку
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php elseif (empty($deliveries)): ?>
            <p>Доставки не найдены.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID доставки</th>
                        <th>ID заказа</th>
                        <th>Дата заказа</th>
                        <th>Покупатель</th>
                        <th>Служба доставки</th>
                        <th>Дата отправки</th>
                        <th>Плановая дата доставки</th>
                        <th>Фактическая дата доставки</th>
                        <th>Статус доставки</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deliveries as $delivery): ?>
                        <?php
                        $customerFullName = trim(
                            (string)$delivery['last_name'] . ' ' .
                            (string)$delivery['first_name'] . ' ' .
                            (string)($delivery['middle_name'] ?? '')
                        );
                        ?>
                        <tr>
                            <td><?= (int)$delivery['delivery_id'] ?></td>
                            <td><?= (int)$delivery['order_id'] ?></td>
                            <td><?= htmlspecialchars((string)$delivery['order_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($customerFullName, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$delivery['carrier_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($delivery['ship_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($delivery['estimated_delivery_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($delivery['actual_delivery_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$delivery['delivery_status'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="<?= htmlspecialchars(base_url('deliveries/edit.php?id=' . (int)$delivery['delivery_id']), ENT_QUOTES, 'UTF-8') ?>">
                                    Редактировать
                                </a>
                                |
                                <a href="<?= htmlspecialchars(base_url('deliveries/delete.php?id=' . (int)$delivery['delivery_id']), ENT_QUOTES, 'UTF-8') ?>"
                                   onclick="return confirm('Удалить доставку?');">
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