<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: auth.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора | Luxe Accessories</title>
    <link rel="stylesheet" href="/acs/css/main.css">
    <style>
        .admin-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            margin-top: 30px;
        }
        .admin-card {
            background: #1e1e1e;
            border-radius: 16px;
            padding: 20px;
            flex: 1;
            min-width: 200px;
            text-align: center;
            transition: transform 0.2s;
            border: 1px solid #2a2a2a;
        }
        .admin-card:hover {
            transform: translateY(-5px);
            border-color: #e74c3c;
        }
        .admin-card a {
            text-decoration: none;
            color: #fff;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .admin-card .icon {
            font-size: 48px;
        }
        .admin-card .title {
            font-size: 1.2rem;
            font-weight: 600;
        }
        .admin-card .desc {
            font-size: 0.8rem;
            color: #aaa;
        }
        .logout-card {
            border-color: #e74c3c;
        }
        .logout-card a {
            color: #e74c3c;
        }
    </style>
</head>
<body>
<header class="main-header">
    <div class="container">
        <div class="logo">
            <a href="/acs/">
                <img src="/acs/img/logo.png" alt="Luxe Accessories" style="height: 48px;">
            </a>
        </div>
        <nav class="main-nav">
            <a href="/acs/">На сайт</a>
        </nav>
    </div>
</header>

<div class="container" style="margin-top: 2rem;">
    <h1 style="color: #e74c3c;">Панель администратора</h1>
    <p>Управление интернет-магазином Luxe Accessories</p>

    <div class="admin-grid">

        <div class="admin-card">
            <a href="products.php">
                <div class="icon">🛍️</div>
                <div class="title">Товары</div>
                <div class="desc">Добавление, редактирование, мягкое удаление</div>
            </a>
        </div>

        <div class="admin-card">
            <a href="deleted_products.php">
                <div class="icon">📦</div>
                <div class="title">Архив товаров</div>
                <div class="desc">Восстановление и полное удаление</div>
            </a>
        </div>

        <div class="admin-card">
            <a href="clients.php">
                <div class="icon">👥</div>
                <div class="title">Клиенты</div>
                <div class="desc">Просмотр пользователей</div>
            </a>
        </div>

        <div class="admin-card">
            <a href="reviews.php">
                <div class="icon">⭐</div>
                <div class="title">Отзывы</div>
                <div class="desc">Управление отзывами клиентов</div>
            </a>
        </div>

        <div class="admin-card">
            <a href="manufactures.php">
                <div class="icon">🏭</div>
                <div class="title">Производители</div>
                <div class="desc">Управление производителями</div>
            </a>
        </div>

        <div class="admin-card">
            <a href="order_items.php">
                <div class="icon">🧾</div>
                <div class="title">Заказы</div>
                <div class="desc">Просмотр позиций заказов</div>
            </a>
        </div>

        <div class="admin-card">
            <a href="reports.php">
                <div class="icon">📊</div>
                <div class="title">Отчёты</div>
                <div class="desc">Графики и статистика</div>
            </a>
        </div>

        <div class="admin-card logout-card">
            <a href="logout.php">
                <div class="icon">🚪</div>
                <div class="title">Выход</div>
                <div class="desc">Завершить сеанс</div>
            </a>
        </div>

    </div>
</div>
</body>
</html>