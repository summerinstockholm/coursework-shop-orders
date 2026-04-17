<?php
$pageTitle = 'Удаление покупателя';

require_once __DIR__ . '/../includes/db.php';

$customerId = (int)($_GET['id'] ?? 0);

$errorMessage = null;

if ($customerId <= 0) {
    $errorMessage = 'Некорректный идентификатор покупателя.';
} else {
    try {
        $pdo = db();

        $checkStmt = $pdo->prepare(
            'SELECT customer_id
             FROM customers
             WHERE customer_id = :customer_id'
        );

        $checkStmt->execute([
            ':customer_id' => $customerId,
        ]);

        $customer = $checkStmt->fetch();

        if (!$customer) {
            $errorMessage = 'Покупатель не найден.';
        } else {
            $deleteStmt = $pdo->prepare(
                'DELETE FROM customers
                 WHERE customer_id = :customer_id'
            );

            $deleteStmt->execute([
                ':customer_id' => $customerId,
            ]);

            header('Location: ' . base_url('customers/list.php'));
            exit;
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $errorMessage = 'Нельзя удалить покупателя, потому что с ним связаны другие записи, например заказы.';
        } else {
            $errorMessage = 'Не удалось удалить покупателя. Попробуй еще раз.';
        }
    } catch (Throwable $e) {
        $errorMessage = 'Произошла непредвиденная ошибка при удалении покупателя.';
    }
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Удаление покупателя</h2>

        <div class="error-box">
            <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('customers/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                Вернуться к списку покупателей
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>