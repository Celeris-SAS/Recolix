<?php

require_once __DIR__ . '/../../Controlador/solicitudRegistroController.php';

final class SolicitudRegistroControllerTest extends UsuarioTestCase
{
    private function controller(): SolicitudRegistroController
    {
        return $this->newWithoutConstructor(SolicitudRegistroController::class);
    }

    // --- validarCI ---

    public function testValidarCIRechazaMenosDeSieteDigitos(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarCI', ['123']);
    }

    // --- validarNombre ---

    public function testValidarNombreRechazaNumeros(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarNombre', ['Juan123', 'Nombre']);
    }

    // --- validarEmail ---

    public function testValidarEmailRechazaFormatoInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarEmail', ['no-es-un-email']);
    }

    // --- validarTel ---

    public function testValidarTelRechazaMenosDeNueveDigitos(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarTel', ['12345']);
    }

    // --- validarIdMunicipio ---

    public function testValidarIdMunicipioRechazaNoNumerico(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarIdMunicipio', ['abc']);
    }

    public function testValidarIdMunicipioAceptaEnteroPositivo(): void
    {
        $controller = $this->controller();
        $this->assertSame(3, $this->callPrivate($controller, 'validarIdMunicipio', ['3']));
    }

    // --- validarContrasenia ---

    public function testValidarContraseniaRechazaMenosDeOchoCaracteres(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarContrasenia', ['1234567']);
    }

    // --- reglasValidacion ---

    public function testReglasValidacionMarcaMunicipioComoOpcional(): void
    {
        $controller = $this->controller();
        $reglas = $controller->reglasValidacion();

        $this->assertTrue($reglas['idMunicipio']['opcional']);
    }

    // --- crearSolicitud ---

    public function testCrearSolicitudRechazaCiYaRegistrado(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function existePorCI($ci) { return true; }
        };
        $this->setPrivateProperty($controller, 'solicitudModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->crearSolicitud(12345678, 'Juan', 'Pérez', 'juan@recolix.uy', '099123456', 'claveTest123');
    }

    public function testCrearSolicitudAceptaMunicipioNuloPorSerOpcional(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function existePorCI($ci) { return false; }
            public function existePorEmail($email) { return false; }
            public function existePorTel($tel) { return false; }
            public function crearSolicitud($ci, $primerNombre, $pApellido, $email, $tel, $idMunicipio, $contrasenia) {
                return $idMunicipio;
            }
        };
        $this->setPrivateProperty($controller, 'solicitudModel', $modelStub);

        $resultado = $controller->crearSolicitud(12345678, 'Juan', 'Pérez', 'juan@recolix.uy', '099123456', 'claveTest123');

        $this->assertNull($resultado);
    }

    public function testCrearSolicitudRechazaMunicipioInvalidoCuandoSeEnvia(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function existePorCI($ci) { return false; }
        };
        $this->setPrivateProperty($controller, 'solicitudModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->crearSolicitud(12345678, 'Juan', 'Pérez', 'juan@recolix.uy', '099123456', 'claveTest123', 'no-es-un-id');
    }

    // --- aprobar / rechazar ---

    public function testAprobarRechazaCiInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->aprobar('abc');
    }

    public function testRechazarRechazaCiInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->rechazar('abc');
    }
}
