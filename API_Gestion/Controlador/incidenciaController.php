<?php

class IncidenciaController {

    private $incidenciaModel;
    private $tiposValidos = ['rotura', 'desborde', 'falta_recoleccion', 'vandalismo', 'otro'];
    private $prioridadesValidas = ['baja', 'media', 'alta', 'critica'];
    private $estadosValidos = ['abierta', 'asignada', 'en_curso', 'resuelta'];
    private $mimesImagenValidos = ['jpeg' => 'jpg', 'jpg' => 'jpg', 'png' => 'png'];
    private $tamanoMaxImagen = 5242880; // RF07: 5 MB
    private $estadosContenedorUrgentes = ['roto', 'desbordado', 'inactivo'];

    private $contenedorModel;

    public function __construct() {
        require_once __DIR__ . '/../Modelo/incidenciaModel.php';
        require_once __DIR__ . '/../Modelo/contenedorModel.php';
        $this->incidenciaModel = new IncidenciaModel();
        $this->contenedorModel = new ContenedorModel();
    }

    private function validarId($valor, $nombre = 'ID') {
        if (!is_numeric($valor) || intval($valor) <= 0) {
            throw new InvalidArgumentException("$nombre debe ser un número entero positivo.");
        }
        return intval($valor);
    }

    private function validarEstado($estado) {
        if (!in_array($estado, $this->estadosValidos)) {
            throw new InvalidArgumentException("Estado no válido. Valores permitidos: " . implode(', ', $this->estadosValidos));
        }
        return $estado;
    }

    private function validarTipo($tipo) {
        if (!in_array($tipo, $this->tiposValidos)) {
            throw new InvalidArgumentException("Tipo no válido. Valores permitidos: " . implode(', ', $this->tiposValidos));
        }
        return $tipo;
    }

    private function validarPrioridad($prioridad) {
        if (!in_array($prioridad, $this->prioridadesValidas)) {
            throw new InvalidArgumentException("Prioridad no válida. Valores permitidos: " . implode(', ', $this->prioridadesValidas));
        }
        return $prioridad;
    }

    private function obtenerOFallar($id) {
        $incidencia = $this->incidenciaModel->getIncidencia($id);
        if (!$incidencia) {
            throw new InvalidArgumentException("No existe una incidencia con el ID $id.");
        }
        return $incidencia;
    }

    private function validarDescripcion($descripcion) {
        if ($descripcion === null) {
            return null;
        }
        $descripcion = trim((string) $descripcion);
        if ($descripcion === '') {
            return null;
        }
        if (strlen($descripcion) > 500) {
            throw new InvalidArgumentException("La descripción no puede superar los 500 caracteres.");
        }
        return $descripcion;
    }

    // RF07: recibe la foto como data URL base64 ("data:image/jpeg;base64,...."), formato en el que
    // la envían tanto la Landing Page como el perfil del vecino junto al resto del JSON del reporte.
    // Devuelve los bytes decodificados y la extensión, o null si no se adjuntó imagen.
    private function validarImagen($imagenBase64) {
        if ($imagenBase64 === null || $imagenBase64 === '') {
            return null;
        }
        if (!preg_match('/^data:image\/(jpe?g|png);base64,(.+)$/i', $imagenBase64, $match)) {
            throw new InvalidArgumentException("La imagen debe ser un archivo JPG o PNG.");
        }
        $extension = $this->mimesImagenValidos[strtolower($match[1])];
        $datos = base64_decode($match[2], true);
        if ($datos === false) {
            throw new InvalidArgumentException("La imagen adjunta está corrupta o mal codificada.");
        }
        if (strlen($datos) > $this->tamanoMaxImagen) {
            throw new InvalidArgumentException("La imagen no puede superar los 5 MB.");
        }
        return ['datos' => $datos, 'extension' => $extension];
    }

    // Prioridad escalonada: no la elige el usuario ("para los usuarios su incidencia
    // siempre es urgente"). Escala según cuántas incidencias no resueltas acumula
    // el contenedor, contando la que se está por crear.
    private function calcularPrioridad($cantidadIncidencias) {
        if ($cantidadIncidencias >= 10) {
            return 'critica';
        }
        if ($cantidadIncidencias >= 5) {
            return 'alta';
        }
        if ($cantidadIncidencias >= 2) {
            return 'media';
        }
        return 'baja';
    }

