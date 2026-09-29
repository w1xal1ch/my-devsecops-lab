<?php
session_start();
require_once __DIR__ . '/php/config/db_connect.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: /acs/catalog.php');
    exit;
}

// Добавление в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && !isset($_POST['submit_review'])) {
    $productId = (int)$_POST['product_id'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
    $_SESSION['cart_count'] = array_sum($_SESSION['cart']);
    header('Location: /acs/product.php?id=' . $productId);
    exit;
}

// Добавление отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['client'])) {
        header('Location: /acs/login.php');
        exit;
    }
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment'] ?? '');
    if ($rating >= 1 && $rating <= 5 && $comment !== '') {
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, client_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_id, $_SESSION['client']['client_id'], $rating, $comment]);
        header("Location: /acs/product.php?id=$product_id");
        exit;
    }
}

$stmt = $pdo->prepare("
    SELECT p.*, m.name AS manufacturer_name, t.Name AS type_name
    FROM product p
    LEFT JOIN manufacturer m ON p.manufacturer_id = m.manufacturer_id
    LEFT JOIN souvenir_type t ON p.ID_Type = t.ID_Type
    WHERE p.product_id = ? AND p.is_deleted = 0
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /acs/catalog.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 20px;">
    <a href="/acs/catalog.php" style="display: inline-flex; align-items: center; gap: 8px; background: #333; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none;">← Назад в каталог</a>
</div>

<div style="display: flex; gap: 40px; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 280px;">
        <?php if (!empty($product['photo'])): ?>
            <img src="/acs/img/products/<?= htmlspecialchars($product['photo']) ?>" style="width: 100%; border-radius: 12px;">
        <?php else: ?>
            <div style="width: 100%; height: 300px; background: #2a2a2a; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #666;">Нет фото</div>
        <?php endif; ?>
    </div>
    <div style="flex: 2;">
        <h1 style="color: #e74c3c;"><?= htmlspecialchars($product['name']) ?></h1>
        <p><strong>Производитель:</strong> <?= htmlspecialchars($product['manufacturer_name'] ?? 'Неизвестно') ?></p>
        <p><strong>Тип:</strong> <?= htmlspecialchars($product['type_name'] ?? 'Не указан') ?></p>
        <p><strong>Цена:</strong> <span style="font-size: 1.8rem; color: #e74c3c;"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</span></p>
        
        <?php if (!empty($product['description'])): ?>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #333;">
                <h3>📝 Описание</h3>
                <div style="background: #1e1e1e; padding: 15px; border-radius: 12px; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="post" action="/acs/product.php?id=<?= $product['product_id'] ?>">
            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
            <button type="submit" style="background: #e74c3c; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 1rem; margin-top: 20px;">🛒 Добавить в корзину</button>
        </form>
    </div>
</div>

<h3 style="margin-top: 40px;">⭐ Отзывы</h3>

<?php
$reviewsStmt = $pdo->prepare("
    SELECT r.*, c.full_name 
    FROM reviews r 
    JOIN client c ON r.client_id = c.client_id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$reviewsStmt->execute([$product_id]);
$reviews = $reviewsStmt->fetchAll();
?>

<?php if (isset($_SESSION['client'])): ?>
    <form method="post" style="background: #1e1e1e; padding: 20px; border-radius: 12px; margin-bottom: 30px;">
        <h4>Оставить отзыв</h4>
        <div style="margin-bottom: 10px;">
            <label>Оценка: </label>
            <select name="rating" required>
                <option value="5">⭐ 5</option>
                <option value="4">⭐ 4</option>
                <option value="3">⭐ 3</option>
                <option value="2">⭐ 2</option>
                <option value="1">⭐ 1</option>
            </select>
        </div>
        <textarea name="comment" rows="3" placeholder="Ваш комментарий" style="width: 100%; background: #2a2a2a; border: none; color: #fff; padding: 10px; border-radius: 8px;"></textarea>
        <button type="submit" name="submit_review" style="margin-top: 10px; background: #e74c3c; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">Отправить отзыв</button>
    </form>
<?php else: ?>
    <p><a href="/acs/login.php">Войдите</a>, чтобы оставить отзыв.</p>
<?php endif; ?>

<?php if (empty($reviews)): ?>
    <p style="color: #aaa;">Пока нет отзывов. Будьте первым!</p>
<?php else: ?>
    <?php foreach ($reviews as $review): ?>
        <div style="background: #1e1e1e; padding: 15px; border-radius: 12px; margin-bottom: 15px;">
            <div style="display: flex; justify-content: space-between;">
                <strong><?= htmlspecialchars($review['full_name']) ?></strong>
                <span>⭐ <?= $review['rating'] ?></span>
            </div>
            <div style="margin-top: 10px;"><?= nl2br(htmlspecialchars($review['comment'])) ?></div>
            <small style="color: #aaa;"><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></small>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>