import json
import sys
import time
from math import asin, cos, radians, sin, sqrt

RADIO_TIERRA_KM = 6371.0


def haversine_km(lat1, lon1, lat2, lon2):
    phi1, phi2 = radians(lat1), radians(lat2)
    dphi = radians(lat2 - lat1)
    dlambda = radians(lon2 - lon1)
    a = sin(dphi / 2) ** 2 + cos(phi1) * cos(phi2) * sin(dlambda / 2) ** 2
    return 2 * RADIO_TIERRA_KM * asin(sqrt(a))


# Hasta este tamaño el orden se resuelve de forma exacta (Held-Karp); por encima se usa una
# heurística (vecino más cercano + 2-opt + or-opt), que en la práctica queda muy cerca del óptimo.
MAX_CONTENEDORES_EXACTO = 12
# Tope de tiempo de la mejora heurística: pasado esto se devuelve el mejor camino encontrado.
SEGUNDOS_MAXIMOS_MEJORA = 2.0


def _matriz_distancias(puntos):
    n = len(puntos)
    d = [[0.0] * n for _ in range(n)]
    for i in range(n):
        for j in range(i + 1, n):
            d[i][j] = d[j][i] = haversine_km(*puntos[i], *puntos[j])
    return d


def _longitud(d, camino):
    # camino[0] es siempre la base (índice 0); el recorrido es abierto: no vuelve a la base.
    return sum(d[camino[i]][camino[i + 1]] for i in range(len(camino) - 1))


def _orden_exacto(d, n):
    # Held-Karp para camino abierto desde la base (nodo 0) que visita los nodos 1..n.
    if n == 1:
        return [0, 1]
    mejor = {}
    for j in range(1, n + 1):
        mejor[(1 << (j - 1), j)] = (d[0][j], 0)
    for mascara in range(1, 1 << n):
        for j in range(1, n + 1):
            if not mascara & (1 << (j - 1)) or (mascara, j) not in mejor:
                continue
            costo = mejor[(mascara, j)][0]
            for k in range(1, n + 1):
                if mascara & (1 << (k - 1)):
                    continue
                nueva = mascara | (1 << (k - 1))
                candidato = costo + d[j][k]
                if (nueva, k) not in mejor or candidato < mejor[(nueva, k)][0]:
                    mejor[(nueva, k)] = (candidato, j)
    completa = (1 << n) - 1
    ultimo = min(range(1, n + 1), key=lambda j: mejor[(completa, j)][0])
    camino, mascara, actual = [], completa, ultimo
    while actual != 0:
        camino.append(actual)
        _, previo = mejor[(mascara, actual)]
        mascara &= ~(1 << (actual - 1))
        actual = previo
    camino.append(0)
    return camino[::-1]


def _vecino_mas_cercano(d, n):
    camino, pendientes = [0], set(range(1, n + 1))
    while pendientes:
        siguiente = min(pendientes, key=lambda j: (d[camino[-1]][j], j))
        camino.append(siguiente)
        pendientes.remove(siguiente)
    return camino


def _mejorar(d, camino):
    # 2-opt (invertir tramos) y or-opt (mover un contenedor a otra posición) hasta que no mejoren.
    # La base (posición 0) queda fija.
    n = len(camino)
    limite = time.monotonic() + SEGUNDOS_MAXIMOS_MEJORA
    mejoro = True
    while mejoro and time.monotonic() < limite:
        mejoro = False
        for i in range(1, n - 1):
            for j in range(i + 1, n):
                nuevo = camino[:i] + camino[i:j + 1][::-1] + camino[j + 1:]
                if _longitud(d, nuevo) < _longitud(d, camino) - 1e-12:
                    camino, mejoro = nuevo, True
        for i in range(1, n):
            nodo = camino[i]
            resto = camino[:i] + camino[i + 1:]
            for k in range(1, n):
                nuevo = resto[:k] + [nodo] + resto[k:]
                if _longitud(d, nuevo) < _longitud(d, camino) - 1e-12:
                    camino, mejoro = nuevo, True
                    break
            if mejoro:
                break
    return camino


def calcular_orden_distancia(base, contenedores):
    """Orden de visita que minimiza la distancia recorrida partiendo de la base."""
    puntos = [(float(base[0]), float(base[1]))] + [(float(c["lat"]), float(c["lon"])) for c in contenedores]
    n = len(contenedores)
    d = _matriz_distancias(puntos)
    if n <= MAX_CONTENEDORES_EXACTO:
        camino = _orden_exacto(d, n)
    else:
        camino = _mejorar(d, _vecino_mas_cercano(d, n))
    return [contenedores[i - 1]["id"] for i in camino[1:]], _longitud(d, camino)


def calcular_orden(base, contenedores, modo="llenado"):
    """modo "llenado": prioriza los más llenos (decide qué entra en el camión).
    modo "distancia": ordena para minimizar el recorrido."""
    if not contenedores:
        raise ValueError("No hay contenedores para rutear")

    ids = [c["id"] for c in contenedores]
    if len(ids) != len(set(ids)):
        raise ValueError("Hay ids de contenedor repetidos")

    if modo == "distancia":
        return calcular_orden_distancia(base, contenedores)

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
        orden, distancia_total = calcular_orden(base, contenedores, payload.get("modo", "llenado"))
    except Exception as exc:
        json.dump({"error": str(exc)}, sys.stdout)
        sys.exit(1)

    json.dump({"orden": orden, "distancia_total_km": round(distancia_total, 3)}, sys.stdout)


if __name__ == "__main__":
    main()
