<?php
session_start();
require_once __DIR__ . '/php/config/db_connect.php';

if (!isset($_SESSION['client'])) {
    header('Location: /acs/login.php');
    exit;
}

$client = $_SESSION['client'];
$activeTab = $_GET['tab'] ?? 'data';
$error = '';
$success = '';

// Редактирование данных
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_data'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!validateName($full_name)) {
        $error = 'ФИО может содержать только буквы, пробелы и дефис';
    } else {
        $phoneFormatted = formatPhoneForDB($phone);
        if (!$phoneFormatted) {
            $error = 'Телефон должен содержать 11 цифр (например, 8XXXXXXXXXX)';
        } else {
            $stmt = $pdo->prepare("UPDATE client SET full_name = ?, email = ?, phone = ? WHERE client_id = ?");
            $stmt->execute([$full_name, $email, $phoneFormatted, $client['client_id']]);
            $_SESSION['client']['full_name'] = $full_name;
            $_SESSION['client']['email'] = $email;
            $_SESSION['client']['phone'] = $phoneFormatted;
            $success = 'Данные обновлены';
        }
    }
}

// Добавление адреса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $addr = trim($_POST['new_address'] ?? '');
    if ($addr) {
        $stmt = $pdo->prepare("INSERT INTO user_addresses (client_id, address) VALUES (?, ?)");
        $stmt->execute([$client['client_id'], $addr]);
        $success = 'Адрес добавлен';
    }
}

