<?php
$pageTitle = 'Добавить склад';

require_once __DIR__ . '/../includes/header.php';

$errorMessage = null;

$formData = [
    'warehouse_name' => '',
    'city' => '',
    'street' => '',
    'house' => '',
    'comment' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $key => $value) {
        $formData[$key] = trim((string)($_POST[$key] ?? ''));
    }

    $requiredFields = [
        'warehouse_name' => 'Название склада',
        'city' => 'Город',
        'street' => 'Улица',
        'house' => 'Дом',
    ];

    foreach ($requiredFields as $field => $label) {
        if ($formData[$field] === '') {
            $errorMessage = "Поле «{$label}» обязательно для заполнения.";
            break;
        }
    }

    if ($errorMessage === null) {
        try {
            $pdo = db();

            $stmt = $pdo->prepare(
                'INSERT INTO warehouses
                (
                    warehouse_name,
                    city,
                    street,
                    house,
                    comment
                )
                VALUES
                (
                    :warehouse_name,
                    :city,
                    :street,
                    :house,
                    :comment
                )'
            );

            $stmt->execute([
                ':warehouse_name' => $formData['warehouse_name'],
                ':city' => $formData['city'],
                ':street' => $formData['street'],
                ':house' => $formData['house'],
                ':comment' => $formData['comment'] !== '' ? $formData['comment'] : null,
            ]);

            header('Location: ' . base_url('warehouses/list.php'));
            exit;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Добавить склад</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('warehouses/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                ← Вернуться к списку складов
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars(base_url('warehouses/create.php'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label for="warehouse_name">Название склада *</label>
                    <input
                        type="text"
                        id="warehouse_name"
                        name="warehouse_name"
                        value="<?= htmlspecialchars($formData['warehouse_name'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="city">Город *</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="<?= htmlspecialchars($formData['city'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="street">Улица *</label>
                    <input
                        type="text"
                        id="street"
                        name="street"
                        value="<?= htmlspecialchars($formData['street'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="house">Дом *</label>
                    <input
                        type="text"
                        id="house"
                        name="house"
                        value="<?= htmlspecialchars($formData['house'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group form-group-full">
                    <label for="comment">Комментарий</label>
                    <textarea
                        id="comment"
                        name="comment"
                    ><?= htmlspecialchars($formData['comment'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Сохранить</button>
                <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('warehouses/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                    Отмена
                </a>
            </div>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>