<?php
require_once __DIR__ . '/../config/db_connect.php';

// Получаем параметр поиска
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Подготавливаем запрос
$sql = "
    SELECT p.*, m.name AS manufacturer_name, m.country 
    FROM product p
    LEFT JOIN manufacturer m ON p.manufacturer_id = m.manufacturer_id
";

if ($search !== '') {
    $sql .= " WHERE p.name LIKE :search OR p.description LIKE :search OR m.name LIKE :search";
    $params = ['search' => "%$search%"];
} else {
    $params = [];
}

$sql .= " ORDER BY p.product_id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Выводим результаты поиска, если они есть
if ($search !== '') {
    echo "<div class='search-results-header'>";
    echo "<h3>Результаты поиска: \"".htmlspecialchars($search)."\"</h3>";
    echo "<a href='/acs/catalog.php' class='btn-clear-search'><i class='fas fa-times'></i> Сбросить поиск</a>";
    echo "</div>";
}

echo '<div class="product-grid">';
if ($stmt->rowCount() > 0) {
    while ($product = $stmt->fetch()):
?>
    <div class="product-card animate__animated animate__fadeIn">
        <div class="product-image">
            <img src="/acs/img/products/<?= htmlspecialchars($product['photo']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" />
        </div>
        <div class="product-details">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p><strong>Производитель:</strong> <?= htmlspecialchars($product['manufacturer_name'] ?? 'Неизвестно') ?> (<?= htmlspecialchars($product['country'] ?? '-') ?>)</p>
            <p><strong>Размер:</strong> <?= htmlspecialchars($product['size']) ?></p>
            <p><strong>Цвет:</strong> <?= htmlspecialchars($product['color']) ?></p>
            <p class="price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</p>
            <button class="btn-add-to-cart" data-product-id="<?= $product['product_id'] ?>">Добавить в корзину</button>
        </div>
    </div>
<?php
    endwhile;
} else {
    echo "<div class='no-results'>";
    echo "<p>Товары не найдены</p>";
    if ($search !== '') {
        echo "<a href='/acs/catalog.php' class='btn-primary'>Показать все товары</a>";
    }
    echo "</div>";
}
echo '</div>';