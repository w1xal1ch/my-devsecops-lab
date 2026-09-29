<?php
session_start();
require_once __DIR__ . '/php/config/db_connect.php';

$order_id = $_GET['id'] ?? 0;
if (!$order_id) {
    header('Location: /acs/');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM `order` WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: /acs/');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div style="max-width: 600px; margin: 40px auto; text-align: center;">
    <div style="background: #1e1e1e; border-radius: 16px; padding: 40px;">
        <div style="font-size: 64px;">✅</div>
        <h2 style="color: #2ecc71;">Заказ успешно оформлен!</h2>
        <p>Номер вашего заказа: <strong style="color: #e74c3c; font-size: 1.3rem;">№<?= $order['order_id'] ?></strong></p>
        <p>Статус: <strong><?= htmlspecialchars($order['status']) ?></strong></p>
        <p>Подтверждение отправлено на ваш email.</p>
        
        <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="/acs/" style="background:#e74c3c; color:#fff; padding: 12px 24px; border-radius:8px; text-decoration:none;">🏠 На главную</a>
            <a href="/acs/catalog.php" style="background: #555; color:#fff; padding: 12px 24px; border-radius:8px; text-decoration:none;">🛍️ Продолжить покупки</a>
            <?php if (isset($_SESSION['client'])): ?>
                <a href="/acs/profile.php?tab=orders" style="background: #333; color:#fff; padding: 12px 24px; border-radius:8px; text-decoration:none;">📦 Мои заказы</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>