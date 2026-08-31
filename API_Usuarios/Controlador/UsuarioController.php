<?php

class UsuarioController {
    private $usuarioModel;

    private $rolesValidos   = ['administrador', 'cuadrilla', 'operario', 'vecino'];
    private $accesosValidos = ['pendiente', 'habilitado', 'deshabilitado'];
    private $municipiosValidos = ['Municipio A', 'Municipio B', 'Municipio C', 'Municipio CH', 'Municipio D', 'Municipio E', 'Municipio F', 'Municipio G'];

    public function __construct() {
        require_once __DIR__ . '/../Modelo/UsuarioModel.php';
        $this->usuarioModel = new UsuarioModel();
    }


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

    private function validarAcceso($acceso) {
        if (!in_array($acceso, $this->accesosValidos)) {
            throw new InvalidArgumentException('Acceso no válido. Valores permitidos: ' . implode(', ', $this->accesosValidos));
        }
        return $acceso;
    }
    private function validarMunicipio($municipio) {
        if (!in_array($municipio, $this->municipiosValidos)) {
            throw new InvalidArgumentException('Municipio no válido. Valores permitidos: ' . implode(', ', $this->municipiosValidos));
        }
        return $municipio;
    }

    private function verificarPropioCI($ci) {
        if (!isset($_SESSION['ci_usuario']) || (int)$_SESSION['ci_usuario'] !== (int)$ci) {
            throw new InvalidArgumentException('No tenés permiso para acceder a los datos de otro usuario.');
        }
    }


