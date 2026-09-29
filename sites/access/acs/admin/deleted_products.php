<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: auth.php');
    exit;
}

require_once __DIR__ . '/../php/config/db_connect.php';

if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $stmt = $pdo->prepare("UPDATE product SET is_deleted = 0 WHERE product_id = ?");
    $stmt->execute([$id]);
    header('Location: deleted_products.php');
    exit;
}

if (isset($_GET['force_delete'])) {
    $id = (int)$_GET['force_delete'];
    $stmt = $pdo->prepare("DELETE FROM product WHERE product_id = ?");
    $stmt->execute([$id]);
    header('Location: deleted_products.php');
    exit;
}

$products = $pdo->query("
    SELECT p.*, m.name AS manufacturer_name, t.Name AS type_name 
    FROM product p 
    LEFT JOIN manufacturer m ON p.manufacturer_id = m.manufacturer_id 
    LEFT JOIN souvenir_type t ON p.ID_Type = t.ID_Type
    WHERE p.is_deleted = 1 
    ORDER BY p.product_id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Архив товаров</title>
    <link rel="stylesheet" href="/acs/css/main.css">
</head>
<body>
<div class="container" style="margin-top:1rem;">
    <h1>📦 Архив удалённых товаров</h1>
    <?php if (empty($products)): ?>
        <p>В архиве нет товаров.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" style="border-collapse:collapse; width:100%;">
            <thead><tr><th>ID</th><th>Фото</th><th>Название</th><th>Цена</th><th>Категория</th><th>Действия</th></tr></thead>
            <tbody>
            <?php foreach ($products as $prod): ?>
                <tr>
                    <td><?= $prod['product_id'] ?></td>
                    <td><?php if (!empty($prod['photo'])): ?><img src="/acs/img/products/<?= htmlspecialchars($prod['photo']) ?>" style="width:60px;"><?php else: ?>-<?php endif; ?></td>
                    <td><?= htmlspecialchars($prod['name']) ?></td>
                    <td><?= number_format($prod['price'], 0, ',', ' ') ?> ₽</td>
                    <td><?= htmlspecialchars($prod['type_name'] ?? '-') ?></td>
                    <td>
                        <a href="?restore=<?= $prod['product_id'] ?>">♻️ Восстановить</a>
                        <a href="?force_delete=<?= $prod['product_id'] ?>" onclick="return confirm('Удалить навсегда?');" style="color:#e74c3c;">🗑️ Удалить навсегда</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <p><a href="products.php">← Назад к товарам</a></p>
    <p><a href="index.php">← Вернуться в панель администратора</a></p>
</div>
</body>
</html>