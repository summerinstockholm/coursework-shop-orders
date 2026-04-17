<?php
$pageTitle = 'Удаление доставки';

require_once __DIR__ . '/../includes/db.php';

$deliveryId = (int)($_GET['id'] ?? 0);

$errorMessage = null;

if ($deliveryId <= 0) {
    $errorMessage = 'Некорректный идентификатор доставки.';
} else {
    try {
        $pdo = db();

        $checkStmt = $pdo->prepare(
            'SELECT delivery_id
             FROM deliveries
             WHERE delivery_id = :delivery_id'
        );

        $checkStmt->execute([
            ':delivery_id' => $deliveryId,
        ]);

        $delivery = $checkStmt->fetch();

        if (!$delivery) {
            $errorMessage = 'Доставка не найдена.';
        } else {
            $deleteStmt = $pdo->prepare(
                'DELETE FROM deliveries
                 WHERE delivery_id = :delivery_id'
            );

            $deleteStmt->execute([
                ':delivery_id' => $deliveryId,
            ]);

            header('Location: ' . base_url('deliveries/list.php'));
            exit;
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $errorMessage = 'Нельзя удалить доставку, потому что с ней связаны другие записи.';
        } else {
            $errorMessage = 'Не удалось удалить доставку. Попробуй еще раз.';
        }
    } catch (Throwable $e) {
        $errorMessage = 'Произошла непредвиденная ошибка при удалении доставки.';
    }
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Удаление доставки</h2>

        <div class="error-box">
            <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('deliveries/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                Вернуться к списку доставок
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>