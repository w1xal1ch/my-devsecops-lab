<?php
$types = [];
$typeRes = $mysqli->query("SELECT * FROM souvenir_type");
while ($row = $typeRes->fetch_assoc()) {
    $types[$row['ID_Type']] = $row['Name'];
}

// Добавление товара
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['desc']);
    $price = (float)$_POST['price'];
    $qty = (int)$_POST['qty'];
    $type = (int)$_POST['type'];
    $photo = '';
    if (!empty($_FILES['photo']['name'])) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = uniqid('prod_') . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/img/' . $photo);
    }
    $stmt = $mysqli->prepare("INSERT INTO products (Souvenir_Name, Description, Price, Stock_Quantity, ID_Type, Photo, is_deleted) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param('ssdiis', $name, $desc, $price, $qty, $type, $photo);
    $stmt->execute();
    $stmt->close();
    echo '<div class="success">✅ Товар успешно добавлен!</div>';
}

// Мягкое удаление
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("UPDATE products SET is_deleted = 1 WHERE ID_Products = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo '<div class="success">🗑️ Товар перемещён в архив!</div>';
}

// Редактирование
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $mysqli->query("SELECT * FROM products WHERE ID_Products=$id");
    if ($row = $res->fetch_assoc()) {
        ?>
        <h3>✏️ Редактировать товар</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="edit_id" value="<?=$id?>">
            <div class="form-group"><label>Название</label><input type="text" name="name" value="<?=htmlspecialchars($row['Souvenir_Name'])?>" required></div>
            <div class="form-group"><label>Описание</label><textarea name="desc" rows="4" required><?=htmlspecialchars($row['Description'])?></textarea></div>
            <div class="form-group"><label>Цена (₽)</label><input type="number" name="price" step="0.01" value="<?=$row['Price']?>" required></div>
            <div class="form-group"><label>Количество</label><input type="number" name="qty" value="<?=$row['Stock_Quantity']?>" required></div>
            <div class="form-group"><label>Категория</label>
                <select name="type"><?php foreach ($types as $tid=>$tname): ?><option value="<?=$tid?>" <?=$tid==$row['ID_Type']?'selected':''?>><?=htmlspecialchars($tname)?></option><?php endforeach; ?></select>
            </div>
            <div class="form-group"><label>Фото</label><input type="file" name="photo" accept="image/*"></div>
            <div class="form-group"><img src="/img/<?=($row['Photo']&&file_exists($_SERVER['DOCUMENT_ROOT'].'/img/'.$row['Photo']))?$row['Photo']:'no-image.jpg'?>" width="80"></div>
            <button type="submit" name="save_edit" class="btn-sm btn-edit">Сохранить</button>
            <a href="?tab=products" class="btn-sm">Отмена</a>
        </form>
        <hr>
        <?php
    }
}

// Сохранение редактирования
if (isset($_POST['save_edit'])) {
    $id = (int)$_POST['edit_id'];
    $name = trim($_POST['name']);
    $desc = trim($_POST['desc']);
    $price = (float)$_POST['price'];
    $qty = (int)$_POST['qty'];
    $type = (int)$_POST['type'];
    if (!empty($_FILES['photo']['name'])) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = uniqid('prod_') . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/img/' . $photo);
        $stmt = $mysqli->prepare("UPDATE products SET Photo=? WHERE ID_Products=?");
        $stmt->bind_param('si', $photo, $id);
        $stmt->execute();
        $stmt->close();
    }
    $stmt = $mysqli->prepare("UPDATE products SET Souvenir_Name=?, Description=?, Price=?, Stock_Quantity=?, ID_Type=? WHERE ID_Products=?");
    $stmt->bind_param('ssdiii', $name, $desc, $price, $qty, $type, $id);
    $stmt->execute();
    $stmt->close();
    echo '<div class="success">✅ Товар обновлён!</div>';
}
?>

<h2>➕ Добавить новый товар</h2>
<form method="post" enctype="multipart/form-data">
    <div class="form-group"><label>Название</label><input type="text" name="name" required></div>
    <div class="form-group"><label>Описание</label><textarea name="desc" rows="4" required></textarea></div>
    <div class="form-group"><label>Цена (₽)</label><input type="number" name="price" step="0.01" required></div>
    <div class="form-group"><label>Количество</label><input type="number" name="qty" required></div>
    <div class="form-group"><label>Категория</label>
        <select name="type"><?php foreach ($types as $tid=>$tname): ?><option value="<?=$tid?>"><?=htmlspecialchars($tname)?></option><?php endforeach; ?></select>
    </div>
    <div class="form-group"><label>Фото</label><input type="file" name="photo" accept="image/*" required></div>
    <button type="submit" name="add_product" class="btn-sm btn-edit">➕ Добавить товар</button>
</form>

<hr>

<h2>📋 Список товаров</h2>
<table class="admin-table">
    <thead><tr><th>ID</th><th>Фото</th><th>Название</th><th>Цена</th><th>Кол-во</th><th>Категория</th><th>Действия</th></tr></thead>
    <tbody>
        <?php
        $res = $mysqli->query("SELECT * FROM products WHERE is_deleted = 0 ORDER BY ID_Products ASC");
        while ($row = $res->fetch_assoc()):
            $photoExists = ($row['Photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/img/' . $row['Photo'])) ? $row['Photo'] : 'no-image.jpg';
        ?>
        <tr>
            <td><?=$row['ID_Products']?></td>
            <td><img src="/img/<?=$photoExists?>" width="50" style="border-radius:8px;"></td>
            <td><?=htmlspecialchars($row['Souvenir_Name'])?></td>
            <td><?=$row['Price']?> ₽</td>
            <td><?=$row['Stock_Quantity']?></td>
            <td><?=htmlspecialchars($types[$row['ID_Type']] ?? '—')?></td>
            <td>
                <a href="?tab=products&edit=<?=$row['ID_Products']?>" class="btn-sm btn-edit">✏️</a>
                <a href="?tab=products&delete=<?=$row['ID_Products']?>" onclick="return confirm('Переместить товар в архив?')" class="btn-sm btn-delete">🗑️</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p><a href="?tab=deleted" class="btn-sm">📦 Архив удалённых товаров</a></p>