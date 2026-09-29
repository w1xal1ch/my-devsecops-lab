<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/../php/config/db_connect.php';

// Обработка экспорта CSV
if (isset($_GET['export']) && $_GET['export'] == 1) {
    $status = $_GET['status'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    
    $sql = "SELECT o.*, c.full_name 
            FROM `order` o 
            LEFT JOIN client c ON o.email = c.email 
            WHERE 1=1";
    $params = [];
    
    if ($status !== '') {
        $sql .= " AND o.status = ?";
        $params[] = $status;
    }
    if ($date_from !== '') {
        $sql .= " AND DATE(o.order_date) >= ?";
        $params[] = $date_from;
    }
    if ($date_to !== '') {
        $sql .= " AND DATE(o.order_date) <= ?";
        $params[] = $date_to;
    }
    
    $sql .= " ORDER BY o.order_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['ID заказа', 'Клиент', 'Телефон', 'Email', 'Адрес', 'Статус', 'Дата заказа', 'Комментарий']);
    
    foreach ($orders as $order) {
        fputcsv($output, [
            $order['order_id'],
            $order['client_name'] ?? $order['full_name'] ?? '-',
            $order['phone'],
            $order['email'],
            $order['address'],
            $order['status'],
            $order['order_date'],
            $order['comment']
        ]);
    }
    
    fclose($output);
    exit;
}

$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$sql = "SELECT o.*, c.full_name 
        FROM `order` o 
        LEFT JOIN client c ON o.email = c.email 
        WHERE 1=1";
$params = [];

if ($status !== '') {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}
if ($date_from !== '') {
    $sql .= " AND DATE(o.order_date) >= ?";
    $params[] = $date_from;
}
if ($date_to !== '') {
    $sql .= " AND DATE(o.order_date) <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY o.order_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Получаем сумму выручки
$totalRevenue = 0;
foreach ($orders as $o) {
    $itemStmt = $pdo->prepare("SELECT SUM(quantity * price) as total FROM orderitem WHERE order_id = ?");
    $itemStmt->execute([$o['order_id']]);
    $itemTotal = $itemStmt->fetch();
    $totalRevenue += $itemTotal['total'];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление заказами</title>
    <link rel="stylesheet" href="/acs/css/main.css">
    <style>
        .filter-form { background: #1e1e1e; padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group label { font-size: 0.8rem; color: #aaa; }
        .filter-group input, .filter-group select { padding: 8px 12px; border-radius: 8px; border: none; background: #2a2a2a; color: #fff; }
        .filter-group button { background: #e74c3c; border: none; padding: 8px 20px; border-radius: 8px; color: #fff; cursor: pointer; }
        .stats-bar { background: #2a2a2a; padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 30px; flex-wrap: wrap; align-items: center; justify-content: space-between; }
        .stats-bar div { font-size: 1.2rem; }
        .stats-bar span { color: #e74c3c; font-weight: bold; }
        .order-card { background: #1e1e1e; border-radius: 12px; padding: 15px; margin-bottom: 15px; border-left: 4px solid #e74c3c; }
        .order-header { display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #333; flex-wrap: wrap; gap: 10px; }
        .order-products { margin-top: 10px; padding-left: 15px; }
        .order-products table { width: 100%; font-size: 0.9rem; }
        .order-products td { padding: 5px 0; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; }
        .status-Новый { background: #3498db; }
        .status-В_обработке { background: #f39c12; }
        .status-Отправлен { background: #9b59b6; }
        .status-Доставлен { background: #2ecc71; }
        .status-Отменён { background: #e74c3c; }
        .btn-export { background: #2ecc71; color: #fff; padding: 8px 16px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .btn-export:hover { background: #27ae60; }
    </style>
</head>
<body>
<div class="container" style="margin-top: 1rem;">
    <h1>📋 Управление заказами</h1>

    <!-- Форма фильтрации -->
    <form method="GET" class="filter-form">
        <div class="filter-group">
            <label>Статус</label>
            <select name="status">
                <option value="">Все</option>
                <option value="Новый" <?= $status == 'Новый' ? 'selected' : '' ?>>Новый</option>
                <option value="В обработке" <?= $status == 'В обработке' ? 'selected' : '' ?>>В обработке</option>
                <option value="Отправлен" <?= $status == 'Отправлен' ? 'selected' : '' ?>>Отправлен</option>
                <option value="Доставлен" <?= $status == 'Доставлен' ? 'selected' : '' ?>>Доставлен</option>
                <option value="Отменён" <?= $status == 'Отменён' ? 'selected' : '' ?>>Отменён</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Дата с</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
        </div>
        <div class="filter-group">
            <label>Дата по</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
        </div>
        <div class="filter-group">
            <button type="submit">🔍 Применить</button>
        </div>
        <div class="filter-group">
            <a href="order_items.php" style="background:#555; padding:8px 20px; border-radius:8px; text-decoration:none; color:#fff;">Сбросить</a>
        </div>
    </form>

    <!-- Статистика и экспорт -->
    <div class="stats-bar">
        <div>📦 Заказов: <span><?= count($orders) ?></span></div>
        <div>💰 Выручка: <span><?= number_format($totalRevenue, 0, ',', ' ') ?> ₽</span></div>
        <a href="?export=1&status=<?= urlencode($status) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>" class="btn-export">
            📎 Экспорт в CSV
        </a>
    </div>

    <!-- Список заказов -->
    <?php if (empty($orders)): ?>
        <p style="text-align:center; padding:40px;">Нет заказов по заданным критериям</p>
    <?php else: ?>
        <?php foreach ($orders as $order): 
            $itemsStmt = $pdo->prepare("
                SELECT oi.*, p.name, p.photo 
                FROM orderitem oi 
                JOIN product p ON oi.product_id = p.product_id 
                WHERE oi.order_id = ?
            ");
            $itemsStmt->execute([$order['order_id']]);
            $items = $itemsStmt->fetchAll();
            $orderTotal = 0;
            foreach ($items as $item) {
                $orderTotal += $item['quantity'] * $item['price'];
            }
        ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <strong>Заказ #<?= $order['order_id'] ?></strong><br>
                    <small><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></small>
                </div>
                <div>
                    <span class="status-badge status-<?= str_replace(' ', '_', $order['status']) ?>">
                        <?= htmlspecialchars($order['status']) ?>
                    </span>
                </div>
            </div>
            <div>
                <div><strong>Клиент:</strong> <?= htmlspecialchars($order['client_name'] ?? $order['full_name'] ?? '-') ?></div>
                <div><strong>Телефон:</strong> <?= htmlspecialchars($order['phone']) ?></div>
                <div><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></div>
                <div><strong>Адрес:</strong> <?= htmlspecialchars($order['address']) ?></div>
                <?php if (!empty($order['comment'])): ?>
                    <div><strong>Комментарий:</strong> <?= htmlspecialchars($order['comment']) ?></div>
                <?php endif; ?>
            </div>
            <div class="order-products">
                <table>
                    <thead><tr><th>Товар</th><th>Кол-во</th><th>Цена</th><th>Сумма</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['price'], 0, ',', ' ') ?> ₽</td>
                            <td><?= number_format($item['quantity'] * $item['price'], 0, ',', ' ') ?> ₽</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td colspan="3" style="text-align:right"><strong>Итого:</strong></td><td><strong><?= number_format($orderTotal, 0, ',', ' ') ?> ₽</strong></td></tr></tfoot>
                </table>
            </div>
            <div style="margin-top: 10px;">
                <form method="post" style="display:inline;" action="order_items.php">
                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                    <select name="status" onchange="this.form.submit()" style="padding:5px; border-radius:5px;">
                        <option value="Новый" <?= $order['status'] == 'Новый' ? 'selected' : '' ?>>Новый</option>
                        <option value="В обработке" <?= $order['status'] == 'В обработке' ? 'selected' : '' ?>>В обработке</option>
                        <option value="Отправлен" <?= $order['status'] == 'Отправлен' ? 'selected' : '' ?>>Отправлен</option>
                        <option value="Доставлен" <?= $order['status'] == 'Доставлен' ? 'selected' : '' ?>>Доставлен</option>
                        <option value="Отменён" <?= $order['status'] == 'Отменён' ? 'selected' : '' ?>>Отменён</option>
                    </select>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="index.php" style="display:inline-block; margin-top:20px;">← Вернуться в панель администратора</a></p>
</div>

<?php
// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = trim($_POST['status']);
    $allowed_statuses = ['Новый', 'В обработке', 'Отправлен', 'Доставлен', 'Отменён'];
    if (in_array($status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE `order` SET status = ? WHERE order_id = ?");
        $stmt->execute([$status, $order_id]);
    }
    header('Location: order_items.php');
    exit;
}
?>
</body>
</html>