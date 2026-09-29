<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    
    // Проверяем, существует ли товар
    $res = $mysqli->query("SELECT ID_Products FROM products WHERE ID_Products = $product_id AND is_deleted = 0");
    if ($res->num_rows === 0) {
        echo 'error: Товар не найден';
        exit;
    }
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }
    
    // Перенаправляем обратно на страницу, откуда пришли
    $referer = $_SERVER['HTTP_REFERER'] ?? '/index.php?page=catalog';
    header('Location: ' . $referer);
    exit;
} else {
    echo 'error: Неверный запрос';
}
?>