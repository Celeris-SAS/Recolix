<?php

require_once __DIR__ . '/../../../conexion.php';

/**
 * Base para tests de integración: instancian los Controller/Model reales
 * (constructor normal, conexión mysqli real) contra la base 'recolix_test'
 * -- nunca la 'recolix' de desarrollo -- para probar que las queries SQL
 * realmente funcionan contra el schema real, no solo la lógica en PHP.
 *
 * Antes de cada test se vacían (TRUNCATE) las tablas que usa API_Usuarios,
 * así que cada test arranca de una base limpia y puede crear sus propios
 * datos de prueba con los helpers `crearX()` de abajo.
 *
 * Requiere haber corrido Testing/setup-test-db.sh al menos una vez.
 */
abstract class DbTestCase extends UsuarioTestCase
{
    private static ?mysqli $shared = null;

    private const TABLAS_USUARIOS = [
        'notificacion_usuario',
        'notificacion',
        'incidencia',
        'levante',
        'cuadrilla',
        'camion_estado_historial',
        'camion',
        'ruta_recoleccion',
        'circuito',
        'municipio',
        'usuario',
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
        foreach (self::TABLAS_USUARIOS as $tabla) {
            $conn->query("TRUNCATE TABLE `$tabla`");
        }
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
    }

    // --- Fixtures mínimas, cada una devuelve el id/ci insertado ---

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

    protected function crearUsuario(?int $ci = null, array $overrides = []): int
    {
        $ci ??= random_int(10000000, 99999999);
        $datos = array_merge([
            'id_municipio' => null,
            'primer_nombre' => 'Test',
            'p_apellido' => 'Usuario',
            'email' => "test{$ci}@recolix.uy",
            'tel' => '099' . random_int(100000, 999999),
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

    protected function crearLevante(?int $cuadrillaId = null, array $overrides = []): int
    {
        $cuadrillaId ??= $this->crearCuadrilla();
        $camionId = $overrides['id_camion'] ?? $this->crearCamion();
        $rutaId = $overrides['id_ruta'] ?? $this->crearRuta();
        $datos = array_merge([
            'hora_salida' => date('Y-m-d H:i:s'),
            'hora_llegada' => null,
            'total_recolectado' => 0,
            'hora_levante' => date('Y-m-d'),
            'estado' => 'pendiente',
        ], $overrides);
        unset($datos['id_camion'], $datos['id_ruta']);

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

    protected function crearIncidencia(int $ciUsuario, array $overrides = []): int
    {
        $datos = array_merge([
            'id_cuadrilla' => null,
            'tipo' => 'rotura',
            'descripcion' => 'Incidencia de prueba',
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO incidencia (ci_usuario, id_cuadrilla, tipo, descripcion) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiss', $ciUsuario, $datos['id_cuadrilla'], $datos['tipo'], $datos['descripcion']);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function crearNotificacion(array $overrides = []): int
    {
        $datos = array_merge([
            'mensaje' => 'Notificación de prueba',
            'tipo_origen' => 'incidencia',
            'prioridad' => 'informativa',
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO notificacion (mensaje, tipo_origen, prioridad) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $datos['mensaje'], $datos['tipo_origen'], $datos['prioridad']);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function vincularNotificacion(int $idNotificacion, int $ciUsuario): void
    {
        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO notificacion_usuario (id_notificacion, ci_usuario) VALUES (?, ?)');
        $stmt->bind_param('ii', $idNotificacion, $ciUsuario);
        $stmt->execute();
        $stmt->close();
    }

    protected function contarFilas(string $tabla, string $where = '1=1'): int
    {
        $result = $this->conn()->query("SELECT COUNT(*) AS c FROM `$tabla` WHERE $where");
        return (int) $result->fetch_assoc()['c'];
    }
}
