<?php
    class RutaController {

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

        public function getAllRutas() {
            return $this->rutaModel->getAllRutas();
        }

        public function getRuta($id) {
            $id = $this->validarId($id);
            return $this->rutaModel->getRuta($id);
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
            return $this->rutaModel->obtenerPorCircuito($circuitoId);
        }

        public function filtrarPorActividad($actividad) {
            $actividad = $this->validarString($actividad, 'Actividad');
            return $this->rutaModel->filtrarPorActividad($actividad);
        }

        public function filtrarPorNombre($nombre) {
            $nombre = $this->validarString($nombre, 'Nombre');
            return $this->rutaModel->filtrarPorNombre($nombre);
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

        public function obtenerLevantes($rutaId) {
            $rutaId = $this->validarId($rutaId);
            return $this->rutaModel->obtenerLevantes($rutaId);
        }

        public function obtenerResumen() {
            $rutas = $this->rutaModel->obtenerResumen();
            return array_map(function ($r) {
                $enCurso = (int) $r['en_curso_count'];
                $pendientes = (int) $r['pendiente_count'];
                $totalLevantes = (int) $r['levantes_count'];

                if ($enCurso > 0) {
                    $estado = 'activa';
                } elseif ($pendientes > 0 || $totalLevantes === 0) {
                    $estado = 'pendiente';
                } else {
                    $estado = 'finalizada';
                }

                $r['estado'] = $estado;
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

            $idRuta = $this->rutaModel->insertRuta($circuitoId, $nombre, $actividad);
            $ordenVisita = 1;
            foreach ($resultado['orden'] as $idContenedor) {
                $this->rutaModel->agregarContenedor($idRuta, (int) $idContenedor, $ordenVisita);
                $ordenVisita++;
            }

            return [
                'ruta' => $this->rutaModel->getRuta($idRuta),
                'contenedores' => $this->rutaModel->obtenerContenedores($idRuta),
                'distancia_total_km' => $resultado['distancia_total_km']
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

        private function ejecutarRutas($payload) {
            $pythonBin = getenv('RECOLIX_PYTHON');
            if (!$pythonBin) {
                $candidatos = ['/usr/local/bin/python3', '/usr/bin/python3', 'python3', 'python'];
                foreach ($candidatos as $c) {
                    if (is_executable($c) || $c === 'python3' || $c === 'python') {
                        $pythonBin = $c;
                        break;
                    }
                }
            }
            if (!$pythonBin) {
                $pythonBin = 'python3';
            }
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
