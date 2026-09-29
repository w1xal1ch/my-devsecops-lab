<?php
require_once 'php/db.php';
require_once 'php/helpers.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION['user'];

$stmt = $mysqli->prepare("SELECT * FROM client WHERE ID_User = ?");
$stmt->bind_param("i", $user['ID_User']);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
$stmt->close();

$activeTab = $_GET['tab'] ?? 'data';
$error = '';
$success = '';

// Редактирование данных
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_data'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    if (!validateName($full_name)) {
        $error = 'ФИО может содержать только буквы, пробелы и дефис';
    } else {
        $phoneFormatted = formatPhoneForDB($phone);
        if (!$phoneFormatted) {
            $error = 'Телефон должен содержать 11 цифр';
        } else {
            $stmt = $mysqli->prepare("UPDATE client SET Full_Name=?, Phone_Number=?, Email=? WHERE ID_User=?");
            $stmt->bind_param("sssi", $full_name, $phoneFormatted, $email, $user['ID_User']);
            $stmt->execute();
            $success = 'Данные обновлены';
        }
    }
}

// Смена пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    $stmt = $mysqli->prepare("SELECT Password FROM user WHERE ID_User=?");
    $stmt->bind_param("i", $user['ID_User']);
    $stmt->execute();
    $hash = $stmt->get_result()->fetch_assoc()['Password'];
    
    if (!password_verify($old, $hash)) {
        $error = 'Неверный текущий пароль';
    } elseif ($new !== $confirm) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($new) < 6) {
        $error = 'Пароль не менее 6 символов';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE user SET Password=? WHERE ID_User=?");
        $stmt->bind_param("si", $newHash, $user['ID_User']);
        $stmt->execute();
        $success = 'Пароль изменён';
    }
}

// Добавление адреса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $addr = trim($_POST['new_address']);
    if ($addr) {
        $stmt = $mysqli->prepare("INSERT INTO user_addresses (client_id, address) VALUES (?, ?)");
        $stmt->bind_param("is", $client['ID_Client'], $addr);
        $stmt->execute();
        $success = 'Адрес добавлен';
    }
}

// Удаление адреса
if (isset($_GET['delete_address'])) {
    $id = (int)$_GET['delete_address'];
    $stmt = $mysqli->prepare("DELETE FROM user_addresses WHERE id=? AND client_id=?");
    $stmt->bind_param("ii", $id, $client['ID_Client']);
    $stmt->execute();
    header('Location: profile.php?tab=addresses');
    exit;
}

