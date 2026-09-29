<?php
// Обновление статуса заказа
if (isset($_GET['update_status'])) {
    $id = (int)$_GET['update_status'];
    $status = (int)$_GET['status'];
    $mysqli->query("UPDATE sale SET Status=$status WHERE ID_S=$id");
    echo '<div class="success">✅ Статус заказа #'.$id.' обновлён!</div>';
}

$status_filter = $_GET['status_filter'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where = [];
if ($status_filter !== '') $where[] = "s.Status = " . (int)$status_filter;
if ($date_from) $where[] = "s.Sale_Date >= '$date_from'";
if ($date_to) $where[] = "s.Sale_Date <= '$date_to'";
$whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

function statusText($status) {
    return $status == 1 ? '🟡 В обработке' : ($status == 2 ? '✅ Выполнен' : '❌ Неизвестно');
}
?>

<h2>📋 Заказы</h2>

<form method="get" class="filter-form">
    <input type="hidden" name="tab" value="orders">
    <div>
        <label>Статус</label>
        <select name="status_filter">
            <option value="">Все</option>
            <option value="1" <?= $status_filter == '1' ? 'selected' : '' ?>>В обработке</option>
            <option value="2" <?= $status_filter == '2' ? 'selected' : '' ?>>Выполнен</option>
        </select>
    </div>
    <div>
        <label>Дата с</label>
        <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
    </div>
    <div>
        <label>Дата по</label>
        <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
    </div>
    <div>
        <button type="submit">🔍 Фильтровать</button>
    </div>
    <div>
        <a href="?tab=orders" class="btn-sm">Сбросить</a>
    </div>
</form>

<?php
// Получаем заказы с суммой через JOIN
$res = $mysqli->query("
    SELECT s.*, c.Full_Name, 
           COALESCE(SUM(sc.Quantity * sc.Price), 0) as total
    FROM sale s 
    LEFT JOIN client c ON s.ID_Client = c.ID_Client 
    LEFT JOIN sale_contents sc ON s.ID_S = sc.ID_Sale
    $whereSql 
    GROUP BY s.ID_S
    ORDER BY s.ID_S DESC
");

$ordersList = [];
$totalRevenue = 0;
while ($row = $res->fetch_assoc()) {
    $ordersList[] = $row;
    $totalRevenue += $row['total'];
}
?>

<div class="stats-grid">
    <div class="stat-card"><div class="number"><?= count($ordersList) ?></div><div>Заказов</div></div>
    <div class="stat-card"><div class="number"><?= number_format($totalRevenue, 0, ',', ' ') ?> ₽</div><div>Выручка</div></div>
</div>

<?php foreach ($ordersList as $row): 
    $itemsRes = $mysqli->query("
        SELECT p.Souvenir_Name, sc.Quantity, sc.Price, p.Photo 
        FROM sale_contents sc 
        LEFT JOIN products p ON sc.ID_Product = p.ID_Products 
        WHERE sc.ID_Sale = {$row['ID_S']}
    ");
    $orderSum = $row['total'] ?? 0;
?>
<div class="order-card">
    <div style="display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 10px;">
        <div>
            <strong>Заказ #<?= $row['ID_S'] ?></strong><br>
            <small>📅 <?= date('d.m.Y', strtotime($row['Sale_Date'])) ?></small>
        </div>
        <div>
            <span class="status-<?= $row['Status'] == 1 ? 'new' : 'completed' ?>">
                <?= statusText($row['Status']) ?>
            </span>
        </div>
    </div>
    <div><strong>Клиент:</strong> <?= htmlspecialchars($row['Full_Name'] ?? 'Гость') ?></div>
    <div style="margin-top: 10px;">
        <table style="width:100%; font-size:0.9rem;">
            <thead><tr><th>Фото</th><th>Товар</th><th>Кол-во</th><th>Цена</th><th>Сумма</th></tr></thead>
            <tbody>
            <?php 
            $subtotal = 0;
            while ($item = $itemsRes->fetch_assoc()): 
                $itemSum = $item['Quantity'] * $item['Price'];
                $subtotal += $itemSum;
            ?>
            <tr>
                <td><img src="/img/<?= $item['Photo'] ?: 'no-image.jpg' ?>" width="40" style="border-radius:5px;"></td>
                <td><?= htmlspecialchars($item['Souvenir_Name']) ?></td>
                <td><?= $item['Quantity'] ?></td>
                <td><?= number_format($item['Price'], 0, ',', ' ') ?> ₽</td>
                <td><?= number_format($itemSum, 0, ',', ' ') ?> ₽</td>
            </tr>
            <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right; padding-top: 10px;">
                        <strong>Итого по заказу:</strong>
                    </td>
                    <td style="padding-top: 10px;">
                        <strong><?= number_format($subtotal, 0, ',', ' ') ?> ₽</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div style="margin-top: 10px;">
        <select onchange="location.href='?tab=orders&update_status=<?= $row['ID_S'] ?>&status='+this.value">
            <option value="1" <?= $row['Status'] == 1 ? 'selected' : '' ?>>🟡 В обработке</option>
            <option value="2" <?= $row['Status'] == 2 ? 'selected' : '' ?>>✅ Выполнен</option>
        </select>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($ordersList)): ?>
    <p style="text-align:center; padding:40px;">Нет заказов</p>
<?php endif; ?>