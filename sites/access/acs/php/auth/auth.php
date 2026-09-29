<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($login === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Вход для администратора</title>
    <style>
        body {background:#121212; color:#eee; font-family:sans-serif; display:flex; justify-content:center; align-items:center; height:100vh;}
        form {background:#1f1f1f; padding:2rem; border-radius:10px; width:300px;}
        input {width:100%; padding:0.5rem; margin-bottom:1rem; border:none; border-radius:5px;}
        button {width:100%; padding:0.7rem; background:#e74c3c; border:none; color:#fff; font-weight:bold; cursor:pointer;}
        .error {color:#e74c3c; margin-bottom:1rem;}
    </style>
</head>
<body>
<form method="post" action="">
    <h2>Вход для администратора</h2>
    <?php if ($error): ?>
        <div class="error"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>
    <input type="text" name="login" placeholder="Логин" required />
    <input type="password" name="password" placeholder="Пароль" required />
    <button type="submit">Войти</button>
</form>
</body>
</html>
