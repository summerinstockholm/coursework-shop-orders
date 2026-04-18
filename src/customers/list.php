<?php
$pageTitle = 'Покупатели';

require_once __DIR__ . '/../includes/header.php';

$customers = [];
$errorMessage = null;

try {
    $pdo = db();

    $stmt = $pdo->query(
        'SELECT
            customer_id,
            last_name,
            first_name,
            middle_name,
            phone,
            email,
            city,
            street,
            house,
            apartment,
            postal_code
         FROM customers
         ORDER BY customer_id ASC'
    );

    $customers = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Список покупателей</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('customers/create.php'), ENT_QUOTES, 'UTF-8') ?>">
                Добавить покупателя
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php elseif (empty($customers)): ?>
            <p>Покупатели не найдены.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Фамилия</th>
                            <th>Имя</th>
                            <th>Отчество</th>
                            <th>Телефон</th>
                            <th>Email</th>
                            <th>Город</th>
                            <th>Улица</th>
                            <th>Дом</th>
                            <th>Квартира</th>
                            <th>Индекс</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= (int)$customer['customer_id'] ?></td>
                                <td><?= htmlspecialchars((string)$customer['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$customer['first_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($customer['middle_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$customer['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($customer['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$customer['city'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$customer['street'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$customer['house'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($customer['apartment'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$customer['postal_code'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="actions-cell">
                                    <a href="<?= htmlspecialchars(base_url('customers/edit.php?id=' . (int)$customer['customer_id']), ENT_QUOTES, 'UTF-8') ?>">
                                        Редактировать
                                    </a>
                                    |
                                    <a
                                        href="<?= htmlspecialchars(base_url('customers/delete.php?id=' . (int)$customer['customer_id']), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="return confirm('Удалить покупателя?');"
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