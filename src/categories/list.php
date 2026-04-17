<?php
$pageTitle = 'Категории';

require_once __DIR__ . '/../includes/header.php';

$categories = [];
$errorMessage = null;

try {
    $pdo = db();

    $stmt = $pdo->query(
        'SELECT
            category_id,
            category_name
         FROM categories
         ORDER BY category_id ASC'
    );

    $categories = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Список категорий</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('categories/create.php'), ENT_QUOTES, 'UTF-8') ?>">
                Добавить категорию
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php elseif (empty($categories)): ?>
            <p>Категории не найдены.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название категории</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= (int)$category['category_id'] ?></td>
                            <td><?= htmlspecialchars((string)$category['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="<?= htmlspecialchars(base_url('categories/edit.php?id=' . (int)$category['category_id']), ENT_QUOTES, 'UTF-8') ?>">
                                    Редактировать
                                </a>
                                |
                                <a href="<?= htmlspecialchars(base_url('categories/delete.php?id=' . (int)$category['category_id']), ENT_QUOTES, 'UTF-8') ?>"
                                   onclick="return confirm('Удалить категорию?');">
                                    Удалить
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>