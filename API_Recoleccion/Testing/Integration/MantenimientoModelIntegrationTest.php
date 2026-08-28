<?php

require_once __DIR__ . '/../../Modelo/mantenimientoModel.php';

final class MantenimientoModelIntegrationTest extends DbTestCase
{
    public function testRegistrarYGetMantenimientoRoundtrip(): void
    {
        $model = new MantenimientoModel();
        $camionId = $this->crearCamion();

        $id = $model->registrar($camionId, 'preventivo', 'Cambio de aceite', '2026-01-10 08:00:00', '2026-01-10 12:00:00', 150.5);

        $this->assertGreaterThan(0, $id);
        $mantenimiento = $model->getMantenimiento($id);
        $this->assertSame('preventivo', $mantenimiento['tipo']);
        $this->assertEqualsWithDelta(150.5, (float) $mantenimiento['costo'], 0.001);
    }

    public function testProgramarInsertaConCostoCero(): void
    {
        $model = new MantenimientoModel();
        $camionId = $this->crearCamion();

        $id = $model->programar($camionId, 'correctivo', 'Rotura de motor', '2026-01-10 08:00:00', '2026-01-12 08:00:00');

        $this->assertSame(0.0, (float) $model->getMantenimiento($id)['costo']);
    }

    public function testActualizarMantenimientoModificaCampos(): void
    {
        $model = new MantenimientoModel();
        $camionId = $this->crearCamion();
        $id = $model->registrar($camionId, 'preventivo', 'Cambio de aceite', '2026-01-10 08:00:00', null, 100);

        $actualizado = $model->actualizarMantenimiento($id, 'correctivo', 'Reparación', '2026-01-11 08:00:00', '2026-01-11 10:00:00', 500);

        $this->assertTrue($actualizado);
        $mantenimiento = $model->getMantenimiento($id);
        $this->assertSame('correctivo', $mantenimiento['tipo']);
        $this->assertEqualsWithDelta(500.0, (float) $mantenimiento['costo'], 0.001);
    }

    public function testDeleteMantenimientoLoElimina(): void
    {
        $model = new MantenimientoModel();
        $camionId = $this->crearCamion();
        $id = $model->registrar($camionId, 'preventivo', 'Cambio de aceite', '2026-01-10 08:00:00', null, 100);

        $model->deleteMantenimiento($id);

        $this->assertNull($model->getMantenimiento($id));
    }

    public function testObtenerPorCamionFiltraCorrectamente(): void
    {
        $model = new MantenimientoModel();
        $camionA = $this->crearCamion(['matricula' => 'AAA1111']);
        $camionB = $this->crearCamion(['matricula' => 'BBB2222']);
        $model->registrar($camionA, 'preventivo', 'A', '2026-01-10 08:00:00', null, 100);
        $model->registrar($camionB, 'preventivo', 'B', '2026-01-10 08:00:00', null, 100);

        $resultado = $model->obtenerPorCamion($camionA);

        $this->assertCount(1, $resultado);
        $this->assertSame('A', $resultado[0]['descripcion']);
    }

    public function testGetProximosSoloDevuelveFuturos(): void
    {
        $model = new MantenimientoModel();
        $camionId = $this->crearCamion();
        $model->registrar($camionId, 'preventivo', 'Pasado', '2020-01-01 08:00:00', null, 100);
        $model->registrar($camionId, 'preventivo', 'Futuro', '2099-01-01 08:00:00', null, 100);

        $resultado = $model->getProximos();

        $this->assertCount(1, $resultado);
        $this->assertSame('Futuro', $resultado[0]['descripcion']);
    }

    public function testGetProximosFiltraPorTipo(): void
    {
        $model = new MantenimientoModel();
        $camionId = $this->crearCamion();
        $model->registrar($camionId, 'preventivo', 'Preventivo futuro', '2099-01-01 08:00:00', null, 100);
        $model->registrar($camionId, 'correctivo', 'Correctivo futuro', '2099-01-01 08:00:00', null, 100);

        $resultado = $model->getProximos('correctivo');

        $this->assertCount(1, $resultado);
        $this->assertSame('correctivo', $resultado[0]['tipo']);
    }

    public function testFiltrarPorTipo(): void
    {
        $model = new MantenimientoModel();
        $camionId = $this->crearCamion();
        $model->registrar($camionId, 'preventivo', 'A', '2026-01-10 08:00:00', null, 100);
        $model->registrar($camionId, 'correctivo', 'B', '2026-01-10 08:00:00', null, 100);

        $resultado = $model->filtrarPorTipo('correctivo');

        $this->assertCount(1, $resultado);
        $this->assertSame('B', $resultado[0]['descripcion']);
    }

    public function testObtenerCostoTotalSumaLosMantenimientosDelCamion(): void
    {
        $model = new MantenimientoModel();
        $camionId = $this->crearCamion();
        $model->registrar($camionId, 'preventivo', 'A', '2026-01-10 08:00:00', null, 100);
        $model->registrar($camionId, 'correctivo', 'B', '2026-01-10 08:00:00', null, 250.5);

        $this->assertEqualsWithDelta(350.5, $model->obtenerCostoTotal($camionId), 0.001);
    }

    public function testObtenerCostoTotalSinMantenimientosEsCero(): void
    {
        $model = new MantenimientoModel();
        $camionId = $this->crearCamion();

        $this->assertSame(0.0, $model->obtenerCostoTotal($camionId));
    }
}
