<?php
session_start();
require_once __DIR__ . '/php/config/db_connect.php';

// Получаем топ-4 товара по количеству продаж
$topStmt = $pdo->query("
    SELECT p.product_id, p.name, p.price, p.photo, SUM(oi.quantity) as total_sold
    FROM product p
    JOIN orderitem oi ON p.product_id = oi.product_id
    GROUP BY p.product_id
    ORDER BY total_sold DESC
    LIMIT 4
");
$topProducts = $topStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero баннер -->
<div class="hero-banner">
    <div>
        <h1>Luxe Accessories</h1>
        <p>Стильные аксессуары для вас</p>
        <a href="/acs/catalog.php" class="btn-primary">В каталог</a>
    </div>
</div>

<!-- Популярные товары -->
<?php if (!empty($topProducts)): ?>
    <section style="margin: 60px 0;">
        <h2 class="section-title">🔥 Популярные товары</h2>
        <div class="product-grid">
            <?php foreach ($topProducts as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="/acs/img/products/<?= htmlspecialchars($product['photo']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <div class="product-details">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</p>
                        <form method="post" action="/acs/catalog.php">
                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="btn-add-to-cart">В корзину</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php else: ?>
    <section style="margin: 60px 0;">
        <h2 class="section-title">🔥 Популярные товары</h2>
        <p style="text-align: center; color: #aaa;">Пока нет данных о продажах. Добавьте заказы.</p>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>