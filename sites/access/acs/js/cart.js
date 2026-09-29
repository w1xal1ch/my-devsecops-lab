document.addEventListener('DOMContentLoaded', () => {
    console.log('Cart.js загружен!');
    
    // Обновление количества товара
    document.querySelectorAll('.btn-quantity').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Кнопка нажата:', this.className);
            
            const productId = this.dataset.productId;
            const isPlus = this.classList.contains('plus');
            const row = this.closest('tr');
            const quantityElement = row.querySelector('.quantity-value');
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
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                console.log('CSRF Token найден:', csrfToken ? 'да' : 'нет');
                
                const response = await fetch('/acs/php/cart/update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    })
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
                        cartTotalElement.textContent = result.totalAmount.toLocaleString('ru-RU', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        }) + ' ₽';
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

function closeModals() {
    const bg = document.getElementById('modal-bg');
    const cart = document.getElementById('modal-cart');
    const product = document.getElementById('modal-product');
    if (bg) bg.style.display = 'none';
    if (cart) cart.style.display = 'none';
    if (product) product.style.display = 'none';
}