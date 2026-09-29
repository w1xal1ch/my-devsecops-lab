<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php';
    $login = trim($_POST['login'] ?? '');
    $pass = trim($_POST['pass'] ?? '');
    
    if (!$login || !$pass) {
        $error = 'Заполните все поля';
    } else {
        $res = $mysqli->query("SELECT * FROM user WHERE Login='" . $mysqli->real_escape_string($login) . "'");
        if (!$row = $res->fetch_assoc()) {
            $error = 'Неверный логин или пароль';
        } elseif (!password_verify($pass, $row['Password'])) {
            $error = 'Неверный логин или пароль';
        } else {
            $_SESSION['user'] = $row;
            header('Location: /index.php');
            exit;
        }
    }
}
?>

<div class="auth-container" style="max-width: 400px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2 style="text-align:center;">Вход</h2>
    <?php if ($error): ?>
        <div style="background:#f8d7da; color:#721c24; padding:10px; margin-bottom:15px; border-radius:6px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <div style="margin-bottom:15px;">
            <label>Логин</label>
            <input type="text" name="login" required style="width:100%; padding:8px;">
        </div>
        <div style="margin-bottom:15px;">
            <label>Пароль</label>
            <input type="password" name="pass" required style="width:100%; padding:8px;">
        </div>
        <button type="submit" style="width:100%; background:#2196F3; color:#fff; border:none; padding:10px; border-radius:6px; cursor:pointer;">Войти</button>
    </form>
    <p style="text-align:center; margin-top:15px;">Нет аккаунта? <a href="/index.php?page=register">Зарегистрироваться</a></p>
</div>