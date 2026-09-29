document.addEventListener('DOMContentLoaded', () => {
    const cartCounter = document.getElementById('cartCounter');

    document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
        btn.addEventListener('click', async (event) => {
            event.preventDefault();

            const productId = btn.dataset.productId;
            btn.disabled = true;
            btn.textContent = 'Добавляем...';

            try {
                const response = await fetch('/acs/php/cart/add.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ product_id: productId })
                });

                const result = await response.json();

                if (result.success) {
                    if (cartCounter) {
                        cartCounter.textContent = result.totalCount;
                    }
                    btn.textContent = 'Добавлено';
                    setTimeout(() => {
                        btn.textContent = 'Добавить в корзину';
                        btn.disabled = false;
                    }, 1500);
                } else {
                    alert(result.message || 'Ошибка при добавлении товара');
                    btn.textContent = 'Добавить в корзину';
                    btn.disabled = false;
                }
            } catch {
                alert('Ошибка сети');
                btn.textContent = 'Добавить в корзину';
                btn.disabled = false;
            }
        });
    });
});
