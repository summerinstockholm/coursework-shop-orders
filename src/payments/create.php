<?php
$pageTitle = 'Добавить оплату';

require_once __DIR__ . '/../includes/db.php';

$errorMessage = null;

$formData = [
    'order_id' => '',
    'payment_date' => '',
    'payment_amount' => '',
    'payment_status' => '',
    'payment_type' => '',
];

$paymentStatuses = [
    'ожидает оплаты',
    'оплачено',
    'отменено',
];

$paymentTypes = [
    'банковская карта',
    'СБП',
    'банковский перевод',
    'наличными',
];

$availableOrders = [];

try {
    $pdo = db();

    $availableOrders = $pdo->query(
        'SELECT
            o.order_id,
            o.order_date,
            o.total_amount,
            c.last_name,
            c.first_name,
            c.middle_name
         FROM orders o
         INNER JOIN customers c ON c.customer_id = o.customer_id
         LEFT JOIN payments p ON p.order_id = o.order_id
         WHERE p.payment_id IS NULL
         ORDER BY o.order_id ASC'
    )->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($formData as $key => $value) {
            $formData[$key] = trim((string)($_POST[$key] ?? ''));
        }

        $requiredFields = [
            'order_id' => 'Заказ',
            'payment_amount' => 'Сумма оплаты',
            'payment_status' => 'Статус оплаты',
            'payment_type' => 'Тип оплаты',
        ];

        foreach ($requiredFields as $field => $label) {
            if ($formData[$field] === '') {
                $errorMessage = "Поле «{$label}» обязательно для заполнения.";
                break;
            }
        }

        if ($errorMessage === null && !ctype_digit($formData['order_id'])) {
            $errorMessage = 'Некорректный заказ.';
        }

        if ($errorMessage === null && !is_numeric($formData['payment_amount'])) {
            $errorMessage = 'Поле «Сумма оплаты» должно быть числом.';
        }

        if ($errorMessage === null && (float)$formData['payment_amount'] < 0) {
            $errorMessage = 'Поле «Сумма оплаты» не может быть отрицательным.';
        }

        if ($errorMessage === null && !in_array($formData['payment_status'], $paymentStatuses, true)) {
            $errorMessage = 'Некорректный статус оплаты.';
        }

        if ($errorMessage === null && !in_array($formData['payment_type'], $paymentTypes, true)) {
            $errorMessage = 'Некорректный тип оплаты.';
        }

        if ($errorMessage === null && $formData['payment_date'] !== '') {
            $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $formData['payment_date']);
            if (!$dateTime || $dateTime->format('Y-m-d\TH:i') !== $formData['payment_date']) {
                $errorMessage = 'Некорректная дата оплаты.';
            }
        }

        if ($errorMessage === null) {
            $checkStmt = $pdo->prepare(
                'SELECT
                    o.order_id,
                    p.payment_id
                 FROM orders o
                 LEFT JOIN payments p ON p.order_id = o.order_id
                 WHERE o.order_id = :order_id'
            );

            $checkStmt->execute([
                ':order_id' => (int)$formData['order_id'],
            ]);

            $orderCheck = $checkStmt->fetch();

            if (!$orderCheck) {
                $errorMessage = 'Выбранный заказ не существует.';
            } elseif (!empty($orderCheck['payment_id'])) {
                $errorMessage = 'Для выбранного заказа оплата уже существует.';
            }
        }

        if ($errorMessage === null) {
            $paymentDate = null;

            if ($formData['payment_date'] !== '') {
                $paymentDate = str_replace('T', ' ', $formData['payment_date']);
            }

            $stmt = $pdo->prepare(
                'INSERT INTO payments
                (
                    order_id,
                    payment_date,
                    payment_amount,
                    payment_status,
                    payment_type
                )
                VALUES
                (
                    :order_id,
                    :payment_date,
                    :payment_amount,
                    :payment_status,
                    :payment_type
                )'
            );

            $stmt->execute([
                ':order_id' => (int)$formData['order_id'],
                ':payment_date' => $paymentDate,
                ':payment_amount' => $formData['payment_amount'],
                ':payment_status' => $formData['payment_status'],
                ':payment_type' => $formData['payment_type'],
            ]);

            header('Location: ' . base_url('payments/list.php'));
            exit;
        }
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Добавить оплату</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('payments/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                ← Вернуться к списку оплат
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (empty($availableOrders)): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> Нет доступных заказов без оплаты.
            </div>
        <?php else: ?>
            <form method="post" action="<?= htmlspecialchars(base_url('payments/create.php'), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-grid">
                    <div class="form-group form-group-full">
                        <label for="order_id">Заказ *</label>
                        <select id="order_id" name="order_id" required>
                            <option value="">Выберите заказ</option>
                            <?php foreach ($availableOrders as $order): ?>
                                <?php
                                $customerFullName = trim(
                                    (string)$order['last_name'] . ' ' .
                                    (string)$order['first_name'] . ' ' .
                                    (string)($order['middle_name'] ?? '')
                                );

                                $optionText = 'Заказ #' . (int)$order['order_id']
                                    . ' — ' . $customerFullName
                                    . ' — ' . (string)$order['order_date']
                                    . ' — ' . number_format((float)$order['total_amount'], 2, '.', ' ');
                                ?>
                                <option
                                    value="<?= (int)$order['order_id'] ?>"
                                    <?= $formData['order_id'] === (string)$order['order_id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($optionText, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment_date">Дата оплаты</label>
                        <input
                            type="datetime-local"
                            id="payment_date"
                            name="payment_date"
                            value="<?= htmlspecialchars($formData['payment_date'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="payment_amount">Сумма оплаты *</label>
                        <input
                            type="number"
                            id="payment_amount"
                            name="payment_amount"
                            step="0.01"
                            min="0"
                            value="<?= htmlspecialchars($formData['payment_amount'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="payment_status">Статус оплаты *</label>
                        <select id="payment_status" name="payment_status" required>
                            <option value="">Выберите статус оплаты</option>
                            <?php foreach ($paymentStatuses as $paymentStatus): ?>
                                <option
                                    value="<?= htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $formData['payment_status'] === $paymentStatus ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment_type">Тип оплаты *</label>
                        <select id="payment_type" name="payment_type" required>
                            <option value="">Выберите тип оплаты</option>
                            <?php foreach ($paymentTypes as $paymentType): ?>
                                <option
                                    value="<?= htmlspecialchars($paymentType, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $formData['payment_type'] === $paymentType ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($paymentType, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit">Сохранить</button>
                    <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('payments/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                        Отмена
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>