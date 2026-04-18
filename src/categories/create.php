<?php
$pageTitle = 'Добавить категорию';

$errorMessage = null;

$formData = [
    'category_name' => '',
];

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $key => $value) {
        $formData[$key] = trim((string)($_POST[$key] ?? ''));
    }

    if ($formData['category_name'] === '') {
        $errorMessage = 'Поле «Название категории» обязательно для заполнения.';
    }

    if ($errorMessage === null) {
        try {
            $pdo = db();

            $checkStmt = $pdo->prepare(
                'SELECT category_id
                 FROM categories
                 WHERE category_name = :category_name'
            );

            $checkStmt->execute([
                ':category_name' => $formData['category_name'],
            ]);

            $existingCategory = $checkStmt->fetch();

            if ($existingCategory) {
                $errorMessage = 'Категория с таким названием уже существует.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO categories
                    (
                        category_name
                    )
                    VALUES
                    (
                        :category_name
                    )'
                );

                $stmt->execute([
                    ':category_name' => $formData['category_name'],
                ]);

                header('Location: ' . base_url('categories/list.php'));
                exit;
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errorMessage = 'Категория с таким названием уже существует.';
            } else {
                $errorMessage = 'Не удалось сохранить категорию.';
            }
        } catch (Throwable $e) {
            $errorMessage = 'Произошла непредвиденная ошибка при сохранении категории.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
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