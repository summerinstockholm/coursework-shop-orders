<?php
$pageTitle = 'Производители';

require_once __DIR__ . '/../includes/header.php';

$manufacturers = [];
$errorMessage = null;

try {
    $pdo = db();

    $stmt = $pdo->query(
        'SELECT
            manufacturer_id,
            manufacturer_name
         FROM manufacturers
         ORDER BY manufacturer_id ASC'
    );

    $manufacturers = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Список производителей</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('manufacturers/create.php'), ENT_QUOTES, 'UTF-8') ?>">
                Добавить производителя
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php elseif (empty($manufacturers)): ?>
            <p>Производители не найдены.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название производителя</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($manufacturers as $manufacturer): ?>
                        <tr>
                            <td><?= (int)$manufacturer['manufacturer_id'] ?></td>
                            <td><?= htmlspecialchars((string)$manufacturer['manufacturer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="<?= htmlspecialchars(base_url('manufacturers/edit.php?id=' . (int)$manufacturer['manufacturer_id']), ENT_QUOTES, 'UTF-8') ?>">
                                    Редактировать
                                </a>
                                |
                                <a href="<?= htmlspecialchars(base_url('manufacturers/delete.php?id=' . (int)$manufacturer['manufacturer_id']), ENT_QUOTES, 'UTF-8') ?>"
                                   onclick="return confirm('Удалить производителя?');">
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