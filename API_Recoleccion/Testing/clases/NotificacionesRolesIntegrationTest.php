<?php

require_once __DIR__ . '/../../Modelo/levanteModel.php';
require_once __DIR__ . '/../../Controlador/rutaController.php';

// Verifica contra la base real los avisos a cuadrilla (ruta/contenedor asignado), a operario
// (centro de acopio asignado como destino) y al admin (contenedor no recolectado por el levante).
final class NotificacionesRolesIntegrationTest extends DbTestCase
{
    private function integranteDeCuadrilla(int $idCuadrilla): int
    {
        $ci = $this->crearUsuario(null, ['rol' => 'cuadrilla']);
        $this->conn()->query("UPDATE usuario SET id_cuadrilla = $idCuadrilla WHERE ci_usuario = $ci");
        return $ci;
    }

    private function operarioDeCentro(int $idCentro): int
    {
        $ci = $this->crearUsuario(null, ['rol' => 'operario']);
        $this->conn()->query("UPDATE usuario SET id_centro = $idCentro WHERE ci_usuario = $ci");
        return $ci;
    }

    private function contarAvisos(int $ci, string $fragmento): int
    {
        $like = '%' . $this->conn()->real_escape_string($fragmento) . '%';
        $stmt = $this->conn()->prepare(
            "SELECT COUNT(*) AS n FROM NOTIFICACION n
             JOIN NOTIFICACION_USUARIO nu ON nu.id_notificacion = n.id_notificacion
             WHERE nu.ci_usuario = ? AND n.mensaje LIKE ?"
        );
        $stmt->bind_param('is', $ci, $like);
        $stmt->execute();
        $n = (int) $stmt->get_result()->fetch_assoc()['n'];
        $stmt->close();
        return $n;
    }

    public function testPlanificarLevanteAvisaALaCuadrillaDeLaRutaAsignada(): void
    {
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1]);
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();
        $integrante = $this->integranteDeCuadrilla($cuadrillaId);

        (new LevanteModel())->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');

        $this->assertSame(1, $this->contarAvisos($integrante, "Se te asignó la ruta #$rutaId"));
    }

    public function testAsignarCuadrillaManualATambienAvisa(): void
    {
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1]);
        $cuadrillaId = $this->crearCuadrilla();
        $integrante = $this->integranteDeCuadrilla($cuadrillaId);

        (new RutaController())->asignarCuadrilla($rutaId, $cuadrillaId);

        $this->assertSame(1, $this->contarAvisos($integrante, "Se te asignó la ruta #$rutaId"));
    }

    public function testPlanificarLevanteAvisaALaCuadrillaDelContenedorConIncidenciaAsignada(): void
    {
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1]);
        $contenedorId = $this->crearContenedor($circuitoId);
        $this->agregarContenedorARuta($rutaId, $contenedorId);
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();
        $integrante = $this->integranteDeCuadrilla($cuadrillaId);
        $ciVecino = $this->crearUsuario();
        $idIncidencia = $this->crearIncidencia($ciVecino, ['tipo' => 'desborde', 'estado' => 'abierta']);
        $this->vincularIncidenciaContenedor($idIncidencia, $contenedorId);

        (new LevanteModel())->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');

        $this->assertSame(1, $this->contarAvisos($integrante, "Se te asignó el contenedor #$contenedorId"));
    }

    public function testPlanificarLevanteConDestinoAvisaAlOperarioDelCentro(): void
    {
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1]);
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();
        $centroId = $this->crearCentro(['estado' => 'operativo']);
        $operario = $this->operarioDeCentro($centroId);

        $levante = (new LevanteModel())->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10', $centroId);

        $this->assertSame(1, $this->contarAvisos($operario, "fue asignado como destino del levante #{$levante['id_levante']}"));
    }

    public function testRegistrarContenedorNoRecolectadoAvisaAlAdministrador(): void
    {
        $admin = $this->crearUsuario(null, ['rol' => 'administrador', 'acceso' => 'habilitado']);
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1]);
        $contenedorId = $this->crearContenedor($circuitoId);
        $this->agregarContenedorARuta($rutaId, $contenedorId);
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();
        $model = new LevanteModel();
        $levante = $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');
        $idLevante = (int) $levante['id_levante'];
        $model->iniciarLevante($idLevante);

        $model->registrarContenedor($idLevante, $contenedorId, 'acceso_bloqueado', null, 'Portón con candado');

        $this->assertSame(1, $this->contarAvisos($admin, "no pudo recolectar el contenedor #$contenedorId"));
    }
}
