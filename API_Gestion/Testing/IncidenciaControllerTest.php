<?php

require_once __DIR__ . '/../Controlador/incidenciaController.php';

/**
 * IncidenciaController valida todos sus argumentos antes de tocar el Model,
 * así que se puede instanciar sin constructor (sin IncidenciaModel/ContenedorModel
 * reales, sin conexión a MySQL) y ejercitar las validaciones directamente.
 */
final class IncidenciaControllerTest extends GestionTestCase
{
    private function controller(): IncidenciaController
    {
        return $this->newWithoutConstructor(IncidenciaController::class);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['ci_usuario']);
        parent::tearDown();
    }

    // --- validarId / validarEstado / validarTipo / validarPrioridad ---

    public function testValidarIdAceptaEnteroPositivo(): void
    {
        $controller = $this->controller();
        $this->assertSame(5, $this->callPrivate($controller, 'validarId', ['5']));
    }

    public function testValidarIdRechazaCero(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarId', [0]);
    }

    public function testValidarEstadoRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarEstado', ['flotando']);
    }

    public function testValidarTipoRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarTipo', ['inventado']);
    }

    public function testValidarPrioridadRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarPrioridad', ['urgentisima']);
    }

    // --- validarDescripcion ---

    public function testValidarDescripcionNullDevuelveNull(): void
    {
        $controller = $this->controller();
        $this->assertNull($this->callPrivate($controller, 'validarDescripcion', [null]));
    }

    public function testValidarDescripcionRecortaEspaciosYVaciaDevuelveNull(): void
    {
        $controller = $this->controller();
        $this->assertSame('Contenedor roto', $this->callPrivate($controller, 'validarDescripcion', ['  Contenedor roto  ']));
        $this->assertNull($this->callPrivate($controller, 'validarDescripcion', ['   ']));
    }

    public function testValidarDescripcionRechazaMasDe500Caracteres(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarDescripcion', [str_repeat('a', 501)]);
    }

    // --- validarImagen (RF07) ---

    public function testValidarImagenNullODevuelveNull(): void
    {
        $controller = $this->controller();
        $this->assertNull($this->callPrivate($controller, 'validarImagen', [null]));
        $this->assertNull($this->callPrivate($controller, 'validarImagen', ['']));
    }

    public function testValidarImagenRechazaFormatoQueNoEsDataUrlDeImagen(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JPG o PNG');
        $this->callPrivate($controller, 'validarImagen', ['no-es-una-imagen']);
    }

    public function testValidarImagenRechazaMimeNoSoportado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarImagen', ['data:image/gif;base64,' . base64_encode('x')]);
    }

    public function testValidarImagenAceptaJpegYDecodificaLosBytes(): void
    {
        $controller = $this->controller();
        $binario = 'contenido-de-prueba';
        $dataUrl = 'data:image/jpeg;base64,' . base64_encode($binario);

        $resultado = $this->callPrivate($controller, 'validarImagen', [$dataUrl]);

        $this->assertSame($binario, $resultado['datos']);
        $this->assertSame('jpg', $resultado['extension']);
    }

    public function testValidarImagenAceptaPngConExtensionCorrecta(): void
    {
        $controller = $this->controller();
        $dataUrl = 'data:image/png;base64,' . base64_encode('otro-contenido');

        $resultado = $this->callPrivate($controller, 'validarImagen', [$dataUrl]);

        $this->assertSame('png', $resultado['extension']);
    }

    public function testValidarImagenRechazaMasDe5Mb(): void
    {
        $controller = $this->controller();
        // 5 MB + 1 byte antes de codificar en base64.
        $binarioGrande = str_repeat('a', 5 * 1024 * 1024 + 1);
        $dataUrl = 'data:image/png;base64,' . base64_encode($binarioGrande);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('5 MB');
        $this->callPrivate($controller, 'validarImagen', [$dataUrl]);
    }

    // --- crearIncidencia (RF03/RF04/RF09) ---

    public function testCrearIncidenciaRechazaSinSesionIniciada(): void
    {
        unset($_SESSION['ci_usuario']);
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('iniciar sesión');
        $controller->crearIncidencia(1, 'rotura');
    }

    public function testCrearIncidenciaRechazaTipoInvalidoAntesDeTocarElModel(): void
    {
        $_SESSION['ci_usuario'] = 12345678;
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->crearIncidencia(1, 'incendio-planetario');
    }

    public function testCrearIncidenciaRechazaImagenInvalidaAntesDeTocarElModel(): void
    {
        $_SESSION['ci_usuario'] = 12345678;
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->crearIncidencia(1, 'rotura', null, 'no-es-una-imagen');
    }

    // No se puede ser "pesado": si el usuario ya tiene una incidencia suya sin
    // resolver sobre este mismo contenedor, se rechaza antes de crear nada nuevo.
    public function testCrearIncidenciaRechazaSegundoReporteDelMismoUsuarioSobreElMismoContenedor(): void
    {
        $_SESSION['ci_usuario'] = 12345678;
        $controller = $this->controller();

        $incidenciaModelStub = new class {
            public function usuarioExiste($ci) { return true; }
            public function usuarioTieneIncidenciaAbiertaEnContenedor($ci, $idContenedor) { return true; }
        };
        $contenedorModelStub = new class {
            public function getContenedor($id) { return ['id_contenedor' => $id, 'estado' => 'funcional']; }
        };
        $this->setPrivateProperty($controller, 'incidenciaModel', $incidenciaModelStub);
        $this->setPrivateProperty($controller, 'contenedorModel', $contenedorModelStub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ya tenés un reporte');
        $controller->crearIncidencia(1, 'rotura');
    }

    /**
     * RF09: la urgencia se calcula a partir del estado del contenedor, no la
     * decide el cliente. Se inyectan dobles de Model/Contenedor para llegar
     * hasta ese cálculo sin necesitar una base de datos real.
     *
     * @dataProvider estadosContenedorYUrgenciaEsperada
     */
    public function testCrearIncidenciaCalculaUrgenciaSegunEstadoDelContenedor(string $estadoContenedor, int $urgenciaEsperada): void
    {
        $_SESSION['ci_usuario'] = 12345678;
        $controller = $this->controller();

        $incidenciaModelStub = new class {
            public $urgenciaRecibida = null;
            public function usuarioExiste($ci) { return true; }
            public function usuarioTieneIncidenciaAbiertaEnContenedor($ci, $idContenedor) { return false; }
            public function contarIncidenciasAbiertasPorContenedor($idContenedor) { return 0; }
            public function crearIncidencia($ci, $tipo, $descripcion, $urlImagen, $urgencia, $prioridad) {
                $this->urgenciaRecibida = $urgencia;
                return 99;
            }
            public function vincularContenedor($idIncidencia, $idContenedor) {}
            public function getIncidencia($id) { return ['id_incidencia' => $id]; }
        };
        $contenedorModelStub = new class ($estadoContenedor) {
            private $estado;
            public function __construct($estado) { $this->estado = $estado; }
            public function getContenedor($id) { return ['id_contenedor' => $id, 'estado' => $this->estado]; }
        };

        $this->setPrivateProperty($controller, 'incidenciaModel', $incidenciaModelStub);
        $this->setPrivateProperty($controller, 'contenedorModel', $contenedorModelStub);

        $controller->crearIncidencia(1, 'rotura');

        $this->assertSame($urgenciaEsperada, $incidenciaModelStub->urgenciaRecibida);
    }

    public static function estadosContenedorYUrgenciaEsperada(): array
    {
        return [
            'roto es urgente' => ['roto', 1],
            'desbordado es urgente' => ['desbordado', 1],
            'inactivo es urgente' => ['inactivo', 1],
            'funcional no es urgente' => ['funcional', 0],
            'en_deposito no es urgente' => ['en_deposito', 0],
        ];
    }

    // --- Prioridad escalonada: no la elige el usuario, depende de cuántas
    // incidencias no resueltas ya tiene el contenedor (contando la nueva). ---

    /** @dataProvider cantidadesYPrioridadEsperada */
    public function testCalcularPrioridadEscalaSegunCantidad(int $cantidad, string $esperado): void
    {
        $controller = $this->controller();
        $this->assertSame($esperado, $this->callPrivate($controller, 'calcularPrioridad', [$cantidad]));
    }

    public static function cantidadesYPrioridadEsperada(): array
    {
        return [
            'una sola incidencia' => [1, 'baja'],
            'dos incidencias' => [2, 'media'],
            'justo antes del escalón a alta' => [4, 'media'],
            'cinco incidencias' => [5, 'alta'],
            'justo antes del escalón a critica' => [9, 'alta'],
            'diez incidencias' => [10, 'critica'],
            'muy por encima de diez' => [25, 'critica'],
        ];
    }

    public function testCrearIncidenciaCuentaLaPropiaIncidenciaAlEscalarLaPrioridad(): void
    {
        $_SESSION['ci_usuario'] = 12345678;
        $controller = $this->controller();

        $incidenciaModelStub = new class {
            public $prioridadRecibida = null;
            public function usuarioExiste($ci) { return true; }
            public function usuarioTieneIncidenciaAbiertaEnContenedor($ci, $idContenedor) { return false; }
            // El contenedor ya tiene 4 incidencias no resueltas: esta sería la 5ta -> 'alta'.
            public function contarIncidenciasAbiertasPorContenedor($idContenedor) { return 4; }
            public function crearIncidencia($ci, $tipo, $descripcion, $urlImagen, $urgencia, $prioridad) {
                $this->prioridadRecibida = $prioridad;
                return 99;
            }
            public function vincularContenedor($idIncidencia, $idContenedor) {}
            public function getIncidencia($id) { return ['id_incidencia' => $id]; }
        };
        $contenedorModelStub = new class {
            public function getContenedor($id) { return ['id_contenedor' => $id, 'estado' => 'funcional']; }
        };
        $this->setPrivateProperty($controller, 'incidenciaModel', $incidenciaModelStub);
        $this->setPrivateProperty($controller, 'contenedorModel', $contenedorModelStub);

        $controller->crearIncidencia(1, 'rotura');

        $this->assertSame('alta', $incidenciaModelStub->prioridadRecibida);
    }

    // --- asignarCuadrilla (RF10) ---

    public function testAsignarCuadrillaRechazaSiLaIncidenciaNoEstaAbierta(): void
    {
        $controller = $this->controller();
        $incidenciaModelStub = new class {
            public function getIncidencia($id) { return ['id_incidencia' => $id, 'estado' => 'asignada']; }
        };
        $this->setPrivateProperty($controller, 'incidenciaModel', $incidenciaModelStub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'abierta'");
        $controller->asignarCuadrilla(1, 5);
    }

    public function testAsignarCuadrillaRechazaCuadrillaInexistente(): void
    {
        $controller = $this->controller();
        $incidenciaModelStub = new class {
            public function getIncidencia($id) { return ['id_incidencia' => $id, 'estado' => 'abierta']; }
            public function cuadrillaExiste($id) { return false; }
        };
        $this->setPrivateProperty($controller, 'incidenciaModel', $incidenciaModelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->asignarCuadrilla(1, 999);
    }

    // --- cerrar (RF13) ---

    public function testCerrarRechazaSiLaIncidenciaNoEstaEnCurso(): void
    {
        $controller = $this->controller();
        $incidenciaModelStub = new class {
            public function getIncidencia($id) { return ['id_incidencia' => $id, 'estado' => 'abierta']; }
        };
        $this->setPrivateProperty($controller, 'incidenciaModel', $incidenciaModelStub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'en_curso'");
        $controller->cerrar(1);
    }

    public function testCerrarRechazaIdInexistente(): void
    {
        $controller = $this->controller();
        $incidenciaModelStub = new class {
            public function getIncidencia($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'incidenciaModel', $incidenciaModelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->cerrar(999);
    }

    public function testReglasValidacionExponeValoresValidos(): void
    {
        $controller = $this->controller();
        $reglas = $controller->reglasValidacion();

        $this->assertSame(['rotura', 'desborde', 'falta_recoleccion', 'vandalismo', 'otro'], $reglas['tipo']['valores']);
        $this->assertSame(['baja', 'media', 'alta', 'critica'], $reglas['prioridad']['valores']);
        $this->assertSame(['abierta', 'asignada', 'en_curso', 'resuelta'], $reglas['estado']['valores']);
    }
}
