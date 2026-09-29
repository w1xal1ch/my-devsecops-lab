<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/../php/config/db_connect.php';

// --- ДОБАВЛЕНИЕ НОВОГО ТОВАРА ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $manufacturer_id = $_POST['manufacturer_id'] ? (int)$_POST['manufacturer_id'] : null;
    $type_id = $_POST['type_id'] ? (int)$_POST['type_id'] : null;
    $description = trim($_POST['description']);
    
    // Обработка фото
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $photo = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/../img/products/' . $photo);
        }
    }
    
    if ($name && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO product (name, price, stock, manufacturer_id, ID_Type, description, photo, is_deleted) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$name, $price, $stock, $manufacturer_id, $type_id, $description, $photo]);
        $success_add = "Товар добавлен!";
    }
}

// --- РЕДАКТИРОВАНИЕ ТОВАРА ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $product_id = (int)$_POST['product_id'];
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $manufacturer_id = $_POST['manufacturer_id'] ? (int)$_POST['manufacturer_id'] : null;
    $type_id = $_POST['type_id'] ? (int)$_POST['type_id'] : null;
    $description = trim($_POST['description']);
    
    // Фото (если загружено новое)
    $photo_sql = "";
    $params = [];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $photo = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/../img/products/' . $photo);
            $photo_sql = "photo = ?,";
            $params[] = $photo;
        }
    }
    
    $params = array_merge([$name, $price, $stock, $manufacturer_id, $type_id, $description, $product_id], $params);
    
    if ($photo_sql) {
        $sql = "UPDATE product SET name = ?, price = ?, stock = ?, manufacturer_id = ?, ID_Type = ?, description = ?, $photo_sql is_deleted = 0 WHERE product_id = ?";
    } else {
        $sql = "UPDATE product SET name = ?, price = ?, stock = ?, manufacturer_id = ?, ID_Type = ?, description = ?, is_deleted = 0 WHERE product_id = ?";
        array_pop($params); // убираем лишний элемент для photo
        $params = [$name, $price, $stock, $manufacturer_id, $type_id, $description, $product_id];
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $success_edit = "Товар обновлён!";
}

// --- МЯГКОЕ УДАЛЕНИЕ (в архив) ---
if (isset($_GET['soft_delete'])) {
    $id = (int)$_GET['soft_delete'];
    $stmt = $pdo->prepare("UPDATE product SET is_deleted = 1 WHERE product_id = ?");
    $stmt->execute([$id]);
    header('Location: products.php');
    exit;
}

// --- ДОБАВЛЕНИЕ ПОСТАВКИ (старый код) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supply'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($product_id > 0 && $quantity > 0) {
        $stmt = $pdo->prepare("UPDATE product SET stock = stock + ? WHERE product_id = ?");
        $stmt->execute([$quantity, $product_id]);
        $stmt = $pdo->prepare("INSERT INTO supplies (product_id, quantity, date) VALUES (?, ?, NOW())");
        $stmt->execute([$product_id, $quantity]);
        $success = "Поставка добавлена (+$quantity)";
    }
}

// Получаем списки для выпадающих меню (производители, типы)
$manufacturers = $pdo->query("SELECT manufacturer_id, name FROM manufacturer ORDER BY name")->fetchAll();
$types = $pdo->query("SELECT ID_Type, Name FROM souvenir_type ORDER BY Name")->fetchAll();