    private function guardarImagen($datos, $extension) {
        $directorio = __DIR__ . '/../Vista/uploads/incidencias';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        $nombreArchivo = 'inc_' . uniqid() . '.' . $extension;
        file_put_contents($directorio . '/' . $nombreArchivo, $datos);
        return 'uploads/incidencias/' . $nombreArchivo;
    }

    public function reglasValidacion() {
        return [
            'idCuadrilla'  => ['tipo' => 'entero', 'min' => 1, 'label' => 'Cuadrilla'],
            'idContenedor' => ['tipo' => 'entero', 'min' => 1, 'label' => 'Contenedor'],
            'tipo'         => ['valores' => $this->tiposValidos, 'label' => 'Tipo'],
            'prioridad'    => ['valores' => $this->prioridadesValidas, 'label' => 'Prioridad'],
            'estado'       => ['valores' => $this->estadosValidos, 'label' => 'Estado'],
            'descripcion'  => ['tipo' => 'texto', 'max' => 500, 'label' => 'Descripción']
        ];
    }

    // RF03/RF04: registro de una incidencia por un usuario autenticado.
    // La identidad se toma de la sesión del servidor, nunca de lo que mande el cliente,
    // para que una incidencia no pueda crearse a nombre de otro usuario.
    public function crearIncidencia($idContenedor, $tipo, $descripcion = null, $imagen = null) {
        if (empty($_SESSION['ci_usuario'])) {
            throw new InvalidArgumentException("Debe iniciar sesión para reportar una incidencia.");
        }
        $ciUsuario    = $this->validarId($_SESSION['ci_usuario'], 'Usuario');
        $idContenedor = $this->validarId($idContenedor, 'Contenedor');
        $tipo         = $this->validarTipo($tipo);
        $descripcion  = $this->validarDescripcion($descripcion);
        $imagenValida = $this->validarImagen($imagen);

        if (!$this->incidenciaModel->usuarioExiste($ciUsuario)) {
            throw new InvalidArgumentException("No existe un usuario con la cédula $ciUsuario.");
        }
        $contenedor = $this->contenedorModel->getContenedor($idContenedor);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $idContenedor.");
        }
        if ($this->incidenciaModel->usuarioTieneIncidenciaAbiertaEnContenedor($ciUsuario, $idContenedor)) {
            throw new InvalidArgumentException("Ya tenés un reporte sin resolver sobre este contenedor.");
        }

        if ($imagenValida) {
            $urlImagen = $this->guardarImagen($imagenValida['datos'], $imagenValida['extension']);
        } else {
            $urlImagen = null;
        }

        // RF09: urgente cuando el contenedor está roto, desbordado o inactivo.
        if (in_array($contenedor['estado'], $this->estadosContenedorUrgentes, true)) {
            $urgencia = 1;
        } else {
            $urgencia = 0;
        }

        // Prioridad escalonada: +1 porque esta incidencia todavía no está insertada
        // y debe contarse a sí misma.
        $cantidadIncidencias = $this->incidenciaModel->contarIncidenciasAbiertasPorContenedor($idContenedor) + 1;
        $prioridad = $this->calcularPrioridad($cantidadIncidencias);

