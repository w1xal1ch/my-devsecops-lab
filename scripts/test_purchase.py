"""
End-to-End тест интернет-магазина Luxe Accessories (/acs)
Сценарий: авторизация → каталог → добавление товаров → корзина → оформление заказа
"""

import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException


# ============================================================
# КОНФИГУРАЦИЯ
# ============================================================
BASE_URL = "http://localhost/acs"

TEST_USER = {
    "email_or_phone": "tolik-_-tester@testerov.ru",  # Email или телефон
    "full_name": "Тестеров Анатолий"
}

# Товары: [поисковый_запрос, название_для_проверки]
TEST_PRODUCTS = [
    {"search": "Кепка", "name": "Спортивная кепка", "expected_price": 3500},
    {"search": "Кроссовки", "name": "Кроссовки беговые", "expected_price": 25000}
]


# ============================================================
# ДРАЙВЕР
# ============================================================
def create_driver():
    """Создаёт экземпляр веб-драйвера Chromium"""
    options = Options()
    options.binary_location = "/usr/bin/chromium"
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--start-maximized")
    # options.add_argument("--headless=new")  # Раскомментируй для скрытого режима
    return webdriver.Chrome(options=options)


# ============================================================
# ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
# ============================================================
def add_product_to_cart(driver, wait, product):
    """Добавляет товар в корзину через каталог"""
    print(f"\n→ Поиск: '{product['search']}'")
    
    # Загружаем каталог
    driver.get(f"{BASE_URL}/catalog.php")
    wait.until(
        EC.presence_of_element_located((By.NAME, "search"))
    )
    
    # Поиск товара
    search_input = driver.find_element(By.NAME, "search")
    search_input.clear()
    search_input.send_keys(product['search'])
    
    # Нажимаем кнопку "Применить" (Фильтровать)
    try:
        filter_btn = driver.find_element(
            By.XPATH, "//button[contains(text(), 'Применить')]"
        )
        filter_btn.click()
    except:
        driver.find_element(
            By.XPATH, "//button[@type='submit'][contains(text(), 'Применить')]"
        ).click()
    
    time.sleep(2)
    
    # Ждём загрузки результатов
    wait.until(
        EC.presence_of_element_located((By.CSS_SELECTOR, ".product-grid"))
    )
    
    # Ищем кнопку "В корзину" в найденных товарах
    buttons = driver.find_elements(By.CSS_SELECTOR, "button.btn-add-to-cart")
    
    if buttons:
        buttons[0].click()
        time.sleep(1.5)
        print(f"  ✓ '{product['name']}' добавлен в корзину")
    else:
        # Пробуем через форму внутри карточки
        try:
            form = driver.find_element(By.CSS_SELECTOR, ".product-card form")
            form.find_element(By.TAG_NAME, "button").click()
            time.sleep(1.5)
            print(f"  ✓ '{product['name']}' добавлен в корзину")
        except Exception as e:
            raise Exception(f"Не удалось добавить товар '{product['search']}': {e}")


def is_logged_in(driver):
    """Проверяет, авторизован ли пользователь"""
    try:
        user_menu = driver.find_element(By.CSS_SELECTOR, ".user-menu span")
        return "▼" in user_menu.text or user_menu.is_displayed()
    except:
        return False


def logout_if_logged_in(driver, wait):
    """Выходит из аккаунта, если авторизован"""
    try:
        driver.get(f"{BASE_URL}/")
        time.sleep(1)
        
        # Проверяем, есть ли кнопка выхода
        logout_link = driver.find_elements(By.XPATH, "//a[contains(text(), 'Выйти')]")
        if logout_link:
            logout_link[0].click()
            time.sleep(1)
            print("✓ Выполнен выход из аккаунта")
    except:
        pass


