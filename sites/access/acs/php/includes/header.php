<?php
if(session_status() == PHP_SESSION_NONE) session_start();

// Генерация CSRF токена если его нет
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Luxe Accessories</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/acs/css/main.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body>
<header class="main-header">
  <div class="container">
    <div class="logo">
      <a href="/acs/"><img src="/acs/img/logo.png" alt="Luxe Accessories" /></a>
    </div>
    <nav class="main-nav">
      <a href="/acs/">Главная</a>
      <a href="/acs/catalog.php">Каталог</a>
      <a href="/acs/cart.php" class="cart-link">
        <i class="fas fa-shopping-bag"></i>
        <span class="cart-counter" id="cartCounter"><?= $_SESSION['cart_count'] ?? 0 ?></span>
      </a>
      <?php if(isset($_SESSION['client'])): ?>
        <div class="user-menu">
          <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['client']['full_name']) ?>
          <a href="/acs/logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Выйти</a>
        </div>
      <?php else: ?>
        <a href="/acs/login.php"><i class="fas fa-sign-in-alt"></i> Вход</a>
        <a href="/acs/register.php"><i class="fas fa-user-plus"></i> Регистрация</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">