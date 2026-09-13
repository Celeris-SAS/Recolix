<?php

require_once __DIR__ . '/../../../conexion.php';

/**
 * Base para tests de integración: instancian los Controller/Model reales
 * (constructor normal, conexión mysqli real) contra la base 'recolix_test'
 * -- nunca la 'recolix' de desarrollo -- para probar que las queries SQL
 * realmente funcionan contra el schema real, no solo la lógica en PHP.
 *
 * Antes de cada test se vacían (TRUNCATE) las tablas que usa API_Recoleccion,
 * así que cada test arranca de una base limpia y puede crear sus propios
 * datos de prueba con los helpers `crearX()` de abajo.
 *
 * Requiere haber corrido Testing/setup-test-db.sh al menos una vez.
 */
abstract class DbTestCase extends RecoleccionTestCase
{
    private static ?mysqli $shared = null;

    private const TABLAS_RECOLECCION = [
        'contenedor_levante',
        'ruta_contenedor',
        'ruta_cuadrilla',
        'mantenimiento',
        'levante',
        'camion_centro',
        'contenedor',
        'ruta_recoleccion',
        'circuito',
        'cuadrilla',
        'camion',
        'municipio',
    ];

    protected function conn(): mysqli
    {
        if (self::$shared === null) {
            $dbName = getenv('RECOLIX_DB_NAME');
            if (!$dbName || !str_contains($dbName, 'test')) {
                throw new RuntimeException(
                    "RECOLIX_DB_NAME debe apuntar a una base de test (contener 'test'), valor actual: '$dbName'. " .
                    "Corré la suite vía phpunit.xml (ya trae el <php><env>) o exportá la variable a mano."
                );
            }
            self::$shared = (new conexion())->getConexion();
        }
        return self::$shared;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $conn = $this->conn();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        foreach (self::TABLAS_RECOLECCION as $tabla) {
            $conn->query("TRUNCATE TABLE `$tabla`");
        }
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
    }

    // --- Fixtures mínimas, cada una devuelve el id insertado ---

    protected function crearMunicipio(string $nombre = 'Municipio Test', string $departamento = 'Montevideo'): int
    {
        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO municipio (nombre, departamento) VALUES (?, ?)');
        $stmt->bind_param('ss', $nombre, $departamento);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function crearCircuito(?int $municipioId = null, array $overrides = []): int
    {
        $municipioId ??= $this->crearMunicipio();
        $datos = array_merge([
            'nombre' => 'Circuito Test',
            'codigo' => 'C-' . uniqid(),
            'actividad' => 'barrido',
            'turno' => 'mañana',
            'frecuencia' => 'diaria (lunes a viernes)',
            'poligono' => null,
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO circuito (id_municipio, nombre, codigo, actividad, turno, frecuencia, poligono) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssss', $municipioId, $datos['nombre'], $datos['codigo'], $datos['actividad'], $datos['turno'], $datos['frecuencia'], $datos['poligono']);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function crearCamion(array $overrides = []): int
    {
        $datos = array_merge([
            'matricula' => 'ABC' . random_int(1000, 9999),
            'tipo_carga' => 'carga_lateral',
            'capacidad' => 5000,
            'anio' => 2020,
            'modelo' => 'Modelo Test',
            'estado' => 'disponible',
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO camion (matricula, tipo_carga, capacidad, anio, modelo, estado) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssiiss', $datos['matricula'], $datos['tipo_carga'], $datos['capacidad'], $datos['anio'], $datos['modelo'], $datos['estado']);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function crearCuadrilla(?int $municipioId = null, string $estado = 'activa'): int
    {
        $municipioId ??= $this->crearMunicipio();
        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO cuadrilla (id_municipio, estado) VALUES (?, ?)');
        $stmt->bind_param('is', $municipioId, $estado);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function crearRuta(?int $circuitoId = null, array $overrides = []): int
    {
        $circuitoId ??= $this->crearCircuito();
        $datos = array_merge([
            'nombre' => 'Ruta Test',
            'actividad' => 'barrido',
            'activa' => 1,
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO ruta_recoleccion (id_circuito, nombre, actividad, activa) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('issi', $circuitoId, $datos['nombre'], $datos['actividad'], $datos['activa']);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function crearContenedor(?int $circuitoId = null, array $overrides = []): int
    {
        $circuitoId ??= $this->crearCircuito();
        $datos = array_merge([
            'calle' => 'Calle Test',
            'num_calle' => 123,
            'latitud' => -34.9011,
            'longitud' => -56.1645,
            'capacidad' => 100,
            'nivel_llenado' => 50,
            'estado' => 'funcional',
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO contenedor (id_circuito, calle, num_calle, latitud, longitud, capacidad, nivel_llenado, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param(
            'isiddiis',
            $circuitoId,
            $datos['calle'],
            $datos['num_calle'],
            $datos['latitud'],
            $datos['longitud'],
            $datos['capacidad'],
            $datos['nivel_llenado'],
            $datos['estado']
        );
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function agregarContenedorARuta(int $rutaId, int $contenedorId, int $ordenVisita = 1): void
    {
        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO ruta_contenedor (id_ruta, id_contenedor, orden_visita) VALUES (?, ?, ?)');
        $stmt->bind_param('iii', $rutaId, $contenedorId, $ordenVisita);
        $stmt->execute();
        $stmt->close();
    }

    protected function contarFilas(string $tabla, string $where = '1=1'): int
    {
        $result = $this->conn()->query("SELECT COUNT(*) AS c FROM `$tabla` WHERE $where");
        return (int) $result->fetch_assoc()['c'];
    }
}
