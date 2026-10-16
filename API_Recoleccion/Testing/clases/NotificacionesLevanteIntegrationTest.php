<?php

require_once __DIR__ . '/../../Modelo/levanteModel.php';

// Verifica contra la base real que los cambios de estado automáticos de las incidencias
// (al planificar, iniciar y recolectar una ruta) le llegan como notificación al vecino
// que las reportó (RF94/RF95).
final class NotificacionesLevanteIntegrationTest extends DbTestCase
{
    // Ruta con un contenedor, camión y cuadrilla listos, y una incidencia de $tipo sobre ese contenedor.
    private function escenario(string $tipo): array
    {
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1]);
        $contenedorId = $this->crearContenedor($circuitoId);
        $this->agregarContenedorARuta($rutaId, $contenedorId);
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();

        $ci = $this->crearUsuario();
        $idIncidencia = $this->crearIncidencia($ci, ['tipo' => $tipo, 'estado' => 'abierta']);
        $this->vincularIncidenciaContenedor($idIncidencia, $contenedorId);

        return [$ci, $idIncidencia, $rutaId, $camionId, $cuadrillaId, $contenedorId];
    }

    public function testPlanificarLevanteNotificaQueLaIncidenciaFueAsignada(): void
    {
        [$ci, $idIncidencia, $rutaId, $camionId, $cuadrillaId] = $this->escenario('desborde');

        (new LevanteModel())->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');

        $this->assertSame(1, $this->contarFilas('NOTIFICACION_USUARIO', "ci_usuario = $ci"));
        $mensaje = $this->ultimoMensajeDe($ci);
        $this->assertStringContainsString("#$idIncidencia", $mensaje);
        $this->assertStringContainsString('fue asignada a una cuadrilla', $mensaje);
    }

    public function testIniciarLevanteNotificaQueLaIncidenciaEstaSiendoAtendida(): void
    {
        [$ci, , $rutaId, $camionId, $cuadrillaId] = $this->escenario('falta_recoleccion');
        $model = new LevanteModel();
        $levante = $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');

        $model->iniciarLevante((int) $levante['id_levante']);

        $this->assertSame(2, $this->contarFilas('NOTIFICACION_USUARIO', "ci_usuario = $ci"));
        $this->assertStringContainsString('está siendo atendida', $this->ultimoMensajeDe($ci));
    }

    public function testRecolectarContenedorNotificaQueLaIncidenciaFueResuelta(): void
    {
        [$ci, , $rutaId, $camionId, $cuadrillaId, $contenedorId] = $this->escenario('desborde');
        $model = new LevanteModel();
        $levante = $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');
        $idLevante = (int) $levante['id_levante'];
        $model->iniciarLevante($idLevante);

        $model->registrarContenedor($idLevante, $contenedorId, 'todo_ok', 50, null);

        $this->assertSame(3, $this->contarFilas('NOTIFICACION_USUARIO', "ci_usuario = $ci"));
        $this->assertStringContainsString('fue resuelta', $this->ultimoMensajeDe($ci));
    }

    public function testUnaRoturaNoGeneraAvisosDeLaRuta(): void
    {
        // Las roturas no las atiende la cuadrilla: no cambian de estado con el levante.
        [$ci, , $rutaId, $camionId, $cuadrillaId] = $this->escenario('rotura');

        (new LevanteModel())->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');

        $this->assertSame(0, $this->contarFilas('NOTIFICACION_USUARIO', "ci_usuario = $ci"));
    }

    public function testElAvisoDeContenedorNoRecolectadoMuestraElEstadoLegible(): void
    {
        [, , $rutaId, $camionId, $cuadrillaId, $contenedorId] = $this->escenario('desborde');
        $admin = $this->crearUsuario(null, ['rol' => 'administrador', 'acceso' => 'habilitado']);
        $model = new LevanteModel();
        $idLevante = (int) $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10')['id_levante'];
        $model->iniciarLevante($idLevante);

        $model->registrarContenedor($idLevante, $contenedorId, 'acceso_bloqueado', null, null);

        $mensaje = $this->ultimoMensajeDe($admin);
        $this->assertStringContainsString('(acceso bloqueado)', $mensaje);
        $this->assertStringNotContainsString('acceso_bloqueado', $mensaje);
    }

    public function testElAvisoALaCuadrillaMuestraElTipoDeIncidenciaLegible(): void
    {
        [, , $rutaId, $camionId, $cuadrillaId] = $this->escenario('falta_recoleccion');
        $cuadrilla = $this->crearUsuario(null, ['rol' => 'cuadrilla']);
        $this->conn()->query("UPDATE usuario SET id_cuadrilla = $cuadrillaId WHERE ci_usuario = $cuadrilla");

        (new LevanteModel())->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');

        $mensaje = $this->ultimoMensajeDe($cuadrilla);
        $this->assertStringContainsString('(falta de recolección)', $mensaje);
        $this->assertStringNotContainsString('falta_recoleccion', $mensaje);
    }

    private function ultimoMensajeDe(int $ci): string
    {
        $stmt = $this->conn()->prepare(
            "SELECT n.mensaje FROM NOTIFICACION n
             JOIN NOTIFICACION_USUARIO nu ON nu.id_notificacion = n.id_notificacion
             WHERE nu.ci_usuario = ? ORDER BY n.id_notificacion DESC LIMIT 1"
        );
        $stmt->bind_param('i', $ci);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        return $fila['mensaje'] ?? '';
    }
}
