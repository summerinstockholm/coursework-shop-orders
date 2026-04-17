<?php
$pageTitle = 'Добавить категорию';

require_once __DIR__ . '/../includes/header.php';

$errorMessage = null;

$formData = [
    'category_name' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['category_name'] = trim((string)($_POST['category_name'] ?? ''));

    if ($formData['category_name'] === '') {
        $errorMessage = 'Поле «Название категории» обязательно для заполнения.';
    }

    if ($errorMessage === null) {
        try {
            $pdo = db();

            $stmt = $pdo->prepare(
                'INSERT INTO categories (category_name)
                 VALUES (:category_name)'
            );

            $stmt->execute([
                ':category_name' => $formData['category_name'],
            ]);

            header('Location: ' . base_url('categories/list.php'));
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
        <h2>Добавить категорию</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('categories/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                ← Вернуться к списку категорий
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars(base_url('categories/create.php'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label for="category_name">Название категории *</label>
                    <input
                        type="text"
                        id="category_name"
                        name="category_name"
                        value="<?= htmlspecialchars($formData['category_name'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Сохранить</button>
                <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('categories/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                    Отмена
                </a>
            </div>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>