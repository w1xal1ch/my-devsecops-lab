from flask import Flask, render_template_string, request
import subprocess

app = Flask(__name__)

HTML = """
<!DOCTYPE html>
<html>
<head>
    <title>Pentest Dashboard</title>
    <style>
        body { background: #111; color: #0f0; font-family: monospace; padding: 20px; }
        input { background: #222; color: #0f0; border: 1px solid #0f0; padding: 10px; width: 300px; }
        button { background: #222; color: #0f0; border: 1px solid #0f0; padding: 10px 20px; margin: 10px; cursor: pointer; }
        pre { background: #000; padding: 10px; border: 1px solid #0f0; min-height: 200px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Pentest Dashboard</h1>
    <form method="post">
        <input type="text" name="target" placeholder="https://example.com" />
        <button name="cmd" value="agent">Запустить Pentest Agent</button>
        <button name="cmd" value="idor">Запустить IDOR Scanner</button>
        <button name="cmd" value="subdomains">Поиск поддоменов</button>
    </form>
    <h3>Результат:</h3>
    <pre>{{ result }}</pre>
</body>
</html>
"""

@app.route("/", methods=["GET", "POST"])
def index():
    result = ""
    if request.method == "POST":
        target = request.form.get("target", "").strip()
        command = request.form.get("cmd", "")
        if command == "agent":
            cmd = f"python scripts/pentest_agent.py {target}"
        elif command == "idor":
            cmd = "python scripts/idor_scanner.py"
        elif command == "subdomains":
            cmd = f"python scripts/subdomains.py {target}"
        else:
            cmd = "echo 'Выбери команду'"

        try:
            result = subprocess.check_output(cmd, shell=True, text=True, stderr=subprocess.STDOUT, timeout=60)
        except subprocess.TimeoutExpired:
            result = "Скрипт выполнялся слишком долго и был остановлен."
        except Exception as e:
            result = str(e)
    return render_template_string(HTML, result=result)

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
