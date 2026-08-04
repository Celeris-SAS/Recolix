<?php
    class CircuitoController {

        private $circuitoModel;
        private $turnosValidos = ['mañana', 'tarde', 'noche'];
        private $diasValidos = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

        public function __construct() {
            require_once __DIR__ . '/../Modelo/circuitoModel.php';
            $this->circuitoModel = new CircuitoModel();
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

        private function validarPoligono($valor) {
            if ($valor === null || $valor === '') {
                return null;
            }
            $puntos = is_string($valor) ? json_decode($valor, true) : $valor;
            if (!is_array($puntos) || count($puntos) < 3) {
                throw new InvalidArgumentException("El polígono debe tener al menos 3 puntos [lat, lon]");
            }
            $normalizados = [];
            foreach ($puntos as $punto) {
                if (!is_array($punto) || count($punto) < 2 || !is_numeric($punto[0]) || !is_numeric($punto[1])) {
                    throw new InvalidArgumentException("Cada punto del polígono debe ser un par [lat, lon] numérico");
                }
                $normalizados[] = [(float) $punto[0], (float) $punto[1]];
            }
            return json_encode($normalizados);
        }

        public function reglasValidacion() {
            return [
                'municipioId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del municipio'],
                'nombre' => ['max' => 100, 'label' => 'Nombre'],
                'codigo' => ['max' => 20, 'label' => 'Código'],
                'actividad' => ['max' => 60, 'label' => 'Actividad'],
                'turno' => ['valores' => $this->turnosValidos, 'label' => 'Turno'],
                'frecuencia' => ['max' => 40, 'label' => 'Frecuencia'],
                'dia' => ['valores' => $this->diasValidos, 'label' => 'Día'],
                'poligono' => ['opcional' => true, 'minPuntos' => 3, 'label' => 'Polígono (área del circuito)']
            ];
        }

        public function getAllCircuitos() {
            return $this->circuitoModel->getAllCircuitos();
        }

        public function getCircuito($id) {
            $id = $this->validarId($id);
            return $this->circuitoModel->getCircuito($id);
        }

        public function insertCircuito($municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia, $poligono = null) {
            $municipioId = $this->validarId($municipioId);
            $nombre = $this->validarString($nombre, 'Nombre');
            $codigo = $this->validarString($codigo, 'Código');
            $actividad = $this->validarString($actividad, 'Actividad');
            $turno = $this->validarString($turno, 'Turno');
            if (!in_array($turno, $this->turnosValidos)) {
                throw new InvalidArgumentException("Turno no válido. Valores: " . implode(', ', $this->turnosValidos));
            }
            $frecuencia = $this->validarString($frecuencia, 'Frecuencia');
            $poligono = $this->validarPoligono($poligono);
            if ($this->circuitoModel->getCodigo($codigo)) {
                throw new InvalidArgumentException("Ya hay un circuito con el código " . $codigo . ".");
            }
            return $this->circuitoModel->insertCircuito($municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia, $poligono);
        }

        public function updateCircuito($id, $municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia, $poligono = null) {
            $id = $this->validarId($id);
            $municipioId = $this->validarId($municipioId);
            $nombre = $this->validarString($nombre, 'Nombre');
            $codigo = $this->validarString($codigo, 'Código');
            $actividad = $this->validarString($actividad, 'Actividad');
            $turno = $this->validarString($turno, 'Turno');
            if (!in_array($turno, $this->turnosValidos)) {
                throw new InvalidArgumentException("Turno no válido. Valores: " . implode(', ', $this->turnosValidos));
            }
            $frecuencia = $this->validarString($frecuencia, 'Frecuencia');
            $poligono = $this->validarPoligono($poligono);
            if ($poligono === null) {
                $actual = $this->circuitoModel->getCircuito($id);
                $poligono = $actual ? $actual['poligono'] : null;
            }
            return $this->circuitoModel->updateCircuito($id, $municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia, $poligono);
        }

        public function deleteCircuito($id) {
            $id = $this->validarId($id);
            return $this->circuitoModel->deleteCircuito($id);
        }

        public function obtenerPorMunicipio($municipioId) {
            $municipioId = $this->validarId($municipioId);
            return $this->circuitoModel->obtenerPorMunicipio($municipioId);
        }

        public function filtrarPorTurno($turno) {
            $turno = $this->validarString($turno, 'Turno');
            if (!in_array($turno, $this->turnosValidos)) {
                throw new InvalidArgumentException("Turno no válido. Valores: " . implode(', ', $this->turnosValidos));
            }
            return $this->circuitoModel->filtrarPorTurno($turno);
        }

        public function filtrarPorActividad($actividad) {
            $actividad = $this->validarString($actividad, 'Actividad');
            return $this->circuitoModel->filtrarPorActividad($actividad);
        }

        public function filtrarPorFrecuencia($frecuencia) {
            $frecuencia = $this->validarString($frecuencia, 'Frecuencia');
            return $this->circuitoModel->filtrarPorFrecuencia($frecuencia);
        }

        private function normalizarTexto($texto) {
            $texto = mb_strtolower($texto, 'UTF-8');
            return strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
        }

        // La "frecuencia" del circuito es texto libre (ej: "interdiaria (lunes, miércoles y
        // viernes)", "diaria (lunes a viernes y feriados)"). Esta función extrae qué días de
        // la semana quedan implicados, incluyendo el caso de rangos ("lunes a viernes").
        private function diasDesdeFrecuencia($frecuencia) {
            $texto = $this->normalizarTexto((string) $frecuencia);
            $encontrados = [];

            foreach ($this->diasValidos as $dia) {
                if (mb_strpos($texto, $dia) !== false) {
                    $encontrados[$dia] = true;
                }
            }

            $patronDias = implode('|', $this->diasValidos);
            if (preg_match('/(' . $patronDias . ')\s+a\s+(' . $patronDias . ')/', $texto, $m)) {
                $inicio = array_search($m[1], $this->diasValidos, true);
                $fin = array_search($m[2], $this->diasValidos, true);
                if ($inicio !== false && $fin !== false) {
                    $total = count($this->diasValidos);
                    $i = $inicio;
                    while (true) {
                        $encontrados[$this->diasValidos[$i]] = true;
                        if ($i === $fin) break;
                        $i = ($i + 1) % $total;
                    }
                }
            }

            return array_keys($encontrados);
        }

        public function filtrarPorDia($dia) {
            $dia = $this->normalizarTexto($this->validarString($dia, 'Día'));
            if (!in_array($dia, $this->diasValidos, true)) {
                throw new InvalidArgumentException("Día no válido. Valores: " . implode(', ', $this->diasValidos));
            }
            return array_values(array_filter($this->circuitoModel->getAllCircuitos(), function ($c) use ($dia) {
                return in_array($dia, $this->diasDesdeFrecuencia($c['frecuencia']), true);
            }));
        }

        public function obtenerRutas($circuitoId) {
            $circuitoId = $this->validarId($circuitoId);
            return $this->circuitoModel->obtenerRutas($circuitoId);
        }

        public function obtenerContenedores($circuitoId) {
            $circuitoId = $this->validarId($circuitoId);
            return $this->circuitoModel->obtenerContenedores($circuitoId);
        }

    }
?>
