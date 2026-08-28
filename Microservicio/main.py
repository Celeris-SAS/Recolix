from fastapi import FastAPI, File, UploadFile
from fastapi.responses import JSONResponse
import tensorflow as tf
import numpy as np
from PIL import Image
import io

app = FastAPI(title="API IA - Recolix")

CLASES_RECOLIX = {
    'Plastico': 4,
    'Vidrio': 7,
    'Papel y carton': 5,
    'Residuos compostables': 3,
}
nombres_clases = list(CLASES_RECOLIX.keys())

modelo = tf.keras.models.load_model("modelo_reciclaje.h5")


@app.post("/api/ia/clasificar-residuo")
async def clasificar_imagen(file: UploadFile = File(...)):
    try:
        contenido = await file.read()
        imagen = Image.open(io.BytesIO(contenido)).resize((224, 224))
        img_array = tf.keras.preprocessing.image.img_to_array(imagen)
        img_array = np.expand_dims(img_array, 0)  # lote de 1

        predicciones = modelo.predict(img_array)
        indice_ganador = np.argmax(predicciones[0])
        nombre_clase = nombres_clases[indice_ganador]
        confianza = float(np.max(predicciones[0]))

        return {
            "status": "success",
            "data": {
                "material": nombre_clase,
                "id_tipo": CLASES_RECOLIX[nombre_clase],
                "confianza": confianza
            }
        }
    except Exception as e:
        return JSONResponse(status_code=500, content={"status": "error", "message": str(e)})
