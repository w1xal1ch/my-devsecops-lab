<?php
require_once __DIR__ . '/php/config/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrPhone = trim($_POST['email_or_phone'] ?? '');

    if ($emailOrPhone === '') {
        $error = 'Введите email или телефон';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM client WHERE email = ? OR phone = ?");
        $stmt->execute([$emailOrPhone, $emailOrPhone]);
        $client = $stmt->fetch();

        if ($client) {
            $_SESSION['client'] = $client;
            header('Location: /acs/');
            exit;
        } else {
            $error = 'Пользователь не найден';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<h2 style="margin-bottom: 1rem;">Вход</h2>
<form method="POST" action="/acs/login.php" class="auth-form" style="max-width: 400px; margin: auto;">
    <label for="email_or_phone">Email или телефон</label>
    <input type="text" id="email_or_phone" name="email_or_phone" required style="width: 100%; padding: 8px; margin-bottom: 1rem;" />
    <button type="submit" style="width: 100%; padding: 10px; background:#e74c3c; border:none; color:#fff; font-weight:bold; border-radius:5px; cursor:pointer;">Войти</button>
    <?php if ($error): ?>
        <p style="color:#e74c3c; margin-top: 1rem; font-weight: 600;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
