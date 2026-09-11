<?php

require_once __DIR__ . '/../../Modelo/cuadrillaModel.php';

final class CuadrillaModelIntegrationTest extends DbTestCase
{
    public function testEsDisponibleTrueCuandoActivaYSinLevantesPendientes(): void
    {
        $model = new CuadrillaModel();
        $id = $this->crearCuadrilla(null, 'activa');

        $this->assertTrue($model->esDisponible($id));
    }

    public function testEsDisponibleFalseCuandoEstaInactiva(): void
    {
        $model = new CuadrillaModel();
        $id = $this->crearCuadrilla(null, 'inactiva');

        $this->assertFalse($model->esDisponible($id));
    }

    public function testEsDisponibleFalseConLevantePendiente(): void
    {
        $model = new CuadrillaModel();
        $id = $this->crearCuadrilla(null, 'activa');
        $this->crearLevante($id, ['estado' => 'pendiente']);

        $this->assertFalse($model->esDisponible($id));
    }

    public function testEsDisponibleFalseConLevanteEnCurso(): void
    {
        $model = new CuadrillaModel();
        $id = $this->crearCuadrilla(null, 'activa');
        $this->crearLevante($id, ['estado' => 'en_curso']);

        $this->assertFalse($model->esDisponible($id));
    }

    public function testEsDisponibleTrueConLevanteYaCompletado(): void
    {
        $model = new CuadrillaModel();
        $id = $this->crearCuadrilla(null, 'activa');
        $this->crearLevante($id, ['estado' => 'completado']);

        $this->assertTrue($model->esDisponible($id));
    }

    public function testEsDisponibleFalseSiLaCuadrillaNoExiste(): void
    {
        $model = new CuadrillaModel();

        $this->assertFalse($model->esDisponible(999999));
    }

    public function testExisteMunicipio(): void
    {
        $model = new CuadrillaModel();
        $municipioId = $this->crearMunicipio();

        $this->assertTrue($model->existeMunicipio($municipioId));
        $this->assertFalse($model->existeMunicipio(999999));
    }

    public function testInsertCuadrillaYGetCuadrillaRoundtrip(): void
    {
        $model = new CuadrillaModel();
        $municipioId = $this->crearMunicipio();

        $id = $model->insertCuadrilla($municipioId, 'activa');

        $this->assertGreaterThan(0, $id);
        $cuadrilla = $model->getCuadrilla($id);
        $this->assertSame($municipioId, (int) $cuadrilla['id_municipio']);
        $this->assertSame('activa', $cuadrilla['estado']);
    }

    public function testBajaCuadrillaDejaEstadoInactiva(): void
    {
        $model = new CuadrillaModel();
        $id = $this->crearCuadrilla(null, 'activa');

        $model->bajaCuadrilla($id);

        $this->assertSame('inactiva', $model->getCuadrilla($id)['estado']);
    }

    public function testUpdateCuadrillaModificaMunicipioYEstado(): void
    {
        $model = new CuadrillaModel();
        $municipioOriginal = $this->crearMunicipio('Municipio Original');
        $municipioNuevo = $this->crearMunicipio('Municipio Nuevo');
        $id = $this->crearCuadrilla($municipioOriginal, 'activa');

        $model->updateCuadrilla($id, $municipioNuevo, 'inactiva');

        $cuadrilla = $model->getCuadrilla($id);
        $this->assertSame($municipioNuevo, (int) $cuadrilla['id_municipio']);
        $this->assertSame('inactiva', $cuadrilla['estado']);
    }

    public function testGetAllCuadrillasTraeTodas(): void
    {
        $model = new CuadrillaModel();
        $this->crearCuadrilla();
        $this->crearCuadrilla();

        $this->assertCount(2, $model->getAllCuadrillas());
    }

    public function testFiltrarEstadoDevuelveSoloCoincidencias(): void
    {
        $model = new CuadrillaModel();
        $this->crearCuadrilla(null, 'activa');
        $this->crearCuadrilla(null, 'activa');
        $this->crearCuadrilla(null, 'inactiva');

        $this->assertCount(2, $model->filtrarEstado('activa'));
        $this->assertCount(1, $model->filtrarEstado('inactiva'));
    }

    public function testFiltrarMunicipioDevuelveSoloLasDeEseMunicipio(): void
    {
        $model = new CuadrillaModel();
        $municipioA = $this->crearMunicipio('Municipio A');
        $municipioB = $this->crearMunicipio('Municipio B');
        $this->crearCuadrilla($municipioA);
        $this->crearCuadrilla($municipioA);
        $this->crearCuadrilla($municipioB);

        $this->assertCount(2, $model->filtrarMunicipio($municipioA));
        $this->assertCount(1, $model->filtrarMunicipio($municipioB));
    }

    public function testFiltrarDisponibilidadDevuelveSoloLasActivas(): void
    {
        $model = new CuadrillaModel();
        $this->crearCuadrilla(null, 'activa');
        $this->crearCuadrilla(null, 'inactiva');

        $this->assertCount(1, $model->filtrarDisponibilidad());
    }

    public function testObtenerHistorialIncluyeLevantesEIncidencias(): void
    {
        $model = new CuadrillaModel();
        $id = $this->crearCuadrilla();
        $this->crearLevante($id, ['estado' => 'completado']);
        $ciUsuario = $this->crearUsuario();
        $this->crearIncidencia($ciUsuario, ['id_cuadrilla' => $id, 'tipo' => 'desborde']);

        $historial = $model->obtenerHistorial($id);

        $this->assertCount(2, $historial);
        $tipos = array_column($historial, 'tipo_evento');
        sort($tipos);
        $this->assertSame(['incidencia', 'levante'], $tipos);
    }

    public function testObtenerHistorialVacioSinEventos(): void
    {
        $model = new CuadrillaModel();
        $id = $this->crearCuadrilla();

        $this->assertSame([], $model->obtenerHistorial($id));
    }
}
