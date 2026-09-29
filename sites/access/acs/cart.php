<?php
session_start();
require_once __DIR__ . '/php/config/db_connect.php';

// Обновление количества через AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update'])) {
    header('Content-Type: application/json');
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0 && isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = $quantity;
        $_SESSION['cart_count'] = array_sum($_SESSION['cart']);
        
        // Пересчет общей суммы
        $totalAmount = 0;
        foreach ($_SESSION['cart'] as $id => $qty) {
            $stmt = $pdo->prepare("SELECT price FROM product WHERE product_id = ?");
            $stmt->execute([$id]);
            $prod = $stmt->fetch();
            if ($prod) {
                $totalAmount += $prod['price'] * $qty;
            }
        }
        
        echo json_encode(['success' => true, 'totalCount' => $_SESSION['cart_count'], 'totalAmount' => $totalAmount]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка обновления']);
    }
    exit;
}

// Обычное обновление через форму (для надежности)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $product_id => $qty) {
            $product_id = (int)$product_id;
            $qty = (int)$qty;
            if ($qty > 0 && isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] = $qty;
            }
        }
        $_SESSION['cart_count'] = array_sum($_SESSION['cart']);
    }
    header('Location: /acs/cart.php');
    exit;
}

// Удаление товара
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
        $_SESSION['cart_count'] = array_sum($_SESSION['cart']);
    }
    header('Location: /acs/cart.php');
    exit;
}

include __DIR__ . '/includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$cartItems = [];
$total = 0;

if (!empty($cart)) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM product WHERE product_id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    
    foreach ($products as $product) {
        $qty = $cart[$product['product_id']];
        $sum = $product['price'] * $qty;
        $total += $sum;
        $cartItems[] = [
            'product' => $product,
            'quantity' => $qty,
            'total' => $sum
        ];
    }
}
?>

