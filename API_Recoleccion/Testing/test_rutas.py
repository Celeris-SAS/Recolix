"""Tests del ordenamiento de rutas. Correr con: python3 -m unittest API_Recoleccion/Testing/test_rutas.py"""
import itertools
import os
import random
import sys
import unittest

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'Modelo'))
from rutas import calcular_orden, haversine_km  # noqa: E402

BASE = [-34.9000, -56.1600]


def contenedor(i, lat, lon, nivel=50):
    return {"id": i, "lat": lat, "lon": lon, "nivel_llenado": nivel}


def longitud(base, contenedores, orden):
    por_id = {c["id"]: c for c in contenedores}
    puntos = [tuple(base)] + [(por_id[i]["lat"], por_id[i]["lon"]) for i in orden]
    return sum(haversine_km(*puntos[k], *puntos[k + 1]) for k in range(len(puntos) - 1))


class OrdenDistanciaTest(unittest.TestCase):
    def test_contenedores_cercanos_quedan_juntos(self):
        # B y C a ~1 cuadra entre sí, A a ~10 cuadras de ambos: nunca debe quedar B, A, C.
        a = contenedor(1, -34.9000, -56.1600)
        b = contenedor(2, -34.9000, -56.1500)
        c = contenedor(3, -34.9000, -56.1490)
        orden, _ = calcular_orden([-34.9000, -56.1600], [b, a, c], modo="distancia")
        self.assertEqual([1, 2, 3], orden)

    def test_no_prioriza_llenado_en_modo_distancia(self):
        a = contenedor(1, -34.9000, -56.1600, nivel=10)
        b = contenedor(2, -34.9000, -56.1500, nivel=100)
        c = contenedor(3, -34.9000, -56.1490, nivel=90)
        orden, _ = calcular_orden([-34.9000, -56.1600], [b, a, c], modo="distancia")
        self.assertEqual([1, 2, 3], orden)

    def test_exacto_coincide_con_fuerza_bruta(self):
        rnd = random.Random(7)
        for _ in range(30):
            n = rnd.randint(1, 7)
            cs = [contenedor(i + 1, -34.9 + rnd.uniform(-.02, .02), -56.16 + rnd.uniform(-.02, .02)) for i in range(n)]
            orden, dist = calcular_orden(BASE, cs, modo="distancia")
            mejor = min(longitud(BASE, cs, [c["id"] for c in p]) for p in itertools.permutations(cs))
            self.assertAlmostEqual(mejor, dist, places=9)
            self.assertAlmostEqual(dist, longitud(BASE, cs, orden), places=9)

    def test_heuristica_visita_todos_una_vez_y_mejora_al_vecino_mas_cercano(self):
        rnd = random.Random(3)
        cs = [contenedor(i + 1, -34.9 + rnd.uniform(-.03, .03), -56.16 + rnd.uniform(-.03, .03)) for i in range(40)]
        orden, dist = calcular_orden(BASE, cs, modo="distancia")
        self.assertEqual(sorted(orden), [c["id"] for c in cs])
        _, dist_por_llenado = calcular_orden(BASE, cs, modo="llenado")
        self.assertLess(dist, dist_por_llenado)

    def test_modo_llenado_sigue_priorizando_los_mas_llenos(self):
        a = contenedor(1, -34.9, -56.16, nivel=20)
        b = contenedor(2, -34.9, -56.15, nivel=90)
        orden, _ = calcular_orden(BASE, [a, b], modo="llenado")
        self.assertEqual([2, 1], orden)


if __name__ == '__main__':
    unittest.main()
