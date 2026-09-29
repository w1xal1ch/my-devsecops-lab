<?php
require_once 'db.php';

$search = trim($_GET['search'] ?? '');
$category = (int)($_GET['category'] ?? 0);
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$sort = $_GET['sort'] ?? 'name_asc';

$types = [];
$typeRes = $mysqli->query("SELECT * FROM souvenir_type ORDER BY Name");
while ($row = $typeRes->fetch_assoc()) {
    $types[$row['ID_Type']] = $row['Name'];
}

$where = ["is_deleted = 0"];

// Поиск
if ($search !== '') {
    $searchEsc = $mysqli->real_escape_string($search);
    $where[] = "Souvenir_Name LIKE '%$searchEsc%'";
}

// Категория
if ($category > 0) {
    $where[] = "ID_Type = $category";
}

// Цена от
if ($minPrice > 0) {
    $where[] = "Price >= " . floatval($minPrice);
}

// Цена до
if ($maxPrice > 0) {
    $where[] = "Price <= " . floatval($maxPrice);
}

$whereSql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// ПРАВИЛЬНАЯ СОРТИРОВКА
switch ($sort) {
    case 'price_asc':
        $orderBy = "ORDER BY CAST(Price AS DECIMAL(10,2)) ASC";
        break;
    case 'price_desc':
        $orderBy = "ORDER BY CAST(Price AS DECIMAL(10,2)) DESC";
        break;
    case 'name_desc':
        $orderBy = "ORDER BY Souvenir_Name DESC";
        break;
    case 'name_asc':
    default:
        $orderBy = "ORDER BY Souvenir_Name ASC";
        break;
}

$query = "SELECT * FROM products $whereSql $orderBy";

// ДЛЯ ОТЛАДКИ - раскомментируйте чтобы увидеть запрос
// echo "<pre style='background:#f0f0f0;padding:10px;border-radius:8px;font-size:12px;'>$query</pre>";

$res = $mysqli->query($query);

// Проверка на ошибки запроса
if (!$res) {
    echo '<p class="text-center" style="color:red;">Ошибка запроса: ' . $mysqli->error . '</p>';
    return;
}

if ($res->num_rows === 0) {
    echo '<p class="text-center">Товары не найдены.</p>';
    return;
}
?>

<form method="get" action="/index.php" style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 12px;">
    <input type="hidden" name="page" value="catalog">
    <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div style="flex: 2; min-width: 150px;">
            <label>🔍 Поиск</label>
            <input type="text" name="search" placeholder="Название товара" value="<?= htmlspecialchars($search) ?>" style="width:100%; padding:8px; border-radius:8px; border:1px solid #ddd;">
        </div>
        <div style="flex: 1; min-width: 130px;">
            <label>📂 Категория</label>
            <select name="category" style="width:100%; padding:8px; border-radius:8px; border:1px solid #ddd;">
                <option value="0">Все категории</option>
                <?php foreach ($types as $tid => $tname): ?>
                    <option value="<?= $tid ?>" <?= $category == $tid ? 'selected' : '' ?>><?= htmlspecialchars($tname) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex: 1; min-width: 130px;">
            <label>📊 Сортировка</label>
            <select name="sort" style="width:100%; padding:8px; border-radius:8px; border:1px solid #ddd;">
                <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>По названию (А-Я)</option>
                <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>По названию (Я-А)</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Сначала дешёвые</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Сначала дорогие</option>
            </select>
        </div>
        <div style="flex: 1.5; min-width: 180px;">
            <label>💰 Цена (от — до)</label>
            <div style="display: flex; gap: 8px;">
                <input type="number" name="min_price" placeholder="от" value="<?= $minPrice ?: '' ?>" style="width:100%; padding:8px; border-radius:8px; border:1px solid #ddd;">
                <input type="number" name="max_price" placeholder="до" value="<?= $maxPrice ?: '' ?>" style="width:100%; padding:8px; border-radius:8px; border:1px solid #ddd;">
            </div>
        </div>
        <div>
            <button type="submit" style="background:#2196F3; color:#fff; border:none; padding:8px 20px; border-radius:6px; cursor:pointer;">Применить</button>
            <a href="/index.php?page=catalog" style="display:inline-block; margin-left:10px; padding:8px 16px; background:#ccc; color:#333; border-radius:6px; text-decoration:none;">Сбросить</a>
        </div>
    </div>
</form>

<div class="catalog">
<?php 
$counter = 0;
while ($row = $res->fetch_assoc()): 
    $id = $row['ID_Products'];
    $name = htmlspecialchars($row['Souvenir_Name']);
    $desc = htmlspecialchars($row['Description']);
    $price = (float)$row['Price'];
    $photo = htmlspecialchars($row['Photo']);
    $photoPath = $_SERVER['DOCUMENT_ROOT'] . '/img/' . $photo;
    $photoExists = ($photo && file_exists($photoPath)) ? $photo : 'no-image.jpg';
    $counter++;
?>
<div class="product">
    <img src="/img/<?= $photoExists ?>" alt="<?= $name ?>" onclick="showProduct(<?= $id ?>)" style="cursor:pointer; width:100%; height:200px; object-fit:cover; border-radius:12px;">
    <h3 onclick="showProduct(<?= $id ?>)" style="cursor:pointer; color:#00acc1; margin:10px 0 5px;"><?= $name ?></h3>
    <p style="font-size:0.9rem; color:#666; margin-bottom:10px;"><?= $desc ?></p>
    <div class="price" style="font-size:1.3rem; font-weight:bold; color:#ffc107; margin:10px 0;">
        <?= number_format($price, 0, ',', ' ') ?> ₽
        <span style="font-size:0.7rem; color:#999; font-weight:normal;">(ID: <?= $id ?>)</span>
    </div>
    <form method="post" action="/php/add_to_cart.php">
        <input type="hidden" name="product_id" value="<?= $id ?>">
        <button type="submit" style="background:#00acc1; color:#fff; border:none; padding:10px 20px; border-radius:30px; cursor:pointer; transition:background 0.2s;">В корзину</button>
    </form>
</div>
<?php endwhile; ?>
</div>

<?php if ($counter === 0): ?>
    <p class="text-center">Товары не найдены.</p>
<?php endif; ?>