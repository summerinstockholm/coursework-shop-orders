<?php
$pageTitle = 'Удаление категории';

require_once __DIR__ . '/../includes/header.php';

$categoryId = (int)($_GET['id'] ?? 0);

$errorMessage = null;

if ($categoryId <= 0) {
    $errorMessage = 'Некорректный идентификатор категории.';
} else {
    try {
        $pdo = db();

        $checkStmt = $pdo->prepare(
            'SELECT category_id
             FROM categories
             WHERE category_id = :category_id'
        );

        $checkStmt->execute([
            ':category_id' => $categoryId,
        ]);

        $category = $checkStmt->fetch();

        if (!$category) {
            $errorMessage = 'Категория не найдена.';
        } else {
            $deleteStmt = $pdo->prepare(
                'DELETE FROM categories
                 WHERE category_id = :category_id'
            );

            $deleteStmt->execute([
                ':category_id' => $categoryId,
            ]);

            header('Location: ' . base_url('categories/list.php'));
            exit;
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $errorMessage = 'Нельзя удалить категорию, потому что она используется в товарах.';
        } else {
            $errorMessage = 'Не удалось удалить категорию. Попробуй еще раз.';
        }
    } catch (Throwable $e) {
        $errorMessage = 'Произошла непредвиденная ошибка при удалении категории.';
    }
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Удаление категории</h2>

        <div class="error-box">
            <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('categories/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                Вернуться к списку категорий
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>