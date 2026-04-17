<?php
$pageTitle = 'Удаление оплаты';

require_once __DIR__ . '/../includes/header.php';

$paymentId = (int)($_GET['id'] ?? 0);

$errorMessage = null;

if ($paymentId <= 0) {
    $errorMessage = 'Некорректный идентификатор оплаты.';
} else {
    try {
        $pdo = db();

        $checkStmt = $pdo->prepare(
            'SELECT payment_id
             FROM payments
             WHERE payment_id = :payment_id'
        );

        $checkStmt->execute([
            ':payment_id' => $paymentId,
        ]);

        $payment = $checkStmt->fetch();

        if (!$payment) {
            $errorMessage = 'Оплата не найдена.';
        } else {
            $deleteStmt = $pdo->prepare(
                'DELETE FROM payments
                 WHERE payment_id = :payment_id'
            );

            $deleteStmt->execute([
                ':payment_id' => $paymentId,
            ]);

            header('Location: ' . base_url('payments/list.php'));
            exit;
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $errorMessage = 'Нельзя удалить оплату, потому что с ней связаны другие записи.';
        } else {
            $errorMessage = 'Не удалось удалить оплату. Попробуй еще раз.';
        }
    } catch (Throwable $e) {
        $errorMessage = 'Произошла непредвиденная ошибка при удалении оплаты.';
    }
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Удаление оплаты</h2>

        <div class="error-box">
            <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('payments/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                Вернуться к списку оплат
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>