<?php
require_once __DIR__ . '/../includes/db.php';

function recalculateOrderTotal(PDO $pdo, int $orderId): void
{
    $sumStmt = $pdo->prepare(
        'SELECT COALESCE(SUM(line_total), 0)
         FROM order_items
         WHERE order_id = :order_id'
    );

    $sumStmt->execute([
        ':order_id' => $orderId,
    ]);

    $totalAmount = (float)$sumStmt->fetchColumn();

    $updateStmt = $pdo->prepare(
        'UPDATE orders
         SET total_amount = :total_amount
         WHERE order_id = :order_id'
    );

    $updateStmt->execute([
        ':total_amount' => $totalAmount,
        ':order_id' => $orderId,
    ]);
}

$pageTitle = 'Удаление позиции заказа';

$orderItemId = (int)($_GET['id'] ?? 0);

$errorMessage = null;
$orderId = 0;

if ($orderItemId <= 0) {
    $errorMessage = 'Некорректный идентификатор позиции заказа.';
} else {
    try {
        $pdo = db();

        $itemStmt = $pdo->prepare(
            'SELECT
                order_item_id,
                order_id
             FROM order_items
             WHERE order_item_id = :order_item_id'
        );

        $itemStmt->execute([
            ':order_item_id' => $orderItemId,
        ]);

        $orderItem = $itemStmt->fetch();

        if (!$orderItem) {
            $errorMessage = 'Позиция заказа не найдена.';
        } else {
            $orderId = (int)$orderItem['order_id'];

            $deleteStmt = $pdo->prepare(
                'DELETE FROM order_items
                 WHERE order_item_id = :order_item_id'
            );

            $deleteStmt->execute([
                ':order_item_id' => $orderItemId,
            ]);

            recalculateOrderTotal($pdo, $orderId);

            header('Location: ' . base_url('orders/view.php?id=' . $orderId));
            exit;
        }
    } catch (Throwable $e) {
        $errorMessage = $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Удаление позиции заказа</h2>

        <div class="error-box">
            <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('orders/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                Вернуться к списку заказов
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>