<div style="max-width: 1000px; margin: 0 auto; padding: 20px;">
    <h2>🛒 Корзина</h2>
    
    <?php if (empty($cartItems)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #1e1e1e; border-radius: 12px;">
            <p style="font-size: 1.2rem; color: #aaa;">Корзина пуста</p>
            <a href="/acs/catalog.php" style="display: inline-block; margin-top: 15px; background: #e74c3c; color: #fff; padding: 12px 30px; border-radius: 8px; text-decoration: none;">Перейти в каталог</a>
        </div>
    <?php else: ?>
        <form method="post" id="cartForm">
            <table style="width: 100%; border-collapse: collapse; background: #1e1e1e; border-radius: 12px; overflow: hidden;">
                <thead>
                    <tr style="background: #2a2a2a;">
                        <th style="padding: 15px; text-align: left;">Товар</th>
                        <th style="padding: 15px; text-align: left;">Цена</th>
                        <th style="padding: 15px; text-align: left;">Количество</th>
                        <th style="padding: 15px; text-align: left;">Сумма</th>
                        <th style="padding: 15px; text-align: center;">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $index => $item): 
                        $product = $item['product'];
                        $qty = $item['quantity'];
                    ?>
                    <tr data-product-id="<?= $product['product_id'] ?>">
                        <td style="padding: 15px; border-bottom: 1px solid #333;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <img src="/acs/img/products/<?= htmlspecialchars($product['photo']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <span><?= htmlspecialchars($product['name']) ?></span>
                            </div>
                        </td>
                        <td style="padding: 15px; border-bottom: 1px solid #333;" class="price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</td>
                        <td style="padding: 15px; border-bottom: 1px solid #333;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <button type="button" class="btn-quantity minus" data-product-id="<?= $product['product_id'] ?>" style="background: #333; border: none; color: #fff; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; font-size: 1.2rem;">−</button>
                                <span class="quantity-value" style="min-width: 30px; text-align: center; font-size: 1.1rem; font-weight: 600;"><?= $qty ?></span>
                                <button type="button" class="btn-quantity plus" data-product-id="<?= $product['product_id'] ?>" style="background: #333; border: none; color: #fff; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; font-size: 1.2rem;">+</button>
                            </div>
                            <!-- Скрытое поле для формы -->
                            <input type="hidden" name="quantity[<?= $product['product_id'] ?>]" value="<?= $qty ?>">
                        </td>
                        <td style="padding: 15px; border-bottom: 1px solid #333; font-weight: bold;" class="item-total"><?= number_format($item['total'], 0, ',', ' ') ?> ₽</td>
                        <td style="padding: 15px; border-bottom: 1px solid #333; text-align: center;">
                            <a href="?remove=<?= $product['product_id'] ?>" onclick="return confirm('Удалить товар?')" style="color: #e74c3c; text-decoration: none; font-size: 1.3rem;">✕</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #2a2a2a;">
                        <td colspan="3" style="padding: 15px; text-align: right; font-size: 1.1rem;"><strong>Итого:</strong></td>
                        <td colspan="2" style="padding: 15px; font-size: 1.3rem; color: #e74c3c;" id="cartTotal"><strong><?= number_format($total, 0, ',', ' ') ?> ₽</strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <div style="margin-top: 20px; display: flex; gap: 15px; flex-wrap: wrap;">
                <button type="submit" name="update_cart" style="background: #555; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-size: 1rem;">
                    🔄 Обновить
                </button>
                <a href="/acs/catalog.php" style="background: #333; color: #fff; padding: 12px 25px; border-radius: 8px; text-decoration: none;">Продолжить покупки</a>
                <a href="/acs/checkout.php" style="background: #e74c3c; color: #fff; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: bold;">✅ Оформить заказ</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cart.js загружен!');
    
    // Обработчики кнопок + и -
    document.querySelectorAll('.btn-quantity').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const isPlus = this.classList.contains('plus');
            const row = this.closest('tr');
            const quantityElement = row.querySelector('.quantity-value');
            const hiddenInput = row.querySelector('input[type="hidden"]');
            const itemTotalElement = row.querySelector('.item-total');
            const priceElement = row.querySelector('.price');
            
            let quantity = parseInt(quantityElement.textContent);
            console.log('Текущее количество:', quantity);
            
            if (isPlus) {
                quantity++;
            } else {
                if (quantity > 1) {
                    quantity--;
                } else {
                    console.log('Количество не может быть меньше 1');
                    return;
                }
            }
            
            console.log('Новое количество:', quantity);
            
            // Сохраняем старую кнопку для восстановления
            const oldText = this.textContent;
            this.textContent = '⏳';
            this.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('ajax_update', '1');
                formData.append('product_id', productId);
                formData.append('quantity', quantity);
                
                const response = await fetch('/acs/cart.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Статус ответа:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Ответ сервера:', result);
                
                if (result.success) {
                    // Обновляем количество
                    quantityElement.textContent = quantity;
                    if (hiddenInput) {
                        hiddenInput.value = quantity;
                    }
                    
                    // Обновляем итоговую сумму для товара
                    const priceText = priceElement.textContent.replace(/[^\d,]/g, '').replace(',', '.');
                    const price = parseFloat(priceText);
                    const newTotal = price * quantity;
                    itemTotalElement.textContent = newTotal.toLocaleString('ru-RU', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }) + ' ₽';
                    
                    // Обновляем общую сумму корзины
                    const cartTotalElement = document.getElementById('cartTotal');
                    if (cartTotalElement) {
                        cartTotalElement.innerHTML = '<strong>' + result.totalAmount.toLocaleString('ru-RU', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        }) + ' ₽</strong>';
                    }
                    
                    // Обновляем счетчик в хедере
                    const cartCounter = document.getElementById('cartCounter');
                    if (cartCounter) {
                        cartCounter.textContent = result.totalCount;
                    }
                } else {
                    alert(result.message || 'Ошибка при обновлении количества');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Ошибка: ' + error.message + '. Попробуйте обновить страницу.');
            } finally {
                this.textContent = oldText;
                this.disabled = false;
            }
        });
    });
    
    // Удаление товара
    document.querySelectorAll('.btn-remove-from-cart').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Вы уверены, что хотите удалить товар из корзины?')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>