<?php
require_once 'db.php';

$cart = $_SESSION['cart'] ?? [];
$total = 0;

$items = [];
if (!empty($cart)) {
    $ids = implode(',', array_keys($cart));
    $res = $mysqli->query("SELECT * FROM products WHERE ID_Products IN ($ids)");
    while ($row = $res->fetch_assoc()) {
        $id = $row['ID_Products'];
        $qty = $cart[$id];
        $sum = $qty * $row['Price'];
        $total += $sum;
        $items[] = [
            'id' => $id,
            'name' => htmlspecialchars($row['Souvenir_Name']),
            'price' => $row['Price'],
            'qty' => $qty,
            'sum' => $sum,
            'photo' => $row['Photo']
        ];
    }
}
?>

<div style="max-width: 1000px; margin: 0 auto;">
    <h2 style="color: #00acc1; margin-bottom: 20px;">🛒 Корзина</h2>
    
    <?php if (empty($items)): ?>
        <div style="text-align: center; padding: 60px;">
            <p>Ваша корзина пуста</p>
            <a href="/index.php?page=catalog" class="btn">Вернуться в каталог</a>
        </div>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr><th style="padding:12px; text-align:left; background:#e0f7fa;">Фото</th>
                    <th style="padding:12px; text-align:left; background:#e0f7fa;">Товар</th>
                    <th style="padding:12px; text-align:left; background:#e0f7fa;">Цена</th>
                    <th style="padding:12px; text-align:left; background:#e0f7fa;">Кол-во</th>
                    <th style="padding:12px; text-align:left; background:#e0f7fa;">Сумма</th>
                    <th style="padding:12px; text-align:left; background:#e0f7fa;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:12px;"><img src="/img/<?= $item['photo'] ?: 'no-image.jpg' ?>" width="50" style="border-radius:8px;"></td>
                    <td style="padding:12px;"><?= $item['name'] ?></td>
                    <td style="padding:12px;"><?= $item['price'] ?> ₽</td>
                    <td style="padding:12px;"><?= $item['qty'] ?></td>
                    <td style="padding:12px;"><?= $item['sum'] ?> ₽</td>
                    <td style="padding:12px;"><a href="/php/remove_from_cart.php?product_id=<?= $item['id'] ?>" onclick="return confirm('Удалить товар?')" style="color:#e74c3c;">Удалить</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="text-align:right; margin-top:20px; padding-top:15px; border-top:2px solid #ffc107; font-size:1.2rem; font-weight:bold;">
            Итого: <?= number_format($total, 0, ',', ' ') ?> ₽
        </div>
        
        <div style="text-align:right; margin-top:20px;">
            <a href="/php/place_order.php" style="background:#ffc107; color:#333; border:none; padding:12px 30px; border-radius:30px; cursor:pointer; text-decoration:none; display:inline-block;">✅ Оформить заказ</a>
        </div>
    <?php endif; ?>
</div>