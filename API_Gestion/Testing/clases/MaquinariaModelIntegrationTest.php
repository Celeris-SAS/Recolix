<?php

require_once __DIR__ . '/../../Modelo/maquinariaModel.php';

final class MaquinariaModelIntegrationTest extends DbTestCase
{
    public function testInsertMaquinariaCreaLaFilaYLaVinculaAlCentro(): void
    {
        $model = new MaquinariaModel();
        $centroId = $this->crearCentroAcopio();

        $id = $model->insertMaquinaria($centroId, 'Compactadora Test', 'compactadora', '2024-01-01', 'operativa');

        $this->assertGreaterThan(0, $id);
        $maquinaria = $model->getMaquinaria($id);
        $this->assertSame('Compactadora Test', $maquinaria['nombre']);
        $this->assertSame('operativa', $maquinaria['estado']);
        $this->assertSame($centroId, (int) $maquinaria['id_centro']);
    }

    public function testUpdateMaquinariaModificaLosCampos(): void
    {
        $model = new MaquinariaModel();
        $centroId = $this->crearCentroAcopio();
        $id = $model->insertMaquinaria($centroId, 'Original', 'pala', '2024-01-01', 'operativa');

        $model->updateMaquinaria($id, 'Renombrada', 'cinta', '2023-06-15', 'en_mantenimiento');

        $maquinaria = $model->getMaquinaria($id);
        $this->assertSame('Renombrada', $maquinaria['nombre']);
        $this->assertSame('cinta', $maquinaria['tipo']);
        $this->assertSame('2023-06-15', $maquinaria['fecha_adquisicion']);
        $this->assertSame('en_mantenimiento', $maquinaria['estado']);
    }

    public function testUpdateMaquinariaNoReasignaDeCentro(): void
    {
        $model = new MaquinariaModel();
        $centroId = $this->crearCentroAcopio();
        $id = $model->insertMaquinaria($centroId, 'Original', 'pala', '2024-01-01', 'operativa');

        $model->updateMaquinaria($id, 'Renombrada', 'pala', '2024-01-01', 'operativa');

        $this->assertSame($centroId, (int) $model->getMaquinaria($id)['id_centro']);
    }

    public function testCambiarEstadoActualizaElEstado(): void
    {
        $model = new MaquinariaModel();
        $centroId = $this->crearCentroAcopio();
        $id = $model->insertMaquinaria($centroId, 'Test', 'pala', '2024-01-01', 'operativa');

        $model->cambiarEstado($id, 'en_mantenimiento');

        $this->assertSame('en_mantenimiento', $model->getMaquinaria($id)['estado']);
    }

    public function testBajaMaquinariaDejaEstadoFueraDeServicio(): void
    {
        $model = new MaquinariaModel();
        $centroId = $this->crearCentroAcopio();
        $id = $model->insertMaquinaria($centroId, 'Test', 'pala', '2024-01-01', 'operativa');

        $model->bajaMaquinaria($id);

        $this->assertSame('fuera_servicio', $model->getMaquinaria($id)['estado']);
    }

    public function testObtenerPorCentroSoloTraeLaDeEseCentro(): void
    {
        $model = new MaquinariaModel();
        $centroUno = $this->crearCentroAcopio();
        $centroDos = $this->crearCentroAcopio();
        $model->insertMaquinaria($centroUno, 'Del centro uno', 'pala', '2024-01-01', 'operativa');
        $model->insertMaquinaria($centroUno, 'Tambien del centro uno', 'cinta', '2024-01-01', 'operativa');
        $model->insertMaquinaria($centroDos, 'Del centro dos', 'bascula', '2024-01-01', 'operativa');

        $resultado = $model->obtenerPorCentro($centroUno);

        $this->assertCount(2, $resultado);
        $nombres = array_column($resultado, 'nombre');
        sort($nombres);
        $this->assertSame(['Del centro uno', 'Tambien del centro uno'], $nombres);
    }

    public function testFiltrarPorTipoDevuelveSoloCoincidencias(): void
    {
        $model = new MaquinariaModel();
        $centroId = $this->crearCentroAcopio();
        $model->insertMaquinaria($centroId, 'Pala 1', 'pala', '2024-01-01', 'operativa');
        $model->insertMaquinaria($centroId, 'Pala 2', 'pala', '2024-01-01', 'operativa');
        $model->insertMaquinaria($centroId, 'Cinta 1', 'cinta', '2024-01-01', 'operativa');

        $this->assertCount(2, $model->filtrarPorTipo('pala'));
        $this->assertCount(1, $model->filtrarPorTipo('cinta'));
    }

    public function testFiltrarPorEstadoDevuelveSoloCoincidencias(): void
    {
        $model = new MaquinariaModel();
        $centroId = $this->crearCentroAcopio();
        $model->insertMaquinaria($centroId, 'A', 'pala', '2024-01-01', 'operativa');
        $model->insertMaquinaria($centroId, 'B', 'pala', '2024-01-01', 'fuera_servicio');

        $this->assertCount(1, $model->filtrarPorEstado('operativa'));
        $this->assertCount(1, $model->filtrarPorEstado('fuera_servicio'));
    }

    public function testGetAllMaquinariasTraeTodasConSuCentro(): void
    {
        $model = new MaquinariaModel();
        $centroId = $this->crearCentroAcopio();
        $model->insertMaquinaria($centroId, 'A', 'pala', '2024-01-01', 'operativa');
        $model->insertMaquinaria($centroId, 'B', 'cinta', '2024-01-01', 'operativa');

        $resultado = $model->getAllMaquinarias();

        $this->assertCount(2, $resultado);
        $this->assertSame($centroId, (int) $resultado[0]['id_centro']);
    }
}
