<?php
$pageTitle = 'Отчёт по заказам за период';

require_once __DIR__ . '/../includes/header.php';

$errorMessage = null;
$orders = [];

$dateFrom = trim((string)($_GET['date_from'] ?? ''));
$dateTo = trim((string)($_GET['date_to'] ?? ''));

if ($dateFrom === '' && $dateTo === '') {
    $dateFrom = date('Y-m-01');
    $dateTo = date('Y-m-d');
}

try {
    if ($dateFrom !== '' || $dateTo !== '') {
        if ($dateFrom === '' || $dateTo === '') {
            $errorMessage = 'Нужно указать обе даты: начало и конец периода.';
        } else {
            $fromDate = DateTime::createFromFormat('Y-m-d', $dateFrom);
            $toDate = DateTime::createFromFormat('Y-m-d', $dateTo);

            $isFromValid = $fromDate && $fromDate->format('Y-m-d') === $dateFrom;
            $isToValid = $toDate && $toDate->format('Y-m-d') === $dateTo;

            if (!$isFromValid || !$isToValid) {
                $errorMessage = 'Некорректный формат даты.';
            } elseif ($dateFrom > $dateTo) {
                $errorMessage = 'Дата начала периода не может быть больше даты окончания.';
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
                     WHERE DATE(o.order_date) BETWEEN :date_from AND :date_to
                     ORDER BY o.order_date DESC, o.order_id DESC'
                );

                $stmt->execute([
                    ':date_from' => $dateFrom,
                    ':date_to' => $dateTo,
                ]);

                $orders = $stmt->fetchAll();
            }
        }
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Отчёт по заказам за период</h2>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="get" action="<?= htmlspecialchars(base_url('reports/orders_by_period.php'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label for="date_from">Дата начала</label>
                    <input
                        type="date"
                        id="date_from"
                        name="date_from"
                        value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="date_to">Дата окончания</label>
                    <input
                        type="date"
                        id="date_to"
                        name="date_to"
                        value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Показать заказы</button>
            </div>
        </form>
    </section>

    <?php if ($dateFrom !== '' && $dateTo !== '' && $errorMessage === null): ?>
        <section class="card">
            <h2>Результаты</h2>

            <p>
                Период:
                <strong>
                    <?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>
                    —
                    <?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>
                </strong>
            </p>

            <?php if (empty($orders)): ?>
                <p>Заказы за указанный период не найдены.</p>
            <?php else: ?>
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
                                <td>
                                    <a href="<?= htmlspecialchars(base_url('orders/view.php?id=' . (int)$order['order_id']), ENT_QUOTES, 'UTF-8') ?>">
                                        Открыть
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>