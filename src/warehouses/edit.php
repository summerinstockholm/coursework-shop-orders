<?php
$pageTitle = 'Редактировать склад';

$errorMessage = null;
$warehouseId = (int)($_GET['id'] ?? 0);

$formData = [
    'warehouse_name' => '',
    'city' => '',
    'street' => '',
    'house' => '',
    'comment' => '',
];

require_once __DIR__ . '/../includes/db.php';

if ($warehouseId <= 0) {
    $errorMessage = 'Некорректный идентификатор склада.';
} else {
    try {
        $pdo = db();

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
                $checkStmt = $pdo->prepare(
                    'SELECT warehouse_id
                     FROM warehouses
                     WHERE warehouse_name = :warehouse_name
                       AND warehouse_id <> :warehouse_id'
                );

                $checkStmt->execute([
                    ':warehouse_name' => $formData['warehouse_name'],
                    ':warehouse_id' => $warehouseId,
                ]);

                $existingWarehouse = $checkStmt->fetch();

                if ($existingWarehouse) {
                    $errorMessage = 'Склад с таким названием уже существует.';
                } else {
                    $stmt = $pdo->prepare(
                        'UPDATE warehouses
                         SET
                            warehouse_name = :warehouse_name,
                            city = :city,
                            street = :street,
                            house = :house,
                            `comment` = :comment
                         WHERE warehouse_id = :warehouse_id'
                    );

                    $stmt->execute([
                        ':warehouse_name' => $formData['warehouse_name'],
                        ':city' => $formData['city'],
                        ':street' => $formData['street'],
                        ':house' => $formData['house'],
                        ':comment' => $formData['comment'] !== '' ? $formData['comment'] : null,
                        ':warehouse_id' => $warehouseId,
                    ]);

                    header('Location: ' . base_url('warehouses/list.php'));
                    exit;
                }
            }
        } else {
            $stmt = $pdo->prepare(
                'SELECT
                    warehouse_id,
                    warehouse_name,
                    city,
                    street,
                    house,
                    `comment`
                 FROM warehouses
                 WHERE warehouse_id = :warehouse_id'
            );

            $stmt->execute([
                ':warehouse_id' => $warehouseId,
            ]);

            $warehouse = $stmt->fetch();

            if (!$warehouse) {
                $errorMessage = 'Склад не найден.';
            } else {
                foreach ($formData as $key => $value) {
                    $formData[$key] = (string)($warehouse[$key] ?? '');
                }
            }
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $errorMessage = 'Склад с таким названием уже существует.';
        } else {
            $errorMessage = 'Не удалось сохранить изменения склада.';
        }
    } catch (Throwable $e) {
        $errorMessage = 'Произошла непредвиденная ошибка при редактировании склада.';
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Редактировать склад</h2>

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

        <?php if ($warehouseId > 0 && ($errorMessage === null || $_SERVER['REQUEST_METHOD'] === 'POST')): ?>
            <form method="post" action="<?= htmlspecialchars(base_url('warehouses/edit.php?id=' . $warehouseId), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-grid">
                    <div class="form-group form-group-full">
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
                        <input
                            type="text"
                            id="comment"
                            name="comment"
                            value="<?= htmlspecialchars($formData['comment'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit">Сохранить изменения</button>
                    <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('warehouses/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                        Отмена
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>