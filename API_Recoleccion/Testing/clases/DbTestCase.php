<?php

require_once __DIR__ . '/../../../conexion.php';

abstract class DbTestCase extends RecoleccionTestCase
{
    private static ?mysqli $shared = null;

    private const TABLAS_RECOLECCION = [
        'incidencia_contenedor',
        'incidencia',
        'contenedor_levante',
        'ruta_contenedor',
        'ruta_cuadrilla',
        'mantenimiento',
        'centro_levante',
        'levante',
        'camion_centro',
        'contenedor',
        'ruta_recoleccion',
        'circuito',
        'cuadrilla',
        'camion_estado_historial',
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

    protected function crearCentro(array $overrides = []): int
    {
        $municipioId = $overrides['id_municipio'] ?? $this->crearMunicipio();
        $datos = array_merge([
            'id_municipio' => $municipioId,
            'nombre' => 'Centro Test',
            'tipo' => 'relleno_sanitario',
            'estado' => 'operativo',
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO centro_acopio (id_municipio, nombre, tipo, estado) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('isss', $datos['id_municipio'], $datos['nombre'], $datos['tipo'], $datos['estado']);
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

    protected function crearIncidencia(int $ciUsuario, array $overrides = []): int
    {
        $datos = array_merge([
            'id_cuadrilla' => null,
            'tipo' => 'rotura',
            'descripcion' => 'Incidencia de prueba',
            'estado' => 'abierta',
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO incidencia (ci_usuario, id_cuadrilla, tipo, descripcion, estado) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iisss', $ciUsuario, $datos['id_cuadrilla'], $datos['tipo'], $datos['descripcion'], $datos['estado']);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    protected function vincularIncidenciaContenedor(int $idIncidencia, int $idContenedor): void
    {
        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO incidencia_contenedor (id_incidencia, id_contenedor) VALUES (?, ?)');
        $stmt->bind_param('ii', $idIncidencia, $idContenedor);
        $stmt->execute();
        $stmt->close();
    }
}
