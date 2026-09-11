<?php

require_once __DIR__ . '/../../Modelo/solicitudRegistroModel.php';
require_once __DIR__ . '/../../Modelo/UsuarioModel.php';

final class SolicitudRegistroModelIntegrationTest extends DbTestCase
{
    public function testCrearSolicitudInsertaConAccesoPendienteYRolVecino(): void
    {
        $model = new SolicitudRegistroModel();
        $municipioId = $this->crearMunicipio();

        $ci = $model->crearSolicitud(87654321, 'Lucía', 'Gómez', 'lucia@recolix.uy', '099888777', $municipioId, 'claveTest123');

        $this->assertSame(87654321, $ci);
        $usuarioModel = new UsuarioModel();
        $perfil = $usuarioModel->getPerfil($ci);
        $this->assertSame('pendiente', $perfil['acceso']);
        $this->assertSame('vecino', $perfil['rol']);
    }

    public function testCrearSolicitudGuardaLaContraseniaHasheada(): void
    {
        $model = new SolicitudRegistroModel();
        $municipioId = $this->crearMunicipio();

        $ci = $model->crearSolicitud(87654321, 'Lucía', 'Gómez', 'lucia@recolix.uy', '099888777', $municipioId, 'claveTest123');

        $usuarioModel = new UsuarioModel();
        $this->assertNotNull($usuarioModel->login($ci, 'claveTest123'));
    }

    public function testCrearSolicitudRechazaCiYaRegistrado(): void
    {
        $model = new SolicitudRegistroModel();
        $ci = $this->crearUsuario();

        $this->expectException(Exception::class);
        $model->crearSolicitud($ci, 'Lucía', 'Gómez', 'lucia@recolix.uy', '099888777', null, 'claveTest123');
    }

    public function testExistePorCI(): void
    {
        $model = new SolicitudRegistroModel();
        $ci = $this->crearUsuario();

        $this->assertTrue($model->existePorCI($ci));
        $this->assertFalse($model->existePorCI(99999999));
    }

    public function testGetSolicitudesPendientesSoloTraeLasDeAccesoPendiente(): void
    {
        $model = new SolicitudRegistroModel();
        $this->crearUsuario(null, ['acceso' => 'pendiente']);
        $this->crearUsuario(null, ['acceso' => 'habilitado']);

        $this->assertCount(1, $model->getSolicitudesPendientes());
    }

    public function testAprobarCambiaAccesoAHabilitado(): void
    {
        $model = new SolicitudRegistroModel();
        $usuarioModel = new UsuarioModel();
        $ci = $this->crearUsuario(null, ['acceso' => 'pendiente']);

        $model->aprobar($ci);

        $this->assertSame('habilitado', $usuarioModel->getPerfil($ci)['acceso']);
    }

    public function testRechazarCambiaAccesoADeshabilitado(): void
    {
        $model = new SolicitudRegistroModel();
        $usuarioModel = new UsuarioModel();
        $ci = $this->crearUsuario(null, ['acceso' => 'pendiente']);

        $model->rechazar($ci);

        $this->assertSame('deshabilitado', $usuarioModel->getPerfil($ci)['acceso']);
    }
}
