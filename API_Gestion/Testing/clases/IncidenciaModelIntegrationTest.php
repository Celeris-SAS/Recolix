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

    public function testCrearIncidenciaPersisteLaPrioridadRecibida(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();

        $id = $model->crearIncidencia($ci, 'rotura', null, null, 0, 'critica');

        $this->assertSame('critica', $model->getIncidencia($id)['prioridad']);
    }

    public function testContarIncidenciasAbiertasPorContenedorIgnoraLasResueltas(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();

        $resuelta = $model->crearIncidencia($ci, 'otro', null, null, 0);
        $model->vincularContenedor($resuelta, $contenedorId);
        $model->cerrar($resuelta);

        $abierta = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($abierta, $contenedorId);

        $asignada = $model->crearIncidencia($ci, 'desborde', null, null, 0);
        $model->vincularContenedor($asignada, $contenedorId);
        $model->asignarCuadrilla($asignada, $this->crearCuadrilla());

        $this->assertSame(2, $model->contarIncidenciasAbiertasPorContenedor($contenedorId));
    }

    public function testContarIncidenciasAbiertasPorContenedorEsCeroSinIncidencias(): void
    {
        $model = new IncidenciaModel();
        $contenedorId = $this->crearContenedor();

        $this->assertSame(0, $model->contarIncidenciasAbiertasPorContenedor($contenedorId));
    }


    public function testGetAllIncidenciasOcultaElReporteViejoDelMismoContenedorSinResolver(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();

        $vieja = $model->crearIncidencia($ci, 'rotura', 'Primer reporte', null, 0);
        $model->vincularContenedor($vieja, $contenedorId);

        $nueva = $model->crearIncidencia($ci, 'rotura', 'Segundo reporte', null, 0);
        $model->vincularContenedor($nueva, $contenedorId);

        $ids = array_column($model->getAllIncidencias(), 'id_incidencia');

        $this->assertNotContains($vieja, $ids);
        $this->assertContains($nueva, $ids);
    }

    public function testGetAllIncidenciasNoOcultaIncidenciasYaResueltas(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();

        $primera = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($primera, $contenedorId);
        $model->cerrar($primera);

        $segunda = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($segunda, $contenedorId);
        $model->cerrar($segunda);

        $ids = array_column($model->getAllIncidencias(), 'id_incidencia');

        $this->assertContains($primera, $ids);
        $this->assertContains($segunda, $ids);
    }

    public function testGetAllIncidenciasNoOcultaIncidenciasDeContenedoresDistintos(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorA = $this->crearContenedor();
        $contenedorB = $this->crearContenedor();

        $enA = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($enA, $contenedorA);

        $enB = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($enB, $contenedorB);

        $ids = array_column($model->getAllIncidencias(), 'id_incidencia');

        $this->assertContains($enA, $ids);
        $this->assertContains($enB, $ids);
    }

    public function testFiltrarEstadoOcultaElReporteViejoDelMismoContenedor(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();

        $vieja = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($vieja, $contenedorId);

        $nueva = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($nueva, $contenedorId);

        $resultado = $model->filtrarEstado('abierta');

        $this->assertCount(1, $resultado);
        $this->assertSame($nueva, (int) $resultado[0]['id_incidencia']);
    }

    public function testElReporteOcultoSigueContandoParaLaPrioridad(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();

        $vieja = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($vieja, $contenedorId);

        $this->assertSame(1, $model->contarIncidenciasAbiertasPorContenedor($contenedorId));

        $nueva = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($nueva, $contenedorId);

        $this->assertSame(2, $model->contarIncidenciasAbiertasPorContenedor($contenedorId));
    }

    public function testCerrarResuelveEnCascadaLasOtrasIncidenciasDelMismoContenedor(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();

        $vieja = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($vieja, $contenedorId);

        $nueva = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($nueva, $contenedorId);

        $model->cerrar($nueva);

        $this->assertSame('resuelta', $model->getIncidencia($vieja)['estado']);
        $this->assertNotNull($model->getIncidencia($vieja)['fecha_cierre']);
        $this->assertSame('resuelta', $model->getIncidencia($nueva)['estado']);

        $ids = array_column($model->getAllIncidencias(), 'id_incidencia');
        $this->assertContains($vieja, $ids);
        $this->assertContains($nueva, $ids);
    }

    public function testCerrarNoTocaIncidenciasDeOtroContenedor(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorA = $this->crearContenedor();
        $contenedorB = $this->crearContenedor();

        $enA = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($enA, $contenedorA);

        $enB = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($enB, $contenedorB);

        $model->cerrar($enA);

        $this->assertSame('resuelta', $model->getIncidencia($enA)['estado']);
        $this->assertSame('abierta', $model->getIncidencia($enB)['estado']);
    }

    public function testCerrarFuncionaConIncidenciaSinContenedorVinculado(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $id = $model->crearIncidencia($ci, 'rotura', null, null, 0);

        $model->cerrar($id);

        $this->assertSame('resuelta', $model->getIncidencia($id)['estado']);
    }

    public function testUsuarioTieneIncidenciaAbiertaEnContenedorDetectaElDuplicado(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();

        $this->assertFalse($model->usuarioTieneIncidenciaAbiertaEnContenedor($ci, $contenedorId));

        $id = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($id, $contenedorId);

        $this->assertTrue($model->usuarioTieneIncidenciaAbiertaEnContenedor($ci, $contenedorId));
    }

    public function testUsuarioTieneIncidenciaAbiertaEnContenedorIgnoraLasResueltas(): void
    {
        $model = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();

        $id = $model->crearIncidencia($ci, 'rotura', null, null, 0);
        $model->vincularContenedor($id, $contenedorId);
        $model->cerrar($id);

        $this->assertFalse($model->usuarioTieneIncidenciaAbiertaEnContenedor($ci, $contenedorId));
    }

    public function testUsuarioTieneIncidenciaAbiertaEnContenedorNoConfundeUsuariosDistintos(): void
    {
        $model = new IncidenciaModel();
        $ciA = $this->crearUsuario();
        $ciB = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();

        $id = $model->crearIncidencia($ciA, 'rotura', null, null, 0);
        $model->vincularContenedor($id, $contenedorId);

        $this->assertTrue($model->usuarioTieneIncidenciaAbiertaEnContenedor($ciA, $contenedorId));
        $this->assertFalse($model->usuarioTieneIncidenciaAbiertaEnContenedor($ciB, $contenedorId));
    }

    public function testCrearIncidenciaRechazaSegundoReporteDelMismoUsuarioSobreElMismoContenedor(): void
    {
        require_once __DIR__ . '/../../Controlador/incidenciaController.php';
        require_once __DIR__ . '/../../Modelo/contenedorModel.php';

        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();
        $_SESSION['ci_usuario'] = $ci;

        $controller = new IncidenciaController();
        $controller->crearIncidencia($contenedorId, 'rotura');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ya tenés un reporte');
        $controller->crearIncidencia($contenedorId, 'rotura');
    }
}
