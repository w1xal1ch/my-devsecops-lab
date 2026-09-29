<?php
// Добавление поставки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $date = $mysqli->real_escape_string($_POST['date']);
    $supplier = (int)$_POST['supplier'];
    $responsible = $mysqli->real_escape_string($_POST['responsible']);
    
    $mysqli->query("INSERT INTO delivery (Date, ID_Supplier, Responsible_Person) VALUES ('$date', $supplier, '$responsible')");
    $delivery_id = $mysqli->insert_id;
    
    if(isset($_POST['product_id']) && is_array($_POST['product_id'])) {
        foreach($_POST['product_id'] as $index => $product_id) {
            $product_id = (int)$product_id;
            $quantity = (int)$_POST['quantity'][$index];
            $price = floatval($_POST['price'][$index]);
            if($product_id > 0 && $quantity > 0) {
                $mysqli->query("INSERT INTO delivery_contents (ID_Deliver, ID_Product, Quantity, Purchase_Price, Total) 
                    VALUES ($delivery_id, $product_id, $quantity, '$price', " . ($quantity * $price) . ")");
                // Увеличиваем остаток на складе
                $mysqli->query("UPDATE products SET Stock_Quantity = Stock_Quantity + $quantity WHERE ID_Products = $product_id");
            }
        }
    }
    echo '<div class="success">✅ Поставка #'.$delivery_id.' добавлена! Остатки обновлены.</div>';
}

// Получаем список поставщиков
$suppliers = [];
$res = $mysqli->query("SELECT * FROM supplier");
while ($row = $res->fetch_assoc()) {
    $suppliers[$row['ID_Supplier']] = $row;
}

// Получаем список товаров
$products = [];
$res = $mysqli->query("SELECT ID_Products, Souvenir_Name, Stock_Quantity FROM products WHERE is_deleted = 0");
while ($row = $res->fetch_assoc()) {
    $products[$row['ID_Products']] = $row;
}
?>

<h2>➕ Добавить поставку</h2>

