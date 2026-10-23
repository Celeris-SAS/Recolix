<?php

require_once __DIR__ . '/../../Controlador/UsuarioController.php';

final class UsuarioControllerTest extends UsuarioTestCase
{
    private function controller(): UsuarioController
    {
        return $this->newWithoutConstructor(UsuarioController::class);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['ci_usuario'], $_SESSION['usuario']);
        parent::tearDown();
    }


    public function testValidarCIAceptaCedulaDeOchoDigitos(): void
    {
        $controller = $this->controller();
        $this->assertSame(12345678, $this->callPrivate($controller, 'validarCI', ['12345678']));
    }

    public function testValidarCIRechazaNoNumerico(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarCI', ['abc']);
    }

    public function testValidarCIRechazaMenosDeSieteDigitos(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarCI', ['12345']);
    }

    public function testValidarCIRechazaMasDeOchoDigitos(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarCI', ['123456789']);
    }


    public function testValidarNombreAceptaLetrasYEspacios(): void
    {
        $controller = $this->controller();
        $this->assertSame('María José', $this->callPrivate($controller, 'validarNombre', [' María José ', 'Nombre']));
    }

    public function testValidarNombreRechazaVacio(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarNombre', ['  ', 'Nombre']);
    }

    public function testValidarNombreRechazaNumeros(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarNombre', ['Juan123', 'Nombre']);
    }


    public function testValidarEmailAceptaFormatoValido(): void
    {
        $controller = $this->controller();
        $this->assertSame('test@recolix.uy', $this->callPrivate($controller, 'validarEmail', [' test@recolix.uy ']));
    }

    public function testValidarEmailRechazaFormatoInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarEmail', ['no-es-un-email']);
    }


    public function testValidarTelAceptaNueveDigitos(): void
    {
        $controller = $this->controller();
        $this->assertSame('099123456', $this->callPrivate($controller, 'validarTel', ['099123456']));
    }

    public function testValidarTelRechazaConGuiones(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarTel', ['099-123-456']);
    }

    public function testValidarTelRechazaMenosDeNueveDigitos(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarTel', ['12345']);
    }


    public function testValidarContraseniaRechazaMenosDeOchoCaracteres(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarContrasenia', ['abc123']);
    }


    public function testValidarIdRechazaCero(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarId', [0]);
    }

    public function testValidarRolRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarRol', ['superadmin']);
    }

    public function testValidarAccesoRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarAcceso', ['bloqueado']);
    }

    public function testValidarMunicipioRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarMunicipio', ['Municipio Inventado']);
    }


    public function testVerificarPropioCIPermiteAlMismoUsuario(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 12345678;
        $this->callPrivate($controller, 'verificarPropioCI', [12345678]);
        $this->addToAssertionCount(1);
    }

    public function testVerificarPropioCIRechazaAOtroUsuario(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 11111111;
        $this->expectException(AccesoDenegadoException::class);
        $this->callPrivate($controller, 'verificarPropioCI', [22222222]);
    }

    public function testVerificarAccesoPerfilPermiteARolNoVecino(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 11111111;
        $_SESSION['usuario'] = ['rol' => 'administrador'];
        $this->callPrivate($controller, 'verificarAccesoPerfil', [22222222]);
        $this->addToAssertionCount(1);
    }

    public function testVerificarAccesoPerfilRechazaAVecinoQueMiraOtroPerfil(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 11111111;
        $_SESSION['usuario'] = ['rol' => 'vecino'];
        $this->expectException(AccesoDenegadoException::class);
        $this->callPrivate($controller, 'verificarAccesoPerfil', [22222222]);
    }


    /** @dataProvider rolesInternosNoAdministradores */
    public function testVerificarAccesoPerfilRechazaAlPersonalNoAdministradorQueMiraOtroPerfil(string $rol): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 11111111;
        $_SESSION['usuario'] = ['rol' => $rol];
        $this->expectException(AccesoDenegadoException::class);
        $this->callPrivate($controller, 'verificarAccesoPerfil', [22222222]);
    }

    /** @dataProvider rolesInternosNoAdministradores */
    public function testVerificarAccesoPerfilPermiteAlPersonalVerSuPropioPerfil(string $rol): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 11111111;
        $_SESSION['usuario'] = ['rol' => $rol];
        $this->callPrivate($controller, 'verificarAccesoPerfil', [11111111]);
        $this->addToAssertionCount(1);
    }

    /** @dataProvider rolesInternosNoAdministradores */
    public function testListarUsuariosRechazaAlPersonalNoAdministrador(string $rol): void
    {
        $controller = $this->controller();
        $_SESSION['usuario'] = ['rol' => $rol];
        $this->expectException(AccesoDenegadoException::class);
        $controller->listarUsuarios();
    }

    public function testListarUsuariosPermiteAlAdministrador(): void
    {
        $controller = $this->controller();
        $_SESSION['usuario'] = ['rol' => 'administrador'];
        $this->setPrivateProperty($controller, 'usuarioModel', new class {
            public function listarUsuarios() { return [['ci_usuario' => 1]]; }
        });
        $this->assertCount(1, $controller->listarUsuarios());
    }

    public static function rolesInternosNoAdministradores(): array
    {
        return ['operario' => ['operario'], 'cuadrilla' => ['cuadrilla']];
    }


    public function testReglasValidacionExponeValoresValidos(): void
    {
        $controller = $this->controller();
        $reglas = $controller->reglasValidacion();

        $this->assertSame(['administrador', 'cuadrilla', 'operario', 'vecino'], $reglas['rol']['valores']);
        $this->assertSame(['pendiente', 'habilitado', 'deshabilitado'], $reglas['acceso']['valores']);
    }


    public function testLoginRechazaContraseniaVacia(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->login(12345678, '  ');
    }

    public function testLoginRechazaCredencialesIncorrectas(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function login($ci, $contrasenia) { return null; }
        };
        $this->setPrivateProperty($controller, 'usuarioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->login(12345678, 'claveTest123');
    }

    public function testLoginRechazaCuentaDeshabilitada(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function login($ci, $contrasenia) { return ['acceso' => 'deshabilitado']; }
        };
        $this->setPrivateProperty($controller, 'usuarioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('deshabilitada');
        $controller->login(12345678, 'claveTest123');
    }

    public function testLoginRechazaCuentaPendiente(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function login($ci, $contrasenia) { return ['acceso' => 'pendiente']; }
        };
        $this->setPrivateProperty($controller, 'usuarioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('pendiente');
        $controller->login(12345678, 'claveTest123');
    }

    public function testLoginDevuelveElUsuarioConAccesoHabilitado(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function login($ci, $contrasenia) { return ['ci_usuario' => 12345678, 'acceso' => 'habilitado']; }
        };
        $this->setPrivateProperty($controller, 'usuarioModel', $modelStub);

        $usuario = $controller->login(12345678, 'claveTest123');
        $this->assertSame(12345678, $usuario['ci_usuario']);
    }


    public function testGetPerfilRechazaCiInexistente(): void
    {
        $controller = $this->controller();
        $_SESSION['usuario'] = ['rol' => 'administrador'];
        $modelStub = new class {
            public function getPerfil($ci) { return null; }
        };
        $this->setPrivateProperty($controller, 'usuarioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->getPerfil(99999999);
    }


    public function testUpdatePerfilRechazaSiElUsuarioNoExiste(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 12345678;
        $modelStub = new class {
            public function getPerfil($ci) { return null; }
        };
        $this->setPrivateProperty($controller, 'usuarioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->updatePerfil(12345678, 'test@recolix.uy', '099123456');
    }

    public function testUpdatePerfilRechazaAccesoAPerfilAjeno(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 11111111;
        $_SESSION['usuario'] = ['rol' => 'vecino'];

        $this->expectException(AccesoDenegadoException::class);
        $controller->updatePerfil(22222222, 'test@recolix.uy', '099123456');
    }


    public function testCambiarContraseniaRechazaContraseniaActualIncorrecta(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 12345678;
        $modelStub = new class {
            public function getContrasenia($ci) { return password_hash('otraClave', PASSWORD_DEFAULT); }
        };
        $this->setPrivateProperty($controller, 'usuarioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->cambiarContrasenia(12345678, 'claveIncorrecta', 'nuevaClave123');
    }

    public function testCambiarContraseniaRechazaCiAjeno(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 11111111;

        $this->expectException(AccesoDenegadoException::class);
        $controller->cambiarContrasenia(22222222, 'claveActual123', 'nuevaClave123');
    }


    public function testGetIncidenciasRechazaCiAjeno(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 11111111;

        $this->expectException(AccesoDenegadoException::class);
        $controller->getIncidencias(22222222);
    }

    public function testGetNotificacionesRechazaCiAjeno(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 11111111;

        $this->expectException(AccesoDenegadoException::class);
        $controller->getNotificaciones(22222222);
    }

    public function testMarcarNotificacionLeidaRechazaSinSesion(): void
    {
        $controller = $this->controller();
        unset($_SESSION['ci_usuario']);

        $this->expectException(AccesoDenegadoException::class);
        $controller->marcarNotificacionLeida(1);
    }


    public function testFiltrarRolRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $_SESSION['usuario'] = ['rol' => 'administrador'];
        $this->expectException(InvalidArgumentException::class);
        $controller->filtrarRol('superadmin');
    }

    public function testFiltrarAccesoRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $_SESSION['usuario'] = ['rol' => 'administrador'];
        $this->expectException(InvalidArgumentException::class);
        $controller->filtrarAcceso('bloqueado');
    }


    public function testCambiarRolRechazaCiInexistente(): void
    {
        $controller = $this->controller();
        $_SESSION['usuario'] = ['rol' => 'administrador'];
        $modelStub = new class {
            public function getPerfil($ci) { return null; }
        };
        $this->setPrivateProperty($controller, 'usuarioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->cambiarRol(99999999, 'operario');
    }

    public function testCambiarAccesoRechazaCiInexistente(): void
    {
        $controller = $this->controller();
        $_SESSION['usuario'] = ['rol' => 'administrador'];
        $modelStub = new class {
            public function getPerfil($ci) { return null; }
        };
        $this->setPrivateProperty($controller, 'usuarioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->cambiarAcceso(99999999, 'habilitado');
    }


    public function testCrearUsuarioRechazaCiYaExistente(): void
    {
        $controller = $this->controller();
        $_SESSION['usuario'] = ['rol' => 'administrador'];
        $modelStub = new class {
            public function existeCi($ci) { return true; }
        };
        $this->setPrivateProperty($controller, 'usuarioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->crearUsuario(12345678, 'Juan', 'Pérez', 'juan@recolix.uy', '099123456', 'claveTest123', 'vecino', 1);
    }


    public function testSetMunicipioRechazaMunicipioInvalido(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 12345678;

        $this->expectException(InvalidArgumentException::class);
        $controller->setMunicipio(12345678, 'Municipio Inventado');
    }

    public function testSetMunicipioRechazaAccesoAPerfilAjeno(): void
    {
        $controller = $this->controller();
        $_SESSION['ci_usuario'] = 11111111;
        $_SESSION['usuario'] = ['rol' => 'vecino'];

        $this->expectException(AccesoDenegadoException::class);
        $controller->setMunicipio(22222222, 'Municipio A');
    }
}
