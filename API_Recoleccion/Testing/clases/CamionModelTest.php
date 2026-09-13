<?php

require_once __DIR__ . '/../../Modelo/camionModel.php';

/**
 * Cubre la parte de CamionModel que es lógica pura (distancia Haversine y
 * cálculo de ETA para el mapa de flota en operativa), sin tocar la base
 * de datos: el modelo se instancia sin constructor (ver RecoleccionTestCase)
 * porque su __construct() abre una conexión mysqli real.
 */
final class CamionModelTest extends RecoleccionTestCase
{
    private function model(): CamionModel
    {
        return $this->newWithoutConstructor(CamionModel::class);
    }

    public function testDistanciaEntrePuntoIgualEsCero(): void
    {
        $model = $this->model();
        $km = $this->callPrivate($model, 'distanciaHaversineKm', [-34.9, -56.2, -34.9, -56.2]);
        $this->assertEqualsWithDelta(0.0, $km, 0.0001);
    }

    public function testDistanciaUnGradoDeLongitudEnEcuador(): void
    {
        $model = $this->model();
        $km = $this->callPrivate($model, 'distanciaHaversineKm', [0, 0, 0, 1]);
        // Un grado de longitud sobre el ecuador equivale a ~111.19 km.
        $this->assertEqualsWithDelta(111.19492664456, $km, 0.001);
    }

    public function testEtaConDistanciaMuyCortaRedondeaAUnMinuto(): void
    {
        $model = $this->model();
        $eta = $this->callPrivate($model, 'calcularEta', [0, 0, 0, 0.001]);

        $this->assertSame(1, $eta['minutos']);
        $this->assertSame('≈ 1 min', $eta['texto']);
        $this->assertSame(25.0, $eta['velocidad_kmh']);
        $this->assertEqualsWithDelta(0.11, $eta['distancia_km'], 0.001);
    }

    public function testEtaDentroDeUnaHoraUsaFormatoEnMinutos(): void
    {
        $model = $this->model();
        $eta = $this->callPrivate($model, 'calcularEta', [0, 0, 0, 0.1]);

        $this->assertSame(27, $eta['minutos']);
        $this->assertSame('≈ 27 min', $eta['texto']);
    }

    public function testEtaMayorAUnaHoraUsaFormatoHorasYMinutos(): void
    {
        $model = $this->model();
        $eta = $this->callPrivate($model, 'calcularEta', [0, 0, 0, 1]);

        $this->assertSame(267, $eta['minutos']);
        $this->assertSame('≈ 4h 27min', $eta['texto']);
        $this->assertEqualsWithDelta(111.19, $eta['distancia_km'], 0.001);
    }
}
