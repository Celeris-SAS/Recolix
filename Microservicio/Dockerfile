FROM python:3.9-slim

WORKDIR /app

RUN pip install fastapi uvicorn tensorflow==2.17.0 pillow python-multipart

COPY main.py .
COPY modelo_reciclaje.h5 .

EXPOSE 8000

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]