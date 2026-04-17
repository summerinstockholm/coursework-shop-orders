<?php
$pageTitle = 'Оплаты';

require_once __DIR__ . '/../includes/header.php';

$payments = [];
$errorMessage = null;

try {
    $pdo = db();

    $stmt = $pdo->query(
        'SELECT
            p.payment_id,
            p.order_id,
            p.payment_date,
            p.payment_amount,
            p.payment_status,
            p.payment_type,
            o.order_date,
            c.last_name,
            c.first_name,
            c.middle_name
         FROM payments p
         INNER JOIN orders o ON o.order_id = p.order_id
         INNER JOIN customers c ON c.customer_id = o.customer_id
         ORDER BY p.payment_id ASC'
    );

    $payments = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Список оплат</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('payments/create.php'), ENT_QUOTES, 'UTF-8') ?>">
                Добавить оплату
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php elseif (empty($payments)): ?>
            <p>Оплаты не найдены.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID оплаты</th>
                        <th>ID заказа</th>
                        <th>Дата заказа</th>
                        <th>Покупатель</th>
                        <th>Дата оплаты</th>
                        <th>Сумма</th>
                        <th>Статус оплаты</th>
                        <th>Тип оплаты</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <?php
                        $customerFullName = trim(
                            (string)$payment['last_name'] . ' ' .
                            (string)$payment['first_name'] . ' ' .
                            (string)($payment['middle_name'] ?? '')
                        );
                        ?>
                        <tr>
                            <td><?= (int)$payment['payment_id'] ?></td>
                            <td><?= (int)$payment['order_id'] ?></td>
                            <td><?= htmlspecialchars((string)$payment['order_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($customerFullName, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($payment['payment_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= number_format((float)$payment['payment_amount'], 2, '.', ' ') ?></td>
                            <td><?= htmlspecialchars((string)$payment['payment_status'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$payment['payment_type'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="<?= htmlspecialchars(base_url('payments/edit.php?id=' . (int)$payment['payment_id']), ENT_QUOTES, 'UTF-8') ?>">
                                    Редактировать
                                </a>
                                |
                                <a href="<?= htmlspecialchars(base_url('payments/delete.php?id=' . (int)$payment['payment_id']), ENT_QUOTES, 'UTF-8') ?>"
                                   onclick="return confirm('Удалить оплату?');">
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