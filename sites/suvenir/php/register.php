<?php
require_once 'db.php';
require_once 'helpers.php';

$login = trim($_POST['login'] ?? '');
$pass = trim($_POST['pass'] ?? '');
$email = trim($_POST['email'] ?? '');
$fio = trim($_POST['fio'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if (!$login || !$pass || !$email || !$fio) {
    echo 'Заполните все обязательные поля!';
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Некорректный email адрес';
    exit;
}

if (!validateName($fio)) {
    echo 'ФИО может содержать только буквы, пробелы и дефис';
    exit;
}

$phoneFormatted = formatPhoneForDB($phone);
if (!$phoneFormatted) {
    echo 'Телефон должен содержать 11 цифр (например, 8XXXXXXXXXX или +7XXXXXXXXXX)';
    exit;
}

// Проверка логина
$res = $mysqli->query("SELECT * FROM user WHERE Login='" . $mysqli->real_escape_string($login) . "'");
if ($res->num_rows) {
    echo 'Логин уже занят!';
    exit;
}

// Проверка email
$res = $mysqli->query("SELECT * FROM client WHERE Email='" . $mysqli->real_escape_string($email) . "'");
if ($res->num_rows) {
    echo 'Email уже используется!';
    exit;
}

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

echo 'ok';
?>