// Получаем список товаров (только активные, is_deleted = 0)
$products = $pdo->query("
    SELECT p.*, m.name AS manufacturer_name, t.Name AS type_name, 
           (SELECT SUM(quantity) FROM supplies WHERE product_id = p.product_id) as total_supplied
    FROM product p 
    LEFT JOIN manufacturer m ON p.manufacturer_id = m.manufacturer_id
    LEFT JOIN souvenir_type t ON p.ID_Type = t.ID_Type
    WHERE p.is_deleted = 0
    ORDER BY p.product_id DESC
")->fetchAll();

// История поставок
$suppliesHistory = $pdo->query("
    SELECT s.*, p.name as product_name 
    FROM supplies s 
    JOIN product p ON s.product_id = p.product_id 
    ORDER BY s.date DESC 
    LIMIT 50
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Товары и поставки</title>
    <link rel="stylesheet" href="/acs/css/main.css">
    <style>
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #333; flex-wrap: wrap; }
        .tab { padding: 10px 20px; cursor: pointer; background: none; border: none; color: #aaa; font-size: 1rem; }
        .tab.active { color: #e74c3c; border-bottom: 2px solid #e74c3c; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .supply-form, .product-form { background: #1e1e1e; padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .product-form { flex-direction: column; align-items: stretch; }
        .form-row { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 10px; }
        .form-group { flex: 1; min-width: 150px; display: flex; flex-direction: column; }
        .form-group label { font-size: 0.8rem; color: #aaa; margin-bottom: 5px; }
        .form-group input, .form-group select, .form-group textarea { padding: 8px; border-radius: 6px; background: #2a2a2a; color: #fff; border: none; }
        .stock-low { color: #e74c3c; font-weight: bold; }
        .stock-ok { color: #2ecc71; }
        .supply-table { width: 100%; border-collapse: collapse; background: #1e1e1e; border-radius: 12px; overflow: hidden; }
        .supply-table th, .supply-table td { padding: 10px; text-align: left; border-bottom: 1px solid #333; }
        .supply-table th { background: #333; }
        .btn-supply { background: #2ecc71; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
        .btn-edit { background: #f39c12; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.8rem; margin-right: 5px; }
        .btn-delete { background: #e74c3c; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.8rem; }
        .btn-archive { background: #7f8c8d; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: #1e1e1e; padding: 20px; border-radius: 12px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto; }
        .close-modal { float: right; cursor: pointer; font-size: 24px; color: #aaa; }
        .close-modal:hover { color: #fff; }
    </style>
</head>
<body>
<div class="container" style="margin-top: 1rem;">
    <h1>📦 Управление товарами и поставками</h1>

    <div class="tabs">
        <button class="tab active" onclick="showTab('products')">🛍️ Товары</button>
        <button class="tab" onclick="showTab('addProduct')">➕ Добавить товар</button>
        <button class="tab" onclick="showTab('supplies')">📦 Поставки</button>
        <button class="tab" onclick="showTab('addSupply')">📥 Добавить поставку</button>
        <a href="deleted_products.php" style="padding: 10px 20px; background:#555; color:#fff; border-radius:8px; text-decoration:none; margin-left:auto;">🗑️ Архив</a>
    </div>

    <!-- ===================== ВКЛАДКА ТОВАРЫ ===================== -->
    <div id="tab-products" class="tab-content active">
        <h3>📋 Список товаров</h3>
        <?php if (isset($success_edit)): ?>
            <div style="background:#2ecc7120; border-left:4px solid #2ecc71; padding:10px; margin-bottom:15px;">✅ <?= htmlspecialchars($success_edit) ?></div>
        <?php endif; ?>
        <table class="supply-table">
            <thead>
                <tr><th>ID</th><th>Фото</th><th>Название</th><th>Цена</th><th>Производитель</th><th>Тип</th><th>Остаток</th><th>Всего поставок</th><th>Действия</th></tr>
            </thead>
            <tbody>
            <?php foreach ($products as $prod): ?>
                <tr>
                    <td><?= $prod['product_id'] ?></td>
                    <td><?php if (!empty($prod['photo'])): ?><img src="/acs/img/products/<?= htmlspecialchars($prod['photo']) ?>" style="width:50px; height:50px; object-fit:cover;"><?php else: ?>-<?php endif; ?></td>
                    <td><?= htmlspecialchars($prod['name']) ?></td>
                    <td><?= number_format($prod['price'], 0, ',', ' ') ?> ₽</td>
                    <td><?= htmlspecialchars($prod['manufacturer_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($prod['type_name'] ?? '-') ?></td>
                    <td class="<?= $prod['stock'] < 10 ? 'stock-low' : 'stock-ok' ?>"><?= $prod['stock'] ?> шт.</td>
                    <td><?= $prod['total_supplied'] ?? 0 ?> шт.</td>
                    <td>
                        <button class="btn-edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($prod)) ?>)">✏️ Ред.</button>
                        <a href="?soft_delete=<?= $prod['product_id'] ?>" class="btn-delete btn-archive" onclick="return confirm('Переместить в архив?')">📦 В архив</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ===================== ВКЛАДКА ДОБАВИТЬ ТОВАР ===================== -->
    <div id="tab-addProduct" class="tab-content">
        <h3>➕ Новый товар</h3>
        <?php if (isset($success_add)): ?>
            <div style="background:#2ecc7120; border-left:4px solid #2ecc71; padding:10px; margin-bottom:15px;">✅ <?= htmlspecialchars($success_add) ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="product-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Название *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Цена *</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Остаток</label>
                    <input type="number" name="stock" value="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Производитель</label>
                    <select name="manufacturer_id">
                        <option value="">-- Не выбрано --</option>
                        <?php foreach ($manufacturers as $m): ?>
                            <option value="<?= $m['manufacturer_id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Тип товара</label>
                    <select name="type_id">
                        <option value="">-- Не выбрано --</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= $t['ID_Type'] ?>"><?= htmlspecialchars($t['Name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Фото</label>
                    <input type="file" name="photo" accept="image/*">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
            </div>
            <div>
                <button type="submit" name="add_product" class="btn-supply">➕ Добавить товар</button>
            </div>
        </form>
    </div>

    <!-- ===================== ВКЛАДКА ИСТОРИЯ ПОСТАВОК ===================== -->
    <div id="tab-supplies" class="tab-content">
        <h3>📜 История поставок</h3>
        <?php if (empty($suppliesHistory)): ?>
            <p>История поставок пуста</p>
        <?php else: ?>
            <table class="supply-table">
                <thead><tr><th>ID</th><th>Товар</th><th>Количество</th><th>Дата</th></tr></thead>
                <tbody>
                <?php foreach ($suppliesHistory as $sup): ?>
                    <tr><td><?= $sup['id'] ?></td><td><?= htmlspecialchars($sup['product_name']) ?></td><td>+<?= $sup['quantity'] ?></td><td><?= date('d.m.Y H:i', strtotime($sup['date'])) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- ===================== ВКЛАДКА ДОБАВИТЬ ПОСТАВКУ ===================== -->
    <div id="tab-addSupply" class="tab-content">
        <h3>📥 Добавить поставку</h3>
        <?php if (isset($success)): ?>
            <div style="background:#2ecc7120; border-left:4px solid #2ecc71; padding:10px; margin-bottom:15px;">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" class="supply-form">
            <div class="form-group">
                <label>Товар</label>
                <select name="product_id" required>
                    <option value="">Выберите товар</option>
                    <?php foreach ($products as $prod): ?>
                        <option value="<?= $prod['product_id'] ?>"><?= htmlspecialchars($prod['name']) ?> (остаток: <?= $prod['stock'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Количество</label>
                <input type="number" name="quantity" required min="1">
            </div>
            <div>
                <button type="submit" name="add_supply" class="btn-supply">✅ Добавить поставку</button>
            </div>
        </form>
    </div>

    <p><a href="index.php" style="display:inline-block; margin-top:20px;">← Вернуться в панель администратора</a></p>
</div>

<!-- Модальное окно редактирования товара -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeEditModal()">&times;</span>
        <h3>✏️ Редактировать товар</h3>
        <form method="post" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="product_id" id="edit_product_id">
            <div class="form-group">
                <label>Название *</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="form-group">
                <label>Цена *</label>
                <input type="number" name="price" step="0.01" id="edit_price" required>
            </div>
            <div class="form-group">
                <label>Остаток</label>
                <input type="number" name="stock" id="edit_stock">
            </div>
            <div class="form-group">
                <label>Производитель</label>
                <select name="manufacturer_id" id="edit_manufacturer_id">
                    <option value="">-- Не выбрано --</option>
                    <?php foreach ($manufacturers as $m): ?>
                        <option value="<?= $m['manufacturer_id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Тип товара</label>
                <select name="type_id" id="edit_type_id">
                    <option value="">-- Не выбрано --</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= $t['ID_Type'] ?>"><?= htmlspecialchars($t['Name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Новое фото (оставьте пустым, чтобы не менять)</label>
                <input type="file" name="photo" accept="image/*">
            </div>
            <div class="form-group">
                <label>Описание</label>
                <textarea name="description" id="edit_description" rows="3"></textarea>
            </div>
            <div>
                <button type="submit" name="edit_product" class="btn-supply">💾 Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.classList.add('active');
}

function openEditModal(product) {
    document.getElementById('edit_product_id').value = product.product_id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_stock').value = product.stock || 0;
    document.getElementById('edit_manufacturer_id').value = product.manufacturer_id || '';
    document.getElementById('edit_type_id').value = product.ID_Type || '';
    document.getElementById('edit_description').value = product.description || '';
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Закрытие по клику вне модального окна
window.onclick = function(event) {
    let modal = document.getElementById('editModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>
</body>
</html>