<?php

require_once __DIR__ . '/../../Modelo/UsuarioModel.php';

final class UsuarioModelIntegrationTest extends DbTestCase
{
    public function testLoginDevuelveElUsuarioConCredencialesCorrectas(): void
    {
        $model = new UsuarioModel();
        $ci = $this->crearUsuario(null, ['primer_nombre' => 'Ana']);

        $usuario = $model->login($ci, 'claveTest123');

        $this->assertNotNull($usuario);
        $this->assertSame('Ana', $usuario['primer_nombre']);
        $this->assertArrayNotHasKey('contrasenia', $usuario);
    }

    public function testLoginDevuelveNullConContraseniaIncorrecta(): void
    {
        $model = new UsuarioModel();
        $ci = $this->crearUsuario();

        $this->assertNull($model->login($ci, 'claveIncorrecta'));
    }

    public function testLoginDevuelveNullConCiInexistente(): void
    {
        $model = new UsuarioModel();

        $this->assertNull($model->login(99999999, 'claveTest123'));
    }

    public function testGetPerfilTraeElNombreDelMunicipio(): void
    {
        $model = new UsuarioModel();
        $municipioId = $this->crearMunicipio('Municipio Norte');
        $ci = $this->crearUsuario(null, ['id_municipio' => $municipioId]);

        $perfil = $model->getPerfil($ci);

        $this->assertSame('Municipio Norte', $perfil['municipio']);
    }

    public function testGetPerfilDevuelveNullSiNoExiste(): void
    {
        $model = new UsuarioModel();

        $this->assertNull($model->getPerfil(99999999));
    }

    public function testUpdatePerfilModificaEmailYTelefono(): void
    {
        $model = new UsuarioModel();
        $ci = $this->crearUsuario();

        $model->updatePerfil($ci, 'nuevo@recolix.uy', '098765432');

        $perfil = $model->getPerfil($ci);
        $this->assertSame('nuevo@recolix.uy', $perfil['email']);
        $this->assertSame('098765432', $perfil['tel']);
    }

    public function testCambiarContraseniaActualizaElHash(): void
    {
        $model = new UsuarioModel();
        $ci = $this->crearUsuario();

        $model->cambiarContrasenia($ci, 'nuevaClaveSegura');

        $this->assertNotNull($model->login($ci, 'nuevaClaveSegura'));
        $this->assertNull($model->login($ci, 'claveTest123'));
    }

    public function testGetContraseniaDevuelveElHashAlmacenado(): void
    {
        $model = new UsuarioModel();
        $ci = $this->crearUsuario();

        $hash = $model->getContrasenia($ci);

        $this->assertTrue(password_verify('claveTest123', $hash));
    }

    public function testGetContraseniaDevuelveNullSiNoExiste(): void
    {
        $model = new UsuarioModel();

        $this->assertNull($model->getContrasenia(99999999));
    }

    public function testGetIncidenciasSoloTraeLasDeEseUsuario(): void
    {
        $model = new UsuarioModel();
        $ciUno = $this->crearUsuario();
        $ciDos = $this->crearUsuario();
        $this->crearIncidencia($ciUno);
        $this->crearIncidencia($ciUno);
        $this->crearIncidencia($ciDos);

        $this->assertCount(2, $model->getIncidencias($ciUno));
        $this->assertCount(1, $model->getIncidencias($ciDos));
    }

    public function testGetNotificacionesTraeSoloLasDelUsuarioOrdenadasPorPrioridad(): void
    {
        $model = new UsuarioModel();
        $ci = $this->crearUsuario();
        $informativa = $this->crearNotificacion(['mensaje' => 'Informativa', 'prioridad' => 'informativa']);
        $critica = $this->crearNotificacion(['mensaje' => 'Crítica', 'prioridad' => 'critica']);
        $this->vincularNotificacion($informativa, $ci);
        $this->vincularNotificacion($critica, $ci);

        $notificaciones = $model->getNotificaciones($ci);

        $this->assertCount(2, $notificaciones);
        $this->assertSame('Crítica', $notificaciones[0]['mensaje']);
    }

