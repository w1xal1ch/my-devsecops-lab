<?php
$mysqli = new mysqli('mysql', 'root', 'root', 'syvenir');
$mysqli->set_charset('utf8mb4');
if ($mysqli->connect_errno) {
    die('Ошибка подключения к БД: ' . $mysqli->connect_error);
}

// Настройка сессии
session_set_cookie_params([
    'lifetime' => 86400 * 30, // 30 дней
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
?>
