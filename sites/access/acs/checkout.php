<?php
session_start();
require_once __DIR__ . '/php/config/db_connect.php';

// Проверяем, есть ли корзина
$cart = $_SESSION['cart'] ?? [];

// Если корзина пуста - редирект на корзину
if (empty($cart)) {
    header('Location: /acs/cart.php');
    exit;
}

$cartItems = [];
$totalPrice = 0;

// Получаем товары из корзины
$ids = implode(',', array_keys($cart));
$stmt = $pdo->query("SELECT * FROM product WHERE product_id IN ($ids)");
$products = $stmt->fetchAll();

foreach ($products as $product) {
    $quantity = $cart[$product['product_id']];
    $price = $product['price'];
    $total = $price * $quantity;
    $cartItems[] = [
        'product' => $product,
        'quantity' => $quantity,
        'total' => $total
    ];
    $totalPrice += $total;
}

// Адреса пользователя
$userAddresses = [];
if (isset($_SESSION['client'])) {
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE client_id = ?");
    $stmt->execute([$_SESSION['client']['client_id']]);
    $userAddresses = $stmt->fetchAll();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    // Если выбран новый адрес
    if ($address === 'new') {
        $address = trim($_POST['new_address'] ?? '');
    }

    if ($name === '' || $phone === '' || $email === '' || $address === '') {
        $error = 'Пожалуйста, заполните все обязательные поля.';
    } elseif (!validateName($name)) {
        $error = 'ФИО может содержать только буквы, пробелы и дефис.';
    } else {
        $phoneFormatted = formatPhoneForDB($phone);
        if (!$phoneFormatted) {
            $error = 'Телефон должен содержать 11 цифр (например, 8XXXXXXXXXX или +7XXXXXXXXXX)';
        } else {
            // Проверка остатков
            $stockError = false;
            foreach ($cartItems as $item) {
                $currentStock = $item['product']['stock'];
                $quantity = $item['quantity'];
                
                if ($currentStock < $quantity) {
                    $error = "Недостаточно товара \"{$item['product']['name']}\" на складе. Доступно: {$currentStock} шт.";
                    $stockError = true;
                    break;
                }
            }
            
            if (!$stockError) {
                try {
                    $pdo->beginTransaction();
                    
                    // Создаём заказ
                    $stmt = $pdo->prepare("
                        INSERT INTO `order` (client_name, phone, email, address, comment, status, order_date)
                        VALUES (?, ?, ?, ?, ?, 'Новый', NOW())
                    ");
                    $stmt->execute([$name, $phoneFormatted, $email, $address, $comment]);
                    $orderId = $pdo->lastInsertId();
                    
                    // Добавляем позиции и списываем остатки
                    foreach ($cartItems as $item) {
                        $productId = $item['product']['product_id'];
                        $quantity = $item['quantity'];
                        $price = $item['product']['price'];
                        
                        $stmt = $pdo->prepare("INSERT INTO orderitem (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$orderId, $productId, $quantity, $price]);
                        
                        $stmt = $pdo->prepare("UPDATE product SET stock = stock - ? WHERE product_id = ?");
                        $stmt->execute([$quantity, $productId]);
                    }
                    
                    // Сохраняем новый адрес в профиль, если пользователь авторизован
                    if (isset($_SESSION['client']) && isset($_POST['new_address']) && $address === $_POST['new_address'] && !empty($address)) {
                        $stmt = $pdo->prepare("INSERT INTO user_addresses (client_id, address) VALUES (?, ?)");
                        $stmt->execute([$_SESSION['client']['client_id'], $address]);
                    }
                    
                    $pdo->commit();
                    
                    // Очищаем корзину
                    unset($_SESSION['cart']);
                    
                    // Редирект на страницу успеха
                    header("Location: /acs/order_success.php?id=$orderId");
                    exit;
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Произошла ошибка при оформлении заказа: ' . $e->getMessage();
                    error_log("order error: " . $e->getMessage());
                }
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<h2>📝 Оформление заказа</h2>

<?php if ($error): ?>
    <div style="background:#e74c3c20; border-left:4px solid #e74c3c; padding:12px; margin-bottom:20px; color:#e74c3c; border-radius:8px;">
        ❌ <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="post" action="/acs/checkout.php" style="max-width: 600px;">
    <div style="margin-bottom: 15px;">
        <label style="display:block; margin-bottom:5px; color:#aaa;">ФИО *</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? $_SESSION['client']['full_name'] ?? '') ?>" style="width:100%; padding:10px; background:#2a2a2a; border:none; color:#fff; border-radius:8px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display:block; margin-bottom:5px; color:#aaa;">Телефон *</label>
        <input type="text" name="phone" required value="<?= htmlspecialchars($_POST['phone'] ?? $_SESSION['client']['phone'] ?? '') ?>" style="width:100%; padding:10px; background:#2a2a2a; border:none; color:#fff; border-radius:8px;">
        <small style="color:#666;">Формат: 8XXXXXXXXXX или +7XXXXXXXXXX</small>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display:block; margin-bottom:5px; color:#aaa;">Email *</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? $_SESSION['client']['email'] ?? '') ?>" style="width:100%; padding:10px; background:#2a2a2a; border:none; color:#fff; border-radius:8px;">
    </div>

    <!-- Адрес -->
    <div style="margin-bottom: 15px;">
        <label style="display:block; margin-bottom:5px; color:#aaa;">Адрес доставки *</label>

        <?php if (count($userAddresses) > 0): ?>
            <select name="address" id="address" style="width:100%; padding:10px; margin-bottom:10px; background:#2a2a2a; color:#fff; border:none; border-radius:8px;">
                <option value="">-- Выберите из сохранённых --</option>
                <?php foreach ($userAddresses as $addr): ?>
                    <option value="<?= htmlspecialchars($addr['address']) ?>"><?= htmlspecialchars($addr['address']) ?></option>
                <?php endforeach; ?>
                <option value="new">-- Ввести новый адрес --</option>
            </select>
            <div id="newAddressBlock" style="display: none;">
                <input type="text" name="new_address" placeholder="Введите новый адрес" style="width:100%; padding:10px; background:#2a2a2a; border:none; color:#fff; border-radius:8px;">
            </div>
        <?php else: ?>
            <input type="text" name="new_address" required placeholder="Введите адрес" style="width:100%; padding:10px; background:#2a2a2a; border:none; color:#fff; border-radius:8px;">
        <?php endif; ?>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display:block; margin-bottom:5px; color:#aaa;">Комментарий к заказу</label>
        <textarea name="comment" rows="3" style="width:100%; padding:10px; background:#2a2a2a; border:none; color:#fff; border-radius:8px;"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
    </div>

    <!-- Содержимое корзины -->
    <div style="margin-bottom: 20px; padding:15px; background:#1e1e1e; border-radius:12px;">
        <strong>🛍️ Содержимое заказа:</strong>
        <table style="width:100%; margin-top:10px;">
            <?php foreach ($cartItems as $item): ?>
            <tr>
                <td style="padding:5px 0;"><?= htmlspecialchars($item['product']['name']) ?></td>
                <td style="padding:5px 0; text-align:center;">x<?= $item['quantity'] ?></td>
                <td style="padding:5px 0; text-align:right;"><?= number_format($item['total'], 0, ',', ' ') ?> ₽</td>
            </tr>
            <?php endforeach; ?>
            <tr style="border-top:1px solid #333;">
                <td colspan="2"><strong>Итого:</strong></td>
                <td style="text-align:right"><strong style="color:#e74c3c;"><?= number_format($totalPrice, 0, ',', ' ') ?> ₽</strong></td>
            </tr>
        </table>
    </div>

    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <button type="submit" style="background:#e74c3c; color:#fff; border:none; padding:12px 24px; border-radius:8px; cursor:pointer; font-weight:bold;">✅ Подтвердить заказ</button>
        <a href="/acs/cart.php" style="background:#555; color:#fff; padding:12px 24px; border-radius:8px; text-decoration:none;">← Вернуться в корзину</a>
    </div>
</form>

<script>
    const addressSelect = document.getElementById('address');
    const newAddressBlock = document.getElementById('newAddressBlock');
    if (addressSelect) {
        addressSelect.addEventListener('change', function() {
            if (this.value === 'new') {
                newAddressBlock.style.display = 'block';
            } else {
                newAddressBlock.style.display = 'none';
            }
        });
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>