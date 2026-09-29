from python:3.12-slim
WORKDIR /app
COPY scripts/ /app/
RUN pip install --no-cache-dir requests 
CMD ["python", "pentest_agent.py", "http://example.com"]
