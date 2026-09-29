<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

// Проверка CSRF токена
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Ошибка безопасности']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные']);
    exit;
}

$productId = (int)$input['product_id'];

// Проверяем товар в базе
$stmt = $pdo->prepare("SELECT product_id FROM product WHERE product_id = ?");
$stmt->execute([$productId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Товар не найден']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId]++;
} else {
    $_SESSION['cart'][$productId] = 1;
}

$_SESSION['cart_count'] = array_sum($_SESSION['cart']);

echo json_encode(['success' => true, 'totalCount' => $_SESSION['cart_count']]);