<?php

require_once __DIR__ . '/../../Controlador/incidenciaController.php';

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
        $binarioGrande = str_repeat('a', 5 * 1024 * 1024 + 1);
        $dataUrl = 'data:image/png;base64,' . base64_encode($binarioGrande);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('5 MB');
        $this->callPrivate($controller, 'validarImagen', [$dataUrl]);
    }


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

    /** @dataProvider estadosContenedorYUrgenciaEsperada */
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

    public function testCrearIncidenciaCriticaNotificaAAdministradores(): void
    {
        $_SESSION['ci_usuario'] = 12345678;
        $controller = $this->controller();

        $incidenciaModelStub = new class {
            public function usuarioExiste($ci) { return true; }
            public function usuarioTieneIncidenciaAbiertaEnContenedor($ci, $idContenedor) { return false; }
            public function contarIncidenciasAbiertasPorContenedor($idContenedor) { return 9; }
            public function crearIncidencia($ci, $tipo, $descripcion, $urlImagen, $urgencia, $prioridad) { return 99; }
            public function vincularContenedor($idIncidencia, $idContenedor) {}
            public function getIncidencia($id) { return ['id_incidencia' => $id]; }
        };
        $contenedorModelStub = new class {
            public function getContenedor($id) { return ['id_contenedor' => $id, 'estado' => 'funcional']; }
        };
        $notificacionStub = new class {
            public $llamadas = [];
            public function crearParaAdministradores($mensaje, $tipoOrigen, $prioridad) {
                $this->llamadas[] = [$mensaje, $tipoOrigen, $prioridad];
                return 1;
            }
        };
        $this->setPrivateProperty($controller, 'incidenciaModel', $incidenciaModelStub);
        $this->setPrivateProperty($controller, 'contenedorModel', $contenedorModelStub);
        $this->setPrivateProperty($controller, 'notificacionModel', $notificacionStub);

        $controller->crearIncidencia(1, 'rotura');

        $this->assertCount(1, $notificacionStub->llamadas);
        [$mensaje, $tipoOrigen, $prioridad] = $notificacionStub->llamadas[0];
        $this->assertSame('incidencia', $tipoOrigen);
        $this->assertSame('critica', $prioridad);
        $this->assertStringContainsString('crítica', $mensaje);
    }


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


    public function testCerrarRechazaSiLaIncidenciaNoEstaEnCurso(): void
    {
        $controller = $this->controller();
        $incidenciaModelStub = new class {
            public function getIncidencia($id) { return ['id_incidencia' => $id, 'estado' => 'abierta']; }
        };
        $this->setPrivateProperty($controller, 'incidenciaModel', $incidenciaModelStub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'en_curso'");
        $controller->cerrar(1, 10);
    }

    public function testCerrarRechazaIdInexistente(): void
    {
        $controller = $this->controller();
        $incidenciaModelStub = new class {
            public function getIncidencia($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'incidenciaModel', $incidenciaModelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->cerrar(999, 10);
    }

    // ERR-07: "Total recolectado (Kg)" es obligatorio y debe ser mayor a cero.
    public function testCerrarRechazaPesoNoPositivo(): void
    {
        $controller = $this->controller();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("mayor a cero");
        $controller->cerrar(1, 0);
    }

    public function testCerrarRechazaPesoNoNumerico(): void
    {
        $controller = $this->controller();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("mayor a cero");
        $controller->cerrar(1, 'abc');
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
