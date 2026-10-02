<?php
    class RutaController {

        // Capacidad de referencia (kg) usada al generar una ruta óptima: un tope conservador
        // para que la ruta resultante pueda ser cubierta por un camión real de la flota
        // (equivale al camión de menor capacidad). No depende de qué camión se asigne después
        // en el levante; solo evita generar rutas que ningún camión podría completar.
        private const CAPACIDAD_MAXIMA_RUTA_KG = 5000;

        private $rutaModel;

        public function __construct() {
            require_once __DIR__ . '/../Modelo/rutaModel.php';
            $this->rutaModel = new RutaModel();
        }

        private function validarId($valor) {
            $nombre = 'ID';
            if (!is_numeric($valor) || intval($valor) <= 0) {
                throw new InvalidArgumentException("$nombre debe ser un número entero positivo");
            }
            return intval($valor);
        }

        private function validarString($valor, $nombre) {
            $max = 255;
            if (!is_string($valor) || trim($valor) === '') {
                throw new InvalidArgumentException("$nombre debe ser un texto válido");
            }
            if (strlen(trim($valor)) > $max) {
                throw new InvalidArgumentException("$nombre no puede superar los $max caracteres");
            }
            return trim($valor);
        }

        private function validarEntero($valor, $nombre) {
            if (!is_numeric($valor) || intval($valor) != $valor) {
                throw new InvalidArgumentException("$nombre debe ser un número entero");
            }
            return intval($valor);
        }

        private function validarOrdenVisita($valor) {
            $min = 1;
            $val = $this->validarEntero($valor, 'Orden de visita');
            if ($val < $min) {
                throw new InvalidArgumentException("Orden de visita debe ser mayor o igual a $min");
            }
            return $val;
        }

        public function reglasValidacion() {
            return [
                'circuitoId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del circuito'],
                'nombre' => ['max' => 100, 'label' => 'Nombre'],
                'actividad' => ['max' => 60, 'label' => 'Actividad'],
                'rutaId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID de la ruta'],
                'contenedorId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del contenedor'],
                'ordenVisita' => ['tipo' => 'entero', 'min' => 1, 'label' => 'Orden de visita'],
                'cuadrillaId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID de la cuadrilla']
            ];
        }

        // estado/estado_levante se derivan de los conteos de levante (en_curso/pendiente/
        // completado) que trae SELECT_CON_LEVANTES. Se calculan acá (no en el modelo) y de
        // forma centralizada para que el badge de estado sea el mismo sin importar por qué
        // endpoint haya llegado la ruta (listado completo, por id, por circuito o filtrada).
        private function calcularEstadoLevante($r) {
            $enCurso = (int) ($r['en_curso_count'] ?? 0);
            $pendientes = (int) ($r['pendiente_count'] ?? 0);
            $completados = (int) ($r['completado_count'] ?? 0);
            $totalLevantes = (int) ($r['levantes_count'] ?? 0);

            if ($enCurso > 0) {
                $estado = 'activa';
                $estadoLevante = 'en_curso';
            } elseif ($pendientes > 0) {
                $estado = 'pendiente';
                $estadoLevante = 'pendiente';
            } elseif ($totalLevantes === 0) {
                $estado = 'pendiente';
                $estadoLevante = 'sin_levante';
            } elseif ($completados > 0) {
                $estado = 'finalizada';
                $estadoLevante = 'completado';
            } else {
                $estado = 'finalizada';
                $estadoLevante = 'cancelado';
            }

            $r['estado'] = $estado;
            $r['estado_levante'] = $estadoLevante;
            return $r;
        }

        public function getAllRutas() {
            return array_map([$this, 'calcularEstadoLevante'], $this->rutaModel->getAllRutas());
        }

        public function getRuta($id) {
            $id = $this->validarId($id);
            $ruta = $this->rutaModel->getRuta($id);
            return $ruta ? $this->calcularEstadoLevante($ruta) : $ruta;
        }

        public function insertRuta($circuitoId, $nombre, $actividad) {
            $circuitoId = $this->validarId($circuitoId);
            $nombre = $this->validarString($nombre, 'Nombre');
            $actividad = $this->validarString($actividad, 'Actividad');
            return $this->rutaModel->insertRuta($circuitoId, $nombre, $actividad);
        }

        public function updateRuta($id, $circuitoId, $nombre, $actividad) {
            $id = $this->validarId($id);
            $circuitoId = $this->validarId($circuitoId);
            $nombre = $this->validarString($nombre, 'Nombre');
            $actividad = $this->validarString($actividad, 'Actividad');
            return $this->rutaModel->updateRuta($id, $circuitoId, $nombre, $actividad);
        }

        public function deleteRuta($id) {
            $id = $this->validarId($id);
            $levantes = $this->rutaModel->obtenerLevantes($id);
            if (!empty($levantes)) {
                throw new InvalidArgumentException("No se puede eliminar una ruta con levante asignado");
            }
            return $this->rutaModel->deleteRuta($id);
        }

        public function obtenerPorCircuito($circuitoId) {
            $circuitoId = $this->validarId($circuitoId);
            return array_map([$this, 'calcularEstadoLevante'], $this->rutaModel->obtenerPorCircuito($circuitoId));
        }

        public function filtrarPorActividad($actividad) {
            $actividad = $this->validarString($actividad, 'Actividad');
            return array_map([$this, 'calcularEstadoLevante'], $this->rutaModel->filtrarPorActividad($actividad));
        }

        public function filtrarPorNombre($nombre) {
            $nombre = $this->validarString($nombre, 'Nombre');
            return array_map([$this, 'calcularEstadoLevante'], $this->rutaModel->filtrarPorNombre($nombre));
        }

        public function agregarContenedor($rutaId, $contenedorId, $ordenVisita) {
            $rutaId = $this->validarId($rutaId);
            $contenedorId = $this->validarId($contenedorId);
            $ordenVisita = $this->validarOrdenVisita($ordenVisita);
            return $this->rutaModel->agregarContenedor($rutaId, $contenedorId, $ordenVisita);
        }

        public function quitarContenedor($rutaId, $contenedorId) {
            $rutaId = $this->validarId($rutaId);
            $contenedorId = $this->validarId($contenedorId);
            return $this->rutaModel->quitarContenedor($rutaId, $contenedorId);
        }

        public function obtenerContenedores($rutaId) {
            $rutaId = $this->validarId($rutaId);
            return $this->rutaModel->obtenerContenedores($rutaId);
        }

        public function asignarCuadrilla($rutaId, $cuadrillaId) {
            $rutaId = $this->validarId($rutaId);
            $cuadrillaId = $this->validarId($cuadrillaId);
            return $this->rutaModel->asignarCuadrilla($rutaId, $cuadrillaId);
        }

        public function quitarCuadrilla($rutaId, $cuadrillaId) {
            $rutaId = $this->validarId($rutaId);
            $cuadrillaId = $this->validarId($cuadrillaId);
            return $this->rutaModel->quitarCuadrilla($rutaId, $cuadrillaId);
        }

        public function obtenerCuadrillas($rutaId) {
            $rutaId = $this->validarId($rutaId);
            return $this->rutaModel->obtenerCuadrillas($rutaId);
        }

        public function obtenerPorCuadrilla($cuadrillaId) {
            $cuadrillaId = $this->validarId($cuadrillaId);
            return $this->rutaModel->obtenerPorCuadrilla($cuadrillaId);
        }

        public function obtenerLevantes($rutaId) {
            $rutaId = $this->validarId($rutaId);
            return $this->rutaModel->obtenerLevantes($rutaId);
        }

        public function obtenerResumen() {
            $rutas = $this->rutaModel->obtenerResumen();
            return array_map(function ($r) {
                $r = $this->calcularEstadoLevante($r);
                $r['llenado_promedio'] = $r['llenado_promedio'] !== null ? round((float) $r['llenado_promedio'], 1) : null;
                $r['contenedores_count'] = (int) $r['contenedores_count'];
                return $r;
            }, $rutas);
        }

        public function generarOptima($circuitoId, $nombre, $actividad) {
            $circuitoId = $this->validarId($circuitoId);
            $nombre = $this->validarString($nombre, 'Nombre');
            $actividad = $this->validarString($actividad, 'Actividad');

            require_once __DIR__ . '/../Modelo/circuitoModel.php';
            $circuitoModel = new CircuitoModel();
            $circuito = $circuitoModel->getCircuito($circuitoId);
            if (!$circuito) {
                throw new InvalidArgumentException("Circuito no encontrado");
            }

            // La ruta siempre debe poder generarse: no se descarta ningún contenedor por su
            // nivel de llenado, solo por no estar físicamente disponible (roto/inactivo/en depósito)
            // o no tener coordenadas. El orden de visita prioriza a los contenedores más llenos
            // (ver Modelo/rutas.py, que pondera cada tramo por distancia / nivel de llenado).
            $contenedores = $circuitoModel->obtenerContenedores($circuitoId);
            $elegibles = array_values(array_filter($contenedores, function ($c) {
                $estadoElegible = in_array($c['estado'], ['funcional', 'desbordado'], true);
                $tieneCoordenadas = $c['latitud'] !== null && $c['longitud'] !== null;
                return $estadoElegible && $tieneCoordenadas;
            }));

            if (empty($elegibles)) {
                throw new InvalidArgumentException("No hay contenedores disponibles para rutear en este circuito (revisar estado y coordenadas)");
            }

            $payload = [
                'base' => $this->calcularBase($circuito, $elegibles),
                'contenedores' => array_map(function ($c) {
                    return [
                        'id' => (int) $c['id_contenedor'],
                        'lat' => (float) $c['latitud'],
                        'lon' => (float) $c['longitud'],
                        'nivel_llenado' => (int) $c['nivel_llenado']
                    ];
                }, $elegibles)
            ];

            $resultado = $this->ejecutarRutas($payload);

            $orden = $this->limitarPorCapacidad($resultado['orden'], $elegibles);
            if (empty($orden)) {
                throw new InvalidArgumentException(
                    "El contenedor más lleno ya excede la capacidad máxima estimada del camión (" .
                    self::CAPACIDAD_MAXIMA_RUTA_KG . " kg)"
                );
            }

            $idRuta = $this->rutaModel->insertRuta($circuitoId, $nombre, $actividad);
            $ordenVisita = 1;
            foreach ($orden as $idContenedor) {
                $this->rutaModel->agregarContenedor($idRuta, (int) $idContenedor, $ordenVisita);
                $ordenVisita++;
            }

            return [
                'ruta' => $this->rutaModel->getRuta($idRuta),
                'contenedores' => $this->rutaModel->obtenerContenedores($idRuta),
                'distancia_total_km' => $this->calcularDistanciaOrden($payload['base'], $orden, $elegibles)
            ];
        }

        // Recorre el orden calculado acumulando el peso estimado de cada contenedor
        // (capacidad * nivel_llenado) y descarta el que, sumado a lo ya recolectado,
        // superaría la capacidad del camión. Sigue evaluando los siguientes por si
        // alguno más chico todavía entra.
        private function limitarPorCapacidad($orden, $elegibles) {
            $pesoPorContenedor = [];
            foreach ($elegibles as $c) {
                $capacidad = $c['capacidad'] !== null ? (float) $c['capacidad'] : 0;
                $nivelLlenado = (float) $c['nivel_llenado'];
                $pesoPorContenedor[(int) $c['id_contenedor']] = $capacidad * ($nivelLlenado / 100);
            }

            $ordenFiltrado = [];
            $cargaAcumulada = 0;
            foreach ($orden as $idContenedor) {
                $idContenedor = (int) $idContenedor;
                $peso = $pesoPorContenedor[$idContenedor] ?? 0;
                if ($cargaAcumulada + $peso > self::CAPACIDAD_MAXIMA_RUTA_KG) {
                    continue;
                }
                $cargaAcumulada += $peso;
                $ordenFiltrado[] = $idContenedor;
            }
            return $ordenFiltrado;
        }

        private function haversineKm($lat1, $lon1, $lat2, $lon2) {
            $radioTierra = 6371.0;
            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);
            $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
            return 2 * $radioTierra * asin(sqrt($a));
        }

        private function calcularDistanciaOrden($base, $orden, $elegibles) {
            $coordsPorContenedor = [];
            foreach ($elegibles as $c) {
                $coordsPorContenedor[(int) $c['id_contenedor']] = [(float) $c['latitud'], (float) $c['longitud']];
            }

            $lat = (float) $base[0];
            $lon = (float) $base[1];
            $distanciaTotal = 0;
            foreach ($orden as $idContenedor) {
                [$latSiguiente, $lonSiguiente] = $coordsPorContenedor[(int) $idContenedor];
                $distanciaTotal += $this->haversineKm($lat, $lon, $latSiguiente, $lonSiguiente);
                $lat = $latSiguiente;
                $lon = $lonSiguiente;
            }
            return round($distanciaTotal, 3);
        }

        // Suma el peso estimado (capacidad * nivel_llenado) de los contenedores de una ruta,
        // usado para filtrar qué camiones tienen capacidad suficiente al planificar el levante.
        public function obtenerCargaEstimada($rutaId) {
            $rutaId = $this->validarId($rutaId);
            $contenedores = $this->rutaModel->obtenerContenedores($rutaId);
            $pesoEstimado = 0;
            foreach ($contenedores as $c) {
                $capacidad = $c['capacidad'] !== null ? (float) $c['capacidad'] : 0;
                $nivelLlenado = (float) ($c['nivel_llenado'] ?? 0);
                $pesoEstimado += $capacidad * ($nivelLlenado / 100);
            }
            return [
                'peso_estimado_kg' => round($pesoEstimado, 2),
                'contenedores_count' => count($contenedores)
            ];
        }

        private function calcularBase($circuito, $contenedores) {
            if (!empty($circuito['poligono'])) {
                $puntos = json_decode($circuito['poligono'], true);
                if (is_array($puntos) && count($puntos) > 0) {
                    $sumLat = 0;
                    $sumLon = 0;
                    foreach ($puntos as $p) {
                        $sumLat += $p[0];
                        $sumLon += $p[1];
                    }
                    return [$sumLat / count($puntos), $sumLon / count($puntos)];
                }
            }

            $sumLat = 0;
            $sumLon = 0;
            foreach ($contenedores as $c) {
                $sumLat += (float) $c['latitud'];
                $sumLon += (float) $c['longitud'];
            }
            $n = count($contenedores);
            return [$sumLat / $n, $sumLon / $n];
        }

        private function pythonFunciona($bin) {
            $descriptorSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $process = @proc_open([$bin, '--version'], $descriptorSpec, $pipes);
            if (!is_resource($process)) {
                return false;
            }
            fclose($pipes[0]);
            $salida = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);
            return $exitCode === 0 && preg_match('/^Python \d/', trim($salida)) === 1;
        }

        private function resolverPythonBin() {
            $envBin = getenv('RECOLIX_PYTHON');
            if ($envBin && $this->pythonFunciona($envBin)) {
                return $envBin;
            }

            $candidatos = ['/usr/local/bin/python3', '/usr/bin/python3', 'python3', 'python', 'py'];
            foreach ($candidatos as $c) {
                if ($this->pythonFunciona($c)) {
                    return $c;
                }
            }

            throw new RuntimeException(
                "No se encontró un intérprete de Python funcional. En Windows, 'python'/'python3' " .
                "pueden apuntar al alias de Microsoft Store en vez de una instalación real: instalá " .
                "Python desde python.org o definí la variable de entorno RECOLIX_PYTHON con la ruta " .
                "completa al ejecutable (y reiniciá Apache para que la tome)."
            );
        }

        private function ejecutarRutas($payload) {
            $pythonBin = $this->resolverPythonBin();
            $script = __DIR__ . '/../Modelo/rutas.py';

            $descriptorSpec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ];

            $process = proc_open([$pythonBin, $script], $descriptorSpec, $pipes);
            if (!is_resource($process)) {
                throw new RuntimeException("No se pudo iniciar el script de ruteo (python3: $pythonBin)");
            }

            fwrite($pipes[0], json_encode($payload));
            fclose($pipes[0]);

            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $exitCode = proc_close($process);

            $resultado = json_decode($stdout, true);

            if ($exitCode !== 0 || !is_array($resultado) || isset($resultado['error'])) {
                $mensaje = is_array($resultado) && isset($resultado['error'])
                    ? $resultado['error']
                    : ($stderr ?: 'Error desconocido al calcular la ruta');
                throw new InvalidArgumentException("Error al generar la ruta óptima: " . $mensaje);
            }

            if (!isset($resultado['orden']) || !is_array($resultado['orden'])) {
                throw new InvalidArgumentException("Respuesta inválida del script de ruteo");
            }

            return $resultado;
        }

    }
?>
