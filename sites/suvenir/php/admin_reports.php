<?php
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$reportData = [];
$totalRevenue = 0;
$totalOrders = 0;

if ($date_from && $date_to) {
    // Продажи по товарам
    $stmt = $mysqli->prepare("
        SELECT p.ID_Products, p.Souvenir_Name, p.Photo, 
               SUM(sc.Quantity) as total_sold, 
               SUM(sc.Quantity * sc.Price) as revenue
        FROM sale_contents sc
        JOIN sale s ON sc.ID_Sale = s.ID_S
        JOIN products p ON sc.ID_Product = p.ID_Products
        WHERE s.Sale_Date BETWEEN ? AND ?
        GROUP BY sc.ID_Product
        ORDER BY total_sold DESC
    ");
    $stmt->bind_param("ss", $date_from, $date_to);
    $stmt->execute();
    $reportData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Общая статистика
    $stmt = $mysqli->prepare("
        SELECT COUNT(DISTINCT s.ID_S) as total_orders, 
               SUM(sc.Quantity * sc.Price) as total_revenue
        FROM sale s
        JOIN sale_contents sc ON s.ID_S = sc.ID_Sale
        WHERE s.Sale_Date BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $date_from, $date_to);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $totalOrders = $stats['total_orders'] ?? 0;
    $totalRevenue = $stats['total_revenue'] ?? 0;
}
?>

<h2>📊 Отчёты и аналитика</h2>

<form method="get" class="filter-form" style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
    <input type="hidden" name="tab" value="reports">
    <div>
        <label style="display: block; font-size: 0.85rem; color: #555; margin-bottom: 4px;">📅 Дата с</label>
        <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" required style="padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd; background: #fff;">
    </div>
    <div>
        <label style="display: block; font-size: 0.85rem; color: #555; margin-bottom: 4px;">📅 Дата по</label>
        <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" required style="padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd; background: #fff;">
    </div>
    <div>
        <button type="submit" style="background: #00acc1; color: #fff; border: none; padding: 9px 25px; border-radius: 8px; cursor: pointer; font-size: 0.95rem;">📈 Сформировать</button>
    </div>
</form>

<?php if ($date_from && $date_to): ?>
    <!-- Статистика -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: #e0f7fa; padding: 20px; border-radius: 16px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold; color: #00acc1;"><?= $totalOrders ?></div>
            <div style="color: #555; margin-top: 5px;">Заказов</div>
        </div>
        <div style="background: #fff3e0; padding: 20px; border-radius: 16px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold; color: #e65100;"><?= number_format($totalRevenue, 0, ',', ' ') ?> ₽</div>
            <div style="color: #555; margin-top: 5px;">Выручка</div>
        </div>
        <div style="background: #e8f5e9; padding: 20px; border-radius: 16px; text-align: center;">
            <div style="font-size: 2rem; font-weight: bold; color: #2e7d32;"><?= $totalOrders > 0 ? round($totalRevenue / $totalOrders, 0) : 0 ?> ₽</div>
            <div style="color: #555; margin-top: 5px;">Средний чек</div>
        </div>
    </div>

    <!-- Топ товаров -->
    <h3 style="color: #00acc1; margin-bottom: 15px;">🏆 Топ продаваемых товаров</h3>
    <?php if (empty($reportData)): ?>
        <p style="text-align:center; padding: 30px; color: #888;">Нет продаж за выбранный период</p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            <thead>
                <tr style="background: #e0f7fa;">
                    <th style="padding: 12px; text-align: left;">Фото</th>
                    <th style="padding: 12px; text-align: left;">Товар</th>
                    <th style="padding: 12px; text-align: center;">Продано, шт</th>
                    <th style="padding: 12px; text-align: right;">Выручка</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($reportData as $item): 
                $photoExists = ($item['Photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/img/' . $item['Photo'])) ? $item['Photo'] : 'no-image.jpg';
            ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;">
                        <img src="/img/<?= $photoExists ?>" width="50" style="border-radius: 8px;">
                    </td>
                    <td style="padding: 10px;"><?= htmlspecialchars($item['Souvenir_Name']) ?></td>
                    <td style="padding: 10px; text-align: center; font-weight: bold;"><?= $item['total_sold'] ?></td>
                    <td style="padding: 10px; text-align: right; font-weight: bold; color: #2e7d32;">
                        <?= number_format($item['revenue'], 0, ',', ' ') ?> ₽
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 25px;">
            <a href="/php/export_report.php?date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>" 
               style="background: #2ecc71; color: #fff; padding: 10px 25px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                📎 Экспорт в CSV
            </a>
        </div>
    <?php endif; ?>
    
<?php else: ?>
    <p style="text-align:center; padding: 60px; color: #888; background: #f9f9f9; border-radius: 12px;">
        📅 Выберите период и нажмите «Сформировать»
    </p>
<?php endif; ?>