<?php
session_start();

if (!isset($_SESSION['ci_usuario'])) {
    header("Location: ../LandingPage/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recolix — Mi panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="Vista/styles_vecino.css">
</head>
<body>
    <header class="site-header" id="siteHeader">
        <div class="container header-inner">
            <a href="vecino.php" class="logo">
                <img src="Vista/Logo.png" alt="Logo Recolix" class="logo-image" />
                <div class="logo-text">Recolix</div>
            </a>
            <nav class="main-nav" id="mainNav">
                <ul>
                    <li><a href="../LandingPage/index_LandingPage.html">Inicio</a></li>
                    <li><a href="../LandingPage/mapa.html">Mapa</a></li>
                </ul>
            </nav>
            <div class="nav-right">
                <a href="vecino.php" class="btn-user" title="Perfil de usuario">
                    <img src="Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="Menú">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>
     <main class="container dash-main">

        <section class="dash-section" id="seccionPerfil">
            <div class="section-header">
                <div>
                    <p class="section-eyebrow">Mi cuenta</p>
                    <h2>Mis datos</h2>
                </div>
                <button class="btn btn-outline btn-sm" id="editarPerfilBtn">Editar datos</button>
            </div>

            <div class="panel-card" id="perfilVista">
                <div class="perfil-grid">
                    <div class="perfil-campo">
                        <p class="campo-label">Nombre completo</p>
                        <p class="campo-valor" id="perfilNombre">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label">Cédula de identidad</p>
                        <p class="campo-valor" id="perfilCI">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label">Correo electrónico</p>
                        <p class="campo-valor" id="perfilEmail">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label">Teléfono</p>
                        <p class="campo-valor" id="perfilTel">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label">Municipio</p>
                        <p class="campo-valor" id="perfilMunicipio">—</p>
                    </div>
                </div>
                <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">
                    <button class="btn btn-outline btn-sm" id="cambiarContraseniaBtn">Cambiar contraseña</button>
                    <button class="btn btn-primary btn-sm" id="asignarMunicipioBtn">Agregar municipio</button>
                </div>
            </div>

            <div class="panel-card" id="municipioForm" hidden>
                <h3 id="municipioFormTitle">Agregar municipio</h3>
                <p class="form-nota" id="municipioFormNota">Todavía no tenés un municipio asignado. Seleccioná el que corresponde a tu domicilio.</p>
                <div class="form-row">
                    <label for="municipioSelect">Municipio</label>
                    <select id="municipioSelect">
                        <option value="">Seleccioná tu municipio</option>
                        <option value="Municipio A">Municipio A</option>
                        <option value="Municipio B">Municipio B</option>
                        <option value="Municipio C">Municipio C</option>
                        <option value="Municipio CH">Municipio CH</option>
                        <option value="Municipio D">Municipio D</option>
                        <option value="Municipio E">Municipio E</option>
                        <option value="Municipio F">Municipio F</option>
                        <option value="Municipio G">Municipio G</option>
                    </select>
                    <p class="error-msg" id="municipioSelectError"></p>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="guardarMunicipioBtn">Guardar municipio</button>
                    <button class="btn btn-outline btn-sm" id="cancelarMunicipioBtn">Cancelar</button>
                </div>
                <p class="form-feedback" id="municipioFeedback"></p>
            </div>

            <div class="panel-card" id="perfilForm" hidden>
                <h3>Editar datos de contacto</h3>
                <p class="form-nota">Solo podés modificar tu correo y teléfono. Para otros cambios contactá al
                    administrador.</p>
                <div class="form-row">
                    <label for="editEmail">Correo electrónico</label>
                    <input type="email" id="editEmail" name="email" placeholder="nombre@correo.com">
                    <p class="error-msg" id="editEmailError"></p>
                </div>
                <div class="form-row">
                    <label for="editTel">Teléfono</label>
                    <input type="tel" id="editTel" name="tel" placeholder="Ej: 099123456">
                    <p class="error-msg" id="editTelError"></p>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="guardarPerfilBtn">Guardar cambios</button>
                    <button class="btn btn-outline btn-sm" id="cancelarPerfilBtn">Cancelar</button>
                </div>
                <p class="form-feedback" id="perfilFeedback"></p>
            </div>

            <div class="panel-card" id="contraseniaForm" hidden>
                <h3>Cambiar contraseña</h3>
                <div class="form-row">
                    <label for="contraActual">Contraseña actual</label>
                    <input type="password" id="contraActual" placeholder="Tu contraseña actual">
                    <p class="error-msg" id="contraActualError"></p>
                </div>
                <div class="form-row">
                    <label for="contraNueva">Nueva contraseña</label>
                    <input type="password" id="contraNueva" placeholder="Mínimo 8 caracteres">
                    <p class="error-msg" id="contraNuevaError"></p>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="guardarContraBtn">Cambiar contraseña</button>
                    <button class="btn btn-outline btn-sm" id="cancelarContraBtn">Cancelar</button>
                </div>
                <p class="form-feedback" id="contraFeedback"></p>
            </div>
        </section>

        <section class="dash-section" id="seccionIncidencias">
            <div class="section-header">
                <div>
                    <p class="section-eyebrow">Mis reportes</p>
                    <h2>Incidencias</h2>
                </div>
                <button class="btn btn-primary btn-sm" id="nuevaIncidenciaBtn">+ Nueva incidencia</button>
            </div>

            <div id="incidenciasLista">
                <p class="estado-carga">Cargando tus incidencias...</p>
            </div>
        </section>

        <section class="dash-section" id="seccionNotificaciones">
            <div class="section-header">
                <div>
                    <p class="section-eyebrow">Alertas</p>
                    <h2>Notificaciones</h2>
                </div>
            </div>
            <div id="notificacionesLista">
                <p class="estado-carga">Cargando notificaciones...</p>
            </div>
            <div class="logout">
                <button class="btn btn-outline btn-sm" id="logoutBtn">Cerrar sesión</button>
                
            </div>
        </section>

    </main>
    <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="vecino.php" class="logo">
            <img src="../LandingPage/img/LogoBlanco.png" alt="Logo Recolix" class="logo-imageBlanco" />
            <div class="logo-text">Recolix</div>
          </a>
          <p>Plataforma de gestión de residuos urbanos para Montevideo.</p>
        </div>
        <div class="footer-col">
          <h3>Navegación</h3>
          <ul>
            <li><a href="../LandingPage/index_LandingPage.html">Inicio</a></li>
            <li><a href="../LandingPage/mapa.html">Mapa</a></li>
            <li><a href="../Backoffice/login_backoffice.php">Portal de empleados</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h3>Canales de la Intendencia</h3>
          <ul>
            <li>Línea 1950 3000</li>
            <li>Whatsapp: 092 250 260</li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

    <script>const CI = <?= json_encode($_SESSION['ci_usuario']) ?>;</script>
    <script src="Vista/vecino.js"></script>
</body>

</html>
