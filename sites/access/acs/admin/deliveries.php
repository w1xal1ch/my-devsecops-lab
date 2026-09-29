<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/../php/config/db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = trim($_POST['status']);
    $allowed_statuses = ['Новый', 'В обработке', 'Отправлен', 'Доставлен', 'Отменён'];
    if (!in_array($status, $allowed_statuses)) {
        $error = 'Недопустимый статус';
    } else {
        $stmt = $pdo->prepare("UPDATE `order` SET status = ? WHERE order_id = ?");
        $stmt->execute([$status, $order_id]);
        $success = 'Статус обновлён';
    }
}

// Получаем заказы без клиента
$orders = $pdo->query("SELECT * FROM `order` ORDER BY order_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Управление доставками</title>
    <link rel="stylesheet" href="/acs/css/main.css" />
</head>
<body>
<div class="container" style="margin-top:1rem;">
    <h1>Доставки и заказы</h1>

    <?php if ($error): ?>
        <div style="color:#e74c3c; margin-bottom:1rem;"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:#2ecc71; margin-bottom:1rem;"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>

    <table style="width:100%; border-collapse: collapse; background:#1f1f1f; color:#eee;">
        <thead>
            <tr>
                <th style="padding:10px; border-bottom:1px solid #333;">ID заказа</th>
                <th style="padding:10px; border-bottom:1px solid #333;">Дата заказа</th>
                <th style="padding:10px; border-bottom:1px solid #333;">Статус</th>
                <th style="padding:10px; border-bottom:1px solid #333;">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td style="padding:10px; border-bottom:1px solid #333;"><?= $order['order_id'] ?></td>
                    <td style="padding:10px; border-bottom:1px solid #333;"><?= htmlspecialchars($order['order_date']) ?></td>
                    <td style="padding:10px; border-bottom:1px solid #333;">
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>" />
                            <select name="status" onchange="this.form.submit()" style="padding:5px; border-radius:5px;">
                                <?php
                                $statuses = ['Новый', 'В обработке', 'Отправлен', 'Доставлен', 'Отменён'];
                                foreach ($statuses as $status):
                                ?>
                                    <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td style="padding:10px; border-bottom:1px solid #333;">
                        <a href="order_items.php?order_id=<?= $order['order_id'] ?>" style="color:#2ecc71;">Подробнее</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="index.php" style="display:inline-block; margin-top:20px;">← Вернуться в панель администратора</a></p>
</div>
</body>
</html>
