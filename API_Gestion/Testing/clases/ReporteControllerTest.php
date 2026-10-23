<?php

require_once __DIR__ . '/../../Controlador/reporteController.php';

final class ReporteControllerTest extends GestionTestCase
{
    private function controller(array $niveles): reporteController
    {
        $controller = $this->newWithoutConstructor(reporteController::class);
        $modelStub = new class ($niveles) {
            private $niveles;
            public function __construct($niveles) { $this->niveles = $niveles; }
            public function getNivelesLlenado($rango) { return $this->niveles; }
        };
        $this->setPrivateProperty($controller, 'reporteModel', $modelStub);
        return $controller;
    }

    public function testGetDistribucionLlenadoGeneraDiezEtiquetasDeADiez(): void
    {
        $controller = $this->controller([]);
        $resultado = $controller->getDistribucionLlenado();

        $this->assertSame(
            ['0-10', '10-20', '20-30', '30-40', '40-50', '50-60', '60-70', '70-80', '80-90', '90-100'],
            $resultado['etiquetas']
        );
        $this->assertSame(array_fill(0, 10, 0), $resultado['valores']);
    }

    public function testGetDistribucionLlenadoUbicaCadaNivelEnSuBucket(): void
    {
        $controller = $this->controller([5, 15, 99]);
        $resultado = $controller->getDistribucionLlenado();

        $this->assertSame(1, $resultado['valores'][0]);
        $this->assertSame(1, $resultado['valores'][1]);
        $this->assertSame(1, $resultado['valores'][9]);
    }

    public function testGetDistribucionLlenadoClampeaNivelesFueraDeRango(): void
    {
        $controller = $this->controller([-10, 100, 250]);
        $resultado = $controller->getDistribucionLlenado();

        $this->assertSame(1, $resultado['valores'][0]);
        $this->assertSame(2, $resultado['valores'][9]);
        $this->assertSame(3, array_sum($resultado['valores']));
    }

    /** @dataProvider delegaciones */
    public function testLosInformesNuevosDelegaEnElModelo(string $metodo, array $args): void
    {
        $controller = $this->newWithoutConstructor(reporteController::class);
        $modelStub = new class {
            public $llamada;
            public function __call($nombre, $argumentos) { $this->llamada = [$nombre, $argumentos]; return ['ok']; }
        };
        $this->setPrivateProperty($controller, 'reporteModel', $modelStub);

        $this->assertSame(['ok'], $controller->$metodo(...$args));
        $this->assertSame([$metodo, $args], $modelStub->llamada);
    }

    public static function delegaciones(): array
    {
        return [
            'RF61 tiempos de resolución' => ['getTiemposResolucion', ['mes']],
            'RF59 volumen agrupado' => ['getVolumenPorTipoYPeriodo', ['mes', 'dia']],
            'RF57/58 incidencias por estado' => ['getIncidenciasDetalle', ['siempre', 'abiertas']],
            'RF70 alertas' => ['getAlertasContenedores', []],
            'RF69 proyecciones' => ['getProyeccionesLlenado', []],
            'RF63 zonas con más incidencias' => ['getTopZonasIncidencias', [5, 'mes']],
        ];
    }
}
