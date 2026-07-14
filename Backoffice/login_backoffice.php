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
            $db   = new conexion();
            $conn = $db->getConexion();

            $stmt = $conn->prepare(
                "SELECT ci_usuario, primer_nombre, p_apellido, rol, acceso, contrasenia
                 FROM USUARIO WHERE ci_usuario = ?"
            );
            $stmt->bind_param('i', $ci);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$row || !password_verify($contrasenia, $row['contrasenia'])) {
                $_SESSION['login_error'] = 'Cédula o contraseña incorrectos.';
            } elseif (!$row['acceso']) {
                $_SESSION['login_error'] = 'Tu acceso aún no está habilitado. Contactá al administrador.';
            } elseif ($row['rol'] === 'vecino') {
                $_SESSION['login_error'] = 'No tenés permiso para acceder al panel de administración.';
            } else {
                unset($row['contrasenia']);
                $_SESSION['usuario'] = $row;
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
    <style>
        body { align-items: center; justify-content: center; }

        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.5rem 2.25rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 24px rgba(28,33,23,0.08);
        }

        .login-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2rem;
        }
        .login-logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .login-logo span {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--moss);
        }

        .login-card h1 {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 0.35rem;
        }
        .login-subtitulo {
            color: var(--muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .form-row { margin-bottom: 1.25rem; }
        .form-row label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.4rem;
        }
        .form-row input {
            width: 100%;
            padding: 0.65rem 0.9rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: var(--bg);
            color: var(--text);
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-row input:focus { border-color: var(--moss); }

        .btn-ingresar {
            width: 100%;
            padding: 0.75rem;
            background: var(--moss);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            margin-top: 0.25rem;
            transition: background 0.2s;
        }
        .btn-ingresar:hover { background: var(--moss-dark); }

        .login-error {
            background: rgba(192,57,43,0.08);
            color: var(--red);
            border: 1px solid rgba(192,57,43,0.25);
            border-radius: 10px;
            padding: 0.7rem 1rem;
            font-size: 0.88rem;
            margin-bottom: 1.25rem;
        }
    </style>
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
