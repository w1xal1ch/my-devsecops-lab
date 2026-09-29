<?php
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) exit('Ошибка: товар не найден');

$res = $mysqli->query("SELECT * FROM products WHERE ID_Products = $id AND is_deleted = 0");
if (!$row = $res->fetch_assoc()) exit('Товар не найден');

$photo = $row['Photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/img/' . $row['Photo']) ? $row['Photo'] : 'no-image.jpg';

$user_id = $_SESSION['user']['ID_User'] ?? 0;
$can_review = false;

if ($user_id) {
    $res = $mysqli->query("SELECT 1 
        FROM sale_contents sc
        JOIN sale s ON sc.ID_Sale = s.ID_S
        JOIN client c ON s.ID_Client = c.ID_Client
        WHERE c.ID_User = $user_id AND sc.ID_Product = $id
        LIMIT 1");
    $can_review = $res->num_rows > 0;
}
?>

<!-- КНОПКА ЗАКРЫТИЯ с принудительным вызовом -->
<div style="text-align:right; margin-bottom:10px;">
    <button onclick="document.getElementById('modal-bg').style.display='none'; document.getElementById('modal-product').style.display='none';" style="cursor:pointer; font-size:20px; font-weight:bold; color:#fff; background:#e74c3c; border:none; padding:5px 15px; border-radius:8px;">✖ ЗАКРЫТЬ</button>
</div>

<div style="display:flex; flex-wrap:wrap;">
    <div style="flex:0 0 200px; margin-right:20px;">
        <img src="/img/<?= $photo ?>" width="200" style="border-radius:8px;">
    </div>
    <div style="flex:1;">
        <h2><?= htmlspecialchars($row['Souvenir_Name']) ?></h2>
        <div style="margin:15px 0; font-size:24px; color:#e44d26;">💰 <?= $row['Price'] ?> ₽</div>
        <div style="margin-bottom:20px;"><?= nl2br(htmlspecialchars($row['Description'])) ?></div>
        <button onclick="addToCart(<?= $id ?>)" style="background:#4CAF50; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">🛒 Добавить в корзину</button>
    </div>
</div>

<hr style="margin:25px 0;">

<?php if($can_review): ?>
<div id="review-section">
    <h3>✍️ Оставить отзыв</h3>
    <form id="reviewForm">
        <div style="margin-bottom:10px;">
            <label>Оценка (1-10):</label>
            <select name="rating" required style="padding:5px;">
                <?php for($i=1; $i<=10; $i++): ?>
                    <option value="<?=$i?>" <?=$i==10?'selected':''?>><?=$i?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div style="margin-bottom:15px;">
            <textarea name="text" placeholder="Ваш отзыв" required style="width:100%; height:80px; padding:8px; border:1px solid #ddd; border-radius:8px;"></textarea>
        </div>
        <input type="hidden" name="product_id" value="<?= $id ?>">
        <button type="submit" style="padding:8px 15px; background:#2196F3; color:white; border:none; border-radius:8px; cursor:pointer;">📤 Отправить отзыв</button>
        <div id="reviewError" style="color:#f44336; margin-top:10px;"></div>
    </form>
</div>
<hr style="margin:25px 0;">
<?php endif; ?>

<h3>⭐ Отзывы покупателей</h3>
<?php
$reviews = $mysqli->query("SELECT r.*, c.Full_Name FROM customer_reviews r LEFT JOIN client c ON r.ID_Client = c.ID_Client WHERE r.ID_Product = $id ORDER BY r.Date DESC");
if ($reviews->num_rows) {
    while ($rev = $reviews->fetch_assoc()) {
        echo '<div style="border:1px solid #eee; padding:15px; margin-bottom:15px; border-radius:8px;">
            <div style="display:flex; justify-content:space-between;">
                <b>' . htmlspecialchars($rev['Full_Name']) . '</b>
                <span style="color:#888;">' . $rev['Date'] . '</span>
            </div>
            <div style="margin:8px 0; color:#FF9800;">⭐ ' . $rev['Rating_1_to_10'] . '/10</div>
            <div>' . nl2br(htmlspecialchars($rev['Text'])) . '</div>
        </div>';
    }
} else {
    echo '<p style="color:#888;">Пока нет отзывов. Будьте первым!</p>';
}
?>