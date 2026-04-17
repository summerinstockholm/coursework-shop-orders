<?php
$pageTitle = 'Добавить производителя';

require_once __DIR__ . '/../includes/db.php';

$errorMessage = null;

$formData = [
    'manufacturer_name' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['manufacturer_name'] = trim((string)($_POST['manufacturer_name'] ?? ''));

    if ($formData['manufacturer_name'] === '') {
        $errorMessage = 'Поле «Название производителя» обязательно для заполнения.';
    }

    if ($errorMessage === null) {
        try {
            $pdo = db();

            $stmt = $pdo->prepare(
                'INSERT INTO manufacturers (manufacturer_name)
                 VALUES (:manufacturer_name)'
            );

            $stmt->execute([
                ':manufacturer_name' => $formData['manufacturer_name'],
            ]);

            header('Location: ' . base_url('manufacturers/list.php'));
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errorMessage = 'Такой производитель уже существует.';
            } else {
                $errorMessage = 'Не удалось добавить производителя.';
            }
        } catch (Throwable $e) {
            $errorMessage = 'Произошла непредвиденная ошибка при добавлении производителя.';
        }
    }
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Добавить производителя</h2>

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

        <form method="post" action="<?= htmlspecialchars(base_url('manufacturers/create.php'), ENT_QUOTES, 'UTF-8') ?>">
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
                <button type="submit">Сохранить</button>
                <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('manufacturers/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                    Отмена
                </a>
            </div>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>