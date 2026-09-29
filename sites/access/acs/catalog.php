<?php
session_start();
require_once __DIR__ . '/php/config/db_connect.php';

// Добавление в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
    $_SESSION['cart_count'] = array_sum($_SESSION['cart']);
    header('Location: /acs/catalog.php');
    exit;
}

include __DIR__ . '/includes/header.php';

// Получение параметров фильтрации
$search = trim($_GET['search'] ?? '');
$manufacturerFilter = isset($_GET['manufacturer']) ? (int)$_GET['manufacturer'] : 0;
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : 0;
$selectedType = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$sort = $_GET['sort'] ?? 'name_asc';

// Получаем типы товаров
$typesStmt = $pdo->query("SELECT ID_Type, Name FROM souvenir_type ORDER BY Name");
$productTypes = $typesStmt->fetchAll();

// Получаем производителей
$manufStmt = $pdo->query("SELECT manufacturer_id, name FROM manufacturer ORDER BY name ASC");
$manufacturers = $manufStmt->fetchAll();

// Получаем мин и макс цены для фильтра
$priceRange = $pdo->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM product WHERE is_deleted = 0")->fetch();

// Построение запроса
$sql = "SELECT p.*, m.name AS manufacturer_name 
        FROM product p 
        LEFT JOIN manufacturer m ON p.manufacturer_id = m.manufacturer_id 
        WHERE p.is_deleted = 0";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.name LIKE :search_name OR m.name LIKE :search_manufacturer)";
    $params['search_name'] = "%$search%";
    $params['search_manufacturer'] = "%$search%";
}
if ($manufacturerFilter > 0) {
    $sql .= " AND p.manufacturer_id = :manufacturer";
    $params['manufacturer'] = $manufacturerFilter;
}
if ($minPrice > 0) {
    $sql .= " AND p.price >= :min_price";
    $params['min_price'] = $minPrice;
}
if ($maxPrice > 0) {
    $sql .= " AND p.price <= :max_price";
    $params['max_price'] = $maxPrice;
}
if ($selectedType > 0) {
    $sql .= " AND p.ID_Type = :type";
    $params['type'] = $selectedType;
}

