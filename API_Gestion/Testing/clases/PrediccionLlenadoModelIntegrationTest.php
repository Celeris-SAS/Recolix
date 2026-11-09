<?php

require_once __DIR__ . '/../../Modelo/prediccionLlenadoModel.php';

final class PrediccionLlenadoModelIntegrationTest extends DbTestCase
{
    public function testInsertYGetPrediccionRoundtrip(): void
    {
        $model = new prediccionLlenadoModel();
        $contenedorId = $this->crearContenedor();

        $id = $model->insertPrediccion($contenedorId, 75);

        $this->assertGreaterThan(0, $id);
        $prediccion = $model->getPrediccion($id);
        $this->assertSame($contenedorId, (int) $prediccion['id_contenedor']);
        $this->assertSame(75, (int) $prediccion['nivel_proyectado']);
        $this->assertNotEmpty($prediccion['fecha_calculo']);
    }

    public function testGetUltimaPrediccionDevuelveLaMasReciente(): void
    {
        $model = new prediccionLlenadoModel();
        $contenedorId = $this->crearContenedor();

        $idVieja = $model->insertPrediccion($contenedorId, 40);
        $conn = $this->conn();
        $stmt = $conn->prepare('UPDATE prediccion_llenado SET fecha_calculo = DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE id_prediccion = ?');
        $stmt->bind_param('i', $idVieja);
        $stmt->execute();
        $stmt->close();

        $idNueva = $model->insertPrediccion($contenedorId, 90);

        $ultima = $model->getUltimaPrediccion($contenedorId);
        $this->assertSame($idNueva, (int) $ultima['id_prediccion']);
        $this->assertSame(90, (int) $ultima['nivel_proyectado']);
    }

    public function testGetAllPrediccionesOrdenaDescendentePorFecha(): void
    {
        $model = new prediccionLlenadoModel();
        $contenedorId = $this->crearContenedor();

        $idVieja = $model->insertPrediccion($contenedorId, 30);
        $conn = $this->conn();
        $stmt = $conn->prepare('UPDATE prediccion_llenado SET fecha_calculo = DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE id_prediccion = ?');
        $stmt->bind_param('i', $idVieja);
        $stmt->execute();
        $stmt->close();
        $idNueva = $model->insertPrediccion($contenedorId, 60);

        $resultado = $model->getAllPredicciones($contenedorId);

        $this->assertCount(2, $resultado);
        $this->assertSame($idNueva, (int) $resultado[0]['id_prediccion']);
        $this->assertSame($idVieja, (int) $resultado[1]['id_prediccion']);
    }

    public function testGetAllPrediccionesSoloTraeLasDeEseContenedor(): void
    {
        $model = new prediccionLlenadoModel();
        $contenedorUno = $this->crearContenedor();
        $contenedorDos = $this->crearContenedor();
        $model->insertPrediccion($contenedorUno, 50);
        $model->insertPrediccion($contenedorDos, 80);

        $this->assertCount(1, $model->getAllPredicciones($contenedorUno));
        $this->assertCount(1, $model->getAllPredicciones($contenedorDos));
    }

    public function testGetDatosLevantesTraePesoYHoraDeVaciado(): void
    {
        $model = new prediccionLlenadoModel();
        $contenedorId = $this->crearContenedor();
        $levanteId = $this->crearLevante();

        $conn = $this->conn();
        $stmt = $conn->prepare("INSERT INTO contenedor_levante (id_levante, id_contenedor, estado_encontrado, peso, hora_vaciado) VALUES (?, ?, 'todo_ok', 45, NOW())");
        $stmt->bind_param('ii', $levanteId, $contenedorId);
        $stmt->execute();
        $stmt->close();

        $datos = $model->getDatosLevantes($contenedorId);

        $this->assertCount(1, $datos);
        $this->assertSame(45, (int) $datos[0]['peso']);
        $this->assertNotEmpty($datos[0]['hora_levante']);
    }

    public function testGetDatosLevantesVacioSinLevantesRegistrados(): void
    {
        $model = new prediccionLlenadoModel();
        $contenedorId = $this->crearContenedor();

        $this->assertSame([], $model->getDatosLevantes($contenedorId));
    }
}
