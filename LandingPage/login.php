<?php require_once __DIR__ . '/../extra/i18n/i18n.php'; ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">

<head>
  <meta charset="UTF-8">
  <meta name="google" content="notranslate">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    <?= t('login.title') ?>
  </title>
  <link rel="icon" type="image/png" href="/recolix/LandingPage/img/Logo.png">
  <meta name="description" content="<?= htmlspecialchars(t('login.meta_description')) ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="styles_LandingPage.css">
  <link rel="stylesheet" href="styles_Login.css">
</head>

<body>
  <header class="site-header" id="siteHeader">
    <div class="container header-inner">
      <a href="index_LandingPage.html" class="logo">
        <img src="img/Logo.png" alt="Logo Recolix" class="logo-image" />
        <div class="logo-text">Recolix</div>
      </a>
          <div class="header-actions">
        <?php include __DIR__ . '/../extra/i18n/switcher.php'; ?>
        <a href="index_LandingPage.html" class="btn btn-outline btn-sm">
          <?= t('common.nav_volver_sitio') ?>
        </a>
      </div>
    </div>
  </header>

  <main id="main">
    <section class="login-section">
      <div class="container login-container">
        <div class="login-card">
          <p class="section-eyebrow">
            <?= t('login.eyebrow') ?>
          </p>
          <h1>
            <?= t('login.titulo') ?>
          </h1>
          <p class="login-desc">
            <?= t('login.desc') ?>
          </p>

          <form id="loginForm" method="post" action="#" novalidate>
            <div class="form-row">
              <label for="ci">
                <?= t('login.cedula') ?>
              </label>
              <input type="text" id="ci" name="ci" placeholder="<?= htmlspecialchars(t('login.cedula_placeholder')) ?>"
                required>
              <p class="error-msg" id="ciError"></p>
            </div>

            <div class="form-row">
              <label for="password">
                <?= t('login.contrasenia') ?>
              </label>
              <input type="password" id="password" name="password"
                placeholder="<?= htmlspecialchars(t('login.contrasenia_placeholder')) ?>" required>
              <p class="error-msg" id="passwordError"></p>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="loginSubmitBtn">
              <?= t('login.submit') ?>
            </button>
            <p class="form-feedback" id="formFeedback" role="alert"></p>
          </form>

          <div class="register-banner">
            <p>
              <?= t('login.no_tenes_cuenta') ?>
            </p>
            <a href="index_LandingPage.html#registro" class="link-register">
              <?= t('login.crear_cuenta') ?>
            </a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="index_LandingPage.html" class="logo">
            <img src="img/LogoBlanco.png" alt="Logo Recolix" class="logo-imageBlanco" />
            <div class="logo-text">Recolix</div>
          </a>
          <p>
            <?= t('common.footer_desc') ?>
          </p>
        </div>
        <div class="footer-col">
          <h3>
            <?= t('common.footer_navegacion') ?>
          </h3>
          <ul>
            <li><a href="index_LandingPage.html">
                <?= t('common.footer_inicio') ?>
              </a></li>
            <li><a href="mapa.html">
                <?= t('common.footer_mapa') ?>
              </a></li>
            <li><a href="../Backoffice/login_backoffice.php">
                <?= t('common.footer_portal_empleados') ?>
              </a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h3>
            <?= t('common.footer_canales') ?>
          </h3>
          <ul>
            <li>
              <?= t('common.footer_linea') ?>
            </li>
            <li>
              <?= t('common.footer_whatsapp') ?>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

  <script>
    const API_USUARIOS = '/recolix/API_Usuarios/api.php';
    const I18N = <?= json_encode(t_array([
      'ciInvalid' => 'login.validation_ci',
        'passwordShort' => 'login.validation_contrasenia',
          'revisarDatos' => 'login.feedback_revisar_datos',
            'credencialesInvalidas' => 'login.feedback_credenciales_invalidas',
              'loginCorrecto' => 'login.feedback_login_correcto',
                'errorConexion' => 'login.feedback_error_conexion',
    ])) ?>;

    document.addEventListener('DOMContentLoaded', function () {

      const form = document.getElementById('loginForm');
      const ciError = document.getElementById('ciError');
      const passwordError = document.getElementById('passwordError');
      const feedback = document.getElementById('formFeedback');

      form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const ci = form.elements.ci.value.trim();
        const password = form.elements.password.value.trim();
        let esValido = true;

        if (!/^\d{7,8}$/.test(ci)) {
          ciError.textContent = I18N.ciInvalid;
          esValido = false;
        } else {
          ciError.textContent = '';
        }

        if (password.length < 4) {
          passwordError.textContent = I18N.passwordShort;
          esValido = false;
        } else {
          passwordError.textContent = '';
        }

        if (!esValido) {
          feedback.style.display = 'block';
          feedback.className = 'form-feedback is-error';
          feedback.textContent = I18N.revisarDatos;
          return;
        }


        try {
          const response = await fetch(`${API_USUARIOS}/usuarios/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ci, contrasenia: password })
          });
          const data = await response.json();

          if (data.status !== 'success') {
            feedback.style.display = 'block';
            feedback.className = 'form-feedback is-error';
            feedback.textContent = data.message || I18N.credencialesInvalidas;
            return;
          }

          feedback.style.display = 'block';
          feedback.className = 'form-feedback is-success';
          feedback.textContent = I18N.loginCorrecto;
          const usuario = data.data;

          window.location.href = '/recolix/API_Usuarios/vecino.php';

        } catch (err) {
          console.error(err);
          feedback.style.display = 'block';
          feedback.className = 'form-feedback is-error';
          feedback.textContent = I18N.errorConexion;
        }
      });
    });

  </script>
  <script src="sesionNav.js"></script>
</body>

</html>