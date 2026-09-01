<?php
session_start();
require_once __DIR__ . '/../i18n/i18n.php';

       if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] !== 'vecino') {
    switch ($_SESSION['usuario']['rol']) {
        case 'administrador':
            header('Location: indexBackoffice.php');
            break;
        case 'cuadrilla':
            header('Location: ../API_Usuarios/Vista/cuadrilla.php');
            break;
        case 'operario':
            header('Location: ../API_Usuarios/Vista/operario.php');
            break;
        default:
            header('Location: indexBackoffice.php');
            break;
    }
    exit;
}


$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ci          = trim($_POST['ci'] ?? '');
    $contrasenia = $_POST['contrasenia'] ?? '';

    if (!preg_match('/^\d{6,8}$/', $ci)) {
        $_SESSION['login_error'] = t('backoffice.login_error_ci');
    } elseif (empty($contrasenia)) {
        $_SESSION['login_error'] = t('backoffice.login_error_contrasenia');
    } else {
        try {
            require_once __DIR__ . '/../conexion.php';
            require_once __DIR__ . '/autenticacion.php';
            $db   = new conexion();
            $conn = $db->getConexion();

            $resultado = autenticarUsuarioBackoffice($conn, $ci, $contrasenia);

            $mapaErrores = [
                'credenciales_invalidas' => 'backoffice.login_error_credenciales',
                'acceso_no_habilitado'   => 'backoffice.login_error_acceso_no_habilitado',
                'sin_permiso_vecino'     => 'backoffice.login_error_no_autorizado',
            ];

            if ($resultado['error']) {
                $clave = $mapaErrores[$resultado['error']] ?? 'backoffice.login_error_conexion';
                $_SESSION['login_error'] = t($clave);
            } else {
                $_SESSION['usuario'] = $resultado['usuario'];

                switch ($resultado['usuario']['rol']) {
                    case 'administrador':
                        header('Location: indexBackoffice.php');
                        break;
                    case 'cuadrilla':
                        header('Location: ../API_Usuarios/Vista/cuadrilla.php');
                        break;
                    case 'operario':
                        header('Location: ../API_Usuarios/Vista/operario.php');
                        break;
                    default:
                        header('Location: indexBackoffice.php');
                        break;
                }
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['login_error'] = t('backoffice.login_error_conexion');
        }
    }
    header('Location: login_backoffice.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('backoffice.login_title') ?></title>
    <link rel="stylesheet" href="styleBackoffice.css">
    <link rel="stylesheet" href="styleLogin.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">

        <?php include __DIR__ . '/../i18n/switcher.php'; ?>

        <div class="login-logo">
            <img src="../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix">
            <span>Recolix</span>
        </div>

        <h1><?= t('backoffice.login_titulo') ?></h1>
        <p class="login-subtitulo"><?= t('backoffice.login_subtitulo') ?></p>

        <?php if ($error): ?>
            <div class="login-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="form-row">
                <label for="ci"><?= t('backoffice.login_cedula') ?></label>
                <input
                    type="text"
                    id="ci"
                    name="ci"
                    placeholder="12345678"
                    value="<?= htmlspecialchars($_POST['ci'] ?? '') ?>"
                    required
                >
            </div>
            <div class="form-row">
                <label for="contrasenia"><?= t('backoffice.login_contrasenia') ?></label>
                <input type="password" id="contrasenia" name="contrasenia" placeholder="<?= htmlspecialchars(t('backoffice.login_contrasenia_placeholder')) ?>" required>
            </div>
            <button type="submit" class="btn-ingresar"><?= t('backoffice.login_ingresar') ?></button>
        </form>

        <p style="text-align:center; margin-top:1.25rem; font-size:0.88rem;">
            <a href="/recolix/LandingPage/index_LandingPage.html" style="color:var(--moss); text-decoration:none;"><?= t('backoffice.login_volver') ?></a>
        </p>

    </div>
</div>
</body>
</html>
