<?php
// Нормализация телефона в +7XXXXXXXXXX
function formatPhoneForDB($phone) {
    $clean = preg_replace('/[^0-9]/', '', $phone);
    if (preg_match('/^8(\d{10})$/', $clean, $matches)) {
        return '+7' . $matches[1];
    }
    if (preg_match('/^(\d{10})$/', $clean, $matches)) {
        return '+7' . $matches[1];
    }
    if (preg_match('/^7(\d{10})$/', $clean, $matches)) {
        return '+7' . $matches[1];
    }
    if (preg_match('/^\+7(\d{10})$/', $clean, $matches)) {
        return '+7' . $matches[1];
    }
    return false;
}

// Валидация ФИО: только буквы, дефис, пробел
function validateName($name) {
    // Разрешаем: русские и английские буквы, пробелы, дефис
    return preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $name);
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'mysql');
define('DB_NAME', 'acs');
define('DB_USER', 'root');
define('DB_PASS', 'root');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    echo "Ошибка подключения к базе данных: " . htmlspecialchars($e->getMessage());
    exit;
}
