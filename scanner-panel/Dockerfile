FROM python:3.11-slim

WORKDIR /app

# Устанавливаем Docker CLI (для запуска сканеров) и nmap
RUN apt-get update && apt-get install -y \
    docker.io \
    nmap \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

EXPOSE 5000

CMD ["python", "app.py"]