// Получаем адреса
$addresses = [];
$stmt = $mysqli->prepare("SELECT * FROM user_addresses WHERE client_id=?");
$stmt->bind_param("i", $client['ID_Client']);
$stmt->execute();
$addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Получаем заказы с фото
$orders = [];
$stmt = $mysqli->prepare("
    SELECT s.ID_S, s.Sale_Date, s.Status,
           (SELECT SUM(sc.Quantity * sc.Price) FROM sale_contents sc WHERE sc.ID_Sale = s.ID_S) as total
    FROM sale s WHERE s.ID_Client = ? ORDER BY s.Sale_Date DESC
");
$stmt->bind_param("i", $client['ID_Client']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль | СувенирМаркет</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .profile-container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .profile-tabs { display: flex; gap: 20px; border-bottom: 2px solid #e0f7fa; margin-bottom: 30px; flex-wrap: wrap; }
        .tab { padding: 10px 0; text-decoration: none; color: #666; border-bottom: 3px solid transparent; }
        .tab.active { color: #00acc1; border-bottom-color: #ffc107; }
        .form-card { background: #fff; border-radius: 20px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .form-card input { width: 100%; padding: 10px; margin: 8px 0 15px; border: 1px solid #ddd; border-radius: 8px; }
        .form-card button { background: #00acc1; color: #fff; border: none; padding: 10px 20px; border-radius: 30px; cursor: pointer; }
        .form-card button:hover { background: #008c9e; }
        .address-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #f9f9f9; border-radius: 10px; margin-bottom: 10px; }
        .order-card { background: #fff; border-radius: 16px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .order-header { background: #e0f7fa; padding: 15px 20px; display: flex; justify-content: space-between; flex-wrap: wrap; align-items: center; }
        .order-number { font-size: 1.1rem; font-weight: bold; color: #00acc1; }
        .order-status { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; }
        .status-1 { background: #3498db; color: #fff; }
        .status-2 { background: #2ecc71; color: #fff; }
        .order-body { padding: 20px; }
        .order-products { width: 100%; border-collapse: collapse; }
        .order-products th, .order-products td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .order-total { text-align: right; font-weight: bold; margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee; }
        .btn-delete { background: #e74c3c; color: #fff; padding: 5px 12px; border-radius: 20px; text-decoration: none; font-size: 0.8rem; }
        .btn-delete:hover { background: #c0392b; }
    </style>
</head>
<body>
<?php include 'php/header.php'; ?>

<div class="profile-container">
    <h1 style="margin-bottom: 20px; color: #00acc1;">👤 Личный кабинет</h1>
    
    <div class="profile-tabs">
        <a href="?tab=data" class="tab <?= $activeTab=='data'?'active':'' ?>">📋 Мои данные</a>
        <a href="?tab=orders" class="tab <?= $activeTab=='orders'?'active':'' ?>">📦 Заказы</a>
        <a href="?tab=addresses" class="tab <?= $activeTab=='addresses'?'active':'' ?>">🏠 Адреса</a>
    </div>

    <?php if ($error): ?>
        <div class="form-card" style="background:#f8d7da; color:#721c24;"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="form-card" style="background:#d4edda; color:#155724;"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($activeTab == 'data'): ?>
        <div class="form-card">
            <h3 style="color: #00acc1;">Редактирование данных</h3>
            <form method="post">
                <label>ФИО</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($client['Full_Name']) ?>" required>
                <label>Телефон</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($client['Phone_Number']) ?>" required>
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($client['Email']) ?>" required>
                <button type="submit" name="update_data">Сохранить</button>
            </form>
        </div>
        <div class="form-card">
            <h3 style="color: #00acc1;">Смена пароля</h3>
            <form method="post">
                <label>Текущий пароль</label>
                <input type="password" name="old_password" required>
                <label>Новый пароль</label>
                <input type="password" name="new_password" required>
                <label>Подтверждение</label>
                <input type="password" name="confirm_password" required>
                <button type="submit" name="change_password">Изменить пароль</button>
            </form>
        </div>

    <?php elseif ($activeTab == 'orders'): ?>
        <h3 style="color: #00acc1;">История заказов</h3>
        <?php if (empty($orders)): ?>
            <div class="form-card" style="text-align:center;">У вас пока нет заказов</div>
        <?php else: ?>
            <?php foreach ($orders as $order): 
                $itemsStmt = $mysqli->prepare("
                    SELECT sc.*, p.Souvenir_Name, p.Photo 
                    FROM sale_contents sc 
                    JOIN products p ON sc.ID_Product = p.ID_Products 
                    WHERE sc.ID_Sale = ?
                ");
                $itemsStmt->bind_param("i", $order['ID_S']);
                $itemsStmt->execute();
                $items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span class="order-number">Заказ №<?= $order['ID_S'] ?></span>
                        <div style="font-size:0.8rem; color:#888;">📅 <?= date('d.m.Y', strtotime($order['Sale_Date'])) ?></div>
                    </div>
                    <span class="order-status status-<?= $order['Status'] ?>">
                        <?= $order['Status'] == 1 ? '🟡 В обработке' : '✅ Выполнен' ?>
                    </span>
                    <div class="order-total" style="margin:0;">💰 <?= number_format($order['total'], 0, ',', ' ') ?> ₽</div>
                </div>
                <div class="order-body">
                    <table class="order-products">
                        <thead><tr><th>Фото</th><th>Товар</th><th>Кол-во</th><th>Цена</th><th>Сумма</th></tr></thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><img src="/img/<?= $item['Photo'] ?: 'no-image.jpg' ?>" class="product-img"></td>
                            <td><?= htmlspecialchars($item['Souvenir_Name']) ?></td>
                            <td><?= $item['Quantity'] ?> шт.</td>
                            <td><?= number_format($item['Price'], 0, ',', ' ') ?> ₽</td>
                            <td><?= number_format($item['Quantity'] * $item['Price'], 0, ',', ' ') ?> ₽</td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="order-total">
                        <strong>Итого: <?= number_format($order['total'], 0, ',', ' ') ?> ₽</strong>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

    <?php elseif ($activeTab == 'addresses'): ?>
        <div class="form-card">
            <h3 style="color: #00acc1;">Добавить новый адрес</h3>
            <form method="post" style="display:flex; gap:10px;">
                <input type="text" name="new_address" placeholder="Введите адрес" required style="flex:1;">
                <button type="submit" name="add_address">➕ Добавить</button>
            </form>
        </div>
        
        <h3 style="color: #00acc1;">Мои адреса</h3>
        <?php foreach ($addresses as $addr): ?>
            <div class="address-item">
                <span>🏠 <?= htmlspecialchars($addr['address']) ?></span>
                <a href="?tab=addresses&delete_address=<?= $addr['id'] ?>" class="btn-delete" onclick="return confirm('Удалить адрес?')">🗑️ Удалить</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'php/footer.php'; ?>
</body>
</html>