<?php
$pageTitle = 'Редактировать категорию';

$errorMessage = null;
$categoryId = (int)($_GET['id'] ?? 0);

$formData = [
    'category_name' => '',
];

require_once __DIR__ . '/../includes/db.php';

if ($categoryId <= 0) {
    $errorMessage = 'Некорректный идентификатор категории.';
} else {
    try {
        $pdo = db();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($formData as $key => $value) {
                $formData[$key] = trim((string)($_POST[$key] ?? ''));
            }

            if ($formData['category_name'] === '') {
                $errorMessage = 'Поле «Название категории» обязательно для заполнения.';
            }

            if ($errorMessage === null) {
                $checkStmt = $pdo->prepare(
                    'SELECT category_id
                     FROM categories
                     WHERE category_name = :category_name
                       AND category_id <> :category_id'
                );

                $checkStmt->execute([
                    ':category_name' => $formData['category_name'],
                    ':category_id' => $categoryId,
                ]);

                $existingCategory = $checkStmt->fetch();

                if ($existingCategory) {
                    $errorMessage = 'Категория с таким названием уже существует.';
                } else {
                    $stmt = $pdo->prepare(
                        'UPDATE categories
                         SET
                            category_name = :category_name
                         WHERE category_id = :category_id'
                    );

                    $stmt->execute([
                        ':category_name' => $formData['category_name'],
                        ':category_id' => $categoryId,
                    ]);

                    header('Location: ' . base_url('categories/list.php'));
                    exit;
                }
            }
        } else {
            $stmt = $pdo->prepare(
                'SELECT
                    category_id,
                    category_name
                 FROM categories
                 WHERE category_id = :category_id'
            );

            $stmt->execute([
                ':category_id' => $categoryId,
            ]);

            $category = $stmt->fetch();

            if (!$category) {
                $errorMessage = 'Категория не найдена.';
            } else {
                $formData['category_name'] = (string)$category['category_name'];
            }
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $errorMessage = 'Категория с таким названием уже существует.';
        } else {
            $errorMessage = 'Не удалось сохранить изменения категории.';
        }
    } catch (Throwable $e) {
        $errorMessage = 'Произошла непредвиденная ошибка при редактировании категории.';
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Редактировать категорию</h2>

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

        <?php if ($categoryId > 0 && ($errorMessage === null || $_SERVER['REQUEST_METHOD'] === 'POST')): ?>
            <form method="post" action="<?= htmlspecialchars(base_url('categories/edit.php?id=' . $categoryId), ENT_QUOTES, 'UTF-8') ?>">
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
                    <button type="submit">Сохранить изменения</button>
                    <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('categories/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                        Отмена
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>