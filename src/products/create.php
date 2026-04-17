<?php
$pageTitle = 'Добавить товар';

require_once __DIR__ . '/../includes/db.php';

$errorMessage = null;

$formData = [
    'product_name' => '',
    'description' => '',
    'price' => '',
    'stock_qty' => '',
    'category_id' => '',
    'manufacturer_id' => '',
    'warehouse_id' => '',
];

$categories = [];
$manufacturers = [];
$warehouses = [];

try {
    $pdo = db();

    $categories = $pdo->query(
        'SELECT category_id, category_name
         FROM categories
         ORDER BY category_name ASC'
    )->fetchAll();

    $manufacturers = $pdo->query(
        'SELECT manufacturer_id, manufacturer_name
         FROM manufacturers
         ORDER BY manufacturer_name ASC'
    )->fetchAll();

    $warehouses = $pdo->query(
        'SELECT warehouse_id, warehouse_name
         FROM warehouses
         ORDER BY warehouse_name ASC'
    )->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($formData as $key => $value) {
            $formData[$key] = trim((string)($_POST[$key] ?? ''));
        }

        $requiredFields = [
            'product_name' => 'Название товара',
            'price' => 'Цена',
            'stock_qty' => 'Остаток',
            'category_id' => 'Категория',
            'manufacturer_id' => 'Производитель',
            'warehouse_id' => 'Склад',
        ];

        foreach ($requiredFields as $field => $label) {
            if ($formData[$field] === '') {
                $errorMessage = "Поле «{$label}» обязательно для заполнения.";
                break;
            }
        }

        if ($errorMessage === null && !is_numeric($formData['price'])) {
            $errorMessage = 'Поле «Цена» должно быть числом.';
        }

        if ($errorMessage === null && (!ctype_digit($formData['stock_qty']) || (int)$formData['stock_qty'] < 0)) {
            $errorMessage = 'Поле «Остаток» должно быть неотрицательным целым числом.';
        }

        if ($errorMessage === null && !ctype_digit($formData['category_id'])) {
            $errorMessage = 'Некорректная категория.';
        }

        if ($errorMessage === null && !ctype_digit($formData['manufacturer_id'])) {
            $errorMessage = 'Некорректный производитель.';
        }

        if ($errorMessage === null && !ctype_digit($formData['warehouse_id'])) {
            $errorMessage = 'Некорректный склад.';
        }

        if ($errorMessage === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO products
                (
                    product_name,
                    description,
                    price,
                    stock_qty,
                    category_id,
                    manufacturer_id,
                    warehouse_id
                )
                VALUES
                (
                    :product_name,
                    :description,
                    :price,
                    :stock_qty,
                    :category_id,
                    :manufacturer_id,
                    :warehouse_id
                )'
            );

            $stmt->execute([
                ':product_name' => $formData['product_name'],
                ':description' => $formData['description'] !== '' ? $formData['description'] : null,
                ':price' => $formData['price'],
                ':stock_qty' => (int)$formData['stock_qty'],
                ':category_id' => (int)$formData['category_id'],
                ':manufacturer_id' => (int)$formData['manufacturer_id'],
                ':warehouse_id' => (int)$formData['warehouse_id'],
            ]);

            header('Location: ' . base_url('products/list.php'));
            exit;
        }
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Добавить товар</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('products/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                ← Вернуться к списку товаров
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars(base_url('products/create.php'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label for="product_name">Название товара *</label>
                    <input
                        type="text"
                        id="product_name"
                        name="product_name"
                        value="<?= htmlspecialchars($formData['product_name'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="price">Цена *</label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        step="0.01"
                        min="0"
                        value="<?= htmlspecialchars($formData['price'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="stock_qty">Остаток *</label>
                    <input
                        type="number"
                        id="stock_qty"
                        name="stock_qty"
                        min="0"
                        step="1"
                        value="<?= htmlspecialchars($formData['stock_qty'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="category_id">Категория *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?= (int)$category['category_id'] ?>"
                                <?= $formData['category_id'] === (string)$category['category_id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars((string)$category['category_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="manufacturer_id">Производитель *</label>
                    <select id="manufacturer_id" name="manufacturer_id" required>
                        <option value="">Выберите производителя</option>
                        <?php foreach ($manufacturers as $manufacturer): ?>
                            <option
                                value="<?= (int)$manufacturer['manufacturer_id'] ?>"
                                <?= $formData['manufacturer_id'] === (string)$manufacturer['manufacturer_id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars((string)$manufacturer['manufacturer_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="warehouse_id">Склад *</label>
                    <select id="warehouse_id" name="warehouse_id" required>
                        <option value="">Выберите склад</option>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <option
                                value="<?= (int)$warehouse['warehouse_id'] ?>"
                                <?= $formData['warehouse_id'] === (string)$warehouse['warehouse_id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars((string)$warehouse['warehouse_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group form-group-full">
                    <label for="description">Описание</label>
                    <textarea
                        id="description"
                        name="description"
                    ><?= htmlspecialchars($formData['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Сохранить</button>
                <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('products/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                    Отмена
                </a>
            </div>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>