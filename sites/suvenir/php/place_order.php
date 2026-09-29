<?php
require_once 'db.php';
require_once 'helpers.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 4) {
    exit('Для оформления заказа необходимо войти как клиент');
}

$user = $_SESSION['user'];

// Получаем ID клиента
$stmt = $mysqli->prepare("SELECT ID_Client FROM client WHERE ID_User = ?");
$stmt->bind_param("i", $user['ID_User']);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$client) exit('Клиент не найден');
$client_id = $client['ID_Client'];

// Получаем адреса клиента
$addresses = [];
$stmt = $mysqli->prepare("SELECT * FROM user_addresses WHERE client_id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) exit('Корзина пуста');

$ids = implode(',', array_keys($cart));
$res = $mysqli->query("SELECT ID_Products, Stock_Quantity, Price, Souvenir_Name FROM products WHERE ID_Products IN ($ids)");
$products = [];
while ($row = $res->fetch_assoc()) {
    $products[$row['ID_Products']] = $row;
}

// Проверка наличия
foreach ($cart as $id => $qty) {
    if (!isset($products[$id])) {
        exit("Товар с ID $id не найден");
    }
    if ($products[$id]['Stock_Quantity'] < $qty) {
        exit("Товара '{$products[$id]['Souvenir_Name']}' недостаточно на складе");
    }
}

// Обработка POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['address'] === 'new') {
        $address = trim($_POST['new_address']);
    } else {
        $address = trim($_POST['address']);
    }
    if (!$address) exit('Адрес доставки обязателен');

    $mysqli->begin_transaction();
    try {
        $date = date('Y-m-d');
        $stmt = $mysqli->prepare("INSERT INTO sale (Sale_Date, Status, ID_Client) VALUES (?, 1, ?)");
        $stmt->bind_param("si", $date, $client_id);
        $stmt->execute();
        $order_id = $mysqli->insert_id;
        $stmt->close();

        foreach ($cart as $id => $qty) {
            $price = $products[$id]['Price'];
            $stmt = $mysqli->prepare("INSERT INTO sale_contents (ID_Sale, ID_Product, Quantity, Price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $order_id, $id, $qty, $price);
            $stmt->execute();
            $stmt->close();

            $newQty = $products[$id]['Stock_Quantity'] - $qty;
            $stmt = $mysqli->prepare("UPDATE products SET Stock_Quantity = ? WHERE ID_Products = ?");
            $stmt->bind_param("ii", $newQty, $id);
            $stmt->execute();
            $stmt->close();
        }

        // Сохраняем новый адрес
        if ($_POST['address'] === 'new' && !empty($address)) {
            $stmt = $mysqli->prepare("INSERT INTO user_addresses (client_id, address) VALUES (?, ?)");
            $stmt->bind_param("is", $client_id, $address);
            $stmt->execute();
            $stmt->close();
        }

        unset($_SESSION['cart']);
        $mysqli->commit();
        echo 'ok';
    } catch (Exception $e) {
        $mysqli->rollback();
        echo 'Ошибка при оформлении заказа: ' . $e->getMessage();
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .order-container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .order-form { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .order-form input, .order-form select { width: 100%; padding: 10px; margin: 8px 0 15px; border: 1px solid #ddd; border-radius: 8px; }
        .order-form button { background: #ff6b35; color: #fff; border: none; padding: 12px 24px; border-radius: 30px; cursor: pointer; width: 100%; font-size: 1rem; }
        .cart-items { background: #f9f9f9; padding: 15px; border-radius: 12px; margin-bottom: 20px; }
        .cart-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .total { font-size: 1.2rem; font-weight: bold; text-align: right; margin-top: 15px; padding-top: 10px; border-top: 2px solid #ff6b35; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="order-container">
    <h2>📝 Оформление заказа</h2>
    
    <div class="cart-items">
        <h3>🛍️ Ваш заказ</h3>
        <?php 
        $total = 0;
        foreach ($cart as $id => $qty): 
            $prod = $products[$id];
            $sum = $prod['Price'] * $qty;
            $total += $sum;
        ?>
        <div class="cart-item">
            <span><?= htmlspecialchars($prod['Souvenir_Name']) ?> x<?= $qty ?></span>
            <span><?= number_format($sum, 0, ',', ' ') ?> ₽</span>
        </div>
        <?php endforeach; ?>
        <div class="total">Итого: <?= number_format($total, 0, ',', ' ') ?> ₽</div>
    </div>
    
    <form id="orderForm" class="order-form">
        <div style="margin-bottom: 15px;">
            <label>Адрес доставки *</label>
            <?php if (count($addresses) > 0): ?>
                <select name="address" id="addressSelect" required>
                    <option value="">-- Выберите из сохранённых --</option>
                    <?php foreach ($addresses as $addr): ?>
                        <option value="<?= htmlspecialchars($addr['address']) ?>"><?= htmlspecialchars($addr['address']) ?></option>
                    <?php endforeach; ?>
                    <option value="new">-- Ввести новый адрес --</option>
                </select>
                <div id="newAddressBlock" style="display: none; margin-top: 10px;">
                    <input type="text" name="new_address" placeholder="Введите новый адрес">
                </div>
            <?php else: ?>
                <input type="text" name="new_address" required placeholder="Введите адрес доставки">
                <input type="hidden" name="address" value="new">
            <?php endif; ?>
        </div>
        <button type="button" onclick="submitOrder()">✅ Подтвердить заказ</button>
    </form>
</div>

<script>
    const select = document.getElementById('addressSelect');
    const newBlock = document.getElementById('newAddressBlock');
    if (select) {
        select.addEventListener('change', function() {
            if (this.value === 'new') {
                newBlock.style.display = 'block';
            } else {
                newBlock.style.display = 'none';
            }
        });
    }

    function submitOrder() {
        const form = document.getElementById('orderForm');
        const formData = new FormData(form);
        fetch('/php/place_order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'ok') {
                alert('✅ Заказ успешно оформлен!');
                window.location.href = '/index.php';
            } else {
                alert('❌ Ошибка: ' + data);
            }
        });
    }
</script>

<?php include 'footer.php'; ?>
</body>
</html>