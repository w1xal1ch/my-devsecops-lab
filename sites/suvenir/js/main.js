// ========== ЗАКРЫТИЕ МОДАЛЬНЫХ ОКОН ==========
function closeModals() {
    const bg = document.getElementById('modal-bg');
    const product = document.getElementById('modal-product');
    if (bg) bg.style.display = 'none';
    if (product) product.style.display = 'none';
}

// Клик по подложке закрывает модалку
document.addEventListener('click', function(e) {
    if (e.target.id === 'modal-bg') {
        closeModals();
    }
});

// Закрытие по ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModals();
    }
});

// ========== КАРТОЧКА ТОВАРА ==========
function showProduct(id) {
    const bg = document.getElementById('modal-bg');
    const modal = document.getElementById('modal-product');
    
    if (!bg || !modal) {
        alert('Ошибка: модальные окна не настроены');
        return;
    }
    
    bg.style.display = 'block';
    modal.style.display = 'block';
    modal.innerHTML = '<div style="text-align:center;padding:40px;">⏳ Загрузка товара...</div>';
    
    fetch('/php/product_modal.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            modal.innerHTML = html;
            
            const reviewForm = document.getElementById('reviewForm');
            if (reviewForm) {
                reviewForm.onsubmit = function(e) {
                    e.preventDefault();
                    fetch('/php/add_review.php', {
                        method: 'POST',
                        body: new FormData(this)
                    }).then(r => r.text()).then(res => {
                        if (res.trim() === 'ok') {
                            showProduct(id);
                        } else {
                            const errorDiv = document.getElementById('reviewError');
                            if (errorDiv) errorDiv.innerText = res;
                            else alert('Ошибка: ' + res);
                        }
                    });
                };
            }
        })
        .catch(error => {
            modal.innerHTML = '<div style="text-align:center;padding:40px;color:red;">❌ Ошибка загрузки товара</div>';
            console.error(error);
        });
}

// ========== МЕНЮ ПОЛЬЗОВАТЕЛЯ ==========
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        if (dropdown.hasAttribute('hidden')) dropdown.removeAttribute('hidden');
        else dropdown.setAttribute('hidden', '');
    }
}

document.addEventListener('click', function(event) {
    const menu = document.querySelector('.user-menu');
    const dropdown = document.getElementById('userDropdown');
    if (menu && dropdown && !menu.contains(event.target)) {
        dropdown.setAttribute('hidden', '');
    }
});

// ========== ДОБАВЛЕНИЕ В КОРЗИНУ (редирект на страницу корзины) ==========
function addToCart(productId) {
    fetch('/php/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    }).then(() => {
        window.location.href = '/index.php?page=cart';
    });
}