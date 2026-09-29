<?php
require_once 'php/db.php';
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>СувенирМаркет</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/main.js"></script>
</head>
<body>
<?php include 'php/header.php'; ?>

<div class="container">
    <?php
    switch ($page) {
        case 'catalog':
            include 'php/catalog.php';
            break;
        case 'cart':
            include 'php/cart.php';
            break;
        case 'auth':
            include 'php/auth_form.php';
            break;
        case 'register':
            include 'php/register_form.php';
            break;
        default:
            $topQuery = "
                SELECT p.ID_Products, p.Souvenir_Name, p.Price, p.Photo,
                       SUM(sc.Quantity) as total_sold
                FROM sale_contents sc
                JOIN products p ON sc.ID_Product = p.ID_Products
                WHERE p.is_deleted = 0
                GROUP BY p.ID_Products
                ORDER BY total_sold DESC
                LIMIT 4
            ";
            $topResult = $mysqli->query($topQuery);
            $topProducts = [];
            if ($topResult) {
                while ($row = $topResult->fetch_assoc()) {
                    $topProducts[] = $row;
                }
            }
            ?>
            <div class="hero">
                <h1>СувенирМаркет</h1>
                <p>Уникальные сувениры и подарки</p>
                <a href="/index.php?page=catalog" class="btn">В каталог</a>
            </div>
            <?php if (!empty($topProducts)): ?>
            <section>
                <h2 style="text-align:center;">Популярные товары</h2>
                <div class="popular-grid">
                    <?php foreach ($topProducts as $p):
                        $photoExists = ($p['Photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/img/' . $p['Photo'])) ? $p['Photo'] : 'no-image.jpg';
                    ?>
                    <div class="product">
                        <img src="/img/<?= $photoExists ?>" alt="<?= htmlspecialchars($p['Souvenir_Name']) ?>" onclick="showProduct(<?= $p['ID_Products'] ?>)" style="cursor:pointer;">
                        <h3 onclick="showProduct(<?= $p['ID_Products'] ?>)" style="cursor:pointer; color:#00acc1;"><?= htmlspecialchars($p['Souvenir_Name']) ?></h3>
                        <div class="price"><?= $p['Price'] ?> ₽</div>
                        <form method="post" action="/php/add_to_cart.php">
                            <input type="hidden" name="product_id" value="<?= $p['ID_Products'] ?>">
                            <button type="submit">В корзину</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif;
            break;
    }
    ?>
</div>

<!-- ТОЛЬКО МОДАЛКА ДЛЯ ТОВАРА -->
<div id="modal-bg" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;"></div>
<div id="modal-product" class="modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; z-index:1000; border-radius:16px; max-width:900px; width:90%; max-height:80%; overflow:auto; padding:20px; box-shadow:0 4px 20px rgba(0,0,0,0.3);"></div>

<?php include 'php/footer.php'; ?>
</body>
</html>