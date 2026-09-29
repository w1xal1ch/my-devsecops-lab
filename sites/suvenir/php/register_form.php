<?php
require_once 'db.php';
require_once 'helpers.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass = trim($_POST['pass'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fio = trim($_POST['fio'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!$login || !$pass || !$email || !$fio) {
        $error = 'Заполните все обязательные поля!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email адрес';
    } elseif (!validateName($fio)) {
        $error = 'ФИО может содержать только буквы, пробелы и дефис';
    } else {
        $phoneFormatted = formatPhoneForDB($phone);
        if (!$phoneFormatted) {
            $error = 'Телефон должен содержать 11 цифр (например, 8XXXXXXXXXX или +7XXXXXXXXXX)';
        } else {
            // Проверка логина
            $res = $mysqli->query("SELECT * FROM user WHERE Login='" . $mysqli->real_escape_string($login) . "'");
            if ($res->num_rows) {
                $error = 'Логин уже занят!';
            } else {
                // Проверка email
                $res = $mysqli->query("SELECT * FROM client WHERE Email='" . $mysqli->real_escape_string($email) . "'");
                if ($res->num_rows) {
                    $error = 'Email уже используется!';
                } else {
                    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
                    
                    $mysqli->query("INSERT INTO user (Login, Password, Role) VALUES (
                        '" . $mysqli->real_escape_string($login) . "',
                        '" . $mysqli->real_escape_string($hashed_pass) . "',
                        4
                    )");
                    $uid = $mysqli->insert_id;
                    
                    $mysqli->query("INSERT INTO client (Full_Name, Phone_Number, Email, ID_User) VALUES (
                        '" . $mysqli->real_escape_string($fio) . "',
                        '" . $mysqli->real_escape_string($phoneFormatted) . "',
                        '" . $mysqli->real_escape_string($email) . "',
                        $uid
                    )");
                    
                    $success = 'Регистрация успешна! Теперь вы можете войти.';
                }
            }
        }
    }
}
?>

<div class="auth-container" style="max-width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2 style="text-align:center;">Регистрация</h2>
    <?php if ($error): ?>
        <div style="background:#f8d7da; color:#721c24; padding:10px; margin-bottom:15px; border-radius:6px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="background:#d4edda; color:#155724; padding:10px; margin-bottom:15px; border-radius:6px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post">
        <div style="margin-bottom:15px;">
            <label>Логин *</label>
            <input type="text" name="login" required style="width:100%; padding:8px;">
        </div>
        <div style="margin-bottom:15px;">
            <label>Пароль *</label>
            <input type="password" name="pass" required style="width:100%; padding:8px;">
        </div>
        <div style="margin-bottom:15px;">
            <label>Email *</label>
            <input type="email" name="email" required style="width:100%; padding:8px;">
        </div>
        <div style="margin-bottom:15px;">
            <label>ФИО *</label>
            <input type="text" name="fio" required style="width:100%; padding:8px;">
        </div>
        <div style="margin-bottom:15px;">
            <label>Телефон</label>
            <input type="text" name="phone" style="width:100%; padding:8px;">
        </div>
        <button type="submit" style="width:100%; background:#2196F3; color:#fff; border:none; padding:10px; border-radius:6px; cursor:pointer;">Зарегистрироваться</button>
    </form>
    <p style="text-align:center; margin-top:15px;">Уже есть аккаунт? <a href="/index.php?page=auth">Войти</a></p>
</div>