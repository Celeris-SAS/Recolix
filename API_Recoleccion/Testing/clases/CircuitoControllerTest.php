<?php

require_once __DIR__ . '/../../Controlador/circuitoController.php';

final class CircuitoControllerTest extends RecoleccionTestCase
{
    private function controller(): CircuitoController
    {
        return $this->newWithoutConstructor(CircuitoController::class);
    }

    public function testValidarPoligonoConNuloDevuelveNulo(): void
    {
        $controller = $this->controller();
        $this->assertNull($this->callPrivate($controller, 'validarPoligono', [null]));
        $this->assertNull($this->callPrivate($controller, 'validarPoligono', ['']));
    }

    public function testValidarPoligonoRechazaMenosDeTresPuntos(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarPoligono', [[[0, 0], [1, 1]]]);
    }

    public function testValidarPoligonoRechazaPuntoNoNumerico(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarPoligono', [[[0, 0], [1, 1], ['a', 'b']]]);
    }

    public function testValidarPoligonoNormalizaPuntosAFloat(): void
    {
        $controller = $this->controller();
        $resultado = $this->callPrivate($controller, 'validarPoligono', [[['0', '0'], ['1', '1'], ['2', '2']]]);
        $this->assertEquals([[0.0, 0.0], [1.0, 1.0], [2.0, 2.0]], json_decode($resultado, true));
    }

    public function testValidarPoligonoAceptaStringJson(): void
    {
        $controller = $this->controller();
        $json = json_encode([[0, 0], [1, 1], [2, 2]]);
        $resultado = $this->callPrivate($controller, 'validarPoligono', [$json]);
        $this->assertEquals([[0.0, 0.0], [1.0, 1.0], [2.0, 2.0]], json_decode($resultado, true));
    }

    public function testInsertCircuitoRechazaTurnoInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->insertCircuito(1, 'Circuito A', 'C-01', 'barrido', 'siesta', 'diaria');
    }

    public function testNormalizarTextoQuitaAcentosYPasaAMinusculas(): void
    {
        $controller = $this->controller();
        $this->assertSame('miercoles', $this->callPrivate($controller, 'normalizarTexto', ['MIÉRCOLES']));
        $this->assertSame('sabado', $this->callPrivate($controller, 'normalizarTexto', ['Sábado']));
    }

    public function testDiasDesdeFrecuenciaDetectaDiasSueltosMencionados(): void
    {
        $controller = $this->controller();
        $dias = $this->callPrivate($controller, 'diasDesdeFrecuencia', ['interdiaria (lunes, miércoles y viernes)']);
        sort($dias);
        $this->assertSame(['lunes', 'miercoles', 'viernes'], $dias);
    }

    public function testDiasDesdeFrecuenciaExpandeUnRango(): void
    {
        $controller = $this->controller();
        $dias = $this->callPrivate($controller, 'diasDesdeFrecuencia', ['diaria (lunes a viernes y feriados)']);
        sort($dias);
        $this->assertSame(['jueves', 'lunes', 'martes', 'miercoles', 'viernes'], $dias);
    }

    public function testDiasDesdeFrecuenciaExpandeRangoConWraparound(): void
    {
        $controller = $this->controller();
        // El rango "viernes a lunes" cruza el fin de la semana (viernes, sábado, domingo, lunes).
        $dias = $this->callPrivate($controller, 'diasDesdeFrecuencia', ['viernes a lunes']);
        sort($dias);
        $this->assertSame(['domingo', 'lunes', 'sabado', 'viernes'], $dias);
    }

    public function testFiltrarPorDiaRechazaDiaInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->filtrarPorDia('funday');
    }

    public function testFiltrarPorDiaUsaElModeloInyectadoParaFiltrar(): void
    {
        $controller = $this->controller();
        $stub = new class {
            public function getAllCircuitos(): array
            {
                return [
                    ['id_circuito' => 1, 'frecuencia' => 'lunes y jueves'],
                    ['id_circuito' => 2, 'frecuencia' => 'martes y viernes'],
                    ['id_circuito' => 3, 'frecuencia' => 'lunes a viernes'],
                ];
            }
        };
        $this->setPrivateProperty($controller, 'circuitoModel', $stub);

        $resultado = $controller->filtrarPorDia('jueves');

        $this->assertCount(2, $resultado);
        $ids = array_column($resultado, 'id_circuito');
        sort($ids);
        $this->assertSame([1, 3], $ids);
    }
}
