<?php
$pageTitle = 'Удаление производителя';

require_once __DIR__ . '/../includes/db.php';

$manufacturerId = (int)($_GET['id'] ?? 0);

$errorMessage = null;

if ($manufacturerId <= 0) {
    $errorMessage = 'Некорректный идентификатор производителя.';
} else {
    try {
        $pdo = db();

        $checkStmt = $pdo->prepare(
            'SELECT manufacturer_id
             FROM manufacturers
             WHERE manufacturer_id = :manufacturer_id'
        );

        $checkStmt->execute([
            ':manufacturer_id' => $manufacturerId,
        ]);

        $manufacturer = $checkStmt->fetch();

        if (!$manufacturer) {
            $errorMessage = 'Производитель не найден.';
        } else {
            $deleteStmt = $pdo->prepare(
                'DELETE FROM manufacturers
                 WHERE manufacturer_id = :manufacturer_id'
            );

            $deleteStmt->execute([
                ':manufacturer_id' => $manufacturerId,
            ]);

            header('Location: ' . base_url('manufacturers/list.php'));
            exit;
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $errorMessage = 'Нельзя удалить производителя, потому что он используется в товарах.';
        } else {
            $errorMessage = 'Не удалось удалить производителя. Попробуй еще раз.';
        }
    } catch (Throwable $e) {
        $errorMessage = 'Произошла непредвиденная ошибка при удалении производителя.';
    }
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Удаление производителя</h2>

        <div class="error-box">
            <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('manufacturers/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                Вернуться к списку производителей
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>