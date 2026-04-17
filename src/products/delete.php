<?php
$pageTitle = 'Удаление товара';

require_once __DIR__ . '/../includes/header.php';

$productId = (int)($_GET['id'] ?? 0);

$errorMessage = null;

if ($productId <= 0) {
    $errorMessage = 'Некорректный идентификатор товара.';
} else {
    try {
        $pdo = db();

        $checkStmt = $pdo->prepare(
            'SELECT product_id
             FROM products
             WHERE product_id = :product_id'
        );

        $checkStmt->execute([
            ':product_id' => $productId,
        ]);

        $product = $checkStmt->fetch();

        if (!$product) {
            $errorMessage = 'Товар не найден.';
        } else {
            $deleteStmt = $pdo->prepare(
                'DELETE FROM products
                 WHERE product_id = :product_id'
            );

            $deleteStmt->execute([
                ':product_id' => $productId,
            ]);

            header('Location: ' . base_url('products/list.php'));
            exit;
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $errorMessage = 'Нельзя удалить товар, потому что он используется в других записях, например в позициях заказа.';
        } else {
            $errorMessage = 'Не удалось удалить товар. Попробуй еще раз.';
        }
    } catch (Throwable $e) {
        $errorMessage = 'Произошла непредвиденная ошибка при удалении товара.';
    }
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Удаление товара</h2>

        <div class="error-box">
            <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('products/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                Вернуться к списку товаров
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>