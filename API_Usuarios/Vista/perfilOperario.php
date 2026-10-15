<?php
$rolesPermitidos = ['operario'];
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">
<head>
<meta charset="UTF-8">
<meta name="google" content="notranslate">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('perfil.title') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../Backoffice/styleBackoffice.css">
<link rel="stylesheet" href="styles_vecino.css">
<style>
    /* Forzar menú hamburguesa siempre visible en esta vista */
    .nav-links {
        display: none !important;
    }
    .nav-hamburger {
        display: flex !important;
    }
</style>
</head>

<body>

    <header class="navbar">
        <div class="nav-inner">
            <div class="nav-left">
                <a href="operario.php" class="brand">
                    <img src="../../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                    <span class="brand-name">Recolix</span>
                </a>
                <nav class="nav-links">
                    <a href="operario.php"><?= t('operario.nav_inicio') ?></a>
                </nav>
            </div>
            <div class="nav-right">
                <?php include __DIR__ . '/../../extra/i18n/switcher.php'; ?>
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/recolix/extra/Comun/campana.php'; ?>
                <a href="perfilOperario.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                    <img src="../../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="<?= htmlspecialchars(t('backoffice.nav_menu')) ?>">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
        <nav class="nav-mobile" id="navMobile">
            <a href="operario.php"><?= t('operario.nav_inicio') ?></a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    <main class="container dash-main">
        <section class="dash-section" id="seccionPerfil">
            <div class="section-header">
                <div>
                    <p class="section-eyebrow"><?= t('perfil.eyebrow') ?></p>
                    <h2><?= t('perfil.titulo') ?></h2>
                </div>
                <button class="btn btn-outline btn-sm" id="editarPerfilBtn"><?= t('perfil.btn_editar') ?></button>
            </div>

            <div class="panel-card" id="perfilVista">
                <div class="perfil-grid">
                    <div class="perfil-campo">
                        <p class="campo-label"><?= t('perfil.label_nombre_completo') ?></p>
                        <p class="campo-valor" id="perfilNombre">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label"><?= t('perfil.label_cedula') ?></p>
                        <p class="campo-valor" id="perfilCI">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label"><?= t('perfil.label_email') ?></p>
                        <p class="campo-valor" id="perfilEmail">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label"><?= t('perfil.label_telefono') ?></p>
                        <p class="campo-valor" id="perfilTel">—</p>
                    </div>
                </div>
                <button class="btn btn-outline btn-sm" id="cambiarContraseniaBtn" style="margin-top:20px;"><?= t('perfil.btn_cambiar_contrasenia') ?></button>
            </div>

            <div class="panel-card" id="perfilForm" hidden>
                <h3><?= t('perfil.titulo_editar') ?></h3>
                <p class="form-nota"><?= t('perfil.nota_editar') ?></p>
                <div class="form-row">
                    <label for="editNombre"><?= t('perfil.field_nombre') ?></label>
                    <input type="text" id="editNombre" name="nombre" maxlength="50">
                    <p class="error-msg" id="editNombreError"></p>
                </div>
                <div class="form-row">
                    <label for="editApellido"><?= t('perfil.field_apellido') ?></label>
                    <input type="text" id="editApellido" name="apellido" maxlength="50">
                    <p class="error-msg" id="editApellidoError"></p>
                </div>
                <div class="form-row">
                    <label for="editEmail"><?= t('perfil.field_email') ?></label>
                    <input type="email" id="editEmail" name="email" placeholder="<?= htmlspecialchars(t('perfil.field_email_ph')) ?>">
                    <p class="error-msg" id="editEmailError"></p>
                </div>
                <div class="form-row">
                    <label for="editTel"><?= t('perfil.field_tel') ?></label>
                    <input type="tel" id="editTel" name="tel" placeholder="<?= htmlspecialchars(t('perfil.field_tel_ph')) ?>">
                    <p class="error-msg" id="editTelError"></p>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="guardarPerfilBtn"><?= t('perfil.btn_guardar_cambios') ?></button>
                    <button class="btn btn-outline btn-sm" id="cancelarPerfilBtn"><?= t('perfil.btn_cancelar') ?></button>
                </div>
                <p class="form-feedback" id="perfilFeedback"></p>
            </div>

            <div class="panel-card" id="contraseniaForm" hidden>
                <h3><?= t('perfil.titulo_cambiar_contrasenia') ?></h3>
                <div class="form-row">
                    <label for="contraActual"><?= t('perfil.field_contra_actual') ?></label>
                    <input type="password" id="contraActual" placeholder="<?= htmlspecialchars(t('perfil.field_contra_actual_ph')) ?>">
                    <p class="error-msg" id="contraActualError"></p>
                </div>
                <div class="form-row">
                    <label for="contraNueva"><?= t('perfil.field_contra_nueva') ?></label>
                    <input type="password" id="contraNueva" placeholder="<?= htmlspecialchars(t('perfil.field_contra_nueva_ph')) ?>">
                    <p class="error-msg" id="contraNuevaError"></p>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="guardarContraBtn"><?= t('perfil.btn_cambiar_contrasenia') ?></button>
                    <button class="btn btn-outline btn-sm" id="cancelarContraBtn"><?= t('perfil.btn_cancelar') ?></button>
                </div>
                <p class="form-feedback" id="contraFeedback"></p>
            </div>

            <div class="panel-card" id="borrarCuentaConfirm" hidden>
                <h3><?= t('perfil.titulo_borrar_cuenta') ?></h3>
                <p class="form-nota"><?= t('perfil.nota_borrar_cuenta') ?></p>
                <div class="form-actions">
                    <button class="btn btn-peligro" id="confirmarBorrarBtn"><?= t('perfil.btn_confirmar_borrar') ?></button>
                    <button class="btn btn-outline btn-sm" id="cancelarBorrarBtn"><?= t('perfil.btn_cancelar') ?></button>
                </div>
                <p class="form-feedback" id="borrarFeedback"></p>
            </div>

            <div class="panel-card" id="borrarCuentaTrabajador" hidden>
                <h3><?= t('perfil.titulo_borrar_trabajador') ?></h3>
                <p class="form-nota"><?= t('perfil.nota_borrar_trabajador') ?></p>
                <div class="form-actions">
                    <button class="btn btn-outline btn-sm" id="cerrarAvisoTrabajadorBtn"><?= t('perfil.btn_entendido') ?></button>
                </div>
            </div>

            <div class="logout">
                <button class="btn btn-peligro btn-sm" id="borrarCuentaBtn" data-ci="<?= htmlspecialchars((string) ($_SESSION['usuario']['ci_usuario'] ?? null)) ?>" data-es-vecino="<?= (false) ? '1' : '0' ?>" data-msg-error="<?= htmlspecialchars(t('perfil.msg_no_pudo_borrar')) ?>"><?= t('perfil.btn_borrar_cuenta') ?></button>
                <button class="btn btn-outline btn-sm" id="logoutBtn"><?= t('perfil.btn_cerrar_sesion') ?></button>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="../../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="footer-logo-img">
                    <span class="footer-logo-name">Recolix</span>
                </div>
                <p><?= t('common.footer_desc') ?></p>
            </div>
            <div class="footer-col">
                <h3><?= t('common.footer_navegacion') ?></h3>
                <ul>
                    <li><a href="../../LandingPage/index_LandingPage.html"><?= t('backoffice.footer_pagina_principal') ?></a></li>
                    <li><a href="../../LandingPage/mapa.html"><?= t('backoffice.footer_mapa_publico') ?></a></li>
                </ul>
            </div>
        </div>
        <div class="footer-hr"></div>
        <p class="footer-copy"><?= t('backoffice.footer_copyright') ?></p>
    </footer>

    <script>
        const API_BASE = '/recolix/API_Usuarios/api.php';
        const CI = <?= json_encode($_SESSION['usuario']['ci_usuario'] ?? null) ?>;

        const perfilVista = document.getElementById('perfilVista');
        const perfilForm = document.getElementById('perfilForm');
        const contraseniaForm = document.getElementById('contraseniaForm');

        const perfilNombreEl = document.getElementById('perfilNombre');
        const perfilCIEl = document.getElementById('perfilCI');
        const perfilEmailEl = document.getElementById('perfilEmail');
        const perfilTelEl = document.getElementById('perfilTel');

        const editarPerfilBtn = document.getElementById('editarPerfilBtn');
        const cambiarContraseniaBtn = document.getElementById('cambiarContraseniaBtn');
        const cancelarPerfilBtn = document.getElementById('cancelarPerfilBtn');
        const cancelarContraBtn = document.getElementById('cancelarContraBtn');
        const guardarPerfilBtn = document.getElementById('guardarPerfilBtn');
        const guardarContraBtn = document.getElementById('guardarContraBtn');

        const editNombre = document.getElementById('editNombre');
        const editApellido = document.getElementById('editApellido');
        const editNombreError = document.getElementById('editNombreError');
        const editApellidoError = document.getElementById('editApellidoError');
        const editEmail = document.getElementById('editEmail');
        const editTel = document.getElementById('editTel');
        const editEmailError = document.getElementById('editEmailError');
        const editTelError = document.getElementById('editTelError');
        const perfilFeedback = document.getElementById('perfilFeedback');

        const contraActual = document.getElementById('contraActual');
        const contraNueva = document.getElementById('contraNueva');
        const contraActualError = document.getElementById('contraActualError');
        const contraNuevaError = document.getElementById('contraNuevaError');
        const contraFeedback = document.getElementById('contraFeedback');

        let perfilDatos = {};

        async function cargarPerfil() {
            if (!CI) return;
            try {
                const res = await fetch(`${API_BASE}/usuarios/${CI}`);
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    const u = json.data;
                    perfilDatos = u;
                    perfilNombreEl.textContent = `${u.primer_nombre} ${u.p_apellido}`;
                    perfilCIEl.textContent = u.ci_usuario;
                    perfilEmailEl.textContent = u.email;
                    perfilTelEl.textContent = u.tel;
                }
            } catch (err) {
                console.error(err);
            }
        }
        cargarPerfil();

        function limpiarFeedback(el) {
            el.textContent = '';
            el.classList.remove('is-success', 'is-error');
        }

        function mostrarVista() {
            perfilVista.hidden = false;
            perfilForm.hidden = true;
            contraseniaForm.hidden = true;
        }

        function mostrarFormPerfil() {
            editNombre.value = perfilDatos.primer_nombre || '';
            editApellido.value = perfilDatos.p_apellido || '';
            editNombreError.textContent = '';
            editApellidoError.textContent = '';
            editEmail.value = perfilEmailEl.textContent;
            editTel.value = perfilTelEl.textContent;
            editEmailError.textContent = '';
            editTelError.textContent = '';
            limpiarFeedback(perfilFeedback);
            perfilVista.hidden = true;
            perfilForm.hidden = false;
            contraseniaForm.hidden = true;
        }

        function mostrarFormContrasenia() {
            contraActual.value = '';
            contraNueva.value = '';
            contraActualError.textContent = '';
            contraNuevaError.textContent = '';
            limpiarFeedback(contraFeedback);
            perfilVista.hidden = true;
            contraseniaForm.hidden = false;
            perfilForm.hidden = true;
        }

        editarPerfilBtn.addEventListener('click', mostrarFormPerfil);
        cambiarContraseniaBtn.addEventListener('click', mostrarFormContrasenia);
        cancelarPerfilBtn.addEventListener('click', mostrarVista);
        cancelarContraBtn.addEventListener('click', mostrarVista);

        guardarPerfilBtn.addEventListener('click', async function () {
            editEmailError.textContent = '';
            editTelError.textContent = '';
            limpiarFeedback(perfilFeedback);

            editNombreError.textContent = '';
            editApellidoError.textContent = '';

            const primerNombre = editNombre.value.trim();
            const pApellido = editApellido.value.trim();
            const email = editEmail.value.trim();
            const tel = editTel.value.trim();
            let valido = true;

            const regexNombre = /^[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]{2,50}$/;
            if (!regexNombre.test(primerNombre)) {
                editNombreError.textContent = <?= json_encode(t('perfil.msg_nombre_invalido')) ?>;
                valido = false;
            }
            if (!regexNombre.test(pApellido)) {
                editApellidoError.textContent = <?= json_encode(t('perfil.msg_nombre_invalido')) ?>;
                valido = false;
            }

            if (!email || !email.includes('@')) {
                editEmailError.textContent = <?= json_encode(t('perfil.msg_email_invalido')) ?>;
                valido = false;
            }
            if (!tel) {
                editTelError.textContent = <?= json_encode(t('perfil.msg_tel_requerido')) ?>;
                valido = false;
            }
            if (!valido) return;

            try {
                const res = await fetch(`${API_BASE}/usuarios/${CI}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, tel, primerNombre, pApellido })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    perfilDatos = { ...perfilDatos, primer_nombre: primerNombre, p_apellido: pApellido };
                    perfilNombreEl.textContent = `${primerNombre} ${pApellido}`;
                    perfilEmailEl.textContent = email;
                    perfilTelEl.textContent = tel;
                    perfilFeedback.textContent = <?= json_encode(t('perfil.msg_datos_actualizados')) ?>;
                    perfilFeedback.classList.add('is-success');
                    setTimeout(mostrarVista, 900);
                } else {
                    perfilFeedback.textContent = json.message || <?= json_encode(t('perfil.msg_no_pudo_guardar')) ?>;
                    perfilFeedback.classList.add('is-error');
                }
            } catch (err) {
                perfilFeedback.textContent = <?= json_encode(t('perfil.msg_no_pudo_guardar')) ?>;
                perfilFeedback.classList.add('is-error');
            }
        });

        guardarContraBtn.addEventListener('click', async function () {
            contraActualError.textContent = '';
            contraNuevaError.textContent = '';
            limpiarFeedback(contraFeedback);

            let valido = true;

            if (!contraActual.value) {
                contraActualError.textContent = <?= json_encode(t('perfil.msg_contra_actual_requerida')) ?>;
                valido = false;
            }
            if (contraNueva.value.length < 8) {
                contraNuevaError.textContent = <?= json_encode(t('perfil.msg_contra_nueva_min')) ?>;
                valido = false;
            }
            if (!valido) return;

            try {
                const res = await fetch(`${API_BASE}/usuarios/cambiarContrasenia`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ci: CI, contraseniaActual: contraActual.value, nuevaContrasenia: contraNueva.value })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    contraFeedback.textContent = <?= json_encode(t('perfil.msg_contra_actualizada')) ?>;
                    contraFeedback.classList.add('is-success');
                    setTimeout(mostrarVista, 900);
                } else {
                    contraFeedback.textContent = json.message || <?= json_encode(t('perfil.msg_no_pudo_cambiar_contra')) ?>;
                    contraFeedback.classList.add('is-error');
                }
            } catch (err) {
                contraFeedback.textContent = <?= json_encode(t('perfil.msg_no_pudo_cambiar_contra')) ?>;
                contraFeedback.classList.add('is-error');
            }
        });

        document.getElementById('logoutBtn').addEventListener('click', function () {
    window.location.href = '/recolix/Backoffice/logout_backoffice.php';
        });

        function toggleMobileMenu() {
            document.getElementById('navMobile').classList.toggle('nav-mobile-open');
        }
    </script>
    <script src="borrarCuenta.js"></script>
</body>
</html>