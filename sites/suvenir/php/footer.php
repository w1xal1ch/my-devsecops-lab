<footer>
    <div class="container footer-container">
        <div class="footer-brand">
            <span class="footer-logo">СувенирМаркет</span>
            <div class="footer-desc">
                Ваш онлайн-магазин оригинальных сувениров и подарков из России.<br>
                Доставка по всей стране. Гарантия качества!
            </div>
        </div>
        <div class="footer-links">
            <b>Навигация</b>
            <a href="/index.php">Каталог</a>
            <a href="/profile.php">Профиль</a>
            <a href="/index.php?page=cart">Корзина</a>
        </div>
        <div class="footer-contacts">
            <b>Контакты</b>
            <div>Телефон: <a href="tel:+78001234567">8 (800) 123-45-67</a></div>
            <div>Email: <a href="mailto:info@suvenir-market.ru">info@suvenir-market.ru</a></div>
            <div class="footer-social">
                <a href="#" title="ВКонтакте"><img src="/img/vk.png" alt="VK" width="24"></a>
                <a href="#" title="Telegram"><img src="/img/tg.png" alt="TG" width="24"></a>
            </div>
        </div>
    </div>
    <div class="footer-copy">
        © <?= date('Y') ?> СувенирМаркет. Все права защищены.
    </div>
</footer>

<style>
footer {
    background: #2c3e50;
    color: white;
    padding: 40px 0 0;
    margin-top: 50px;
}
.footer-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}
.footer-logo {
    font-size: 24px;
    font-weight: bold;
    display: block;
    margin-bottom: 15px;
}
.footer-desc {
    line-height: 1.6;
    color: #ecf0f1;
}
.footer-links {
    display: flex;
    flex-direction: column;
}
.footer-links a {
    color: #ecf0f1;
    text-decoration: none;
    margin-bottom: 8px;
    transition: color 0.3s;
}
.footer-links a:hover {
    color: #ff6b35;
}
.footer-contacts div {
    margin-bottom: 10px;
}
.footer-contacts a {
    color: #ff6b35;
    text-decoration: none;
}
.footer-social {
    display: flex;
    gap: 15px;
    margin-top: 15px;
}
.footer-copy {
    text-align: center;
    padding: 20px 0;
    margin-top: 30px;
    border-top: 1px solid #34495e;
    color: #bdc3c7;
}
</style>