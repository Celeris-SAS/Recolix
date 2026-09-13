<?php

require_once __DIR__ . '/../../Controlador/reporteController.php';

/**
 * getDistribucionLlenado() es la única lógica de negocio real de este
 * controller (agrupa niveles de llenado en 10 buckets de a 10 puntos);
 * se ejercita con un doble de reporteModel para no depender de la base
 * de datos.
 */
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

        $this->assertSame(1, $resultado['valores'][0]);  // 5 -> bucket 0-10
        $this->assertSame(1, $resultado['valores'][1]);  // 15 -> bucket 10-20
        $this->assertSame(1, $resultado['valores'][9]);  // 99 -> bucket 90-100
    }

    public function testGetDistribucionLlenadoClampeaNivelesFueraDeRango(): void
    {
        // Un nivel negativo (dato corrupto) cae en el primer bucket;
        // un nivel de 100 exacto no debe desbordar a un bucket 11 inexistente.
        $controller = $this->controller([-10, 100, 250]);
        $resultado = $controller->getDistribucionLlenado();

        $this->assertSame(1, $resultado['valores'][0]);
        $this->assertSame(2, $resultado['valores'][9]);
        $this->assertSame(3, array_sum($resultado['valores']));
    }
}
