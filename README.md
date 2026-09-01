# My DevSecOps Lab

Моя личная среда для тестирования и пентеста.

## Стек
- Docker, Docker Compose
- Python (Flask, requests, psycopg2)
- PostgreSQL + pgAdmin
- Gitea, Portainer, Grafana
- Juice Shop (уязвимое веб-приложение)

## Запуск
```bash
docker compose up -d --build


Сервисы
Сервис				Адрес
Pentest Dashboard	http://localhost:5000
Juice Shop		http://localhost:3001
Gitea			http://localhost:3000
Portainer		http://localhost:9000
Grafana			http://localhost:3030
pgAdmin			http://localhost:8081


Что умеет

    Запускать мои пентест-скрипты через веб.

    Сохранять отчёты на хосте.

    Писать результаты в PostgreSQL.

    Управлять контейнерами через Portainer.
