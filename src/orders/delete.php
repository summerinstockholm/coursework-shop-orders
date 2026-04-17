<?php
$pageTitle = 'Удаление заказа';

require_once __DIR__ . '/../includes/header.php';

$orderId = (int)($_GET['id'] ?? 0);

$errorMessage = null;

if ($orderId <= 0) {
    $errorMessage = 'Некорректный идентификатор заказа.';
} else {
    try {
        $pdo = db();

        $checkStmt = $pdo->prepare(
            'SELECT order_id
             FROM orders
             WHERE order_id = :order_id'
        );

        $checkStmt->execute([
            ':order_id' => $orderId,
        ]);

        $order = $checkStmt->fetch();

        if (!$order) {
            $errorMessage = 'Заказ не найден.';
        } else {
            $deleteStmt = $pdo->prepare(
                'DELETE FROM orders
                 WHERE order_id = :order_id'
            );

            $deleteStmt->execute([
                ':order_id' => $orderId,
            ]);

            header('Location: ' . base_url('orders/list.php'));
            exit;
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $errorMessage = 'Нельзя удалить заказ, потому что с ним связаны другие записи.';
        } else {
            $errorMessage = 'Не удалось удалить заказ. Попробуй еще раз.';
        }
    } catch (Throwable $e) {
        $errorMessage = 'Произошла непредвиденная ошибка при удалении заказа.';
    }
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Удаление заказа</h2>

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