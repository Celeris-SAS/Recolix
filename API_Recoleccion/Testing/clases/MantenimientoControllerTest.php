<?php

require_once __DIR__ . '/../../Controlador/mantenimientoController.php';

final class MantenimientoControllerTest extends RecoleccionTestCase
{
    private function controller(): MantenimientoController
    {
        return $this->newWithoutConstructor(MantenimientoController::class);
    }

    /** @dataProvider fechasValidas */
    public function testValidarFechaAceptaFormatosSoportados(string $entrada, string $esperado): void
    {
        $controller = $this->controller();
        $this->assertSame($esperado, $this->callPrivate($controller, 'validarFecha', [$entrada, 'Fecha']));
    }

    public static function fechasValidas(): array
    {
        return [
            'con segundos y T' => ['2026-03-05T08:30:15', '2026-03-05 08:30:15'],
            'sin segundos con T' => ['2026-03-05T08:30', '2026-03-05 08:30:00'],
            'con espacio y segundos' => ['2026-03-05 08:30:15', '2026-03-05 08:30:15'],
            'con espacio sin segundos' => ['2026-03-05 08:30', '2026-03-05 08:30:00'],
        ];
    }

    public function testValidarFechaSoloDiaCompletaConLaHoraActual(): void
    {
        $controller = $this->controller();
        $resultado = $this->callPrivate($controller, 'validarFecha', ['2026-03-05', 'Fecha']);
        $this->assertMatchesRegularExpression('/^2026-03-05 \d{2}:\d{2}:\d{2}$/', $resultado);
    }

    public function testValidarFechaRechazaTextoInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarFecha', ['no-es-una-fecha', 'Fecha']);
    }

    public function testValidarFechaRechazaVacio(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarFecha', ['', 'Fecha']);
    }

    public function testValidarDecimalRechazaNegativo(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarDecimal', [-0.01, 'Costo']);
    }

    public function testValidarDecimalAceptaCeroYPositivos(): void
    {
        $controller = $this->controller();
        $this->assertSame(0.0, $this->callPrivate($controller, 'validarDecimal', [0, 'Costo']));
        $this->assertSame(1500.5, $this->callPrivate($controller, 'validarDecimal', ['1500.5', 'Costo']));
    }

    public function testValidarDecimalRechazaNoNumerico(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarDecimal', ['gratis', 'Costo']);
    }

    public function testInsertMantenimientoRechazaTipoInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->insertMantenimiento(1, 'estetico', 'lavado', '2026-03-05', null, 0);
    }

    public function testProgramarRechazaTipoInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->programar(1, 'estetico', 'lavado', '2026-03-05', '2026-03-06');
    }

    public function testProximosConTipoVacioNoValida(): void
    {
        $controller = $this->controller();
        $stub = new class {
            public $tipoRecibido = 'sin-llamar';
            public function getProximos($tipo)
            {
                $this->tipoRecibido = $tipo;
                return [];
            }
        };
        $this->setPrivateProperty($controller, 'mantenimientoModel', $stub);

        $controller->proximos('');

        $this->assertNull($stub->tipoRecibido);
    }

    public function testProximosRechazaTipoInvalidoCuandoSeEnvia(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->proximos('estetico');
    }

    public function testReglasValidacionExponeTiposValidos(): void
    {
        $controller = $this->controller();
        $this->assertSame(['preventivo', 'correctivo'], $controller->reglasValidacion()['tipo']['valores']);
    }

    public function testValidarRangoFechasRechazaFinAnteriorAlInicio(): void
    {
        $controller = $this->controller();

        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarRangoFechas', ['2026-10-10 08:00:00', '2026-10-09 08:00:00']);
    }

    public function testValidarRangoFechasAceptaFinIgualOPosteriorYSinFin(): void
    {
        $controller = $this->controller();

        $this->callPrivate($controller, 'validarRangoFechas', ['2026-10-12 08:00:00', '2026-10-12 08:00:00']);
        $this->callPrivate($controller, 'validarRangoFechas', ['2026-10-12 08:00:00', '2026-10-13 08:00:00']);
        $this->callPrivate($controller, 'validarRangoFechas', ['2026-10-12 08:00:00', null]);
        $this->addToAssertionCount(3);
    }

    public function testInsertMantenimientoRechazaFinAnteriorAlInicioSinTocarLaBase(): void
    {
        $controller = $this->controller();

        $this->expectException(InvalidArgumentException::class);
        $controller->insertMantenimiento(1, 'preventivo', 'Cambio de aceite', '2026-10-10 08:00', '2026-10-09 08:00', 100);
    }
}
