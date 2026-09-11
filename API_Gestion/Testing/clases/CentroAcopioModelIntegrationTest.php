<?php

require_once __DIR__ . '/../../Modelo/centroAcopioModel.php';

final class CentroAcopioModelIntegrationTest extends DbTestCase
{
    public function testInsertYGetCentroRoundtrip(): void
    {
        $model = new CentroAcopioModel();
        $municipioId = $this->crearMunicipio();

        $id = $model->insertCentro($municipioId, 'Ecocentro Test', 'ecocentro', 'Calle Nueva', 456, -34.9, -56.1, 5000, 'operativo');

        $this->assertGreaterThan(0, $id);
        $centro = $model->getCentro($id);
        $this->assertSame('Ecocentro Test', $centro['nombre']);
        $this->assertSame('ecocentro', $centro['tipo']);
        $this->assertSame('operativo', $centro['estado']);
        $this->assertSame(5000, (int) $centro['capacidad_total']);
    }

    public function testUpdateCentroModificaLosCampos(): void
    {
        $model = new CentroAcopioModel();
        $municipioId = $this->crearMunicipio();
        $id = $this->crearCentroAcopio($municipioId, ['nombre' => 'Original']);

        $model->updateCentro($id, $municipioId, 'Renombrado', 'vertedero', 'Calle Nueva', 200, -34.8, -56.2, 8000, 'saturado');

        $centro = $model->getCentro($id);
        $this->assertSame('Renombrado', $centro['nombre']);
        $this->assertSame('vertedero', $centro['tipo']);
        $this->assertSame('saturado', $centro['estado']);
        $this->assertSame(8000, (int) $centro['capacidad_total']);
    }

    public function testBajaCentroDejaEstadoCerrado(): void
    {
        $model = new CentroAcopioModel();
        $id = $this->crearCentroAcopio(null, ['estado' => 'operativo']);

        $model->bajaCentro($id);

        $this->assertSame('cerrado', $model->getCentro($id)['estado']);
    }

    public function testFiltrarTipoDevuelveSoloCoincidencias(): void
    {
        $model = new CentroAcopioModel();
        $this->crearCentroAcopio(null, ['tipo' => 'ecocentro']);
        $this->crearCentroAcopio(null, ['tipo' => 'ecocentro']);
        $this->crearCentroAcopio(null, ['tipo' => 'vertedero']);

        $this->assertCount(2, $model->filtrarTipo('ecocentro'));
        $this->assertCount(1, $model->filtrarTipo('vertedero'));
    }

    public function testFiltrarEstadoDevuelveSoloCoincidencias(): void
    {
        $model = new CentroAcopioModel();
        $this->crearCentroAcopio(null, ['estado' => 'operativo']);
        $this->crearCentroAcopio(null, ['estado' => 'saturado']);
        $this->crearCentroAcopio(null, ['estado' => 'saturado']);

        $this->assertCount(1, $model->filtrarEstado('operativo'));
        $this->assertCount(2, $model->filtrarEstado('saturado'));
    }

    public function testFiltrarMunicipioDevuelveSoloLosDeEseMunicipio(): void
    {
        $model = new CentroAcopioModel();
        $municipioA = $this->crearMunicipio('Municipio A');
        $municipioB = $this->crearMunicipio('Municipio B');
        $this->crearCentroAcopio($municipioA);
        $this->crearCentroAcopio($municipioA);
        $this->crearCentroAcopio($municipioB);

        $this->assertCount(2, $model->filtrarMunicipio($municipioA));
        $this->assertCount(1, $model->filtrarMunicipio($municipioB));
    }

    public function testAsignarTipoResiduoYGetOcupacionRoundtrip(): void
    {
        $model = new CentroAcopioModel();
        $centroId = $this->crearCentroAcopio();
        $tipoId = $this->crearTipoResiduo();

        $model->asignarTipoResiduo($centroId, $tipoId, 1000);

        $ocupacion = $model->getOcupacion($centroId);
        $this->assertSame(0, (int) $ocupacion['ocupado']);
        $this->assertSame(1000, (int) $ocupacion['capacidad']);
    }

    public function testAsignarTipoResiduoDosVecesActualizaCapacidadSinDuplicar(): void
    {
        $model = new CentroAcopioModel();
        $centroId = $this->crearCentroAcopio();
        $tipoId = $this->crearTipoResiduo();

        $model->asignarTipoResiduo($centroId, $tipoId, 1000);
        $model->asignarTipoResiduo($centroId, $tipoId, 2500);

        $this->assertCount(1, $model->getResiduosPorCentro($centroId));
        $this->assertSame(2500, (int) $model->getCentroResiduo($centroId, $tipoId)['capacidad']);
    }

    public function testActualizarOcupacionModificaSoloEseTipo(): void
    {
        $model = new CentroAcopioModel();
        $centroId = $this->crearCentroAcopio();
        $tipoUno = $this->crearTipoResiduo(['descripcion' => 'Tipo Uno']);
        $tipoDos = $this->crearTipoResiduo(['descripcion' => 'Tipo Dos']);
        $model->asignarTipoResiduo($centroId, $tipoUno, 1000);
        $model->asignarTipoResiduo($centroId, $tipoDos, 500);

        $model->actualizarOcupacion($centroId, $tipoUno, 300);

        $this->assertSame(300, (int) $model->getCentroResiduo($centroId, $tipoUno)['ocupacion_actual']);
        $this->assertSame(0, (int) $model->getCentroResiduo($centroId, $tipoDos)['ocupacion_actual']);
    }

    public function testGetResiduosPorCentroTraeColorYDescripcionDelTipo(): void
    {
        $model = new CentroAcopioModel();
        $centroId = $this->crearCentroAcopio();
        $tipoId = $this->crearTipoResiduo(['color' => 'Verde', 'descripcion' => 'Reciclables']);
        $model->asignarTipoResiduo($centroId, $tipoId, 1000);

        $residuos = $model->getResiduosPorCentro($centroId);

        $this->assertCount(1, $residuos);
        $this->assertSame('Verde', $residuos[0]['color']);
        $this->assertSame('Reciclables', $residuos[0]['descripcion']);
    }

    public function testMunicipioExiste(): void
    {
        $model = new CentroAcopioModel();
        $municipioId = $this->crearMunicipio();

        $this->assertTrue($model->municipioExiste($municipioId));
        $this->assertFalse($model->municipioExiste(999999));
    }

    public function testTipoResiduoExiste(): void
    {
        $model = new CentroAcopioModel();
        $tipoId = $this->crearTipoResiduo();

        $this->assertTrue($model->tipoResiduoExiste($tipoId));
        $this->assertFalse($model->tipoResiduoExiste(999999));
    }
}
