<?php

require_once __DIR__ . '/../../Modelo/trasladoModel.php';

final class TrasladoModelIntegrationTest extends DbTestCase
{
    public function testInsertYGetTrasladoResiduoRoundtrip(): void
    {
        $model = new TrasladoModel();
        $camionId = $this->crearCamion();

        $id = $model->insertTrasladoResiduo($camionId, 1, 500, '2026-01-10');

        $this->assertGreaterThan(0, $id);
        $traslado = $model->getTrasladoResiduo($id);
        $this->assertSame($camionId, (int) $traslado['id_camion']);
        $this->assertSame(500, (int) $traslado['peso']);
    }

    public function testObtenerPorCamionFiltraCorrectamente(): void
    {
        $model = new TrasladoModel();
        $camionA = $this->crearCamion(['matricula' => 'AAA1111']);
        $camionB = $this->crearCamion(['matricula' => 'BBB2222']);
        $model->insertTrasladoResiduo($camionA, 1, 100, '2026-01-10');
        $model->insertTrasladoResiduo($camionB, 1, 200, '2026-01-10');

        $resultado = $model->obtenerPorCamion($camionA);

        $this->assertCount(1, $resultado);
    }

    public function testUpdateTrasladoResiduoModificaCampos(): void
    {
        $model = new TrasladoModel();
        $camionId = $this->crearCamion();
        $id = $model->insertTrasladoResiduo($camionId, 1, 100, '2026-01-10');

        $model->updateTrasladoResiduo($id, $camionId, 2, 250, '2026-01-11');

        $traslado = $model->getTrasladoResiduo($id);
        $this->assertEqualsWithDelta(250.0, (float) $traslado['peso'], 0.001);
    }

    public function testDeleteTrasladoResiduoLoElimina(): void
    {
        $model = new TrasladoModel();
        $camionId = $this->crearCamion();
        $id = $model->insertTrasladoResiduo($camionId, 1, 100, '2026-01-10');

        $model->deleteTrasladoResiduo($id);

        $this->assertNull($model->getTrasladoResiduo($id));
    }

    public function testObtenerPesoTotalSumaLosTrasladosDelCamion(): void
    {
        $model = new TrasladoModel();
        $camionId = $this->crearCamion();
        $model->insertTrasladoResiduo($camionId, 1, 100, '2026-01-10');
        $model->insertTrasladoResiduo($camionId, 1, 150, '2026-01-11');

        $this->assertEqualsWithDelta(250.0, $model->obtenerPesoTotal($camionId), 0.001);
    }

    public function testFiltrarPorFechaUsaPrefijo(): void
    {
        $model = new TrasladoModel();
        $camionId = $this->crearCamion();
        $model->insertTrasladoResiduo($camionId, 1, 100, '2026-01-10');
        $model->insertTrasladoResiduo($camionId, 1, 100, '2026-02-10');

        $resultado = $model->filtrarPorFecha('2026-01');

        $this->assertCount(1, $resultado);
    }
}
