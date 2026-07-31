"""
Calcula el orden optimo de visita a los contenedores de un circuito.

Entrada (stdin, JSON):
    {
        "base": [lat, lon],
        "contenedores": [
            {"id": 12, "lat": -34.90, "lon": -56.16, "nivel_llenado": 80},
            ...
        ]
    }

Salida (stdout, JSON):
    {"orden": [12, 45, 7], "distancia_total_km": 8.3}
    o, ante un error:
    {"error": "mensaje"}   (exit code != 0)

El circuito ya define la zona (los contenedores llegan pre-filtrados desde PHP);
este script solo resuelve el ORDEN de visita. El peso de cada arista es la
distancia (Haversine) dividida por el nivel de llenado de los contenedores que
conecta -- mismo criterio "distancia / (llenado + 0.1)" del ejemplo de Dijkstra,
extendido a un recorrido de multiples paradas (TSP con heuristica golosa) en vez
de un unico par origen-destino.
"""

import json
import sys
from itertools import combinations
from math import asin, cos, radians, sin, sqrt

import networkx as nx
from networkx.algorithms.approximation import greedy_tsp

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

    coords = {"Base": (float(base[0]), float(base[1]))}
    niveles = {}
    for c in contenedores:
        coords[c["id"]] = (float(c["lat"]), float(c["lon"]))
        niveles[c["id"]] = float(c.get("nivel_llenado", 0))

    nodos = ["Base"] + ids

    grafo = nx.Graph()
    grafo.add_nodes_from(nodos)
    for u, v in combinations(nodos, 2):
        lat_u, lon_u = coords[u]
        lat_v, lon_v = coords[v]
        distancia = haversine_km(lat_u, lon_u, lat_v, lon_v)
        niveles_extremos = [niveles[n] for n in (u, v) if n in niveles]
        llenado_prom = sum(niveles_extremos) / len(niveles_extremos) if niveles_extremos else 0
        peso_ia = distancia / (llenado_prom / 100 + 0.1)
        grafo.add_edge(u, v, distancia=distancia, peso_ia=peso_ia)

    if len(nodos) == 2:
        ciclo = ["Base", ids[0], "Base"]
    else:
        ciclo = greedy_tsp(grafo, weight="peso_ia", source="Base")

    camino = ciclo[:-1]  # descarta el regreso a Base (no cerramos el circuito)
    orden = camino[1:]  # descarta "Base" al inicio

    distancia_total = 0.0
    for u, v in zip(camino, camino[1:]):
        distancia_total += grafo.edges[u, v]["distancia"]

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
