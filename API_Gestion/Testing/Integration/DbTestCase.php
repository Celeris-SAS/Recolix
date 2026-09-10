<?php

require_once __DIR__ . '/../../../conexion.php';

/**
 * Base para tests de integración: instancian los Controller/Model reales
 * (constructor normal, conexión mysqli real) contra la base 'recolix_test'
 * -- nunca la 'recolix' de desarrollo -- para probar que las queries SQL
 * realmente funcionan contra el schema real, no solo la lógica en PHP.
 *
 * Antes de cada test se vacían (TRUNCATE) las tablas que usa API_Gestion,
 * así que cada test arranca de una base limpia y puede crear sus propios
 * datos de prueba con los helpers `crearX()` de abajo.
 *
 * Requiere haber corrido Testing/setup-test-db.sh al menos una vez.
 */
abstract class DbTestCase extends GestionTestCase
{
    private static ?mysqli $shared = null;

    private const TABLAS_GESTION = [
        'incidencia_contenedor',
        'incidencia',
        'centro_residuo',
        'centro_maquinaria',
        'maquinaria',
        'centro_acopio',
        'prediccion_llenado',
        'contenedor_levante',
        'levante',
        'ruta_recoleccion',
        'contenedor',
        'circuito',
        'municipio',
        'tipo_residuo',
        'usuario',
        'cuadrilla',
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
        foreach (self::TABLAS_GESTION as $tabla) {
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
            'actividad' => 'recolección domiciliaria',
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

    protected function crearTipoResiduo(array $overrides = []): int
    {
        $datos = array_merge([
            'color' => 'Verde',
            'descripcion' => 'Tipo de residuo de prueba',
            'reciclable' => 1,
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO tipo_residuo (color, descripcion, reciclable) VALUES (?, ?, ?)');
        $stmt->bind_param('ssi', $datos['color'], $datos['descripcion'], $datos['reciclable']);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function crearContenedor(?int $circuitoId = null, array $overrides = []): int
    {
        $circuitoId ??= $this->crearCircuito();
        $idTipo = $overrides['id_tipo'] ?? null;
        $datos = array_merge([
            'calle' => 'Calle Test',
            'num_calle' => 123,
            'latitud' => -34.9011,
            'longitud' => -56.1645,
            'capacidad' => 100,
            'nivel_llenado' => 50,
            'estado' => 'funcional',
        ], $overrides);
        unset($datos['id_tipo']);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO contenedor (id_circuito, id_tipo, calle, num_calle, latitud, longitud, capacidad, nivel_llenado, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param(
            'iisiddiis',
            $circuitoId,
            $idTipo,
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

    protected function crearCentroAcopio(?int $municipioId = null, array $overrides = []): int
    {
        $municipioId ??= $this->crearMunicipio();
        $datos = array_merge([
            'nombre' => 'Centro Test',
            'tipo' => 'ecocentro',
            'calle' => 'Calle Test',
            'num_calle' => 100,
            'latitud' => -34.9011,
            'longitud' => -56.1645,
            'capacidad_total' => 1000,
            'estado' => 'operativo',
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO centro_acopio (id_municipio, nombre, tipo, calle, num_calle, latitud, longitud, capacidad_total, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param(
            'isssiddis',
            $municipioId,
            $datos['nombre'],
            $datos['tipo'],
            $datos['calle'],
            $datos['num_calle'],
            $datos['latitud'],
            $datos['longitud'],
            $datos['capacidad_total'],
            $datos['estado']
        );
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    // Usada por ReporteModel, que agrega datos de recolección (LEVANTE) aunque
    // esa tabla sea "de Recolección" — Reportes es un módulo transversal.
    protected function crearRuta(?int $circuitoId = null, array $overrides = []): int
    {
        $circuitoId ??= $this->crearCircuito();
        $datos = array_merge([
            'nombre' => 'Ruta Test',
            'actividad' => 'recolección domiciliaria',
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

    // Idem: LEVANTE es de Recolección, pero ReporteModel lo necesita para sus agregados.
    protected function crearLevante(?int $rutaId = null, array $overrides = []): int
    {
        $rutaId ??= $this->crearRuta();
        $cuadrillaId = $overrides['id_cuadrilla'] ?? $this->crearCuadrilla();
        $camionId = $overrides['id_camion'] ?? null;
        if ($camionId === null) {
            $conn = $this->conn();
            $stmt = $conn->prepare("INSERT INTO camion (matricula, tipo_carga, capacidad, anio, modelo, estado) VALUES (?, 'carga_lateral', 5000, 2020, 'Modelo Test', 'disponible')");
            $matricula = 'TST' . random_int(1000, 9999);
            $stmt->bind_param('s', $matricula);
            $stmt->execute();
            $camionId = $conn->insert_id;
            $stmt->close();
        }
        $datos = array_merge([
            'hora_salida' => date('Y-m-d H:i:s'),
            'hora_llegada' => null,
            'total_recolectado' => 0,
            'hora_levante' => date('Y-m-d'),
            'estado' => 'completado',
        ], $overrides);
        unset($datos['id_cuadrilla'], $datos['id_camion']);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO levante (id_camion, id_cuadrilla, id_ruta, hora_salida, hora_llegada, total_recolectado, hora_levante, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param(
            'iiissdss',
            $camionId,
            $cuadrillaId,
            $rutaId,
            $datos['hora_salida'],
            $datos['hora_llegada'],
            $datos['total_recolectado'],
            $datos['hora_levante'],
            $datos['estado']
        );
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function crearMaquinaria(array $overrides = []): int
    {
        $datos = array_merge([
            'nombre' => 'Maquinaria Test',
            'tipo' => 'pala',
            'fecha_adquisicion' => '2024-01-01',
            'estado' => 'operativa',
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO maquinaria (nombre, tipo, fecha_adquisicion, estado) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $datos['nombre'], $datos['tipo'], $datos['fecha_adquisicion'], $datos['estado']);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function crearUsuario(?int $ci = null, array $overrides = []): int
    {
        $ci ??= random_int(10000000, 99999999);
        $datos = array_merge([
            'id_municipio' => null,
            'primer_nombre' => 'Test',
            'p_apellido' => 'Usuario',
            'email' => "test{$ci}@recolix.uy",
            'tel' => null,
            'contrasenia' => password_hash('claveTest123', PASSWORD_DEFAULT),
            'rol' => 'vecino',
            'acceso' => 'habilitado',
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO usuario (ci_usuario, id_municipio, primer_nombre, p_apellido, email, tel, contrasenia, rol, acceso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param(
            'iisssssss',
            $ci,
            $datos['id_municipio'],
            $datos['primer_nombre'],
            $datos['p_apellido'],
            $datos['email'],
            $datos['tel'],
            $datos['contrasenia'],
            $datos['rol'],
            $datos['acceso']
        );
        $stmt->execute();
        $stmt->close();
        return $ci;
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

    protected function contarFilas(string $tabla, string $where = '1=1'): int
    {
        $result = $this->conn()->query("SELECT COUNT(*) AS c FROM `$tabla` WHERE $where");
        return (int) $result->fetch_assoc()['c'];
    }
}
