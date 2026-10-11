import json
import sys
from datetime import datetime

# Mini modelo de IA: regresión lineal simple (mínimos cuadrados), sin
# dependencias externas, en la misma línea que API_Recoleccion/Modelo/rutas.py.
#
# Idea: cada levante "vacía" el contenedor a 0% y lo que se recolecta la vez
# siguiente (peso / capacidad) es, aproximadamente, cuánto se llenó desde
# entonces. Con el historial de pares (días desde el vaciado anterior, % con
# el que se encontró) se ajusta una recta nivel = tasa_diaria * dias, y esa
# tasa se usa para proyectar el nivel a N días desde el último levante.

FORMATOS_FECHA = ("%Y-%m-%d %H:%M:%S", "%Y-%m-%d")


def parsear_fecha(valor):
    for formato in FORMATOS_FECHA:
        try:
            return datetime.strptime(valor[:len(formato) + 8], formato)
        except ValueError:
            continue
    raise ValueError(f"Fecha no reconocida: {valor!r}")


def construir_observaciones(eventos, capacidad):
    """A partir de los levantes (ordenados o no), arma los pares
    (dias_desde_el_levante_anterior, nivel_encontrado_porcentaje).
    Descarta los levantes sin peso (pendientes de pesar): no son "se
    encontró vacío", son "no hay dato" y no deben ensuciar la tasa."""
    con_peso = [e for e in eventos if e.get("peso") is not None]
    ordenados = sorted(con_peso, key=lambda e: parsear_fecha(e["fecha"]))
    observaciones = []
    anterior = None
    for evento in ordenados:
        fecha = parsear_fecha(evento["fecha"])
        peso = float(evento["peso"])
        nivel = max(0.0, min(100.0, (peso / capacidad) * 100.0))
        if anterior is not None:
            dias = (fecha - anterior).total_seconds() / 86400.0
            if dias > 0:
                observaciones.append((dias, nivel))
        anterior = fecha
    return observaciones, anterior


def ajustar_regresion_lineal(observaciones):
    """Mínimos cuadrados para y = m*x (recta forzada por el origen: a los 0
    días de vaciado, el nivel es 0%). Devuelve (tasa_diaria, r2)."""
    n = len(observaciones)
    if n == 0:
        return None, None

    suma_xy = sum(x * y for x, y in observaciones)
    suma_xx = sum(x * x for x, y in observaciones)
    if suma_xx == 0:
        return None, None

    tasa = suma_xy / suma_xx

    media_y = sum(y for _, y in observaciones) / n
    ss_tot = sum((y - media_y) ** 2 for _, y in observaciones)
    ss_res = sum((y - tasa * x) ** 2 for x, y in observaciones)
    r2 = None if ss_tot == 0 else max(0.0, 1 - ss_res / ss_tot)

    return tasa, r2


def proyectar(capacidad, eventos, fecha_referencia, horizontes_dias, umbral_critico=90.0):
    if capacidad <= 0:
        raise ValueError("La capacidad del contenedor debe ser mayor que cero")

    con_peso = [e for e in eventos if e.get("peso") is not None]
    if not con_peso:
        raise ValueError("No hay levantes con peso registrado para proyectar el llenado")

    ordenados = sorted(con_peso, key=lambda e: parsear_fecha(e["fecha"]))
    observaciones, ultimo_vaciado = construir_observaciones(ordenados, capacidad)
    referencia = parsear_fecha(fecha_referencia)

    if ultimo_vaciado is None:
        raise ValueError("No se pudo determinar la fecha del último levante")

    dias_desde_ultimo = max(0.0, (referencia - ultimo_vaciado).total_seconds() / 86400.0)

    if observaciones:
        tasa_diaria, r2 = ajustar_regresion_lineal(observaciones)
    else:
        # Un solo levante con peso: no hay con qué ajustar una recta, se usa
        # ese único dato como si fuera la tasa (nivel encontrado / días transcurridos).
        peso_unico = float(ordenados[-1]["peso"])
        nivel_unico = max(0.0, min(100.0, (peso_unico / capacidad) * 100.0))
        tasa_diaria, r2 = (nivel_unico / dias_desde_ultimo if dias_desde_ultimo > 0 else None), None

    if tasa_diaria is None or tasa_diaria <= 0:
        # Sin tendencia de llenado detectable: se informa sin proyección.
        return {
            "modelo": "regresion_lineal_simple",
            "tasa_diaria_pct": 0.0,
            "r2": r2,
            "dias_desde_ultimo_levante": round(dias_desde_ultimo, 2),
            "nivel_actual_estimado": 0,
            "proyeccion": [{"dias": d, "nivel_proyectado": 0} for d in horizontes_dias],
            "dias_hasta_critico": None,
        }

    def nivel_a(dias_totales):
        return int(round(min(100.0, tasa_diaria * dias_totales)))

    nivel_actual = nivel_a(dias_desde_ultimo)
    proyeccion = [
        {"dias": d, "nivel_proyectado": nivel_a(dias_desde_ultimo + d)}
        for d in horizontes_dias
    ]

    dias_hasta_critico = None
    if nivel_actual < umbral_critico:
        dias_hasta_critico = round((umbral_critico / tasa_diaria) - dias_desde_ultimo, 2)
        if dias_hasta_critico < 0:
            dias_hasta_critico = 0.0

    return {
        "modelo": "regresion_lineal_simple",
        "tasa_diaria_pct": round(tasa_diaria, 3),
        "r2": None if r2 is None else round(r2, 3),
        "dias_desde_ultimo_levante": round(dias_desde_ultimo, 2),
        "nivel_actual_estimado": nivel_actual,
        "proyeccion": proyeccion,
        "dias_hasta_critico": dias_hasta_critico,
    }


def main():
    try:
        payload = json.load(sys.stdin)
        capacidad = float(payload["capacidad"])
        eventos = payload["eventos"]
        fecha_referencia = payload.get("fecha_referencia") or datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        horizontes = payload.get("horizonte_dias") or [1, 3, 7]
        resultado = proyectar(capacidad, eventos, fecha_referencia, horizontes)
    except Exception as exc:
        json.dump({"error": str(exc)}, sys.stdout)
        sys.exit(1)

    json.dump(resultado, sys.stdout)


if __name__ == "__main__":
    main()
