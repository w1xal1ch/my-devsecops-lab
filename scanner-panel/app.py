from flask import Flask, send_file, jsonify, request
import subprocess
import os
import re
from datetime import datetime

app = Flask(__name__)

REPORTS_DIR = "/app/reports"
NETWORK = "my-devsecops-lab_devsecops-net"

ALLOWED_TARGETS = [
    r"^http://juice-shop:3000$",
    r"^http://dvwa$",
    r"^http://site-access$",
    r"^http://site-access/acs/$",
    r"^http://site-suvenir$",
    r"^http://site-suvenir/suvenir/$",
]

ALLOWED_IMAGES = [
    r"^bkimminich/juice-shop:latest$",
    r"^vulnerables/web-dvwa:latest$",
    r"^my-devsecops-lab-site-access:latest$",
    r"^my-devsecops-lab-site-suvenir:latest$",
]

def is_allowed_target(target):
    return any(re.match(pattern, target) for pattern in ALLOWED_TARGETS)

def is_allowed_image(image):
    return any(re.match(pattern, image) for pattern in ALLOWED_IMAGES)

@app.route('/')
def index():
    return send_file('index.html')

@app.route('/scan/<name>', methods=['POST'])
def scan(name):
    data = request.get_json() or {}
    target = data.get('target', '').strip()

    if not target:
        return jsonify({"error": "URL или образ не указан"}), 400

    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")

    # ─── TRIVY (SCA) ─────────────────────────────────────
    if name == 'trivy':
        if not is_allowed_image(target):
            return jsonify({"error": f"Образ {target} не разрешён"}), 403
        cmd = [
            "docker", "run", "--rm",
            "-v", "/var/run/docker.sock:/var/run/docker.sock",
            "aquasec/trivy", "image", target
        ]
        report_file = f"{REPORTS_DIR}/trivy_{timestamp}.txt"

    # ─── ZAP (DAST) ──────────────────────────────────────
    elif name == 'zap':
        if not is_allowed_target(target):
            return jsonify({"error": f"Цель {target} не разрешена"}), 403
        cmd = [
            "docker", "run", "--rm",
            "-v", f"{REPORTS_DIR}:/zap/wrk/:rw",
            "--network", NETWORK,
            "ghcr.io/zaproxy/zaproxy:stable",
            "zap-baseline.py", "-t", target, "-m", "1",
            "-r", f"zap_{timestamp}.html"
        ]
        report_file = f"{REPORTS_DIR}/zap_{timestamp}.txt"

    # ─── ZAP FULL SCAN (DAST) ────────────────────────────
    elif name == 'zap-full':
        if not is_allowed_target(target):
            return jsonify({"error": f"Цель {target} не разрешена"}), 403
        cmd = [
            "docker", "run", "--rm",
            "-v", f"{REPORTS_DIR}:/zap/wrk/:rw",
            "--network", NETWORK,
            "ghcr.io/zaproxy/zaproxy:stable",
            "zap-full-scan.py", "-t", target,
            "-r", f"zap_full_{timestamp}.html"
        ]
        report_file = f"{REPORTS_DIR}/zap_full_{timestamp}.txt"

    # ─── NMAP (Network) ──────────────────────────────────
    elif name == 'nmap':
        if not is_allowed_target(target):
            return jsonify({"error": f"Цель {target} не разрешена"}), 403
        host = target.replace("http://", "").replace("https://", "").split(":")[0].split("/")[0]
        cmd = ["nmap", "-sV", host]
        report_file = f"{REPORTS_DIR}/nmap_{timestamp}.txt"

    else:
        return jsonify({"error": "Неизвестный сканер"}), 400

    try:
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=1800)
        output = result.stdout + result.stderr

        with open(report_file, 'w') as f:
            f.write(output)

        response = {
            "scanner": name,
            "target": target,
            "status": "success",
            "report": report_file,
            "output": output[:5000]
        }
        return jsonify(response)

    except subprocess.TimeoutExpired:
        return jsonify({"error": "Сканер превысил лимит времени (30 минут)"}), 500
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
