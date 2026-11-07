<?php

class prediccionLlenadoController {

    private $prediccionModel;
    private $contenedorModel;

    public function __construct() {
        require_once __DIR__ . '/../Modelo/prediccionLlenadoModel.php';
        require_once __DIR__ . '/../Modelo/contenedorModel.php';
        $this->prediccionModel = new prediccionLlenadoModel();
        $this->contenedorModel = new contenedorModel();
    }
 
 
    private function validarId($id) {
        if (!is_numeric($id) || intval($id) <= 0) {
            throw new InvalidArgumentException("El ID debe ser un número entero positivo.");
        }
        return intval($id);
    }
 
 
    public function calcularPrediccion($idContenedor) {
        $idContenedor = $this->validarId($idContenedor);
 
        $contenedor = $this->contenedorModel->getContenedor($idContenedor);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $idContenedor.");
        }
        if ($contenedor['capacidad'] <= 0) {
            throw new InvalidArgumentException("La capacidad del contenedor no puede ser cero.");
        }
 
        $levantes = $this->prediccionModel->getDatosLevantes($idContenedor);
        if (empty($levantes)) {
            throw new RuntimeException("No hay levantes registrados para el contenedor $idContenedor.");
        }
 
        $hace7dias = strtotime('-7 days');
        $levantesRecientes = array_filter($levantes, function ($l) use ($hace7dias) {
            return strtotime($l['hora_levante']) >= $hace7dias;
        });
 
        if (empty($levantesRecientes)) {
            throw new RuntimeException("No hay levantes en los últimos 7 días para calcular la predicción.");
        }
 
        $totalPeso = array_sum(array_column($levantesRecientes, 'peso'));
        $promedio  = $totalPeso / count($levantesRecientes);
 
        $nivelProyectado = intval(round(($promedio / $contenedor['capacidad']) * 100));
 
        return $this->prediccionModel->insertPrediccion($idContenedor, $nivelProyectado);
    }
 
    public function getUltimaPrediccion($idContenedor) {
        $idContenedor = $this->validarId($idContenedor);
 
        $contenedor = $this->contenedorModel->getContenedor($idContenedor);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $idContenedor.");
        }
 
        return $this->prediccionModel->getUltimaPrediccion($idContenedor);
    }
 
    public function getPrediccion($id) {
        $id = $this->validarId($id);
        return $this->prediccionModel->getPrediccion($id);
    }
 
    public function getAllPredicciones($idContenedor) {
        $idContenedor = $this->validarId($idContenedor);

        $contenedor = $this->contenedorModel->getContenedor($idContenedor);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $idContenedor.");
        }

        return $this->prediccionModel->getAllPredicciones($idContenedor);
    }

    // Proyección de llenado para el mapa de contenedores: ajusta una regresión lineal
    // simple (API_Gestion/Modelo/prediccion_llenado.py) sobre el historial de levantes
    // para estimar el nivel actual y a futuro. No persiste en PREDICCION_LLENADO (se
    // calcula al vuelo para no acumular una fila por cada vez que se abre el mapa).
    public function proyectarLlenadoIA($idContenedor, $horizonteDias = null) {
        $idContenedor = $this->validarId($idContenedor);

        $contenedor = $this->contenedorModel->getContenedor($idContenedor);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $idContenedor.");
        }
        if ($contenedor['capacidad'] <= 0) {
            throw new InvalidArgumentException("La capacidad del contenedor no puede ser cero.");
        }

        $levantes = $this->prediccionModel->getDatosLevantes($idContenedor);
        // Un levante pendiente (todavía no pesado) tiene peso NULL: no es "se encontró
        // vacío", es "no hay dato"; si se manda como 0 arruina la tasa de llenado.
        $levantesConPeso = array_values(array_filter($levantes, function ($l) {
            return $l['peso'] !== null;
        }));
        if (empty($levantesConPeso)) {
            throw new RuntimeException("No hay levantes con peso registrado para el contenedor $idContenedor.");
        }

        $horizontes = $horizonteDias !== null
            ? array_map('intval', (array) $horizonteDias)
            : [1, 3, 7];

        $payload = [
            'capacidad' => (float) $contenedor['capacidad'],
            'eventos' => array_map(function ($l) {
                return ['fecha' => $l['hora_levante'], 'peso' => (float) $l['peso']];
            }, $levantesConPeso),
            'horizonte_dias' => $horizontes,
        ];

        return $this->ejecutarProyeccion($payload);
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

    private function ejecutarProyeccion($payload) {
        $pythonBin = $this->resolverPythonBin();
        $script = __DIR__ . '/../Modelo/prediccion_llenado.py';

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = proc_open([$pythonBin, $script], $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            throw new RuntimeException("No se pudo iniciar el script de proyección de llenado (python3: $pythonBin)");
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
                : ($stderr ?: 'Error desconocido al proyectar el llenado');
            throw new InvalidArgumentException("Error al proyectar el llenado: " . $mensaje);
        }

        if (!isset($resultado['proyeccion']) || !is_array($resultado['proyeccion'])) {
            throw new InvalidArgumentException("Respuesta inválida del script de proyección de llenado");
        }

        return $resultado;
    }
}
?>
