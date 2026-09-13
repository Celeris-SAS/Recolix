<?php

require_once __DIR__ . '/../../Modelo/contenedorModel.php';

final class ContenedorModelIntegrationTest extends DbTestCase
{
    public function testInsertYGetContenedorRoundtrip(): void
    {
        $model = new ContenedorModel();
        $circuitoId = $this->crearCircuito();

        $id = $model->insertContenedor($circuitoId, null, 'Calle Nueva', 456, -34.9, -56.1, 200, 'funcional');

        $this->assertGreaterThan(0, $id);
        $contenedor = $model->getContenedor($id);
        $this->assertSame('Calle Nueva', $contenedor['calle']);
        $this->assertSame('funcional', $contenedor['estado']);
        $this->assertSame(200, (int) $contenedor['capacidad']);
    }

    public function testCambiarEstadoActualizaElEstado(): void
    {
        $model = new ContenedorModel();
        $id = $this->crearContenedor(null, ['estado' => 'funcional']);

        $model->cambiarEstado($id, 'desbordado');

        $this->assertSame('desbordado', $model->getContenedor($id)['estado']);
    }

    public function testBajaContenedorDejaEstadoInactivo(): void
    {
        $model = new ContenedorModel();
        $id = $this->crearContenedor(null, ['estado' => 'funcional']);

        $model->bajaContenedor($id);

        $this->assertSame('inactivo', $model->getContenedor($id)['estado']);
    }

    public function testRegistrarReparacionVuelveAFuncionalYRegistraFecha(): void
    {
        $model = new ContenedorModel();
        $id = $this->crearContenedor(null, ['estado' => 'roto']);

        $model->registrarReparacion($id);

        $contenedor = $model->getContenedor($id);
        $this->assertSame('funcional', $contenedor['estado']);
        $this->assertSame(date('Y-m-d'), $contenedor['fecha_reparacion']);
    }

    public function testFiltrarCriticosSoloTraeRotosYDesbordados(): void
    {
        $model = new ContenedorModel();
        $this->crearContenedor(null, ['estado' => 'funcional']);
        $this->crearContenedor(null, ['estado' => 'roto']);
        $this->crearContenedor(null, ['estado' => 'desbordado']);
        $this->crearContenedor(null, ['estado' => 'inactivo']);

        $resultado = $model->filtrarCriticos();

        $this->assertCount(2, $resultado);
        $estados = array_column($resultado, 'estado');
        sort($estados);
        $this->assertSame(['desbordado', 'roto'], $estados);
    }

    public function testFiltrarEstadoDevuelveSoloCoincidencias(): void
    {
        $model = new ContenedorModel();
        $this->crearContenedor(null, ['estado' => 'funcional']);
        $this->crearContenedor(null, ['estado' => 'funcional']);
        $this->crearContenedor(null, ['estado' => 'roto']);

        $this->assertCount(2, $model->filtrarEstado('funcional'));
        $this->assertCount(1, $model->filtrarEstado('roto'));
    }

    public function testTipoResiduoExiste(): void
    {
        $model = new ContenedorModel();
        $idTipo = $this->crearTipoResiduo();

        $this->assertTrue($model->tipoResiduoExiste($idTipo));
        $this->assertFalse($model->tipoResiduoExiste(999999));
    }

    public function testCircuitoExiste(): void
    {
        $model = new ContenedorModel();
        $circuitoId = $this->crearCircuito();

        $this->assertTrue($model->circuitoExiste($circuitoId));
        $this->assertFalse($model->circuitoExiste(999999));
    }

    public function testGetHistorialCombinaIncidenciaYReparacion(): void
    {
        require_once __DIR__ . '/../../Modelo/incidenciaModel.php';
        $model = new ContenedorModel();
        $contenedorId = $this->crearContenedor();
        $ci = $this->crearUsuario();

        $incidenciaModel = new IncidenciaModel();
        $idIncidencia = $incidenciaModel->crearIncidencia($ci, 'rotura', null, null, 0);
        $incidenciaModel->vincularContenedor($idIncidencia, $contenedorId);

        $model->registrarReparacion($contenedorId);

        $historial = $model->getHistorial($contenedorId);

        $tipos = array_column($historial, 'tipo_evento');
        sort($tipos);
        $this->assertSame(['incidencia', 'reparacion'], $tipos);
    }
}