<form method="post" class="admin-form" style="background:#f9f9f9; padding:20px; border-radius:16px; margin-bottom:30px;">
    <div style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div style="flex:1;">
            <label>📅 Дата поставки</label>
            <input type="date" name="date" required style="width:100%; padding:8px; border-radius:8px; border:1px solid #ddd;">
        </div>
        <div style="flex:1;">
            <label>🏭 Поставщик</label>
            <select name="supplier" required style="width:100%; padding:8px; border-radius:8px; border:1px solid #ddd;">
                <option value="">-- Выберите поставщика --</option>
                <?php foreach ($suppliers as $sup): ?>
                    <option value="<?= $sup['ID_Supplier'] ?>"><?= htmlspecialchars($sup['Company_Name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex:1;">
            <label>👤 Ответственный</label>
            <input type="text" name="responsible" placeholder="ФИО ответственного" required style="width:100%; padding:8px; border-radius:8px; border:1px solid #ddd;">
        </div>
    </div>
    
    <h3>📦 Товары в поставке</h3>
    <div id="delivery-items">
        <div class="delivery-item" style="display:flex; gap:10px; margin-bottom:10px; flex-wrap:wrap;">
            <select name="product_id[]" required style="flex:2; padding:8px; border-radius:8px; border:1px solid #ddd;">
                <option value="">-- Выберите товар --</option>
                <?php foreach ($products as $id => $prod): ?>
                    <option value="<?= $id ?>"><?= htmlspecialchars($prod['Souvenir_Name']) ?> (остаток: <?= $prod['Stock_Quantity'] ?> шт.)</option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="quantity[]" placeholder="Кол-во" min="1" required style="flex:1; padding:8px; border-radius:8px; border:1px solid #ddd;">
            <input type="number" name="price[]" placeholder="Закуп. цена" step="0.01" min="0" required style="flex:1; padding:8px; border-radius:8px; border:1px solid #ddd;">
            <button type="button" class="remove-item" style="background:#e74c3c; color:#fff; border:none; padding:8px 15px; border-radius:8px; cursor:pointer;">✖</button>
        </div>
    </div>
    <button type="button" id="add-item" style="background:#3498db; color:#fff; border:none; padding:8px 20px; border-radius:8px; cursor:pointer; margin:10px 0;">➕ Добавить товар</button><br>
    <button type="submit" name="add" style="background:#2ecc71; color:#fff; border:none; padding:10px 30px; border-radius:8px; cursor:pointer;">✅ Добавить поставку</button>
</form>

<script>
document.getElementById('add-item').addEventListener('click', function() {
    const newItem = document.createElement('div');
    newItem.className = 'delivery-item';
    newItem.style.display = 'flex';
    newItem.style.gap = '10px';
    newItem.style.marginBottom = '10px';
    newItem.style.flexWrap = 'wrap';
    newItem.innerHTML = `
        <select name="product_id[]" required style="flex:2; padding:8px; border-radius:8px; border:1px solid #ddd;">
            <option value="">-- Выберите товар --</option>
            <?php foreach ($products as $id => $prod): ?>
                <option value="<?= $id ?>"><?= htmlspecialchars($prod['Souvenir_Name']) ?> (остаток: <?= $prod['Stock_Quantity'] ?> шт.)</option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="quantity[]" placeholder="Кол-во" min="1" required style="flex:1; padding:8px; border-radius:8px; border:1px solid #ddd;">
        <input type="number" name="price[]" placeholder="Закуп. цена" step="0.01" min="0" required style="flex:1; padding:8px; border-radius:8px; border:1px solid #ddd;">
        <button type="button" class="remove-item" style="background:#e74c3c; color:#fff; border:none; padding:8px 15px; border-radius:8px; cursor:pointer;">✖</button>
    `;
    document.getElementById('delivery-items').appendChild(newItem);
});

document.addEventListener('click', function(e) {
    if(e.target.classList.contains('remove-item')) {
        if(document.querySelectorAll('.delivery-item').length > 1) {
            e.target.closest('.delivery-item').remove();
        } else {
            alert('Должен быть хотя бы один товар');
        }
    }
});
</script>

<hr>

<h2>📋 История поставок</h2>
<table class="admin-table" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr style="background:#e0f7fa;">
            <th style="padding:10px;">ID</th>
            <th style="padding:10px;">Дата</th>
            <th style="padding:10px;">Поставщик</th>
            <th style="padding:10px;">Ответственный</th>
            <th style="padding:10px;">Товары</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $res = $mysqli->query("
        SELECT d.*, s.Company_Name 
        FROM delivery d
        LEFT JOIN supplier s ON d.ID_Supplier = s.ID_Supplier
        ORDER BY d.ID_Delivery DESC
    ");
    while ($row = $res->fetch_assoc()):
        $itemsHtml = '';
        $itemsRes = $mysqli->query("
            SELECT dc.*, p.Souvenir_Name 
            FROM delivery_contents dc
            LEFT JOIN products p ON dc.ID_Product = p.ID_Products
            WHERE dc.ID_Deliver = {$row['ID_Delivery']}
        ");
        if($itemsRes->num_rows) {
            while($item = $itemsRes->fetch_assoc()) {
                $itemsHtml .= "📦 {$item['Souvenir_Name']} (+{$item['Quantity']} шт.)<br>";
            }
        } else { 
            $itemsHtml = "Нет товаров"; 
        }
    ?>
    <tr style="border-bottom:1px solid #eee;">
        <td style="padding:10px;"><?= $row['ID_Delivery'] ?></td>
        <td style="padding:10px;"><?= date('d.m.Y', strtotime($row['Date'])) ?></td>
        <td style="padding:10px;"><?= htmlspecialchars($row['Company_Name'] ?? '—') ?></td>
        <td style="padding:10px;"><?= htmlspecialchars($row['Responsible_Person'] ?? '—') ?></td>
        <td style="padding:10px;"><?= $itemsHtml ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<h2>📊 Текущие остатки на складе</h2>
<table class="admin-table" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr style="background:#e0f7fa;">
            <th style="padding:10px;">ID</th>
            <th style="padding:10px;">Товар</th>
            <th style="padding:10px;">Остаток</th>
            <th style="padding:10px;">Статус</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $res = $mysqli->query("SELECT ID_Products, Souvenir_Name, Stock_Quantity FROM products WHERE is_deleted = 0 ORDER BY Stock_Quantity ASC");
    while ($row = $res->fetch_assoc()):
        $status = $row['Stock_Quantity'] < 10 ? '🟡 Мало' : ($row['Stock_Quantity'] == 0 ? '🔴 Нет' : '🟢 Норма');
        $color = $row['Stock_Quantity'] < 10 ? '#f39c12' : ($row['Stock_Quantity'] == 0 ? '#e74c3c' : '#2ecc71');
    ?>
    <tr style="border-bottom:1px solid #eee;">
        <td style="padding:10px;"><?= $row['ID_Products'] ?></td>
        <td style="padding:10px;"><?= htmlspecialchars($row['Souvenir_Name']) ?></td>
        <td style="padding:10px; color:<?= $color ?>; font-weight:bold;"><?= $row['Stock_Quantity'] ?> шт.</td>
        <td style="padding:10px;"><?= $status ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>