    public function testGetNotificacionesNoTraeLasDeOtroUsuario(): void
    {
        $model = new UsuarioModel();
        $ciUno = $this->crearUsuario();
        $ciDos = $this->crearUsuario();
        $idNotificacion = $this->crearNotificacion();
        $this->vincularNotificacion($idNotificacion, $ciDos);

        $this->assertSame([], $model->getNotificaciones($ciUno));
    }

    public function testMarcarNotificacionLeidaActualizaEstadoYFecha(): void
    {
        $model = new UsuarioModel();
        $ci = $this->crearUsuario();
        $idNotificacion = $this->crearNotificacion();
        $this->vincularNotificacion($idNotificacion, $ci);

        $model->marcarNotificacionLeida($idNotificacion, $ci);

        $notificaciones = $model->getNotificaciones($ci);
        $this->assertSame('leida', $notificaciones[0]['estado']);
        $this->assertNotNull($notificaciones[0]['fecha_lectura']);
    }

    public function testListarUsuariosTraeATodos(): void
    {
        $model = new UsuarioModel();
        $this->crearUsuario();
        $this->crearUsuario();

        $this->assertCount(2, $model->listarUsuarios());
    }

    public function testFiltrarRolDevuelveSoloCoincidencias(): void
    {
        $model = new UsuarioModel();
        $this->crearUsuario(null, ['rol' => 'vecino']);
        $this->crearUsuario(null, ['rol' => 'operario']);

        $this->assertCount(1, $model->filtrarRol('operario'));
    }

    public function testFiltrarAccesoDevuelveSoloCoincidencias(): void
    {
        $model = new UsuarioModel();
        $this->crearUsuario(null, ['acceso' => 'habilitado']);
        $this->crearUsuario(null, ['acceso' => 'pendiente']);

        $this->assertCount(1, $model->filtrarAcceso('pendiente'));
    }

    public function testCambiarRolActualizaElRol(): void
    {
        $model = new UsuarioModel();
        $ci = $this->crearUsuario(null, ['rol' => 'vecino']);

        $model->cambiarRol($ci, 'operario');

        $this->assertSame('operario', $model->getPerfil($ci)['rol']);
    }

    public function testCambiarAccesoActualizaElAcceso(): void
    {
        $model = new UsuarioModel();
        $ci = $this->crearUsuario(null, ['acceso' => 'pendiente']);

        $model->cambiarAcceso($ci, 'habilitado');

        $this->assertSame('habilitado', $model->getPerfil($ci)['acceso']);
    }

    public function testExisteCi(): void
    {
        $model = new UsuarioModel();
        $ci = $this->crearUsuario();

        $this->assertTrue($model->existeCi($ci));
        $this->assertFalse($model->existeCi(99999999));
    }

    public function testCrearUsuarioInsertaConAccesoHabilitado(): void
    {
        $model = new UsuarioModel();
        $municipioId = $this->crearMunicipio();

        $ci = $model->crearUsuario(87654321, 'Lucía', 'Gómez', 'lucia@recolix.uy', '099888777', 'claveTest123', 'vecino', $municipioId);

        $this->assertSame(87654321, $ci);
        $perfil = $model->getPerfil($ci);
        $this->assertSame('habilitado', $perfil['acceso']);
        $this->assertTrue(password_verify('claveTest123', $model->getContrasenia($ci)));
    }

    public function testSetMunicipioActualizaElIdMunicipioPorNombre(): void
    {
        $model = new UsuarioModel();
        $this->crearMunicipio('Municipio A');
        $ci = $this->crearUsuario();

        $ok = $model->setMunicipio($ci, 'Municipio A');

        $this->assertTrue((bool) $ok);
        $this->assertSame('Municipio A', $model->getPerfil($ci)['municipio']);
    }

    public function testSetMunicipioDevuelveFalseSiElMunicipioNoExiste(): void
    {
        $model = new UsuarioModel();
        $ci = $this->crearUsuario();

        $this->assertFalse($model->setMunicipio($ci, 'Municipio Inexistente'));
    }
}
