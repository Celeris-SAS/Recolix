import json
import sys
from math import asin, cos, radians, sin, sqrt

RADIO_TIERRA_KM = 6371.0


def haversine_km(lat1, lon1, lat2, lon2):
    phi1, phi2 = radians(lat1), radians(lat2)
    dphi = radians(lat2 - lat1)
    dlambda = radians(lon2 - lon1)
    a = sin(dphi / 2) ** 2 + cos(phi1) * cos(phi2) * sin(dlambda / 2) ** 2
    return 2 * RADIO_TIERRA_KM * asin(sqrt(a))


def calcular_orden(base, contenedores):
    if not contenedores:
        raise ValueError("No hay contenedores para rutear")

    ids = [c["id"] for c in contenedores]
    if len(ids) != len(set(ids)):
        raise ValueError("Hay ids de contenedor repetidos")

    lat_base, lon_base = float(base[0]), float(base[1])

    def distancia_a_base(c):
        return haversine_km(lat_base, lon_base, float(c["lat"]), float(c["lon"]))

    ordenados = sorted(
        contenedores,
        key=lambda c: (-float(c.get("nivel_llenado", 0)), distancia_a_base(c), c["id"])
    )
    orden = [c["id"] for c in ordenados]

    puntos = [(lat_base, lon_base)] + [(float(c["lat"]), float(c["lon"])) for c in ordenados]
    distancia_total = sum(
        haversine_km(*puntos[i], *puntos[i + 1]) for i in range(len(puntos) - 1)
    )

    return orden, distancia_total


def main():
    try:
        payload = json.load(sys.stdin)
        base = payload["base"]
        contenedores = payload["contenedores"]
        orden, distancia_total = calcular_orden(base, contenedores)
    except Exception as exc:
        json.dump({"error": str(exc)}, sys.stdout)
        sys.exit(1)

    json.dump({"orden": orden, "distancia_total_km": round(distancia_total, 3)}, sys.stdout)


if __name__ == "__main__":
    main()
