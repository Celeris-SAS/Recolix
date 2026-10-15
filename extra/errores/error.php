<?php
// Página de error única. Apache llega acá vía ErrorDocument (ver .htaccess de la raíz)
// y deja en REDIRECT_STATUS el código y en REDIRECT_URL la URL que falló.
session_start();

$errores = [
    400 => ['Solicitud incorrecta', 'El servidor no pudo entender la solicitud. Revisá la dirección e intentá de nuevo.'],
    401 => ['No autorizado', 'Necesitás iniciar sesión para acceder a esta página.'],
    403 => ['Acceso denegado', 'No tenés permisos para ver esta página.'],
    404 => ['Página no encontrada', 'La página que buscás no existe o fue movida.'],
    500 => ['Error del servidor', 'Algo salió mal de nuestro lado. Intentá de nuevo en unos minutos.'],
    503 => ['Servicio no disponible', 'Estamos haciendo mantenimiento. Volvé a intentar en un rato.'],
];

$codigo = (int) ($_SERVER['REDIRECT_STATUS'] ?? 404);
if (!isset($errores[$codigo])) {
    $codigo = 404;
}
[$titulo, $descripcion] = $errores[$codigo];

// El error viene del portal interno si la URL original es del Backoffice o de las vistas de las APIs.
$urlOriginal = $_SERVER['REDIRECT_URL'] ?? '';
$vieneDelBackoffice = preg_match('#^/recolix/(Backoffice|API_[^/]+/Vista)(/|$)#i', $urlOriginal) === 1;

$rol = $_SESSION['usuario']['rol'] ?? '';
$sesionInterna = isset($_SESSION['usuario']) && $rol !== '' && $rol !== 'vecino';

if ($vieneDelBackoffice && $sesionInterna) {
    // Mismo destino por rol que usa login_backoffice.php.
    switch ($rol) {
        case 'cuadrilla':
            $destino = '/Recolix/API_Usuarios/Vista/cuadrilla.php';
            break;
        case 'operario':
            $destino = '/Recolix/API_Usuarios/Vista/operario.php';
            break;
        default:
            $destino = '/Recolix/Backoffice/indexBackoffice.php';
            break;
    }
    $textoBoton = 'Volver al panel';
} elseif ($vieneDelBackoffice) {
    $destino = '/Recolix/Backoffice/login_backoffice.php';
    $textoBoton = 'Iniciar sesión';
} else {
    $destino = '/Recolix/LandingPage/index_LandingPage.html';
    $textoBoton = 'Volver al inicio';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error <?= $codigo ?> | Recolix</title>
  <link rel="icon" href="/Recolix/LandingPage/img/Logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;600&display=swap">
  <link rel="stylesheet" href="/Recolix/extra/errores/errores.css">
</head>
<body>
  <main class="error-wrap">
    <div class="error-logo">
      <img src="/Recolix/LandingPage/img/Logo.png" alt="">
      <span>Recolix</span>
    </div>
    <h1 class="error-codigo"><?= $codigo ?></h1>
    <h2 class="error-titulo"><?= $titulo ?></h2>
    <p class="error-desc"><?= $descripcion ?></p>
    <a class="btn-volver" href="<?= $destino ?>"><?= $textoBoton ?></a>
  </main>
</body>
</html>