    private function verificarAccesoPerfil($ci) {
        if (isset($_SESSION['ci_usuario']) && (int)$_SESSION['ci_usuario'] === (int)$ci) {
            return;
        }
        if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] !== 'vecino') {
            return;
        }
        throw new InvalidArgumentException('No tenés permiso para acceder a los datos de otro usuario.');
    }

    public function reglasValidacion() {
        return [
            'ci'          => ['tipo' => 'entero', 'min' => 1000000, 'max' => 99999999, 'label' => 'Cédula'],
            'email'       => ['tipo' => 'email',  'max' => 120,     'label' => 'Correo electrónico'],
            'tel'         => ['tipo' => 'string', 'min' => 9, 'max' => 9,  'label' => 'Teléfono'],
            'contrasenia' => ['tipo' => 'string', 'min' => 8,       'label' => 'Contraseña'],
            'rol'         => ['valores' => $this->rolesValidos,   'label' => 'Rol'],
            'acceso'      => ['valores' => $this->accesosValidos, 'label' => 'Acceso']
        ];
    }


    public function login($ci, $contrasenia) {
        $ci = $this->validarCI($ci);

        if (!is_string($contrasenia) || trim($contrasenia) === '') {
            throw new InvalidArgumentException('La contraseña no puede estar vacía.');
        }

        $usuario = $this->usuarioModel->login($ci, $contrasenia);

        if (!$usuario) {
            throw new InvalidArgumentException('Cédula o contraseña incorrectos.');
        }
        if ($usuario['acceso'] === 'deshabilitado') {
            throw new InvalidArgumentException('Tu cuenta está deshabilitada. Contactá al administrador.');
        }
        if ($usuario['acceso'] === 'pendiente') {
            throw new InvalidArgumentException('Tu solicitud está pendiente de aprobación por un administrador.');
        }

        $_SESSION['ci_usuario'] = $usuario['ci_usuario'];
        $_SESSION['usuario'] = $usuario;

        return $usuario;
    }
    


    public function logout() {
        return ['mensaje' => 'Sesión cerrada correctamente.'];
    }


    public function getPerfil($ci) {
        $ci = $this->validarCI($ci);
        $perfil = $this->usuarioModel->getPerfil($ci);
        if (!$perfil) {
            throw new InvalidArgumentException("No existe un usuario con la cédula $ci.");
        }
        return $perfil;
    }


    public function updatePerfil($ci, $email, $tel) {
        $ci    = $this->validarCI($ci);
        $email = $this->validarEmail($email);
        $tel   = $this->validarTel($tel);
        $this->verificarAccesoPerfil($ci);

        $perfil = $this->usuarioModel->getPerfil($ci);
        if (!$perfil) {
            throw new InvalidArgumentException("No existe un usuario con la cédula $ci.");
        }

        return $this->usuarioModel->updatePerfil($ci, $email, $tel);
    }


    public function cambiarContrasenia($ci, $contraseniaActual, $nuevaContrasenia) {
        $ci               = $this->validarCI($ci);
        $nuevaContrasenia = $this->validarContrasenia($nuevaContrasenia);
        $this->verificarPropioCI($ci);

        if (!is_string($contraseniaActual) || trim($contraseniaActual) === '') {
            throw new InvalidArgumentException('La contraseña actual no puede estar vacía.');
        }

        $hash = $this->usuarioModel->getContrasenia($ci);
        if (!$hash || !password_verify($contraseniaActual, $hash)) {
            throw new InvalidArgumentException('La contraseña actual es incorrecta.');
        }

        return $this->usuarioModel->cambiarContrasenia($ci, $nuevaContrasenia);
    }




    public function getIncidencias($ci) {
        $ci = $this->validarCI($ci);
        $this->verificarPropioCI($ci);
        return $this->usuarioModel->getIncidencias($ci);
    }




    public function getNotificaciones($ci) {
        $ci = $this->validarCI($ci);
        $this->verificarPropioCI($ci);
        return $this->usuarioModel->getNotificaciones($ci);
    }


    public function marcarNotificacionLeida($idNotificacion) {
        $idNotificacion = $this->validarId($idNotificacion);
        if (!isset($_SESSION['ci_usuario'])) {
            throw new InvalidArgumentException('Sesión inválida.');
        }
        return $this->usuarioModel->marcarNotificacionLeida($idNotificacion, $_SESSION['ci_usuario']);
    }



    public function listarUsuarios() {
        return $this->usuarioModel->listarUsuarios();
    }


    public function filtrarRol($rol) {
        $rol = $this->validarRol($rol);
        return $this->usuarioModel->filtrarRol($rol);
    }

    public function filtrarAcceso($acceso) {
        $acceso = $this->validarAcceso($acceso);
        return $this->usuarioModel->filtrarAcceso($acceso);
    }

    public function cambiarRol($ci, $nuevoRol) {
        $ci       = $this->validarCI($ci);
        $nuevoRol = $this->validarRol($nuevoRol);

        $usuario = $this->usuarioModel->getPerfil($ci);
        if (!$usuario) {
            throw new InvalidArgumentException("No existe un usuario con la cédula $ci.");
        }

        return $this->usuarioModel->cambiarRol($ci, $nuevoRol);
    }

    public function cambiarAcceso($ci, $nuevoAcceso) {
        $ci         = $this->validarCI($ci);
        $nuevoAcceso = $this->validarAcceso($nuevoAcceso);

        $usuario = $this->usuarioModel->getPerfil($ci);
        if (!$usuario) {
            throw new InvalidArgumentException("No existe un usuario con la cédula $ci.");
        }

        return $this->usuarioModel->cambiarAcceso($ci, $nuevoAcceso);
    }

    public function crearUsuario($ci, $primerNombre, $pApellido, $email, $tel, $contrasenia, $rol, $idMunicipio) {
        $ci           = $this->validarCI($ci);
        $primerNombre = $this->validarNombre($primerNombre, 'Nombre');
        $pApellido    = $this->validarNombre($pApellido, 'Apellido');
        $email        = $this->validarEmail($email);
        $tel          = $this->validarTel($tel);
        $contrasenia  = $this->validarContrasenia($contrasenia);
        $rol          = $this->validarRol($rol);
        $idMunicipio  = $this->validarId($idMunicipio);

        if ($this->usuarioModel->existeCi($ci)) {
            throw new InvalidArgumentException("Ya existe un usuario con la cédula $ci.");
        }

        return $this->usuarioModel->crearUsuario($ci, $primerNombre, $pApellido, $email, $tel, $contrasenia, $rol, $idMunicipio);
    }
    public function setMunicipio($ci, $municipio) {
        $ci        = $this->validarCI($ci);
        $municipio = $this->validarMunicipio($municipio);
        $this->verificarAccesoPerfil($ci);

        return $this->usuarioModel->setMunicipio($ci, $municipio);
    }
}
?>