        $idIncidencia = $this->incidenciaModel->crearIncidencia($ciUsuario, $tipo, $descripcion, $urlImagen, $urgencia, $prioridad);
        $this->incidenciaModel->vincularContenedor($idIncidencia, $idContenedor);
        return $this->incidenciaModel->getIncidencia($idIncidencia);
    }

    public function getAllIncidencias() {
        return $this->incidenciaModel->getAllIncidencias();
    }

    public function getIncidencia($id) {
        $id = $this->validarId($id);
        return $this->incidenciaModel->getIncidencia($id);
    }

    public function filtrarEstado($estado) {
        $estado = $this->validarEstado($estado);
        return $this->incidenciaModel->filtrarEstado($estado);
    }

    public function filtrarTipo($tipo) {
        $tipo = $this->validarTipo($tipo);
        return $this->incidenciaModel->filtrarTipo($tipo);
    }

    public function filtrarPrioridad($prioridad) {
        $prioridad = $this->validarPrioridad($prioridad);
        return $this->incidenciaModel->filtrarPrioridad($prioridad);
    }

    public function listarUrgentes() {
        return $this->incidenciaModel->listarUrgentes();
    }

    // RF72: incidencias activas con ubicación, para el mapa interactivo del backoffice.
    public function getActivasConUbicacion() {
        return $this->incidenciaModel->getActivasConUbicacion();
    }

    public function getContenedoresVinculados($id) {
        $id = $this->validarId($id);
        $this->obtenerOFallar($id);
        return $this->incidenciaModel->getContenedoresVinculados($id);
    }

    // RF10: solo se asigna cuadrilla a una incidencia recién abierta.
    public function asignarCuadrilla($id, $idCuadrilla) {
        $id          = $this->validarId($id);
        $idCuadrilla = $this->validarId($idCuadrilla, 'Cuadrilla');

        $incidencia = $this->obtenerOFallar($id);
        if ($incidencia['estado'] !== 'abierta') {
            throw new InvalidArgumentException("Solo se puede asignar una cuadrilla a incidencias en estado 'abierta'. Estado actual: " . $incidencia['estado']);
        }
        if (!$this->incidenciaModel->cuadrillaExiste($idCuadrilla)) {
            throw new InvalidArgumentException("No existe una cuadrilla con el ID $idCuadrilla.");
        }

        return $this->incidenciaModel->asignarCuadrilla($id, $idCuadrilla);
    }

    // RF13: cierre de la cuadrilla, de 'en_curso' a 'resuelta'.
    public function cerrar($id) {
        $id = $this->validarId($id);
        $incidencia = $this->obtenerOFallar($id);
        if ($incidencia['estado'] !== 'en_curso') {
            throw new InvalidArgumentException("Solo se puede cerrar una incidencia en estado 'en_curso'. Estado actual: " . $incidencia['estado']);
        }
        return $this->incidenciaModel->cerrar($id);
    }

    // La sesión solo puede operar sobre su propia cuadrilla (se compara contra la base).
    private function verificarCuadrillaPropia($idCuadrilla) {
        $ci = $_SESSION['usuario']['ci_usuario'] ?? $_SESSION['ci_usuario'] ?? null;
        $real = $ci !== null ? $this->incidenciaModel->cuadrillaDeUsuario($ci) : null;
        if ($real === null || $idCuadrilla === null || (int) $real !== (int) $idCuadrilla) {
            throw new AccesoDenegadoException('Solo podés operar sobre incidencias asignadas a tu cuadrilla.');
        }
    }

    // Lista de incidencias asignadas a la cuadrilla (interfaz de la cuadrilla).
    public function obtenerPorCuadrilla($idCuadrilla) {
        $idCuadrilla = $this->validarId($idCuadrilla, 'Cuadrilla');
        $this->verificarCuadrillaPropia($idCuadrilla);
        return $this->incidenciaModel->obtenerPorCuadrilla($idCuadrilla);
    }

    // La cuadrilla empieza a atender una incidencia que le fue asignada.
    public function iniciarAtencion($id) {
        $id = $this->validarId($id);
        $incidencia = $this->obtenerOFallar($id);
        $this->verificarCuadrillaPropia($incidencia['id_cuadrilla']);
        if ($incidencia['estado'] !== 'asignada') {
            throw new InvalidArgumentException("Solo se puede iniciar la atención de una incidencia asignada. Estado actual: " . $incidencia['estado']);
        }
        return $this->incidenciaModel->iniciarAtencion($id);
    }

    // RF13: la cuadrilla cierra la incidencia que está atendiendo (de 'en_curso' a 'resuelta').
    public function cerrarPorCuadrilla($id) {
        $id = $this->validarId($id);
        $incidencia = $this->obtenerOFallar($id);
        $this->verificarCuadrillaPropia($incidencia['id_cuadrilla']);
        if ($incidencia['estado'] !== 'en_curso') {
            throw new InvalidArgumentException("Solo se puede cerrar una incidencia en estado 'en_curso'. Estado actual: " . $incidencia['estado']);
        }
        return $this->incidenciaModel->cerrar($id);
    }
}
?>
