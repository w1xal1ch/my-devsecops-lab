<?php
require_once 'db.php';

$login = trim($_POST['login'] ?? '');
$pass = trim($_POST['pass'] ?? '');

if (!$login || !$pass) {
    echo 'Заполните все поля';
    exit;
}

$res = $mysqli->query("SELECT * FROM user WHERE Login='" . $mysqli->real_escape_string($login) . "'");
if (!$row = $res->fetch_assoc()) {
    echo 'Неверный логин или пароль';
    exit;
}

// Проверка пароля с использованием password_verify
if (!password_verify($pass, $row['Password'])) {
    echo 'Неверный логин или пароль';
    exit;
}

// Успешная авторизация
$_SESSION['user'] = $row;
echo 'ok';
?>
