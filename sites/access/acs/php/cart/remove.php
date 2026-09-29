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

if (isset($_SESSION['cart'][$productId])) {
    unset($_SESSION['cart'][$productId]);
}

$_SESSION['cart_count'] = array_sum($_SESSION['cart'] ?? []);

// Рассчитываем общую сумму
$totalAmount = 0;
foreach ($_SESSION['cart'] as $id => $qty) {
    $stmt = $pdo->prepare("SELECT price FROM product WHERE product_id = ?");
    $stmt->execute([$id]);
    $prod = $stmt->fetch();
    if ($prod) {
        $totalAmount += $prod['price'] * $qty;
    }
}

echo json_encode([
    'success' => true,
    'totalCount' => $_SESSION['cart_count'],
    'totalAmount' => $totalAmount
]);