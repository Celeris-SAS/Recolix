<?php

require_once __DIR__ . '/../../Controlador/levanteController.php';

final class LevanteControllerTest extends RecoleccionTestCase
{
    private function controller(): LevanteController
    {
        return $this->newWithoutConstructor(LevanteController::class);
    }

    public function testValidarPesoAceptaNuloYVacioComoNulo(): void
    {
        $controller = $this->controller();
        $this->assertNull($this->callPrivate($controller, 'validarPeso', [null]));
        $this->assertNull($this->callPrivate($controller, 'validarPeso', ['']));
    }

    public function testValidarPesoRechazaNegativo(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarPeso', [-1]);
    }

    public function testValidarPesoAceptaCero(): void
    {
        $controller = $this->controller();
        $this->assertSame(0, $this->callPrivate($controller, 'validarPeso', [0]));
    }

    public function testValidarMotivoAceptaNuloYVacioComoNulo(): void
    {
        $controller = $this->controller();
        $this->assertNull($this->callPrivate($controller, 'validarMotivo', [null]));
        $this->assertNull($this->callPrivate($controller, 'validarMotivo', ['']));
    }

    public function testValidarMotivoRecortaTextoValido(): void
    {
        $controller = $this->controller();
        $this->assertSame('contenedor roto', $this->callPrivate($controller, 'validarMotivo', ['  contenedor roto  ']));
    }

    public function testValidarStringRechazaMasDe150Caracteres(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarString', [str_repeat('a', 151), 'Estado encontrado']);
    }

    public function testPlanificarRellenaFechasPorDefectoCuandoNoSeEnvian(): void
    {
        $controller = $this->controller();
        $stub = new class {
            public array $llamada = [];
            public function planificarLevante($rutaId, $camionId, $cuadrillaId, $horaSalida, $horaLevante)
            {
                $this->llamada = compact('rutaId', 'camionId', 'cuadrillaId', 'horaSalida', 'horaLevante');
                return 42;
            }
        };
        $this->setPrivateProperty($controller, 'levanteModel', $stub);

        $resultado = $controller->planificar(1, 2, 3, null, '');

        $this->assertSame(42, $resultado);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $stub->llamada['horaSalida']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $stub->llamada['horaLevante']);
    }

    public function testPlanificarRespetaFechasExplicitas(): void
    {
        $controller = $this->controller();
        $stub = new class {
            public array $llamada = [];
            public function planificarLevante($rutaId, $camionId, $cuadrillaId, $horaSalida, $horaLevante)
            {
                $this->llamada = compact('rutaId', 'camionId', 'cuadrillaId', 'horaSalida', 'horaLevante');
                return 42;
            }
        };
        $this->setPrivateProperty($controller, 'levanteModel', $stub);

        $controller->planificar(1, 2, 3, '2026-01-01 08:00:00', '2026-01-01');

        $this->assertSame('2026-01-01 08:00:00', $stub->llamada['horaSalida']);
        $this->assertSame('2026-01-01', $stub->llamada['horaLevante']);
    }

    public function testRegistrarContenedorRechazaPesoNegativo(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->registrarContenedor(1, 2, 'vaciado', -5);
    }

    public function testIniciarRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->iniciar('no-numero');
    }

    public function testCompletarRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->completar(0);
    }

    public function testCancelarRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->cancelar(-3);
    }

    public function testObtenerPorRutaRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->obtenerPorRuta(0);
    }

    public function testPlanificarPasaCentroIdValidadoAlModel(): void
    {
        $controller = $this->controller();
        $stub = new class {
            public array $llamada = [];
            public function planificarLevante($rutaId, $camionId, $cuadrillaId, $horaSalida, $horaLevante, $centroId = null)
            {
                $this->llamada = compact('rutaId', 'camionId', 'cuadrillaId', 'horaSalida', 'horaLevante', 'centroId');
                return 42;
            }
        };
        $this->setPrivateProperty($controller, 'levanteModel', $stub);

        $controller->planificar(1, 2, 3, '2026-01-01 08:00:00', '2026-01-01', '5');

        $this->assertSame(5, $stub->llamada['centroId']);
    }

    public function testPlanificarTrataCentroIdVacioComoNulo(): void
    {
        $controller = $this->controller();
        $stub = new class {
            public array $llamada = [];
            public function planificarLevante($rutaId, $camionId, $cuadrillaId, $horaSalida, $horaLevante, $centroId = null)
            {
                $this->llamada = compact('rutaId', 'camionId', 'cuadrillaId', 'horaSalida', 'horaLevante', 'centroId');
                return 42;
            }
        };
        $this->setPrivateProperty($controller, 'levanteModel', $stub);

        $controller->planificar(1, 2, 3, '2026-01-01 08:00:00', '2026-01-01');

        $this->assertNull($stub->llamada['centroId']);
    }

    public function testPlanificarRechazaCentroIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->planificar(1, 2, 3, '2026-01-01 08:00:00', '2026-01-01', 0);
    }
}
