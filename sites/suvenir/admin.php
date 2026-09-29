<?php
require_once 'php/db.php';
$user = $_SESSION['user'] ?? null;
if (!$user || ($user['Role'] != 1 && $user['Role'] != 2)) {
    header('Location: index.php');
    exit;
}

$tab = $_GET['tab'] ?? 'products';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель | СувенирМаркет</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7fc; }
        .admin-header { background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-bottom: 3px solid #ffc107; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .admin-header h1 { color: #00acc1; font-size: 1.5rem; }
        .admin-header a { color: #333; text-decoration: none; margin-left: 20px; }
        .admin-header a:hover { color: #00acc1; }
        .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        .nav { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 30px; border-bottom: 2px solid #e0f7fa; padding-bottom: 15px; }
        .nav a { padding: 8px 20px; text-decoration: none; color: #555; border-radius: 30px; background: #f0f4f8; transition: all 0.3s; }
        .nav a.active { background: #00acc1; color: #fff; }
        .nav a:hover { background: #e0f7fa; color: #00acc1; }
        .card { background: #fff; border-radius: 20px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #e0f7fa; color: #006064; }
        .btn-sm { padding: 5px 12px; border-radius: 20px; text-decoration: none; display: inline-block; margin: 0 3px; background: #00acc1; color: #fff; font-size: 12px; border: none; cursor: pointer; }
        .btn-sm.delete { background: #e74c3c; }
        .btn-sm.edit { background: #ffc107; color: #333; }
        .btn-sm:hover { opacity: 0.8; }
        input, select, textarea { width: 100%; padding: 10px; margin: 8px 0 15px; border: 1px solid #ddd; border-radius: 12px; }
        button { background: #00acc1; color: #fff; border: none; padding: 10px 20px; border-radius: 30px; cursor: pointer; }
        button:hover { background: #008c9e; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 12px; margin-bottom: 20px; }
        .filter-form { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px; align-items: flex-end; }
        .filter-form input, .filter-form select { width: auto; min-width: 150px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #e0f7fa; padding: 20px; border-radius: 16px; text-align: center; }
        .stat-card .number { font-size: 2rem; font-weight: bold; color: #00acc1; }
        .order-card { background: #fff; border-radius: 12px; padding: 15px; margin-bottom: 15px; border-left: 4px solid #ffc107; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .status-new { background: #3498db; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .status-completed { background: #2ecc71; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { background: #e0f7fa; color: #006064; padding: 12px; }
        .admin-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .form-group { margin-bottom: 15px; }
        .form-group label { font-weight: 500; color: #555; }
    </style>
</head>
<body>

<div class="admin-header">
    <h1>🎁 Админ-панель | СувенирМаркет</h1>
    <div>
        <span>👋 <?= htmlspecialchars($user['Login']) ?></span>
        <a href="/index.php">На сайт</a>
        <a href="/php/logout.php">Выйти</a>
    </div>
</div>

<div class="container">
    <div class="nav">
        <a href="?tab=products" class="<?= $tab=='products'?'active':'' ?>">📦 Товары</a>
        <a href="?tab=orders" class="<?= $tab=='orders'?'active':'' ?>">📋 Заказы</a>
        <a href="?tab=reviews" class="<?= $tab=='reviews'?'active':'' ?>">⭐ Отзывы</a>
        <a href="?tab=deliveries" class="<?= $tab=='deliveries'?'active':'' ?>">🚚 Поставки</a>
        <a href="?tab=reports" class="<?= $tab=='reports'?'active':'' ?>">📊 Отчёты</a>
        <a href="?tab=deleted" class="<?= $tab=='deleted'?'active':'' ?>">🗑️ Архив</a>
    </div>

    <div class="card">
        <?php
        switch ($tab) {
            case 'products': include 'php/admin_products.php'; break;
            case 'orders': include 'php/admin_orders.php'; break;
            case 'reviews': include 'php/admin_reviews.php'; break;
            case 'deliveries': include 'php/admin_deliveries.php'; break;
            case 'reports': include 'php/admin_reports.php'; break;
            case 'deleted': include 'php/admin_deleted.php'; break;
            default: include 'php/admin_products.php';
        }
        ?>
    </div>
</div>

</body>
</html>