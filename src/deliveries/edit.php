<?php
$pageTitle = 'Редактировать доставку';

require_once __DIR__ . '/../includes/db.php';

$errorMessage = null;

$deliveryId = (int)($_GET['id'] ?? 0);

if ($deliveryId <= 0) {
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
    require_once __DIR__ . '/../includes/menu.php';
    ?>
    <main>
        <section class="card">
            <h2>Редактировать доставку</h2>
            <div class="error-box">
                <strong>Ошибка:</strong> Некорректный идентификатор доставки.
            </div>
            <p>
                <a href="<?= htmlspecialchars(base_url('deliveries/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                    ← Вернуться к списку доставок
                </a>
            </p>
        </section>
    </main>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$formData = [
    'order_id' => '',
    'carrier_name' => '',
    'ship_date' => '',
    'estimated_delivery_date' => '',
    'actual_delivery_date' => '',
    'delivery_status' => '',
];

$deliveryStatuses = [
    'создана',
    'готовится к отправке',
    'передана в доставку',
    'в пути',
    'доставлено',
    'отменена',
];

$orders = [];

try {
    $pdo = db();

    $orders = $pdo->query(
        'SELECT
            o.order_id,
            o.order_date,
            o.total_amount,
            c.last_name,
            c.first_name,
            c.middle_name
         FROM orders o
         INNER JOIN customers c ON c.customer_id = o.customer_id
         ORDER BY o.order_id ASC'
    )->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($formData as $key => $value) {
            $formData[$key] = trim((string)($_POST[$key] ?? ''));
        }

        $requiredFields = [
            'order_id' => 'Заказ',
            'carrier_name' => 'Служба доставки',
            'delivery_status' => 'Статус доставки',
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

        if ($errorMessage === null && !in_array($formData['delivery_status'], $deliveryStatuses, true)) {
            $errorMessage = 'Некорректный статус доставки.';
        }

        $dateFields = [
            'ship_date' => 'Дата отправки',
            'estimated_delivery_date' => 'Плановая дата доставки',
            'actual_delivery_date' => 'Фактическая дата доставки',
        ];

        foreach ($dateFields as $field => $label) {
            if ($errorMessage === null && $formData[$field] !== '') {
                $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $formData[$field]);
                if (!$dateTime || $dateTime->format('Y-m-d\TH:i') !== $formData[$field]) {
                    $errorMessage = "Поле «{$label}» содержит некорректную дату.";
                    break;
                }
            }
        }

        if ($errorMessage === null) {
            $checkStmt = $pdo->prepare(
                'SELECT delivery_id
                 FROM deliveries
                 WHERE order_id = :order_id
                   AND delivery_id <> :delivery_id'
            );

            $checkStmt->execute([
                ':order_id' => (int)$formData['order_id'],
                ':delivery_id' => $deliveryId,
            ]);

            $existingDelivery = $checkStmt->fetch();

            if ($existingDelivery) {
                $errorMessage = 'Для выбранного заказа уже существует другая запись о доставке.';
            }
        }

        if ($errorMessage === null) {
            $shipDate = $formData['ship_date'] !== '' ? str_replace('T', ' ', $formData['ship_date']) : null;
            $estimatedDeliveryDate = $formData['estimated_delivery_date'] !== '' ? str_replace('T', ' ', $formData['estimated_delivery_date']) : null;
            $actualDeliveryDate = $formData['actual_delivery_date'] !== '' ? str_replace('T', ' ', $formData['actual_delivery_date']) : null;

            $stmt = $pdo->prepare(
                'UPDATE deliveries
                 SET
                    order_id = :order_id,
                    carrier_name = :carrier_name,
                    ship_date = :ship_date,
                    estimated_delivery_date = :estimated_delivery_date,
                    actual_delivery_date = :actual_delivery_date,
                    delivery_status = :delivery_status
                 WHERE delivery_id = :delivery_id'
            );

            $stmt->execute([
                ':order_id' => (int)$formData['order_id'],
                ':carrier_name' => $formData['carrier_name'],
                ':ship_date' => $shipDate,
                ':estimated_delivery_date' => $estimatedDeliveryDate,
                ':actual_delivery_date' => $actualDeliveryDate,
                ':delivery_status' => $formData['delivery_status'],
                ':delivery_id' => $deliveryId,
            ]);

            header('Location: ' . base_url('deliveries/list.php'));
            exit;
        }
    } else {
        $stmt = $pdo->prepare(
            'SELECT
                delivery_id,
                order_id,
                carrier_name,
                ship_date,
                estimated_delivery_date,
                actual_delivery_date,
                delivery_status
             FROM deliveries
             WHERE delivery_id = :delivery_id'
        );

        $stmt->execute([
            ':delivery_id' => $deliveryId,
        ]);

        $delivery = $stmt->fetch();

        if (!$delivery) {
            $errorMessage = 'Доставка не найдена.';
        } else {
            $formData['order_id'] = (string)$delivery['order_id'];
            $formData['carrier_name'] = (string)$delivery['carrier_name'];
            $formData['ship_date'] = !empty($delivery['ship_date'])
                ? date('Y-m-d\TH:i', strtotime((string)$delivery['ship_date']))
                : '';
            $formData['estimated_delivery_date'] = !empty($delivery['estimated_delivery_date'])
                ? date('Y-m-d\TH:i', strtotime((string)$delivery['estimated_delivery_date']))
                : '';
            $formData['actual_delivery_date'] = !empty($delivery['actual_delivery_date'])
                ? date('Y-m-d\TH:i', strtotime((string)$delivery['actual_delivery_date']))
                : '';
            $formData['delivery_status'] = (string)$delivery['delivery_status'];
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
        <h2>Редактировать доставку</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('deliveries/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                ← Вернуться к списку доставок
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($orders) && ($errorMessage === null || $_SERVER['REQUEST_METHOD'] === 'POST')): ?>
            <form method="post" action="<?= htmlspecialchars(base_url('deliveries/edit.php?id=' . $deliveryId), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-grid">
                    <div class="form-group form-group-full">
                        <label for="order_id">Заказ *</label>
                        <select id="order_id" name="order_id" required>
                            <option value="">Выберите заказ</option>
                            <?php foreach ($orders as $order): ?>
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
                        <label for="carrier_name">Служба доставки *</label>
                        <input
                            type="text"
                            id="carrier_name"
                            name="carrier_name"
                            value="<?= htmlspecialchars($formData['carrier_name'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="delivery_status">Статус доставки *</label>
                        <select id="delivery_status" name="delivery_status" required>
                            <option value="">Выберите статус доставки</option>
                            <?php foreach ($deliveryStatuses as $deliveryStatus): ?>
                                <option
                                    value="<?= htmlspecialchars($deliveryStatus, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $formData['delivery_status'] === $deliveryStatus ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($deliveryStatus, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ship_date">Дата отправки</label>
                        <input
                            type="datetime-local"
                            id="ship_date"
                            name="ship_date"
                            value="<?= htmlspecialchars($formData['ship_date'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="estimated_delivery_date">Плановая дата доставки</label>
                        <input
                            type="datetime-local"
                            id="estimated_delivery_date"
                            name="estimated_delivery_date"
                            value="<?= htmlspecialchars($formData['estimated_delivery_date'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="actual_delivery_date">Фактическая дата доставки</label>
                        <input
                            type="datetime-local"
                            id="actual_delivery_date"
                            name="actual_delivery_date"
                            value="<?= htmlspecialchars($formData['actual_delivery_date'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit">Сохранить изменения</button>
                    <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('deliveries/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                        Отмена
                    </a>
                </div>
            </form>
        <?php elseif (empty($orders)): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> В системе нет заказов для привязки доставки.
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>