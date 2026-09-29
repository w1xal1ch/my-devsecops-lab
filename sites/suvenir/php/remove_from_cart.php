<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        echo 'ok';
    } else {
        echo 'error';
    }
    exit;
} else {
    // Если это GET-запрос (прямая ссылка) - перенаправляем на корзину
    if (isset($_GET['product_id'])) {
        $product_id = (int)$_GET['product_id'];
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        header('Location: /index.php?page=cart');
        exit;
    }
    
    echo 'error';
    exit;
}
?>