# ============================================================
# ОСНОВНОЙ ТЕСТ
# ============================================================
def test_full_purchase_flow():
    driver = create_driver()
    wait = WebDriverWait(driver, 10)

    try:
        # ------------------------------------------------
        # ШАГ 1: АВТОРИЗАЦИЯ
        # ------------------------------------------------
        print("\n" + "="*60)
        print("ШАГ 1: Авторизация")
        print("="*60)

        # Выходим, если был авторизован
        logout_if_logged_in(driver, wait)

        driver.get(f"{BASE_URL}/login.php")
        print("✓ Страница входа загружена")

        # Ждём форму входа
        login_input = wait.until(
            EC.presence_of_element_located((By.NAME, "email_or_phone"))
        )
        login_input.clear()
        login_input.send_keys(TEST_USER["email_or_phone"])
        print(f"✓ Введён логин: {TEST_USER['email_or_phone']}")

        # Кнопка входа
        login_btn = driver.find_element(
            By.XPATH, "//button[contains(text(), 'Войти')]"
        )
        login_btn.click()
        time.sleep(2)
        print("✓ Кнопка 'Войти' нажата")

        # Проверяем успешный вход (редирект на главную или появление профиля)
        if "login" in driver.current_url:
            # Если остались на странице входа - ошибка
            error_elem = driver.find_elements(By.CSS_SELECTOR, ".error, div[style*='color:#e74c3c']")
            if error_elem:
                print(f"❌ Ошибка входа: {error_elem[0].text}")
                raise Exception("Не удалось войти: " + error_elem[0].text)
        else:
            print("✓ Вход выполнен успешно")

        # ------------------------------------------------
        # ШАГ 2: ДОБАВЛЕНИЕ ТОВАРОВ
        # ------------------------------------------------
        print("\n" + "="*60)
        print("ШАГ 2: Добавление товаров")
        print("="*60)

        for product in TEST_PRODUCTS:
            add_product_to_cart(driver, wait, product)

        # ------------------------------------------------
        # ШАГ 3: ПРОВЕРКА КОРЗИНЫ
        # ------------------------------------------------
        print("\n" + "="*60)
        print("ШАГ 3: Проверка корзины")
        print("="*60)

        driver.get(f"{BASE_URL}/cart.php")
        time.sleep(2)
        
        # Ждём загрузки корзины
        wait.until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".cart-container, .empty-cart"))
        )
        
        # Проверяем, что корзина не пуста
        page_text = driver.find_element(By.TAG_NAME, "body").text
        
        if "пуста" in page_text.lower():
            raise Exception("Корзина пуста! Товары не добавились.")
        
        print("✓ Корзина загружена, содержит товары")

        # Проверяем каждый товар
        cart_total = 0
        for product in TEST_PRODUCTS:
            try:
                # Ищем товар по имени
                product_row = driver.find_element(
                    By.XPATH, f"//td[contains(text(), '{product['name']}')]"
                )
                print(f"✓ '{product['name']}' найден в корзине")
                cart_total += product['expected_price']
            except NoSuchElementException:
                # Пробуем найти по поисковому запросу
                try:
                    driver.find_element(
                        By.XPATH, f"//td[contains(text(), '{product['search']}')]"
                    )
                    print(f"✓ '{product['search']}' найден в корзине")
                    cart_total += product['expected_price']
                except:
                    print(f"⚠️ Товар '{product['name']}' не найден в корзине")

        # Получаем итоговую сумму из корзины
        try:
            total_element = driver.find_element(
                By.XPATH, "//tfoot//td[contains(@class, 'total-amount')]"
            )
            total_text = total_element.text
            print(f"✓ Итого по корзине: {total_text}")
        except:
            # Альтернативный селектор
            total_element = driver.find_element(
                By.XPATH, "//tfoot//td[last()]"
            )
            total_text = total_element.text
            print(f"✓ Итого по корзине: {total_text}")

        # ------------------------------------------------
        # ШАГ 4: ОФОРМЛЕНИЕ ЗАКАЗА
        # ------------------------------------------------
        print("\n" + "="*60)
        print("ШАГ 4: Оформление заказа")
        print("="*60)

        # Кнопка "Оформить заказ"
        try:
            checkout_btn = wait.until(
                EC.element_to_be_clickable((By.CLASS_NAME, "btn-checkout"))
            )
        except TimeoutException:
            checkout_btn = driver.find_element(
                By.XPATH, "//a[contains(text(), 'Оформить заказ')]"
            )
        
        checkout_btn.click()
        time.sleep(2)
        print("✓ Переход на страницу оформления")

        # Ждём страницу оформления
        wait.until(
            EC.presence_of_element_located(
                (By.XPATH, "//h2[contains(text(), 'Оформление заказа')]")
            )
        )
        print("✓ Страница оформления загружена")

        # Заполняем данные, если они не подтянулись из профиля
        name_field = driver.find_element(By.NAME, "name")
        if name_field.get_attribute("value") == "":
            name_field.send_keys(TEST_USER["full_name"])
            print("✓ ФИО заполнено")
        
        phone_field = driver.find_element(By.NAME, "phone")
        if phone_field.get_attribute("value") == "":
            phone_field.send_keys("+79567890133")
            print("✓ Телефон заполнен")
        
        email_field = driver.find_element(By.NAME, "email")
        if email_field.get_attribute("value") == "":
            email_field.send_keys("tolik-_-tester@testerov.ru")
            print("✓ Email заполнен")

        # Выбираем или вводим адрес
        try:
            address_select = driver.find_element(By.NAME, "address")
            if address_select.tag_name == "select":
                # Выбираем сохранённый адрес
                for option in address_select.find_elements(By.TAG_NAME, "option"):
                    if option.get_attribute("value") and option.get_attribute("value") != "new":
                        option.click()
                        print("✓ Выбран сохранённый адрес")
                        break
                else:
                    # Если нет сохранённых, выбираем "new"
                    driver.find_element(By.XPATH, "//option[@value='new']").click()
                    time.sleep(0.5)
                    new_address = driver.find_element(By.NAME, "new_address")
                    new_address.clear()
                    new_address.send_keys("г. Ижевск, ул. Пушкина, д. 1")
                    print("✓ Введён новый адрес")
            else:
                # Поле ввода адреса
                address_field = driver.find_element(By.NAME, "new_address")
                address_field.clear()
                address_field.send_keys("г. Ижевск, ул. Пушкина, д. 1")
                print("✓ Адрес заполнен")
        except:
            # Если нет выбора адреса
            address_field = driver.find_element(By.NAME, "new_address")
            address_field.clear()
            address_field.send_keys("г. Ижевск, ул. Пушкина, д. 1")
            print("✓ Адрес заполнен")

        # Подтверждаем заказ
        submit_btn = driver.find_element(
            By.XPATH, "//button[contains(text(), 'Подтвердить заказ')]"
        )
        submit_btn.click()
        time.sleep(3)
        print("✓ Заказ отправлен")

        # ------------------------------------------------
        # ШАГ 5: ПРОВЕРКА УСПЕХА
        # ------------------------------------------------
        print("\n" + "="*60)
        print("ШАГ 5: Проверка результата")
        print("="*60)

        # Ждём страницу успеха
        try:
            # Проверяем редирект на order_success.php
            if "order_success" in driver.current_url:
                success_elem = wait.until(
                    EC.presence_of_element_located(
                        (By.XPATH, "//h2[contains(text(), 'Заказ успешно оформлен')]")
                    )
                )
                print(f"✅ {success_elem.text}")
            else:
                # Проверяем, есть ли сообщение об успехе на текущей странице
                page_text = driver.find_element(By.TAG_NAME, "body").text
                if "успешно" in page_text.lower() or "спасибо" in page_text.lower():
                    print("✅ Заказ успешно оформлен")
                else:
                    raise Exception("Не удалось подтвердить успешное оформление заказа")
        except TimeoutException:
            # Если нет явного сообщения, но заказ создался
            if "order_success" in driver.current_url or "order" in driver.current_url:
                print("✅ Заказ оформлен (страница успеха загружена)")
            else:
                raise Exception("Не удалось подтвердить успешное оформление заказа")

        # ------------------------------------------------
        # ИТОГ
        # ------------------------------------------------
        print("\n" + "="*60)
        print("🎉 ТЕСТ УСПЕШНО ПРОЙДЕН!")
        print("="*60)
        return True

    except Exception as e:
        print(f"\n❌ ОШИБКА: {e}")
        print(f"   Текущий URL: {driver.current_url}")
        driver.save_screenshot("/home/alehandro/auto_tests/error_screenshot_acs.png")
        print("📸 Скриншот сохранён: error_screenshot_acs.png")
        return False

    finally:
        time.sleep(2)
        driver.quit()
        print("Браузер закрыт.")


# ============================================================
# ЗАПУСК
# ============================================================
if __name__ == "__main__":
    print("\n" + "="*60)
    print("🚀 АВТОТЕСТ LUXE ACCESSORIES (/acs)")
    print("="*60)
    print(f"👤 Тестовый пользователь: {TEST_USER['email_or_phone']}")
    print(f"🛍️  Товары: {', '.join([p['name'] for p in TEST_PRODUCTS])}")
    print("="*60)
    
    result = test_full_purchase_flow()

    print("\n" + "="*60)
    if result:
        print("✅ ТЕСТ ПРОЙДЕН УСПЕШНО!")
    else:
        print("❌ ТЕСТ УПАЛ! Проверьте скриншот и логи.")
    print("="*60)