<?php

require_once __DIR__ . '/../../Controlador/AccesoDenegadoException.php';
require_once __DIR__ . '/../../Controlador/incidenciaController.php';
require_once __DIR__ . '/../../Modelo/notificacionModel.php';

final class NotificacionesIntegrationTest extends DbTestCase
{
    private function crearIncidenciaDeVecino(int $ci, int $contenedorId, string $tipo = 'desborde'): int
    {
        $model = new IncidenciaModel();
        $id = $model->crearIncidencia($ci, $tipo, 'Test', null, 0);
        $model->vincularContenedor($id, $contenedorId);
        return $id;
    }

    private function integranteDeCuadrilla(int $idCuadrilla): int
    {
        $ci = $this->crearUsuario(null, ['rol' => 'cuadrilla']);
        $this->conn()->query("UPDATE usuario SET id_cuadrilla = $idCuadrilla WHERE ci_usuario = $ci");
        return $ci;
    }

    public function testAsignarCuadrillaNotificaTambienALaCuadrilla(): void
    {
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();
        $cuadrillaId = $this->crearCuadrilla();
        $integrante = $this->integranteDeCuadrilla($cuadrillaId);
        $id = $this->crearIncidenciaDeVecino($ci, $contenedorId);

        (new IncidenciaController())->asignarCuadrilla($id, $cuadrillaId);

        $this->assertSame(1, $this->contarFilas('NOTIFICACION_USUARIO', "ci_usuario = $integrante"));
        $this->assertStringContainsString("Se te asignó la incidencia #$id", $this->ultimoMensajeDe($integrante));
    }

    public function testAsignarCuadrillaNotificaAlVecinoQueReporto(): void
    {
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();
        $cuadrillaId = $this->crearCuadrilla();
        $id = $this->crearIncidenciaDeVecino($ci, $contenedorId);

        (new IncidenciaController())->asignarCuadrilla($id, $cuadrillaId);

        $this->assertSame(1, $this->contarFilas('NOTIFICACION_USUARIO', "ci_usuario = $ci"));
        $mensaje = $this->ultimoMensajeDe($ci);
        $this->assertStringContainsString("#$id", $mensaje);
        $this->assertStringContainsString('fue asignada a una cuadrilla', $mensaje);
    }

    private function loguearComoIntegranteDe(int $cuadrillaId): void
    {
        $ci = $this->crearUsuario(null, ['rol' => 'cuadrilla']);
        $this->conn()->query("UPDATE usuario SET id_cuadrilla = $cuadrillaId WHERE ci_usuario = $ci");
        $_SESSION['ci_usuario'] = $ci;
    }

    public function testIniciarAtencionNotificaAlVecino(): void
    {
        $ci = $this->crearUsuario();
        $contenedorId = $this->crearContenedor();
        $cuadrillaId = $this->crearCuadrilla();
        $id = $this->crearIncidenciaDeVecino($ci, $contenedorId);
        $controller = new IncidenciaController();
        $controller->asignarCuadrilla($id, $cuadrillaId);
        $this->loguearComoIntegranteDe($cuadrillaId);

        $controller->iniciarAtencion($id);

        $this->assertSame(2, $this->contarFilas('NOTIFICACION_USUARIO', "ci_usuario = $ci"));
        $this->assertStringContainsString('está siendo atendida', $this->ultimoMensajeDe($ci));
    }

    public function testCerrarNotificaATodosLosVecinosDelMismoContenedor(): void
    {
        $contenedorId = $this->crearContenedor();
        $cuadrillaId = $this->crearCuadrilla();
        $ciA = $this->crearUsuario();
        $ciB = $this->crearUsuario();
        $idA = $this->crearIncidenciaDeVecino($ciA, $contenedorId);
        $idB = $this->crearIncidenciaDeVecino($ciB, $contenedorId, 'rotura');
        $controller = new IncidenciaController();
        $controller->asignarCuadrilla($idA, $cuadrillaId);
        $this->loguearComoIntegranteDe($cuadrillaId);
        $controller->iniciarAtencion($idA);

        $controller->cerrar($idA, 10);

        $this->assertStringContainsString('fue resuelta', $this->ultimoMensajeDe($ciA));
        $this->assertStringContainsString('fue resuelta', $this->ultimoMensajeDe($ciB));
        $this->assertStringContainsString("#$idB", $this->ultimoMensajeDe($ciB));
    }

    public function testNotificarAdministradoresSoloLlegaAHabilitados(): void
    {
        $admin = $this->crearUsuario(null, ['rol' => 'administrador', 'acceso' => 'habilitado']);
        $pendiente = $this->crearUsuario(null, ['rol' => 'administrador', 'acceso' => 'pendiente']);
        $vecino = $this->crearUsuario(null, ['rol' => 'vecino', 'acceso' => 'habilitado']);

        (new NotificacionModel())->crearParaAdministradores('Prueba', null, 'advertencia');

        $this->assertSame(1, $this->contarFilas('NOTIFICACION_USUARIO', "ci_usuario = $admin"));
        $this->assertSame(0, $this->contarFilas('NOTIFICACION_USUARIO', "ci_usuario = $pendiente"));
        $this->assertSame(0, $this->contarFilas('NOTIFICACION_USUARIO', "ci_usuario = $vecino"));
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