switch ($sort) {
    case 'price_asc': $sql .= " ORDER BY p.price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY p.price DESC"; break;
    case 'name_desc': $sql .= " ORDER BY p.name DESC"; break;
    default: $sql .= " ORDER BY p.name ASC"; break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<style>
    .filters-form {
        background: #1e1e1e;
        padding: 20px;
        border-radius: 16px;
        margin-bottom: 30px;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }
    
    .filter-group {
        flex: 1 1 auto;
        min-width: 140px;
    }
    
    .filter-group label {
        display: block;
        font-size: 0.75rem;
        color: #aaa;
        margin-bottom: 6px;
        letter-spacing: 0.5px;
    }
    
    .filter-group input,
    .filter-group select {
        width: 100%;
        padding: 10px 12px;
        background: #2a2a2a;
        border: none;
        border-radius: 10px;
        color: #eee;
        font-size: 0.9rem;
        outline: none;
        transition: all 0.2s;
        box-sizing: border-box;
    }
    
    .filter-group input:focus,
    .filter-group select:focus {
        background: #333;
        box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.3);
    }
    
    .price-range {
        flex: 1;
        min-width: 150px;
    }
    
    .price-inputs {
        display: flex;
        flex-direction: column;
        gap: 8px;
        width: 100%;
    }
    
    .price-field {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #2a2a2a;
        border-radius: 10px;
        padding: 0 12px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .price-field span {
        color: #aaa;
        font-size: 0.85rem;
        flex-shrink: 0;
        min-width: 24px;
    }
    
    .price-field input {
        padding: 10px 0;
        background: transparent;
        width: 100%;
        min-width: 0;
        border: none;
        outline: none;
        color: #eee;
    }
    
    .button-group {
        flex: 0 0 auto;
        display: flex;
        gap: 10px;
    }
    
    .button-group button,
    .reset-link {
        background: #e74c3c;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-size: 0.9rem;
        min-width: 110px;
    }
    
    .button-group button:hover {
        background: #c0392b;
    }
    
    .reset-link {
        background: #555;
    }
    
    .reset-link:hover {
        background: #666;
    }
    
    @media (max-width: 768px) {
        .filters-form {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            padding: 15px;
        }
        .filter-group {
            width: 100%;
            min-width: auto;
        }
        .price-range {
            width: 100%;
        }
        .button-group {
            width: 100%;
            gap: 10px;
        }
        .button-group button,
        .reset-link {
            flex: 1;
            text-align: center;
            justify-content: center;
            padding: 10px 16px;
            min-width: 0;
        }
    }
    
    .search-info {
        background: #1e1e1e;
        padding: 10px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .search-info span {
        color: #e74c3c;
        font-weight: bold;
    }
    
    .clear-search {
        color: #e74c3c;
        text-decoration: none;
        background: #2a2a2a;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    
    .clear-search:hover {
        background: #e74c3c;
        color: #fff;
    }
    
    .product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    @media (max-width: 1024px) {
        .product-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
    }
    
    @media (max-width: 768px) {
        .product-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
    }
    
    @media (max-width: 480px) {
        .product-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .no-products {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: #aaa;
        font-size: 1.1rem;
    }
    
    .no-products a {
        display: inline-block;
        margin-top: 15px;
    }
</style>

<h2 class="section-title">Каталог товаров</h2>

<!-- Форма фильтрации -->
<form method="get" action="/acs/catalog.php" class="filters-form" id="filterForm">
    <div class="filter-group">
        <label>🔍 Поиск</label>
        <input type="text" name="search" placeholder="По названию или производителю..." value="<?= htmlspecialchars($search) ?>">
    </div>
    
    <div class="filter-group">
        <label>📂 Категория</label>
        <select name="type">
            <option value="0">Все категории</option>
            <?php foreach ($productTypes as $type): ?>
                <option value="<?= $type['ID_Type'] ?>" <?= $selectedType == $type['ID_Type'] ? 'selected' : '' ?>><?= htmlspecialchars($type['Name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="filter-group">
        <label>🏭 Производитель</label>
        <select name="manufacturer">
            <option value="0">Все производители</option>
            <?php foreach ($manufacturers as $manuf): ?>
                <option value="<?= $manuf['manufacturer_id'] ?>" <?= $manufacturerFilter == $manuf['manufacturer_id'] ? 'selected' : '' ?>><?= htmlspecialchars($manuf['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="filter-group">
        <label>📊 Сортировка</label>
        <select name="sort">
            <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>По названию (А-Я)</option>
            <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>По названию (Я-А)</option>
            <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Сначала дешёвые</option>
            <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Сначала дорогие</option>
        </select>
    </div>
    
    <div class="filter-group price-range">
        <label>💰 Цена (₽)</label>
        <div class="price-inputs">
            <div class="price-field">
                <span>от</span>
                <input type="number" name="min_price" placeholder="<?= floor($priceRange['min_price'] ?? 0) ?>" value="<?= $minPrice > 0 ? $minPrice : '' ?>">
            </div>
            <div class="price-field">
                <span>до</span>
                <input type="number" name="max_price" placeholder="<?= ceil($priceRange['max_price'] ?? 100000) ?>" value="<?= $maxPrice > 0 ? $maxPrice : '' ?>">
            </div>
        </div>
    </div>
    
    <div class="button-group">
        <button type="submit">🔍 Применить</button>
        <a href="/acs/catalog.php" class="reset-link">🗑️ Сбросить</a>
    </div>
</form>

<!-- Информация о результатах поиска -->
<?php if ($search !== ''): ?>
    <div class="search-info">
        <span>🔍 Результаты поиска: "<?= htmlspecialchars($search) ?>"</span>
        <a href="/acs/catalog.php" class="clear-search">✖ Очистить поиск</a>
    </div>
<?php endif; ?>

<!-- Список товаров -->
<div class="product-grid">
<?php if (count($products) > 0): ?>
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <div class="product-image">
                <a href="/acs/product.php?id=<?= $product['product_id'] ?>">
                    <img src="/acs/img/products/<?= htmlspecialchars($product['photo']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                </a>
            </div>
            <div class="product-details">
                <h3><a href="/acs/product.php?id=<?= $product['product_id'] ?>"><?= htmlspecialchars($product['name']) ?></a></h3>
                <p>Производитель: <?= htmlspecialchars($product['manufacturer_name'] ?? 'Неизвестно') ?></p>
                <p class="price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</p>
                <form method="post" action="/acs/catalog.php">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                    <button type="submit" class="btn-add-to-cart">🛒 В корзину</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="no-products">
        <p>😕 Товары не найдены по вашему запросу.</p>
        <a href="/acs/catalog.php" class="btn-primary">Показать все товары</a>
    </div>
<?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>