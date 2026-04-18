<?php
$pageTitle = 'Склады';

require_once __DIR__ . '/../includes/header.php';

$warehouses = [];
$errorMessage = null;

try {
    $pdo = db();

    $stmt = $pdo->query(
        'SELECT
            warehouse_id,
            warehouse_name,
            city,
            street,
            house,
            comment
         FROM warehouses
         ORDER BY warehouse_id ASC'
    );

    $warehouses = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Список складов</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('warehouses/create.php'), ENT_QUOTES, 'UTF-8') ?>">
                Добавить склад
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php elseif (empty($warehouses)): ?>
            <p>Склады не найдены.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название склада</th>
                            <th>Город</th>
                            <th>Улица</th>
                            <th>Дом</th>
                            <th>Комментарий</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <tr>
                                <td><?= (int)$warehouse['warehouse_id'] ?></td>
                                <td><?= htmlspecialchars((string)$warehouse['warehouse_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$warehouse['city'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$warehouse['street'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$warehouse['house'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($warehouse['comment'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="actions-cell">
                                    <a href="<?= htmlspecialchars(base_url('warehouses/edit.php?id=' . (int)$warehouse['warehouse_id']), ENT_QUOTES, 'UTF-8') ?>">
                                        Редактировать
                                    </a>
                                    |
                                    <a
                                        href="<?= htmlspecialchars(base_url('warehouses/delete.php?id=' . (int)$warehouse['warehouse_id']), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="return confirm('Удалить склад?');"
                                    >
                                        Удалить
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>