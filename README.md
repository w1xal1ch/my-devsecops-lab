# My DevSecOps Lab

Учебный полигон для практики DevOps и DevSecOps: тестирование, сканирование, мониторинг.

## Цель

Собрать реальную инфраструктуру для отработки:
- Docker и docker-compose
- CI/CD (GitHub Actions)
- SCA-сканирование (Trivy)
- DAST-сканирование (OWASP ZAP)
- Сетевое сканирование (Nmap)
- Мониторинг (Grafana, Prometheus)
- Работу с БД (MySQL, phpMyAdmin)
- Управление контейнерами (Portainer)

## Что внутри

### Инфраструктура
| Сервис | Порт | Описание |
|--------|------|----------|
| Homepage | 80 | Дашборд полигона |
| Scanner Panel | 5000 | Панель запуска сканеров (Trivy, ZAP, Nmap) |
| MySQL | 3306 | База данных полигона |
| phpMyAdmin | 8081 | Управление БД |
| Portainer | 9000 | Управление Docker |

### Уязвимые приложения
| Сервис | Порт | Описание |
|--------|------|----------|
| Juice Shop | 3000 | OWASP Juice Shop — уязвимое веб-приложение |
| DVWA | 8080 | Damn Vulnerable Web Application |

### Мои проекты
| Сервис | Порт | Описание |
|--------|------|----------|
| site-access | 8001 | Дипломный проект — интернет-магазин аксессуаров |
| site-suvenir | 8002 | Дипломный проект — магазин сувениров |

## Как запустить

```bash
git clone https://github.com/w1xal1ch/my-devsecops-lab.git
cd my-devsecops-lab
docker compose up -d
