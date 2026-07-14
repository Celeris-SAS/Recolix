<?php

class UsuarioController {
    private $usuarioModel;

    private $rolesValidos   = ['administrador', 'cuadrilla', 'vecino'];
    private $estadosValidos = ['habilitado', 'deshabilitado'];
    private $accesosValidos = ['pendiente', 'aprobado', 'rechazado'];

    public function __construct() {
        require_once __DIR__ . '/../Modelo/UsuarioModel.php';
        $this->usuarioModel = new UsuarioModel();
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

    private function validarContrasenia($contrasenia) {
        if (!is_string($contrasenia) || strlen($contrasenia) < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }
        return $contrasenia;
    }

    private function validarId($id) {
        if (!is_numeric($id) || intval($id) <= 0) {
            throw new InvalidArgumentException('El ID debe ser un número entero positivo.');
        }
        return intval($id);
    }

    private function validarRol($rol) {
        if (!in_array($rol, $this->rolesValidos)) {
            throw new InvalidArgumentException('Rol no válido. Valores permitidos: ' . implode(', ', $this->rolesValidos));
        }
        return $rol;
    }

    private function validarEstadoUsuario($estado) {
        if (!in_array($estado, $this->estadosValidos)) {
            throw new InvalidArgumentException('Estado no válido. Valores permitidos: ' . implode(', ', $this->estadosValidos));
        }
        return $estado;
    }

    private function validarAcceso($acceso) {
        if (!in_array($acceso, $this->accesosValidos)) {
            throw new InvalidArgumentException('Acceso no válido. Valores permitidos: ' . implode(', ', $this->accesosValidos));
        }
        return $acceso;
    }

    public function reglasValidacion() {
        return [
            'ci'          => ['tipo' => 'entero', 'min' => 1000000, 'max' => 99999999, 'label' => 'Cédula'],
            'email'       => ['tipo' => 'email',  'max' => 120,     'label' => 'Correo electrónico'],
            'tel'         => ['tipo' => 'string', 'min' => 9, 'max' => 9,  'label' => 'Teléfono'],
            'contrasenia' => ['tipo' => 'string', 'min' => 8,       'label' => 'Contraseña'],
            'rol'         => ['valores' => $this->rolesValidos,   'label' => 'Rol'],
            'estado'      => ['valores' => $this->estadosValidos, 'label' => 'Estado'],
            'acceso'      => ['valores' => $this->accesosValidos, 'label' => 'Acceso']
        ];
    }

    // ==========================================
    // AUTENTICACIÓN
    // ==========================================

    // RF81 — Inicio de sesión por CI y contraseña.
    // RF82 — El rol devuelto en la respuesta permite que el frontend
    //        muestre solo las funcionalidades correspondientes al vecino.
    public function login($ci, $contrasenia) {
        $ci = $this->validarCI($ci);

        if (!is_string($contrasenia) || trim($contrasenia) === '') {
            throw new InvalidArgumentException('La contraseña no puede estar vacía.');
        }

        $usuario = $this->usuarioModel->login($ci, $contrasenia);

        if (!$usuario) {
            throw new InvalidArgumentException('Cédula o contraseña incorrectos.');
        }
        if ($usuario['estado'] === 'deshabilitado') {
            throw new InvalidArgumentException('Tu cuenta está deshabilitada. Contactá al administrador.');
        }
        if ($usuario['acceso'] === 'pendiente') {
            throw new InvalidArgumentException('Tu solicitud está pendiente de aprobación por un administrador.');
        }
        if ($usuario['acceso'] === 'rechazado') {
            throw new InvalidArgumentException('Tu solicitud fue rechazada. Contactá al administrador.');
        }

        // TODO: generar y devolver un JWT cuando el módulo de sesiones esté implementado.
        return $usuario;
    }

    // RF83 — Cierre de sesión.
    // Con JWT el token se invalida en el cliente eliminándolo del almacenamiento local.
    // Este endpoint documenta el flujo y permite agregar lista negra en el futuro.
    public function logout() {
        // TODO: invalidar JWT en lista negra si se implementa server-side session.
        return ['mensaje' => 'Sesión cerrada correctamente.'];
    }

    // ==========================================
    // PERFIL (RF90, RF91)
    // ==========================================

    // RF90 — Consultar datos del perfil propio. Nunca devuelve la contraseña.
    public function getPerfil($ci) {
        $ci = $this->validarCI($ci);
        $perfil = $this->usuarioModel->getPerfil($ci);
        if (!$perfil) {
            throw new InvalidArgumentException("No existe un usuario con la cédula $ci.");
        }
        return $perfil;
    }

    // RF91 — Modificar email y teléfono del perfil propio.
    // La contraseña nunca se recibe ni se devuelve en este endpoint (RF90).
    public function updatePerfil($ci, $email, $tel) {
        $ci    = $this->validarCI($ci);
        $email = $this->validarEmail($email);
        $tel   = $this->validarTel($tel);

        $perfil = $this->usuarioModel->getPerfil($ci);
        if (!$perfil) {
            throw new InvalidArgumentException("No existe un usuario con la cédula $ci.");
        }

        return $this->usuarioModel->updatePerfil($ci, $email, $tel);
    }

    // RF92 — Cambio de contraseña.
    // Exige la contraseña actual para confirmar la identidad antes de cambiarla.
    // La contraseña vigente nunca se devuelve en ninguna respuesta (RF90).
    public function cambiarContrasenia($ci, $contraseniaActual, $nuevaContrasenia) {
        $ci               = $this->validarCI($ci);
        $nuevaContrasenia = $this->validarContrasenia($nuevaContrasenia);

        if (!is_string($contraseniaActual) || trim($contraseniaActual) === '') {
            throw new InvalidArgumentException('La contraseña actual no puede estar vacía.');
        }

        // Verificar la contraseña actual antes de permitir el cambio.
        $hash = $this->usuarioModel->getContrasenia($ci);
        if (!$hash || !password_verify($contraseniaActual, $hash)) {
            throw new InvalidArgumentException('La contraseña actual es incorrecta.');
        }

        return $this->usuarioModel->cambiarContrasenia($ci, $nuevaContrasenia);
    }

    // ==========================================
    // INCIDENCIAS PROPIAS (RF03)
    // ==========================================

    // Devuelve las incidencias reportadas por el vecino autenticado.
    public function getIncidencias($ci) {
        $ci = $this->validarCI($ci);
        return $this->usuarioModel->getIncidencias($ci);
    }

    // ==========================================
    // NOTIFICACIONES (RF95, RF96)
    // ==========================================

    // RF95 — Consultar notificaciones del vecino, ordenadas por fecha y prioridad.
    public function getNotificaciones($ci) {
        $ci = $this->validarCI($ci);
        return $this->usuarioModel->getNotificaciones($ci);
    }

    // RF96 — Marcar una notificación como leída.
    public function marcarNotificacionLeida($idNotificacion) {
        $idNotificacion = $this->validarId($idNotificacion);
        return $this->usuarioModel->marcarNotificacionLeida($idNotificacion);
    }

    // ==========================================
    // ADMINISTRACIÓN DE USUARIOS
    // ==========================================

    // Listado general de usuarios para el panel de administración.
    public function listarUsuarios() {
        return $this->usuarioModel->listarUsuarios();
    }

    // Filtrar usuarios por rol.
    public function filtrarRol($rol) {
        $rol = $this->validarRol($rol);
        return $this->usuarioModel->filtrarRol($rol);
    }

    // Filtrar usuarios por estado (habilitado/deshabilitado).
    public function filtrarEstadoUsuario($estado) {
        $estado = $this->validarEstadoUsuario($estado);
        return $this->usuarioModel->filtrarEstadoUsuario($estado);
    }

    // Filtrar usuarios por acceso (pendiente/aprobado/rechazado).
    public function filtrarAcceso($acceso) {
        $acceso = $this->validarAcceso($acceso);
        return $this->usuarioModel->filtrarAcceso($acceso);
    }

    // Cambia el rol de un usuario existente (RNE 01: administrador, cuadrilla, vecino).
    public function cambiarRol($ci, $nuevoRol) {
        $ci       = $this->validarCI($ci);
        $nuevoRol = $this->validarRol($nuevoRol);

        $usuario = $this->usuarioModel->getPerfil($ci);
        if (!$usuario) {
            throw new InvalidArgumentException("No existe un usuario con la cédula $ci.");
        }

        return $this->usuarioModel->cambiarRol($ci, $nuevoRol);
    }

    // Habilita o deshabilita la cuenta de un usuario (equivalente a "dar de baja").
    public function cambiarEstadoUsuario($ci, $nuevoEstado) {
        $ci          = $this->validarCI($ci);
        $nuevoEstado = $this->validarEstadoUsuario($nuevoEstado);

        $usuario = $this->usuarioModel->getPerfil($ci);
        if (!$usuario) {
            throw new InvalidArgumentException("No existe un usuario con la cédula $ci.");
        }

        return $this->usuarioModel->cambiarEstadoUsuario($ci, $nuevoEstado);
    }
}
?>