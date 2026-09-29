<?php
if(session_status() == PHP_SESSION_NONE) session_start();

// Генерация CSRF токена если его нет
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Подсчёт товаров в корзине
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
$_SESSION['cart_count'] = $cartCount;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    <title>Luxe Accessories</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/acs/css/main.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body>
<header class="main-header">
    <div class="container">
        <div class="logo">
            <a href="/acs/">
                <img src="/acs/img/logo.png" alt="Luxe Accessories">
            </a>
        </div>
        <nav class="main-nav">
            <a href="/acs/"><i class="fas fa-home"></i> Главная</a>
            <a href="/acs/catalog.php"><i class="fas fa-store"></i> Каталог</a>
            <a href="/acs/cart.php" class="cart-link">
                <i class="fas fa-shopping-bag"></i> Корзина
                <?php if ($cartCount > 0): ?>
                    <span class="cart-counter"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            
            <?php if (isset($_SESSION['client'])): ?>
                <div class="user-menu" style="position: relative;">
                    <span style="cursor: pointer; color: #e74c3c; font-weight: 600;" onclick="toggleUserMenu()">
                        <i class="fas fa-user"></i> <?= htmlspecialchars(explode(' ', $_SESSION['client']['full_name'])[0]) ?> ▼
                    </span>
                    <div id="userDropdown" style="display: none; position: absolute; right: 0; top: 100%; background: #1e1e1e; border: 1px solid #e74c3c; border-radius: 8px; min-width: 180px; z-index: 1000;">
                        <a href="/acs/profile.php?tab=data" style="display: block; padding: 10px 16px; color: #eee; text-decoration: none;"><i class="fas fa-id-card"></i> Мои данные</a>
                        <a href="/acs/profile.php?tab=orders" style="display: block; padding: 10px 16px; color: #eee; text-decoration: none;"><i class="fas fa-box"></i> Мои заказы</a>
                        <a href="/acs/profile.php?tab=addresses" style="display: block; padding: 10px 16px; color: #eee; text-decoration: none;"><i class="fas fa-map-marker-alt"></i> Мои адреса</a>
                        <hr style="margin: 4px 0; border-color: #333;">
                        <a href="/acs/logout.php" style="display: block; padding: 10px 16px; color: #e74c3c; text-decoration: none;"><i class="fas fa-sign-out-alt"></i> Выйти</a>
                    </div>
                </div>
                <script>
                    function toggleUserMenu() {
                        var menu = document.getElementById('userDropdown');
                        if (menu) menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
                    }
                    document.addEventListener('click', function(event) {
                        var menu = document.getElementById('userDropdown');
                        var trigger = event.target.closest('.user-menu');
                        if (!trigger && menu) menu.style.display = 'none';
                    });
                </script>
            <?php else: ?>
                <a href="/acs/login.php"><i class="fas fa-sign-in-alt"></i> Вход</a>
                <a href="/acs/register.php"><i class="fas fa-user-plus"></i> Регистрация</a>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['admin_logged_in'])): ?>
                <a href="/acs/admin/" style="color: #e74c3c;"><i class="fas fa-crown"></i> Админка</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">