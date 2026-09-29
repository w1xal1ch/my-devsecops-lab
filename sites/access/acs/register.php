<?php
require_once __DIR__ . '/php/config/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($full_name === '' || $email === '' || $phone === '') {
        $error = 'Пожалуйста, заполните все поля';
    } elseif (!validateName($full_name)) {
        $error = 'ФИО может содержать только буквы (русские или английские), пробелы и дефис. Пример: Иванов Иван Иванович';
    } else {
        $phoneFormatted = formatPhoneForDB($phone);
        if (!$phoneFormatted) {
            $error = 'Телефон должен содержать 11 цифр (например, 8XXXXXXXXXX или +7XXXXXXXXXX)';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM client WHERE email = ? OR phone = ?");
            $stmt->execute([$email, $phoneFormatted]);
            $exists = $stmt->fetch();

            if ($exists) {
                $error = 'Пользователь с таким email или телефоном уже существует';
            } else {
                $stmt = $pdo->prepare("INSERT INTO client (full_name, email, phone) VALUES (?, ?, ?)");
                $stmt->execute([$full_name, $email, $phoneFormatted]);
                $_SESSION['client'] = [
                    'client_id' => $pdo->lastInsertId(),
                    'full_name' => $full_name,
                    'email' => $email,
                    'phone' => $phoneFormatted
                ];
                header('Location: /acs/');
                exit;
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<h2 style="margin-bottom: 1rem;">Регистрация</h2>
<form method="POST" action="/acs/register.php" class="auth-form" style="max-width: 400px; margin: auto;">
    <label for="full_name">ФИО</label>
    <input type="text" id="full_name" name="full_name" required style="width: 100%; padding: 8px; margin-bottom: 1rem;" />
    
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required style="width: 100%; padding: 8px; margin-bottom: 1rem;" />
    
    <label for="phone">Телефон</label>
    <input type="text" id="phone" name="phone" required style="width: 100%; padding: 8px; margin-bottom: 1rem;" />
    
    <button type="submit" style="width: 100%; padding: 10px; background:#e74c3c; border:none; color:#fff; font-weight:bold; border-radius:5px; cursor:pointer;">Зарегистрироваться</button>
    
    <?php if ($error): ?>
        <p style="color:#e74c3c; margin-top: 1rem; font-weight: 600;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>