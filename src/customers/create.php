<?php
$pageTitle = 'Добавить покупателя';

$errorMessage = null;

$formData = [
    'last_name'   => '',
    'first_name'  => '',
    'middle_name' => '',
    'phone'       => '',
    'email'       => '',
    'city'        => '',
    'street'      => '',
    'house'       => '',
    'apartment'   => '',
    'postal_code' => '',
];

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $key => $value) {
        $formData[$key] = trim((string)($_POST[$key] ?? ''));
    }

    $requiredFields = [
        'last_name'   => 'Фамилия',
        'first_name'  => 'Имя',
        'phone'       => 'Телефон',
        'city'        => 'Город',
        'street'      => 'Улица',
        'house'       => 'Дом',
        'postal_code' => 'Индекс',
    ];

    foreach ($requiredFields as $field => $label) {
        if ($formData[$field] === '') {
            $errorMessage = "Поле «{$label}» обязательно для заполнения.";
            break;
        }
    }

    if ($errorMessage === null) {
        try {
            $pdo = db();

            $stmt = $pdo->prepare(
                'INSERT INTO customers
                (
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
                )
                VALUES
                (
                    :last_name,
                    :first_name,
                    :middle_name,
                    :phone,
                    :email,
                    :city,
                    :street,
                    :house,
                    :apartment,
                    :postal_code
                )'
            );

            $stmt->execute([
                ':last_name'   => $formData['last_name'],
                ':first_name'  => $formData['first_name'],
                ':middle_name' => $formData['middle_name'] !== '' ? $formData['middle_name'] : null,
                ':phone'       => $formData['phone'],
                ':email'       => $formData['email'] !== '' ? $formData['email'] : null,
                ':city'        => $formData['city'],
                ':street'      => $formData['street'],
                ':house'       => $formData['house'],
                ':apartment'   => $formData['apartment'] !== '' ? $formData['apartment'] : null,
                ':postal_code' => $formData['postal_code'],
            ]);

            header('Location: ' . base_url('customers/list.php'));
            exit;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';
?>

<main>
    <section class="card">
        <h2>Добавить покупателя</h2>

        <p>
            <a href="<?= htmlspecialchars(base_url('customers/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                ← Вернуться к списку покупателей
            </a>
        </p>

        <?php if ($errorMessage !== null): ?>
            <div class="error-box">
                <strong>Ошибка:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars(base_url('customers/create.php'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label for="last_name">Фамилия *</label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        value="<?= htmlspecialchars($formData['last_name'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="first_name">Имя *</label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        value="<?= htmlspecialchars($formData['first_name'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="middle_name">Отчество</label>
                    <input
                        type="text"
                        id="middle_name"
                        name="middle_name"
                        value="<?= htmlspecialchars($formData['middle_name'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="phone">Телефон *</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="<?= htmlspecialchars($formData['phone'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="city">Город *</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="<?= htmlspecialchars($formData['city'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="street">Улица *</label>
                    <input
                        type="text"
                        id="street"
                        name="street"
                        value="<?= htmlspecialchars($formData['street'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="house">Дом *</label>
                    <input
                        type="text"
                        id="house"
                        name="house"
                        value="<?= htmlspecialchars($formData['house'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="apartment">Квартира</label>
                    <input
                        type="text"
                        id="apartment"
                        name="apartment"
                        value="<?= htmlspecialchars($formData['apartment'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="postal_code">Индекс *</label>
                    <input
                        type="text"
                        id="postal_code"
                        name="postal_code"
                        value="<?= htmlspecialchars($formData['postal_code'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Сохранить</button>
                <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url('customers/list.php'), ENT_QUOTES, 'UTF-8') ?>">
                    Отмена
                </a>
            </div>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>