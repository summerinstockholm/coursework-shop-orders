<?php
$pageTitle = 'Добавить заказ';

require_once __DIR__ . '/../includes/header.php';

$errorMessage = null;

$formData = [
    'order_date' => date('Y-m-d\TH:i'),
    'status' => 'новый',
    'total_amount' => '0.00',
    'customer_id' => '',
    'payment_method' => '',
    'delivery_method' => '',
    'delivery_address' => '',
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($formData as $key => $value) {
            $formData[$key] = trim((string)($_POST[$key] ?? ''));
        }

        $requiredFields = [
            'order_date' => 'Дата заказа',
            'status' => 'Статус',
            'total_amount' => 'Сумма',
            'customer_id' => 'Покупатель',
            'payment_method' => 'Способ оплаты',
            'delivery_method' => 'Способ доставки',
            'delivery_address' => 'Адрес доставки',
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

        if ($errorMessage === null && !is_numeric($formData['total_amount'])) {
            $errorMessage = 'Поле «Сумма» должно быть числом.';
        }

        if ($errorMessage === null && (float)$formData['total_amount'] < 0) {
            $errorMessage = 'Поле «Сумма» не может быть отрицательным.';
        }

        if ($errorMessage === null) {
            $orderDate = str_replace('T', ' ', $formData['order_date']);

            $stmt = $pdo->prepare(
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

            $stmt->execute([
                ':order_date' => $orderDate,
                ':status' => $formData['status'],
                ':total_amount' => $formData['total_amount'],
                ':customer_id' => (int)$formData['customer_id'],
                ':payment_method' => $formData['payment_method'],
                ':delivery_method' => $formData['delivery_method'],
                ':delivery_address' => $formData['delivery_address'],
            ]);

            header('Location: ' . base_url('orders/list.php'));
            exit;
        }
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

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
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (empty($customers)): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> Невозможно создать заказ, пока в системе нет ни одного покупателя.
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

                    <div class="form-group">
                        <label for="total_amount">Сумма *</label>
                        <input
                            type="number"
                            id="total_amount"
                            name="total_amount"
                            step="0.01"
                            min="0"
                            value="<?= htmlspecialchars($formData['total_amount'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="customer_id">Покупатель *</label>
                        <select id="customer_id" name="customer_id" required>
                            <option value="">Выберите покупателя</option>
                            <?php foreach ($customers as $customer): ?>
                                <?php
                                $fullName = trim(
                                    (string)$customer['last_name'] . ' ' .
                                    (string)$customer['first_name'] . ' ' .
                                    (string)($customer['middle_name'] ?? '')
                                );

                                $address = trim(
                                    (string)$customer['city'] . ', ' .
                                    (string)$customer['street'] . ', д. ' .
                                    (string)$customer['house'] .
                                    (
                                        ($customer['apartment'] ?? '') !== ''
                                            ? ', кв. ' . (string)$customer['apartment']
                                            : ''
                                    )
                                );
                                ?>
                                <option
                                    value="<?= (int)$customer['customer_id'] ?>"
                                    <?= $formData['customer_id'] === (string)$customer['customer_id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($fullName . ' — ' . $address, ENT_QUOTES, 'UTF-8') ?>
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
                        <textarea
                            id="delivery_address"
                            name="delivery_address"
                            required
                        ><?= htmlspecialchars($formData['delivery_address'], ENT_QUOTES, 'UTF-8') ?></textarea>
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