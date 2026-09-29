import requests

# Тест 1: Получить список товаров
print("=" * 50)
print("[Тест 1] Получение списка товаров")
url = "http://localhost:3000/api/Products"
response = requests.get(url)
print(f"Статус: {response.status_code}")
if response.status_code == 200:
    print("✅ Товары получены успешно")
else:
    print("❌ Ошибка при получении товаров")
# Тест 2: Проверка IDOR (доступ к чужому профилю)
print("=" * 50)
print("[Тест 2] Проверка IDOR — доступ к профилю админа")

# Используем токен обычного пользователя
user_token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdGF0dXMiOiJzdWNjZXNzIiwiZGF0YSI6eyJpZCI6MjQsInVzZXJuYW1lIjoiIiwiZW1haWwiOiJ0b2xpa0B0ZXN0ZXIucnUiLCJwYXNzd29yZCI6IjM1ZDY5MTJjM2MwY2QxMzNiOTI3MTNjODkzZTVjYWIyIiwicm9sZSI6ImN1c3RvbWVyIiwiZGVsdXhlVG9rZW4iOiIiLCJsYXN0TG9naW5JcCI6IiIsInByb2ZpbGVJbWFnZSI6Ii9hc3NldHMvcHVibGljL2ltYWdlcy91cGxvYWRzL2RlZmF1bHQuc3ZnIiwidG90cFNlY3JldCI6IiIsImlzQWN0aXZlIjp0cnVlLCJjcmVhdGVkQXQiOiIyMDI2LTA3LTExIDE4OjE2OjIyLjE1OCArMDA6MDAiLCJ1cGRhdGVkQXQiOiIyMDI2LTA3LTExIDE4OjIwOjUxLjU3MiArMDA6MDAiLCJkZWxldGVkQXQiOm51bGx9LCJpYXQiOjE3ODM3OTQ3MzN9.SsVpy41B5XcgyORKOUKFyUqp0GFhme-oI0UZsZ1XRkyh5X3kcCfFsGAowPZmM5D6neDfTTABZSJivmHlxouWlrs1j2YSlg4X9sanuJfNZZ4iRrVOfdrnssw1uiJeTqy-2rCQ0YnsbKZDcwGFbabLwjKn3PRhco1LzmCy-_YqBwQ"

headers = {
    "Authorization": f"Bearer {user_token}",
    "Content-Type": "application/json"
}

# Пробуем получить профиль админа (id=1)
response = requests.get("http://localhost:3000/api/Users/1", headers=headers)
print(f"Статус: {response.status_code}")
if response.status_code == 200:
    print("❌ IDOR обнаружен! Обычный пользователь видит данные админа!")
    print(f"Ответ: {response.text[:200]}...")
else:
    print("✅ IDOR не обнаружен. Доступ к профилю админа закрыт.")