// Удаление адреса
if (isset($_GET['delete_address'])) {
    $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND client_id = ?");
    $stmt->execute([$_GET['delete_address'], $client['client_id']]);
    $success = 'Адрес удалён';
    header('Location: /acs/profile.php?tab=addresses');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    .profile-tabs {
        display: flex;
        gap: 5px;
        border-bottom: 2px solid #e74c3c;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    .profile-tab {
        padding: 12px 24px;
        text-decoration: none;
        color: #aaa;
        font-weight: 600;
        transition: all 0.2s;
        border-radius: 8px 8px 0 0;
    }
    .profile-tab.active {
        color: #e74c3c;
        background: #1e1e1e;
    }
    .profile-tab:hover:not(.active) {
        color: #fff;
        background: #2a2a2a;
    }
    .form-card {
        background: #1e1e1e;
        padding: 25px;
        border-radius: 16px;
        margin-bottom: 20px;
    }
    .form-card input, .form-card select {
        background: #2a2a2a;
        border: none;
        padding: 10px 15px;
        border-radius: 8px;
        color: #fff;
        width: 100%;
        box-sizing: border-box;
    }
    .form-card button {
        background: #e74c3c;
        color: #fff;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }
    .address-item {
        background: #2a2a2a;
        padding: 12px 15px;
        border-radius: 10px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .order-card {
        background: #1e1e1e;
        border-radius: 16px;
        margin-bottom: 25px;
        overflow: hidden;
    }
    .order-header {
        background: #2a2a2a;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        border-left: 4px solid #e74c3c;
    }
    .order-number {
        font-size: 1.2rem;
        font-weight: bold;
        color: #e74c3c;
    }
    .order-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .status-Новый { background: #3498db; }
    .status-В_обработке { background: #f39c12; }
    .status-Отправлен { background: #9b59b6; }
    .status-Доставлен { background: #2ecc71; }
    .status-Отменён { background: #e74c3c; }
    .order-date {
        color: #aaa;
        font-size: 0.85rem;
    }
    .order-total {
        font-size: 1.1rem;
        font-weight: bold;
        color: #e74c3c;
    }
    .order-body {
        padding: 20px;
    }
    .order-products {
        width: 100%;
        border-collapse: collapse;
    }
    .order-products th {
        text-align: left;
        padding: 10px;
        background: #2a2a2a;
        color: #aaa;
        font-weight: 500;
    }
    .order-products td {
        padding: 12px 10px;
        border-bottom: 1px solid #333;
    }
    .product-img-small {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }
    .product-name {
        font-weight: 500;
    }
    .order-summary {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #333;
        text-align: right;
        font-size: 1.1rem;
    }
    .empty-state {
        text-align: center;
        padding: 50px;
        color: #aaa;
    }
    .btn-view-order {
        background: #333;
        color: #fff;
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.8rem;
    }
    @media (max-width: 768px) {
        .order-products th, .order-products td {
            font-size: 0.8rem;
            padding: 8px 5px;
        }
        .product-img-small {
            width: 35px;
            height: 35px;
        }
        .order-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="profile-container">
    <h2 style="color: #e74c3c; margin-bottom: 1.5rem;">👤 Личный кабинет</h2>

    <div class="profile-tabs">
        <a href="?tab=data" class="profile-tab <?= $activeTab == 'data' ? 'active' : '' ?>">📋 Мои данные</a>
        <a href="?tab=orders" class="profile-tab <?= $activeTab == 'orders' ? 'active' : '' ?>">📦 Мои заказы</a>
        <a href="?tab=addresses" class="profile-tab <?= $activeTab == 'addresses' ? 'active' : '' ?>">🏠 Мои адреса</a>
    </div>

    <!-- Вкладка: Данные -->
    <?php if ($activeTab == 'data'): ?>
        <div class="form-card">
            <h3 style="margin-bottom: 20px;">Редактирование данных</h3>
            <form method="post">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #aaa;">ФИО</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($client['full_name']) ?>" required>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #aaa;">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($client['email']) ?>" required>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #aaa;">Телефон</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($client['phone']) ?>" required>
                    <small style="color: #666;">Формат: 8XXXXXXXXXX или +7XXXXXXXXXX</small>
                </div>
                <button type="submit" name="update_data">Сохранить изменения</button>
                <?php if ($error): echo "<p style='color:#e74c3c; margin-top: 15px;'>$error</p>"; endif; ?>
                <?php if ($success): echo "<p style='color:#2ecc71; margin-top: 15px;'>$success</p>"; endif; ?>
            </form>
        </div>
    <?php endif; ?>

    <!-- Вкладка: Заказы (с подробным содержимым и фото) -->
    <?php if ($activeTab == 'orders'): ?>
        <h3 style="margin-bottom: 20px;">История заказов</h3>
        <?php
        $stmt = $pdo->prepare("
            SELECT o.*, 
                   (SELECT SUM(oi.quantity * oi.price) FROM orderitem oi WHERE oi.order_id = o.order_id) as order_total
            FROM `order` o 
            WHERE o.email = ? 
            ORDER BY o.order_date DESC
        ");
        $stmt->execute([$client['email']]);
        $orders = $stmt->fetchAll();
        ?>
        
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <p>📭 У вас пока нет заказов</p>
                <a href="/acs/catalog.php" class="btn-primary" style="display: inline-block; margin-top: 15px;">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): 
                // Получаем товары в заказе с фото
                $itemsStmt = $pdo->prepare("
                    SELECT oi.*, p.name, p.photo, p.price as product_price
                    FROM orderitem oi
                    JOIN product p ON oi.product_id = p.product_id
                    WHERE oi.order_id = ?
                ");
                $itemsStmt->execute([$order['order_id']]);
                $items = $itemsStmt->fetchAll();
                
                $orderTotal = $order['order_total'] ?? 0;
                if (!$orderTotal) {
                    foreach ($items as $item) {
                        $orderTotal += $item['quantity'] * $item['price'];
                    }
                }
            ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span class="order-number">Заказ №<?= $order['order_id'] ?></span>
                        <div class="order-date">📅 <?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></div>
                    </div>
                    <div>
                        <span class="order-status status-<?= str_replace(' ', '_', $order['status']) ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </div>
                    <div class="order-total">
                        💰 <?= number_format($orderTotal, 0, ',', ' ') ?> ₽
                    </div>
                </div>
                <div class="order-body">
                    <table class="order-products">
                        <thead>
                            <tr>
                                <th>Фото</th>
                                <th>Товар</th>
                                <th>Кол-во</th>
                                <th>Цена</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['photo'])): ?>
                                        <img src="/acs/img/products/<?= htmlspecialchars($item['photo']) ?>" class="product-img-small" alt="<?= htmlspecialchars($item['name']) ?>">
                                    <?php else: ?>
                                        <div style="width:50px; height:50px; background:#333; border-radius:8px; display:flex; align-items:center; justify-content:center;">📷</div>
                                    <?php endif; ?>
                                </td>
                                <td class="product-name"><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= $item['quantity'] ?> шт.</td>
                                <td><?= number_format($item['price'], 0, ',', ' ') ?> ₽</td>
                                <td><?= number_format($item['quantity'] * $item['price'], 0, ',', ' ') ?> ₽</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="order-summary">
                        <strong>Итого: <?= number_format($orderTotal, 0, ',', ' ') ?> ₽</strong>
                    </div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #333;">
                        <div><strong>🚚 Адрес доставки:</strong> <?= htmlspecialchars($order['address']) ?></div>
                        <?php if (!empty($order['comment'])): ?>
                            <div style="margin-top: 8px;"><strong>💬 Комментарий:</strong> <?= htmlspecialchars($order['comment']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Вкладка: Адреса -->
    <?php if ($activeTab == 'addresses'): ?>
        <div class="form-card">
            <h3 style="margin-bottom: 20px;">Добавить новый адрес</h3>
            <form method="post">
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="new_address" placeholder="Введите адрес" required style="flex: 1;">
                    <button type="submit" name="add_address">➕ Добавить</button>
                </div>
            </form>
        </div>

        <h3 style="margin-bottom: 15px;">Сохранённые адреса</h3>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE client_id = ? ORDER BY id DESC");
        $stmt->execute([$client['client_id']]);
        $addresses = $stmt->fetchAll();
        ?>
        <?php if (empty($addresses)): ?>
            <div class="empty-state" style="padding: 30px;">
                <p>📭 У вас пока нет сохранённых адресов</p>
            </div>
        <?php else: ?>
            <?php foreach ($addresses as $addr): ?>
                <div class="address-item">
                    <span>🏠 <?= htmlspecialchars($addr['address']) ?></span>
                    <a href="?tab=addresses&delete_address=<?= $addr['id'] ?>" onclick="return confirm('Удалить адрес?')" style="color:#e74c3c; text-decoration: none;">🗑️ Удалить</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if ($success): echo "<p style='color:#2ecc71; margin-top: 15px;'>$success</p>"; endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>