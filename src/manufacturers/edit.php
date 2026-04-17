<?php
$pageTitle = 'Редактировать производителя';

require_once __DIR__ . '/../includes/header.php';

$errorMessage = null;

$manufacturerId = (int)($_GET['id'] ?? 0);

if ($manufacturerId <= 0) {
    require_once __DIR__ . '/../includes/menu.php';
    ?>
    <main>
        <section class="card">
            <h2>Редактировать производителя</h2>
            <div class="error-box">
                <strong>Ошибка:</strong> Некорректный идентификатор производителя.
            </div>
            <p>
                <a href="<?= htmlspecialchars(base_url('manufacturers/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                    ← Вернуться к списку производителей
                </a>
            </p>
        </section>
    </main>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$formData = [
    'manufacturer_name' => '',
];

try {
    $pdo = db();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formData['manufacturer_name'] = trim((string)($_POST['manufacturer_name'] ?? ''));

        if ($formData['manufacturer_name'] === '') {
            $errorMessage = 'Поле «Название производителя» обязательно для заполнения.';
        }

        if ($errorMessage === null) {
            $stmt = $pdo->prepare(
                'UPDATE manufacturers
                 SET manufacturer_name = :manufacturer_name
                 WHERE manufacturer_id = :manufacturer_id'
            );

            $stmt->execute([
                ':manufacturer_name' => $formData['manufacturer_name'],
                ':manufacturer_id' => $manufacturerId,
            ]);

            header('Location: ' . base_url('manufacturers/list.php'));
            exit;
        }
    } else {
        $stmt = $pdo->prepare(
            'SELECT
                manufacturer_id,
                manufacturer_name
             FROM manufacturers
             WHERE manufacturer_id = :manufacturer_id'
        );

        $stmt->execute([
            ':manufacturer_id' => $manufacturerId,
        ]);

        $manufacturer = $stmt->fetch();

        if (!$manufacturer) {
            $errorMessage = 'Производитель не найден.';
        } else {
            $formData['manufacturer_name'] = (string)$manufacturer['manufacturer_name'];
        }
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $errorMessage = 'Такой производитель уже существует.';
    } else {
        $errorMessage = 'Не удалось обновить производителя.';
    }
} catch (Throwable $e) {
    $errorMessage = 'Произошла непредвиденная ошибка при редактировании производителя.';
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Редактировать производителя</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('manufacturers/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                ← Вернуться к списку производителей
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage === null || $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <form method="post" action="<?= htmlspecialchars(base_url('manufacturers/edit.php?id=' . $manufacturerId), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-grid">
                    <div class="form-group form-group-full">
                        <label for="manufacturer_name">Название производителя *</label>
                        <input
                            type="text"
                            id="manufacturer_name"
                            name="manufacturer_name"
                            value="<?= htmlspecialchars($formData['manufacturer_name'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit">Сохранить изменения</button>
                    <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('manufacturers/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                        Отмена
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>