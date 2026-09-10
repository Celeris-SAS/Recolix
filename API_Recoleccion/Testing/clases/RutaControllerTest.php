<?php

require_once __DIR__ . '/../../Controlador/rutaController.php';

final class RutaControllerTest extends RecoleccionTestCase
{
    private function controller(): RutaController
    {
        return $this->newWithoutConstructor(RutaController::class);
    }

    public function testValidarOrdenVisitaRechazaMenorAUno(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarOrdenVisita', [0]);
    }

    public function testValidarOrdenVisitaAceptaUno(): void
    {
        $controller = $this->controller();
        $this->assertSame(1, $this->callPrivate($controller, 'validarOrdenVisita', [1]));
    }

    public function testCalcularBaseUsaCentroideDelPoligonoSiEsta(): void
    {
        $controller = $this->controller();
        $circuito = ['poligono' => json_encode([[0, 0], [0, 2], [2, 0], [2, 2]])];
        $base = $this->callPrivate($controller, 'calcularBase', [$circuito, []]);
        $this->assertEqualsWithDelta([1.0, 1.0], $base, 0.0001);
    }

    public function testCalcularBaseUsaPromedioDeContenedoresSinPoligono(): void
    {
        $controller = $this->controller();
        $circuito = ['poligono' => null];
        $contenedores = [
            ['latitud' => '0', 'longitud' => '0'],
            ['latitud' => '2', 'longitud' => '4'],
        ];
        $base = $this->callPrivate($controller, 'calcularBase', [$circuito, $contenedores]);
        $this->assertEqualsWithDelta([1.0, 2.0], $base, 0.0001);
    }

    public function testDeleteRutaRechazaSiTieneLevantesAsignados(): void
    {
        $controller = $this->controller();
        $stub = new class {
            public function obtenerLevantes($id)
            {
                return [['id_levante' => 1]];
            }
            public function deleteRuta($id)
            {
                throw new RuntimeException('no debería llegar acá');
            }
        };
        $this->setPrivateProperty($controller, 'rutaModel', $stub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No se puede eliminar una ruta con levante asignado');
        $controller->deleteRuta(1);
    }

    public function testDeleteRutaBorraCuandoNoTieneLevantes(): void
    {
        $controller = $this->controller();
        $stub = new class {
            public $borrada = null;
            public function obtenerLevantes($id)
            {
                return [];
            }
            public function deleteRuta($id)
            {
                $this->borrada = $id;
                return true;
            }
        };
        $this->setPrivateProperty($controller, 'rutaModel', $stub);

        $this->assertTrue($controller->deleteRuta(7));
        $this->assertSame(7, $stub->borrada);
    }

    private function stubRutaModelParaResumen(array $filas): object
    {
        return new class ($filas) {
            private array $filas;
            public function __construct(array $filas)
            {
                $this->filas = $filas;
            }
            public function obtenerResumen()
            {
                return $this->filas;
            }
        };
    }

    /** @dataProvider filasDeResumen */
    public function testObtenerResumenCalculaEstadoYEstadoLevante(array $fila, string $estadoEsperado, string $estadoLevanteEsperado): void
    {
        $controller = $this->controller();
        $this->setPrivateProperty($controller, 'rutaModel', $this->stubRutaModelParaResumen([$fila]));

        $resultado = $controller->obtenerResumen();

        $this->assertSame($estadoEsperado, $resultado[0]['estado']);
        $this->assertSame($estadoLevanteEsperado, $resultado[0]['estado_levante']);
    }

    public static function filasDeResumen(): array
    {
        $base = [
            'en_curso_count' => 0,
            'pendiente_count' => 0,
            'completado_count' => 0,
            'levantes_count' => 0,
            'llenado_promedio' => null,
            'contenedores_count' => 0,
        ];

        return [
            'en curso manda sobre el resto' => [
                array_merge($base, ['en_curso_count' => 1, 'pendiente_count' => 1, 'levantes_count' => 2]),
                'activa', 'en_curso',
            ],
            'pendiente sin en_curso' => [
                array_merge($base, ['pendiente_count' => 1, 'levantes_count' => 1]),
                'pendiente', 'pendiente',
            ],
            'sin ningun levante' => [
                $base,
                'pendiente', 'sin_levante',
            ],
            'completado sin pendientes ni en curso' => [
                array_merge($base, ['completado_count' => 1, 'levantes_count' => 1]),
                'finalizada', 'completado',
            ],
            'todos cancelados' => [
                array_merge($base, ['levantes_count' => 1]),
                'finalizada', 'cancelado',
            ],
        ];
    }

    public function testObtenerResumenRedondeaLlenadoPromedioYCasteaConteos(): void
    {
        $controller = $this->controller();
        $fila = [
            'en_curso_count' => '0',
            'pendiente_count' => '0',
            'completado_count' => '1',
            'levantes_count' => '1',
            'llenado_promedio' => '73.456',
            'contenedores_count' => '5',
        ];
        $this->setPrivateProperty($controller, 'rutaModel', $this->stubRutaModelParaResumen([$fila]));

        $resultado = $controller->obtenerResumen();

        $this->assertSame(73.5, $resultado[0]['llenado_promedio']);
        $this->assertSame(5, $resultado[0]['contenedores_count']);
    }

    public function testObtenerResumenMantieneLlenadoPromedioNuloSinContenedores(): void
    {
        $controller = $this->controller();
        $fila = [
            'en_curso_count' => 0,
            'pendiente_count' => 0,
            'completado_count' => 0,
            'levantes_count' => 0,
            'llenado_promedio' => null,
            'contenedores_count' => 0,
        ];
        $this->setPrivateProperty($controller, 'rutaModel', $this->stubRutaModelParaResumen([$fila]));

        $resultado = $controller->obtenerResumen();

        $this->assertNull($resultado[0]['llenado_promedio']);
    }
}
