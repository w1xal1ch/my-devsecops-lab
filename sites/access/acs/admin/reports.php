<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/../php/config/db_connect.php';

$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$reportData = [];
$totalRevenue = 0;
$totalorders = 0;

if ($date_from && $date_to) {
    // Продажи по товарам за период
    $stmt = $pdo->prepare("
        SELECT p.product_id, p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
        FROM orderitem oi
        JOIN `order` o ON oi.order_id = o.order_id
        JOIN product p ON oi.product_id = p.product_id
        WHERE DATE(o.order_date) BETWEEN ? AND ?
        GROUP BY oi.product_id
        ORDER BY total_sold DESC
    ");
    $stmt->execute([$date_from, $date_to]);
    $reportData = $stmt->fetchAll();
    
    // Общая статистика
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT o.order_id) as total_orders, SUM(oi.quantity * oi.price) as total_revenue
        FROM `order` o
        JOIN orderitem oi ON o.order_id = oi.order_id
        WHERE DATE(o.order_date) BETWEEN ? AND ?
    ");
    $stmt->execute([$date_from, $date_to]);
    $stats = $stmt->fetch();
    $totalorders = $stats['total_orders'] ?? 0;
    $totalRevenue = $stats['total_revenue'] ?? 0;
    
    // Статусы заказов за период
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count
        FROM `order`
        WHERE DATE(order_date) BETWEEN ? AND ?
        GROUP BY status
    ");
    $stmt->execute([$date_from, $date_to]);
    $statusStats = $stmt->fetchAll();
}

// Обработка экспорта CSV
if (isset($_GET['export']) && $_GET['export'] == 1 && $date_from && $date_to) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="report_' . $date_from . '_to_' . $date_to . '.csv"');
    
    $output = fopen('php://output', 'w');
    // BOM для корректной работы с русскими символами в Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['Отчёт о продажах']);
    fputcsv($output, ['Период: ' . $date_from . ' - ' . $date_to]);
    fputcsv($output, []);
    fputcsv($output, ['Товар', 'Продано, шт', 'Выручка, руб']);
    
    foreach ($reportData as $row) {
        fputcsv($output, [$row['name'], $row['total_sold'], number_format($row['revenue'], 2, '.', '')]);
    }
    
    fputcsv($output, []);
    fputcsv($output, ['Итого заказов:', $totalorders]);
    fputcsv($output, ['Общая выручка:', number_format($totalRevenue, 2, '.', '') . ' руб']);
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отчёты</title>
    <link rel="stylesheet" href="/acs/css/main.css">
    <style>
        .report-form { background: #1e1e1e; padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group label { font-size: 0.8rem; color: #aaa; }
        .filter-group input, .filter-group select { padding: 8px 12px; border-radius: 8px; border: none; background: #2a2a2a; color: #fff; }
        .filter-group button { background: #e74c3c; border: none; padding: 8px 20px; border-radius: 8px; color: #fff; cursor: pointer; }
        .report-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #1e1e1e; padding: 20px; border-radius: 12px; text-align: center; border-bottom: 3px solid #e74c3c; }
        .stat-card .number { font-size: 2rem; font-weight: bold; color: #e74c3c; }
        .stat-card .label { color: #aaa; margin-top: 5px; }
        .report-table { width: 100%; border-collapse: collapse; background: #1e1e1e; border-radius: 12px; overflow: hidden; }
        .report-table th, .report-table td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
        .report-table th { background: #e74c3c; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; background: #2a2a2a; }
        .btn-export { background: #2ecc71; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-top: 20px; }
        .btn-export:hover { background: #27ae60; }
    </style>
</head>
<body>
<div class="container" style="margin-top: 1rem;">
    <h1>📊 Отчёты и аналитика</h1>

    <form method="GET" class="report-form">
        <div class="filter-group">
            <label>📅 Дата с</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" required>
        </div>
        <div class="filter-group">
            <label>📅 Дата по</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" required>
        </div>
        <div class="filter-group">
            <button type="submit">📈 Сформировать отчёт</button>
        </div>
    </form>

    <?php if ($date_from && $date_to): ?>
        <div class="report-stats">
            <div class="stat-card">
                <div class="number"><?= $totalorders ?></div>
                <div class="label">Всего заказов</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= number_format($totalRevenue, 0, ',', ' ') ?> ₽</div>
                <div class="label">Общая выручка</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $totalorders > 0 ? round($totalRevenue / $totalorders, 0) : 0 ?> ₽</div>
                <div class="label">Средний чек</div>
            </div>
        </div>

        <!-- Статусы -->
        <?php if (!empty($statusStats)): ?>
            <h3>📌 Статусы заказов</h3>
            <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 30px;">
                <?php foreach ($statusStats as $stat): ?>
                    <div class="stat-card" style="min-width: 100px;">
                        <div class="number"><?= $stat['count'] ?></div>
                        <div class="label"><?= htmlspecialchars($stat['status']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Топ товаров -->
        <h3>🏆 Топ продаваемых товаров</h3>
        <?php if (empty($reportData)): ?>
            <p>Нет продаж за выбранный период</p>
        <?php else: ?>
            <table class="report-table">
                <thead>
                    <tr><th>Товар</th><th>Продано, шт</th><th>Выручка</th></tr>
                </thead>
                <tbody>
                <?php foreach ($reportData as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['total_sold'] ?></td>
                        <td><?= number_format($item['revenue'], 0, ',', ' ') ?> ₽</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Экспорт -->
        <a href="?export=1&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>" class="btn-export">
            📎 Экспорт в CSV
        </a>
    <?php else: ?>
        <p style="text-align:center; padding:40px;">Выберите период для формирования отчёта</p>
    <?php endif; ?>

    <p><a href="index.php" style="display:inline-block; margin-top:20px;">← Вернуться в панель администратора</a></p>
</div>
</body>
</html>