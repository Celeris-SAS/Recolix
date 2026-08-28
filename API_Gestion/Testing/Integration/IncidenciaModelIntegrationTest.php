<?php

require_once __DIR__ . '/../../Modelo/incidenciaModel.php';

final class IncidenciaModelIntegrationTest extends DbTestCase
{
    public function testCrearIncidenciaYVincularContenedorRoundtrip(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();

        $idIncidencia = $model->crearIncidencia($ci, 'rotura', 'Tapa rota', 'uploads/incidencias/inc_test.jpg', 1);
        $model->vincularContenedor($idIncidencia, $contenedorId);

        $incidencia = $model->getIncidencia($idIncidencia);
        $this->assertSame('rotura', $incidencia['tipo']);
        $this->assertSame('Tapa rota', $incidencia['descripcion']);
        $this->assertSame('abierta', $incidencia['estado']);
        $this->assertSame(1, (int) $incidencia['urgencia']);
        $this->assertSame('uploads/incidencias/inc_test.jpg', $incidencia['url_imagen']);

        $vinculados = $model->getContenedoresVinculados($idIncidencia);
        $this->assertCount(1, $vinculados);
        $this->assertSame($contenedorId, (int) $vinculados[0]['id_contenedor']);
    }

    public function testCrearIncidenciaSinImagenGuardaUrlImagenNull(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();

        $id = $model->crearIncidencia($ci, 'desborde', null, null, 0);

        $this->assertNull($model->getIncidencia($id)['url_imagen']);
    }

    public function testUsuarioExiste(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();

        $this->assertTrue($model->usuarioExiste($ci));
        $this->assertFalse($model->usuarioExiste(99999999));
    }

    public function testFiltrarEstadoDevuelveSoloCoincidencias(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $abiertaId = $model->crearIncidencia($ci, 'desborde', null, null, 0);

        $resultado = $model->filtrarEstado('abierta');

        $this->assertCount(2, $resultado);
    }

    public function testListarUrgentesSoloTraeIncidenciasUrgentesNoResueltas(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorUrgente = $this->crearContenedor(null, ['estado' => 'desbordado']);
        $contenedorNormal = $this->crearContenedor(null, ['estado' => 'funcional']);

        $idUrgente = $model->crearIncidencia($ci, 'desborde', null, null, 1);
        $model->vincularContenedor($idUrgente, $contenedorUrgente);

        $idNoUrgente = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($idNoUrgente, $contenedorNormal);

        $resultado = $model->listarUrgentes();

        $this->assertCount(1, $resultado);
        $this->assertSame($idUrgente, (int) $resultado[0]['id_incidencia']);
    }

    public function testListarUrgentesExcluyeLasYaResueltas(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor(null, ['estado' => 'roto']);

        $idIncidencia = $model->crearIncidencia($ci, 'rotura', null, null, 1);
        $model->vincularContenedor($idIncidencia, $contenedorId);
        $model->cerrar($idIncidencia);

        $this->assertSame([], $model->listarUrgentes());
    }

    public function testAsignarCuadrillaCambiaEstadoAAsignada(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $cuadrillaId = $this->crearCuadrilla();
        $idIncidencia = $model->crearIncidencia($ci, 'rotura', null, null, 0);

        $model->asignarCuadrilla($idIncidencia, $cuadrillaId);

        $incidencia = $model->getIncidencia($idIncidencia);
        $this->assertSame('asignada', $incidencia['estado']);
        $this->assertSame($cuadrillaId, (int) $incidencia['id_cuadrilla']);
    }

    public function testCerrarCambiaEstadoAResueltaYRegistraFechaDeCierre(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $idIncidencia = $model->crearIncidencia($ci, 'rotura', null, null, 0);

        $model->cerrar($idIncidencia);

        $incidencia = $model->getIncidencia($idIncidencia);
        $this->assertSame('resuelta', $incidencia['estado']);
        $this->assertNotNull($incidencia['fecha_cierre']);
    }

    public function testCuadrillaExiste(): void
    {
        $model = new IncidenciaModel();
        $cuadrillaId = $this->crearCuadrilla();

        $this->assertTrue($model->cuadrillaExiste($cuadrillaId));
        $this->assertFalse($model->cuadrillaExiste(999999));
    }
}
