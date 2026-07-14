<?php
session_start();

 //Si ya está logueado con un rol válido, redirigir al panel.
    if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] !== 'vecino') {
    header('Location: indexBackoffice.php');
    exit;
}

// Leer y limpiar el error flash de sesión (patrón PRG)
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ci          = trim($_POST['ci'] ?? '');
    $contrasenia = $_POST['contrasenia'] ?? '';

    if (!preg_match('/^\d{6,8}$/', $ci)) {
        $_SESSION['login_error'] = 'Ingresá tu cédula sin puntos ni guiones (6 a 8 dígitos).';
    } elseif (empty($contrasenia)) {
        $_SESSION['login_error'] = 'Ingresá tu contraseña.';
    } else {
        try {
            require_once __DIR__ . '/../conexion.php';
            require_once __DIR__ . '/autenticacion.php';
            $db   = new conexion();
            $conn = $db->getConexion();

            $resultado = autenticarUsuarioBackoffice($conn, $ci, $contrasenia);

            if ($resultado['error']) {
                $_SESSION['login_error'] = $resultado['error'];
            } else {
                $_SESSION['usuario'] = $resultado['usuario'];
                header('Location: indexBackoffice.php');
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['login_error'] = 'Error al conectar con el servidor. Intentá de nuevo más tarde.';
        }
    }
    header('Location: login_backoffice.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Empleados — Recolix</title>
    <link rel="stylesheet" href="styleBackoffice.css">
    <link rel="stylesheet" href="styleLogin.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">

        <div class="login-logo">
            <img src="../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix">
            <span>Recolix</span>
        </div>

        <h1>Portal de Empleados</h1>
        <p class="login-subtitulo">Ingresá con tu cédula y contraseña.</p>

        <?php if ($error): ?>
            <div class="login-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="form-row">
                <label for="ci">Cédula de identidad</label>
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
                <label for="contrasenia">Contraseña</label>
                <input type="password" id="contrasenia" name="contrasenia" placeholder="Tu contraseña" required>
            </div>
            <button type="submit" class="btn-ingresar">Ingresar</button>
        </form>

        <p style="text-align:center; margin-top:1.25rem; font-size:0.88rem;">
            <a href="/recolix/LandingPage/index_LandingPage.html" style="color:var(--moss); text-decoration:none;">← Volver a la página principal</a>
        </p>

    </div>
</div>
</body>
</html>
