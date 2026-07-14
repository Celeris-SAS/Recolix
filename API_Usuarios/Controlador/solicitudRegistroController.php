<?php

class SolicitudRegistroController {
    private $solicitudModel;

    public function __construct() {
        require_once __DIR__ . '/../Modelo/solicitudRegistroModel.php';
        $this->solicitudModel = new SolicitudRegistroModel();
    }
    // ==========================================
    // VALIDACIONES
    // ==========================================

    private function validarCI($ci) {
        if (!is_numeric($ci) || intval($ci) <= 0) {
            throw new InvalidArgumentException('La cédula debe ser un número entero positivo.');
        }
        $ci = intval($ci);
        if ($ci < 1000000 || $ci > 99999999) {
            throw new InvalidArgumentException('La cédula debe tener entre 7 y 8 dígitos.');
        }
        return $ci;
    }

    private function validarNombre($valor, $nombre) {
        if (!is_string($valor) || trim($valor) === '') {
            throw new InvalidArgumentException("$nombre no puede estar vacío.");
        }
        if (!preg_match('/^[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]{2,50}$/', trim($valor))) {
            throw new InvalidArgumentException("$nombre solo puede contener letras y no puede superar los 50 caracteres.");
        }
        return trim($valor);
    }

    private function validarEmail($email) {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
            throw new InvalidArgumentException('El correo electrónico no tiene un formato válido.');
        }
        return $email;
    }

    private function validarTel($tel) {
        $tel = trim($tel);
        if (!preg_match('/^\d{9}$/', $tel)) {
            throw new InvalidArgumentException('El teléfono debe tener 9 dígitos, sin espacios ni guiones.');
        }
        return $tel;
    }

    public function reglasValidacion() {
        return [
            'ci'          => ['tipo' => 'entero', 'min' => 1000000, 'max' => 99999999, 'label' => 'Cédula de identidad'],
            'primerNombre' => ['tipo' => 'string', 'min' => 2, 'max' => 50,  'label' => 'Nombre'],
            'pApellido'   => ['tipo' => 'string', 'min' => 2, 'max' => 50,  'label' => 'Apellido'],
            'email'       => ['tipo' => 'email',  'max' => 120,             'label' => 'Correo electrónico'],
            'tel'         => ['tipo' => 'string', 'min' => 9, 'max' => 9,  'label' => 'Teléfono']
        ];
    }

    // ==========================================
    // RF02 — Solicitud de registro de vecino
    // ==========================================

    // Recibe los datos del formulario de la Landing Page y crea la solicitud.
    // La solicitud queda en estado 'pendiente' hasta que el administrador la apruebe (RF84).
    // Esta validación se repite aquí además de en el frontend (RNF12).
    public function crearSolicitud($ci, $primerNombre, $pApellido, $email, $tel) {
        $ci           = $this->validarCI($ci);
        $primerNombre = $this->validarNombre($primerNombre, 'Nombre');
        $pApellido    = $this->validarNombre($pApellido, 'Apellido');
        $email        = $this->validarEmail($email);
        $tel          = $this->validarTel($tel);

        // Verificar duplicado antes de intentar insertar.
        if ($this->solicitudModel->existePorCI($ci)) {
            throw new InvalidArgumentException("Ya existe una solicitud registrada con la cédula $ci.");
        }

        return $this->solicitudModel->crearSolicitud($ci, $primerNombre, $pApellido, $email, $tel);
    }

    // RF84 — Aprobar solicitud de registro.
    public function aprobar($ci) {
        $ci = $this->validarCI($ci);
        return $this->solicitudModel->aprobar($ci);
    }

    // RF85 — Rechazar solicitud de registro.
    public function rechazar($ci) {
        $ci = $this->validarCI($ci);
        return $this->solicitudModel->rechazar($ci);
    }

    // RF84, RF85 — Listar solicitudes pendientes para el administrador.
    public function getSolicitudesPendientes() {
        return $this->solicitudModel->getSolicitudesPendientes();
    }
}
?>