<?php

require_once __DIR__ . '/../../Controlador/levanteController.php';
require_once __DIR__ . '/../../Controlador/rutaController.php';

// Una cuadrilla solo puede ver y operar sobre sus propios levantes y rutas; el administrador sobre todos.
final class CuadrillaPropiaIntegrationTest extends DbTestCase
{
    private int $cuadrillaA;
    private int $cuadrillaB;
    private int $ciA;
    private int $ciAdmin;
    private int $levanteA;
    private int $levanteB;

    protected function setUp(): void
    {
        parent::setUp();
        $levanteModel = new LevanteModel();
        $this->cuadrillaA = $this->crearCuadrilla();
        $this->cuadrillaB = $this->crearCuadrilla();
        $this->ciA = $this->crearUsuario(null, ['rol' => 'cuadrilla']);
        $this->vincularUsuarioACuadrilla($this->ciA, $this->cuadrillaA);
        $this->ciAdmin = $this->crearUsuario(null, ['rol' => 'administrador']);

        $this->levanteA = $this->planificar($levanteModel, 'AAA1111', $this->cuadrillaA);
        $this->levanteB = $this->planificar($levanteModel, 'BBB2222', $this->cuadrillaB);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['usuario'], $_SESSION['ci_usuario']);
        parent::tearDown();
    }

    private function vincularUsuarioACuadrilla(int $ci, int $idCuadrilla): void
    {
        $stmt = $this->conn()->prepare('UPDATE usuario SET id_cuadrilla = ? WHERE ci_usuario = ?');
        $stmt->bind_param('ii', $idCuadrilla, $ci);
        $stmt->execute();
        $stmt->close();
    }

    private function planificar(LevanteModel $model, string $matricula, int $cuadrillaId): int
    {
        $rutaId = $this->crearRuta($this->crearCircuito(), ['activa' => 1]);
        $this->agregarContenedorARuta($rutaId, $this->crearContenedor(), 1);
        $camionId = $this->crearCamion(['matricula' => $matricula, 'estado' => 'disponible']);
        return (int) $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10')['id_levante'];
    }

    private function iniciarSesion(string $rol, int $ci): void
    {
        $_SESSION['usuario'] = ['rol' => $rol, 'ci_usuario' => $ci];
    }

    public function testCuadrillaVeSuPropioLevante(): void
    {
        $this->iniciarSesion('cuadrilla', $this->ciA);

        $levante = (new LevanteController())->getLevante($this->levanteA);

        $this->assertSame($this->levanteA, (int) $levante['id_levante']);
    }

    public function testCuadrillaNoVeElLevanteDeOtra(): void
    {
        $this->iniciarSesion('cuadrilla', $this->ciA);
        $this->expectException(AccesoDenegadoException::class);
        (new LevanteController())->getLevante($this->levanteB);
    }

    public function testCuadrillaNoPuedeIniciarElLevanteDeOtra(): void
    {
        $this->iniciarSesion('cuadrilla', $this->ciA);
        try {
            (new LevanteController())->iniciar($this->levanteB);
            $this->fail('Debió rechazar iniciar un levante ajeno');
        } catch (AccesoDenegadoException $e) {
            $this->assertSame(1, $this->contarFilas('levante', "id_levante = {$this->levanteB} AND estado = 'pendiente'"));
        }
    }

    public function testCuadrillaNoPuedeRegistrarContenedoresEnElLevanteDeOtra(): void
    {
        $this->iniciarSesion('cuadrilla', $this->ciA);
        $this->expectException(AccesoDenegadoException::class);
        (new LevanteController())->registrarContenedor($this->levanteB, 1, 'todo_ok', 10);
    }

    public function testCuadrillaNoPuedeCompletarElLevanteDeOtra(): void
    {
        $this->iniciarSesion('cuadrilla', $this->ciA);
        $this->expectException(AccesoDenegadoException::class);
        (new LevanteController())->completar($this->levanteB);
    }

    public function testCuadrillaNoVeElLevanteActivoNiLosCompletadosDeOtra(): void
    {
        $this->iniciarSesion('cuadrilla', $this->ciA);
        $controller = new LevanteController();
        foreach (['obtenerActivoPorCuadrilla', 'obtenerCompletadosPorCuadrilla'] as $metodo) {
            try {
                $controller->$metodo($this->cuadrillaB);
                $this->fail("$metodo debió rechazar una cuadrilla ajena");
            } catch (AccesoDenegadoException $e) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testCuadrillaNoVeLasRutasDeOtra(): void
    {
        $this->iniciarSesion('cuadrilla', $this->ciA);
        $this->expectException(AccesoDenegadoException::class);
        (new RutaController())->obtenerPorCuadrilla($this->cuadrillaB);
    }

    public function testCuadrillaVeSusPropiasRutas(): void
    {
        $this->iniciarSesion('cuadrilla', $this->ciA);

        $this->assertIsArray((new RutaController())->obtenerPorCuadrilla($this->cuadrillaA));
    }

    public function testListadoDeLevantesDeUnaCuadrillaSoloTraeLosPropios(): void
    {
        $this->iniciarSesion('cuadrilla', $this->ciA);

        $ids = array_map('intval', array_column((new LevanteController())->getAllLevantes(), 'id_levante'));

        $this->assertSame([$this->levanteA], $ids);
    }

    public function testAdministradorOperaSobreCualquierLevante(): void
    {
        $this->iniciarSesion('administrador', $this->ciAdmin);
        $controller = new LevanteController();

        $this->assertSame($this->levanteB, (int) $controller->getLevante($this->levanteB)['id_levante']);
        $this->assertCount(2, $controller->getAllLevantes());
        $this->assertIsArray((new RutaController())->obtenerPorCuadrilla($this->cuadrillaB));
    }
}
