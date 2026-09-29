<?php
if (session_status() == PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;
$isAdmin = $user && ($user['Role'] == 1 || $user['Role'] == 2);
$isClient = $user && $user['Role'] == 4;

$cartCount = 0;
if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
?>
<header>
    <div class="container header-container">
        <a href="/index.php" class="logo">СувенирМаркет</a>
        <nav class="main-nav">
            <a href="/index.php?page=catalog">Каталог</a>
            <a href="/index.php?page=cart">Корзина <?= $cartCount > 0 ? '<span class="cart-count">'.$cartCount.'</span>' : '' ?></a>
            <?php if ($user): ?>
                <div class="user-menu">
                    <button class="user-button" onclick="toggleUserMenu()">
                        <?= $isClient ? 'Профиль' : 'Админ' ?> ▼
                    </button>
                    <ul class="user-dropdown" id="userDropdown" hidden>
                        <?php if ($isClient): ?>
                            <li><a href="/profile.php">Профиль</a></li>
                        <?php endif; ?>
                        <?php if ($isAdmin): ?>
                            <li><a href="/admin.php">Админ-панель</a></li>
                        <?php endif; ?>
                        <li><a href="/php/logout.php">Выйти</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="/index.php?page=auth">Войти</a>
                <a href="/index.php?page=register">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<style>
.cart-count {
    background: #ffc107;
    color: #2c3e2f;
    border-radius: 50%;
    padding: 2px 8px;
    font-size: 14px;
    margin-left: 5px;
}
.user-button {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    color: #333;
}
.user-button:hover {
    color: #00acc1;
}
.user-dropdown {
    position: absolute;
    background: #fff;
    border: 1px solid #e0f7fa;
    border-radius: 8px;
    list-style: none;
    padding: 5px 0;
    min-width: 150px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.user-dropdown li a {
    display: block;
    padding: 8px 15px;
    text-decoration: none;
    color: #333;
}
.user-dropdown li a:hover {
    background: #e0f7fa;
    color: #00acc1;
}
</style>

<script>
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
</script>