<?php

require_once __DIR__ . '/../../Modelo/mantenimientoModel.php';
require_once __DIR__ . '/../../Controlador/mantenimientoController.php';

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

    // RF37: la cuadrilla reporta un desperfecto y el administrador recibe la notificación.
    public function testNotificarDesperfectoAvisaAlAdministrador(): void
    {
        $admin = $this->crearUsuario(null, ['rol' => 'administrador', 'acceso' => 'habilitado']);
        $camionId = $this->crearCamion();

        (new MantenimientoController())->notificarDesperfecto($camionId, 'Pérdida de frenos');

        $like = '%Pérdida de frenos%';
        $stmt = $this->conn()->prepare(
            "SELECT COUNT(*) AS n FROM NOTIFICACION n
             JOIN NOTIFICACION_USUARIO nu ON nu.id_notificacion = n.id_notificacion
             WHERE nu.ci_usuario = ? AND n.mensaje LIKE ?"
        );
        $stmt->bind_param('is', $admin, $like);
        $stmt->execute();
        $n = (int) $stmt->get_result()->fetch_assoc()['n'];
        $stmt->close();

        $this->assertSame(1, $n);
    }

    // Un desperfecto deja al camión en mantenimiento: el levante en curso se cancela y la ruta
    // no queda como completada, con los contenedores sin recolectar.
    public function testRegistrarMantenimientoCancelaElLevanteEnCursoDelCamion(): void
    {
        require_once __DIR__ . '/../../Modelo/levanteModel.php';
        $levanteModel = new LevanteModel();
        $rutaId = $this->crearRuta($this->crearCircuito(), ['activa' => 1]);
        $contenedorId = $this->crearContenedor();
        $this->agregarContenedorARuta($rutaId, $contenedorId, 1);
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();
        $idLevante = (int) $levanteModel->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10')['id_levante'];
        $levanteModel->iniciarLevante($idLevante);

        (new MantenimientoController())->insertMantenimiento($camionId, 'correctivo', 'Falla de motor', '2026-01-10 09:00:00', null, 0);

        $this->assertSame(1, $this->contarFilas('levante', "id_levante = $idLevante AND estado = 'cancelado'"));
        $this->assertSame(1, $this->contarFilas('camion', "id_camion = $camionId AND estado = 'en_mantenimiento'"));
        $this->assertSame(0, $this->contarFilas('contenedor_levante', "id_levante = $idLevante AND hora_vaciado IS NOT NULL"));
    }

    public function testRegistrarMantenimientoNoTocaLevantesDeOtrosCamiones(): void
    {
        require_once __DIR__ . '/../../Modelo/levanteModel.php';
        $levanteModel = new LevanteModel();
        $rutaId = $this->crearRuta($this->crearCircuito(), ['activa' => 1]);
        $camionConLevante = $this->crearCamion(['matricula' => 'AAA1111', 'estado' => 'disponible']);
        $camionAveriado = $this->crearCamion(['matricula' => 'BBB2222', 'estado' => 'disponible']);
        $idLevante = (int) $levanteModel->planificarLevante($rutaId, $camionConLevante, $this->crearCuadrilla(), '2026-01-10 08:00:00', '2026-01-10')['id_levante'];

        (new MantenimientoController())->insertMantenimiento($camionAveriado, 'correctivo', 'Falla de frenos', '2026-01-10 09:00:00', null, 0);

        $this->assertSame(1, $this->contarFilas('levante', "id_levante = $idLevante AND estado = 'pendiente'"));
    }
}
