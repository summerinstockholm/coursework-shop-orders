<?php
$pageTitle = 'Удаление склада';

require_once __DIR__ . '/../includes/header.php';

$warehouseId = (int)($_GET['id'] ?? 0);

$errorMessage = null;

if ($warehouseId <= 0) {
    $errorMessage = 'Некорректный идентификатор склада.';
} else {
    try {
        $pdo = db();

        $checkStmt = $pdo->prepare(
            'SELECT warehouse_id
             FROM warehouses
             WHERE warehouse_id = :warehouse_id'
        );

        $checkStmt->execute([
            ':warehouse_id' => $warehouseId,
        ]);

        $warehouse = $checkStmt->fetch();

        if (!$warehouse) {
            $errorMessage = 'Склад не найден.';
        } else {
            $deleteStmt = $pdo->prepare(
                'DELETE FROM warehouses
                 WHERE warehouse_id = :warehouse_id'
            );

            $deleteStmt->execute([
                ':warehouse_id' => $warehouseId,
            ]);

            header('Location: ' . base_url('warehouses/list.php'));
            exit;
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $errorMessage = 'Нельзя удалить склад, потому что он используется в товарах.';
        } else {
            $errorMessage = 'Не удалось удалить склад. Попробуй еще раз.';
        }
    } catch (Throwable $e) {
        $errorMessage = 'Произошла непредвиденная ошибка при удалении склада.';
    }
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Удаление склада</h2>

        <div class="error-box">
            <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('warehouses/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                Вернуться к списку складов
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>