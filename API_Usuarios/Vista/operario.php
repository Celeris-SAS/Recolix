<?php
$rolesPermitidos = ['operario'];
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';

// El centro se vuelve a leer de la base en cada carga (no solo de la sesión):
// si un admin asigna o cambia el centro del usuario mientras ya tenía la sesión
// abierta, el dato en $_SESSION quedaría viejo (mismo criterio que cuadrilla.php).
require_once __DIR__ . '/../../conexion.php';
$conn = new conexion();
$stmt = mysqli_prepare($conn->getConexion(), "SELECT id_centro FROM USUARIO WHERE ci_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario']['ci_usuario']);
mysqli_stmt_execute($stmt);
$filaUsuario = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

$idCentro = $filaUsuario['id_centro'] ?? null;
$_SESSION['usuario']['id_centro'] = $idCentro;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">
<head>
<meta charset="UTF-8">
<meta name="google" content="notranslate">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('operario.title') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../Backoffice/styleBackoffice.css">
<link rel="stylesheet" href="styles_vecino.css">
<link rel="stylesheet" href="operario.css">
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
                    <a href="operario.php" class="nav-active"><?= t('operario.nav_inicio') ?></a>
                </nav>
            </div>
            <div class="nav-right">
                <?php include __DIR__ . '/../../i18n/switcher.php'; ?>
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/recolix/Comun/campana.php'; ?>
                <a href="perfilOperario.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">                    <img src="../../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="<?= htmlspecialchars(t('backoffice.nav_menu')) ?>">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
               <nav class="nav-mobile" id="navMobile">
            <a href="operario.php" class="nav-active"><?= t('operario.nav_inicio') ?></a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    <main class="container dash-main">

        <div class="page-title">
            <h1><?= t('operario.page_titulo') ?></h1>
            <p><?= t('operario.page_sub') ?></p>
        </div>

        <!-- Usuario sin id_centro asignado (USUARIO.id_centro) -->
        <div class="alert-banner alert-banner--info" id="sin-centro-banner" hidden>
            <span class="alert-banner-icon">ℹ</span>
            <span><?= t('operario.msg_sin_centro') ?></span>
        </div>

        <!-- RF77: Listado de centros de acopio (el/los que tiene asignados este operario) -->
        <section class="dash-section" id="seccion-centro">
            <div class="panel-card">
                <div class="card-header">
                    <span class="card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h1"/><path d="M14 9h1"/><path d="M9 13h1"/><path d="M14 13h1"/><path d="M9 21v-4h6v4"/></svg>
                    </span>
                    <div>
                        <h2><?= t('operario.centro_titulo') ?></h2>
                        <p><?= t('operario.centro_sub') ?></p>
                    </div>
                </div>

                <div class="centro-row">
                    <div class="centro-titulo">
                        <h3 id="centro-nombre"></h3>
                        <span class="badge" id="centro-estado-badge"></span>
                    </div>
                    <div class="centro-datos">
                        <span class="centro-item"><?= t('operario.centro_tipo_label') ?> <strong id="centro-tipo"></strong></span>
                        <span class="centro-item"><?= t('operario.centro_direccion_label') ?> <strong id="centro-direccion"></strong></span>
                        <span class="centro-item"><?= t('operario.centro_capacidad_label') ?> <strong id="centro-capacidad"></strong></span>
                    </div>
                </div>


            </div>
        </section>

        <!-- RF78: Consulta de ocupación del centro de acopio -->
        <section class="dash-section" id="seccion-ocupacion">
            <div class="panel-card">
                <div class="card-header">
                    <span class="card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15v4"/><path d="M12 10v9"/><path d="M17 6v13"/></svg>
                    </span>
                    <div>
                        <h2><?= t('operario.ocupacion_titulo') ?></h2>
                        <p><?= t('operario.ocupacion_sub') ?></p>
                    </div>
                </div>

                <div class="ocupacion-list" id="ocupacion-list">
                    <div class="ocupacion-item" data-tipo="Plástico">
                        <div class="ocupacion-item-header">
                            <span class="tipo-nombre"><?= t('operario.tipo_plastico') ?></span>
                            <span class="tipo-valores"><span class="valor-actual">2040</span> / <span class="valor-capacidad">3000</span> kg (<span class="valor-porcentaje">68</span>%)</span>
                        </div>
                        <div class="progreso-track">
                            <div class="progreso-fill" style="width: 68%;"></div>
                        </div>
                    </div>

                    <div class="ocupacion-item" data-tipo="Papel y cartón">
                        <div class="ocupacion-item-header">
                            <span class="tipo-nombre"><?= t('operario.tipo_papel_carton') ?></span>
                            <span class="tipo-valores"><span class="valor-actual">1350</span> / <span class="valor-capacidad">3000</span> kg (<span class="valor-porcentaje">45</span>%)</span>
                        </div>
                        <div class="progreso-track">
                            <div class="progreso-fill" style="width: 45%;"></div>
                        </div>
                    </div>

                    <div class="ocupacion-item" data-tipo="Vidrio">
                        <div class="ocupacion-item-header">
                            <span class="tipo-nombre"><?= t('operario.tipo_vidrio') ?></span>
                            <span class="tipo-valores"><span class="valor-actual">2460</span> / <span class="valor-capacidad">3000</span> kg (<span class="valor-porcentaje">82</span>%)</span>
                        </div>
                        <div class="progreso-track">
                            <div class="progreso-fill nivel-warning" style="width: 82%;"></div>
                        </div>
                    </div>

                    <div class="ocupacion-item" data-tipo="Orgánico">
                        <div class="ocupacion-item-header">
                            <span class="tipo-nombre"><?= t('operario.tipo_organico') ?></span>
                            <span class="tipo-valores"><span class="valor-actual">900</span> / <span class="valor-capacidad">3000</span> kg (<span class="valor-porcentaje">30</span>%)</span>
                        </div>
                        <div class="progreso-track">
                            <div class="progreso-fill" style="width: 30%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Registro de ingreso de residuos: función descrita para el operario en 2.3
             (no tiene un RF numerado propio en el documento; alimenta los datos que
             RF78 consulta). Ver aclaración en el chat. -->
        <section class="dash-section" id="seccion-ingreso">
            <div class="panel-card">
                <div class="card-header">
                    <span class="card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17h4V5H2v12h3"/><path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                    </span>
                    <div>
                        <h2><?= t('operario.ingreso_titulo') ?></h2>
                        <p><?= t('operario.ingreso_sub') ?></p>
                    </div>
                </div>

                <form id="form-ingreso" class="form-grid">
                    <div class="form-group">
                        <label for="ingreso-camion"><?= t('operario.ingreso_camion_label') ?></label>
                        <input type="text" id="ingreso-camion" name="camion" placeholder="<?= htmlspecialchars(t('operario.ingreso_camion_ph')) ?>">
                    </div>

                    <div class="form-group">
                        <label for="ingreso-tipo"><?= t('operario.ingreso_tipo_label') ?></label>
                        <select id="ingreso-tipo" name="tipo">
                            <option value="Plástico"><?= t('operario.tipo_plastico') ?></option>
                            <option value="Papel y cartón"><?= t('operario.tipo_papel_carton') ?></option>
                            <option value="Vidrio"><?= t('operario.tipo_vidrio') ?></option>
                            <option value="Orgánico"><?= t('operario.tipo_organico') ?></option>
                            <option value="Metal"><?= t('operario.tipo_metal') ?></option>
                            <option value="No reciclable"><?= t('operario.tipo_no_reciclable') ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ingreso-peso"><?= t('operario.ingreso_peso_label') ?></label>
                        <input type="number" id="ingreso-peso" name="peso" min="1" placeholder="<?= htmlspecialchars(t('operario.ingreso_peso_ph')) ?>">
                    </div>

                    <div class="form-group">
                        <label for="ingreso-fecha"><?= t('operario.ingreso_fecha_label') ?></label>
                        <input type="date" id="ingreso-fecha" name="fecha">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-full">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                            <?= t('operario.btn_registrar_ingreso') ?>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- RF80: Actualización del estado de la maquinaria -->
        <section class="dash-section" id="seccion-maquinaria">
            <div class="panel-card">
                <div class="card-header">
                    <span class="card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
                    </span>
                    <div>
                        <h2><?= t('operario.maquinaria_titulo') ?></h2>
                        <p><?= t('operario.maquinaria_sub') ?></p>
                    </div>
                </div>

                <div class="maquinaria-list" id="maquinaria-list">
                    <p class="maquinaria-meta" id="maquinaria-cargando"><?= t('operario.maquinaria_cargando') ?></p>
                </div>
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

    <!-- Toast de confirmación -->
    <div class="toast" id="toast"></div>

    <script>
        const I18N = <?= json_encode(t_array([
            'ingresoIncompleto' => 'operario.msg_ingreso_incompleto',
            'ingresoRegistrado' => 'operario.msg_ingreso_registrado',
            'noPudoActualizarEstado' => 'operario.msg_no_pudo_actualizar_estado',
            'errorConectarApi' => 'operario.msg_error_conectar_api',
            'maquinariaCargando' => 'operario.maquinaria_cargando',
            'maquinariaVacia' => 'operario.maquinaria_vacia',
            'maquinariaNoPudoCargar' => 'operario.maquinaria_no_pudo_cargar',
            'maquinariaActualizada' => 'operario.msg_maquinaria_actualizada',
            'adquiridaLabel' => 'operario.maquinaria_adquirida_label',
            'tipoCompactadora' => 'operario.maq_tipo_compactadora',
            'tipoCinta' => 'operario.maq_tipo_cinta',
            'tipoPala' => 'operario.maq_tipo_pala',
            'tipoBascula' => 'operario.maq_tipo_bascula',
            'tipoHerramienta' => 'operario.maq_tipo_herramienta',
            'tipoOtro' => 'operario.maq_tipo_otro',
            'estadoOperativa' => 'operario.maq_estado_operativa',
            'estadoEnMantenimiento' => 'operario.maq_estado_en_mantenimiento',
            'estadoFueraServicio' => 'operario.maq_estado_fuera_servicio',
            'centroTipoEcocentro' => 'centrosAcopio.opt_ecocentro',
            'centroTipoPlanta' => 'centrosAcopio.opt_planta_clasificacion',
            'centroTipoRelleno' => 'centrosAcopio.opt_relleno_sanitario',
            'centroTipoVertedero' => 'centrosAcopio.opt_vertedero',
            'centroEstadoOperativo' => 'centrosAcopio.opt_operativo',
            'centroEstadoSaturado' => 'centrosAcopio.opt_saturado',
            'centroEstadoCerrado' => 'centrosAcopio.opt_cerrado',
            'sinDireccion' => 'operario.centro_sin_direccion',
        ])) ?>;

        // Toast
        const toast = document.getElementById('toast');
        let toastTimeout;

        function mostrarToast(mensaje, tipo) {
            toast.textContent = mensaje;
            toast.className = 'toast is-visible' + (tipo === 'warning' ? ' toast-warning' : '');
            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(function () {
                toast.classList.remove('is-visible');
            }, 3500);
        }

        // ---- Carga inicial ----
        // Si el operario no tiene centro asignado, se avisa y no se muestra nada más
        // (mismo criterio que cuadrilla.php con ID_CUADRILLA).
        const sinCentroBanner = document.getElementById('sin-centro-banner');
        const seccionesCentro = ['seccion-centro', 'seccion-ocupacion', 'seccion-ingreso', 'seccion-maquinaria']
            .map(id => document.getElementById(id));

        const LABELS_TIPO_CENTRO = {
            ecocentro: I18N.centroTipoEcocentro,
            planta_clasificacion: I18N.centroTipoPlanta,
            relleno_sanitario: I18N.centroTipoRelleno,
            vertedero: I18N.centroTipoVertedero
        };
        const LABELS_ESTADO_CENTRO = {
            operativo: I18N.centroEstadoOperativo,
            saturado: I18N.centroEstadoSaturado,
            cerrado: I18N.centroEstadoCerrado
        };
        const BADGE_ESTADO_CENTRO = {
            operativo: 'badge-ok',
            saturado: 'badge-warning',
            cerrado: 'badge-danger'
        };

        async function cargarCentroAsignado() {
            try {
                const res = await fetch(`${API_BASE_GESTION}/centros-acopio/${ID_CENTRO}`);
                const json = await res.json();
                if (json.status !== 'success' || !json.data) return;
                const c = json.data;

                document.getElementById('centro-nombre').textContent = c.nombre;
                const badge = document.getElementById('centro-estado-badge');
                badge.textContent = LABELS_ESTADO_CENTRO[c.estado] || c.estado;
                badge.className = 'badge ' + (BADGE_ESTADO_CENTRO[c.estado] || 'badge-neutral');
                document.getElementById('centro-tipo').textContent = LABELS_TIPO_CENTRO[c.tipo] || c.tipo;
                const direccion = [c.calle, c.num_calle].filter(Boolean).join(' ');
                document.getElementById('centro-direccion').textContent = direccion || I18N.sinDireccion;
                document.getElementById('centro-capacidad').textContent = c.capacidad_total
                    ? `${c.capacidad_total} m³`
                    : I18N.sinDireccion;
            } catch {
                mostrarToast(I18N.errorConectarApi, 'warning');
            }
        }

        async function init() {
            if (!ID_CENTRO) {
                sinCentroBanner.hidden = false;
                seccionesCentro.forEach(s => { s.hidden = true; });
                return;
            }
            sinCentroBanner.hidden = true;
            seccionesCentro.forEach(s => { s.hidden = false; });
            await cargarCentroAsignado();
            await cargarMaquinaria();
        }

        // RF78 (datos base) — ocupación por tipo de residuo del centro
        const OCUPACION = {
            'Plástico': { actual: 2040, capacidad: 3000 },
            'Papel y cartón': { actual: 1350, capacidad: 3000 },
            'Vidrio': { actual: 2460, capacidad: 3000 },
            'Orgánico': { actual: 900, capacidad: 3000 }
        };

        function actualizarBarraOcupacion(tipo) {
            const item = document.querySelector('.ocupacion-item[data-tipo="' + tipo + '"]');
            if (!item || !OCUPACION[tipo]) return;

            const datos = OCUPACION[tipo];
            const porcentaje = Math.min(100, Math.round((datos.actual / datos.capacidad) * 100));

            item.querySelector('.valor-actual').textContent = datos.actual;
            item.querySelector('.valor-capacidad').textContent = datos.capacidad;
            item.querySelector('.valor-porcentaje').textContent = porcentaje;

            const fill = item.querySelector('.progreso-fill');
            fill.style.width = porcentaje + '%';
            fill.classList.remove('nivel-warning', 'nivel-danger');
            if (porcentaje >= 90) {
                fill.classList.add('nivel-danger');
            } else if (porcentaje >= 70) {
                fill.classList.add('nivel-warning');
            }
        }

        // Registro de ingreso de residuos (alimenta la ocupación que consulta RF78)
        const formIngreso = document.getElementById('form-ingreso');
        const ingresoFecha = document.getElementById('ingreso-fecha');
        ingresoFecha.valueAsDate = new Date();

        formIngreso.addEventListener('submit', function (e) {
            e.preventDefault();

            const camion = document.getElementById('ingreso-camion').value.trim();
            const tipo = document.getElementById('ingreso-tipo').value;
            const peso = Number(document.getElementById('ingreso-peso').value);
            const fecha = ingresoFecha.value;

            if (!camion || !peso || peso <= 0 || !fecha) {
                mostrarToast(I18N.ingresoIncompleto, 'warning');
                return;
            }

            if (OCUPACION[tipo]) {
                OCUPACION[tipo].actual = Math.min(OCUPACION[tipo].capacidad, OCUPACION[tipo].actual + peso);
                actualizarBarraOcupacion(tipo);
            }

            mostrarToast(I18N.ingresoRegistrado + ' ' + peso + ' kg — ' + tipo.toLowerCase() + ' (' + camion + ').');
            formIngreso.reset();
            ingresoFecha.valueAsDate = new Date();
        });

        // RF80 — Actualización del estado de la maquinaria
        const API_BASE_GESTION = '/recolix/API_Gestion';
        const API_BASE_USUARIOS = '/recolix/API_Usuarios/api.php';
        // Centro real asignado al operario (USUARIO.id_centro), leído fresco de la
        // base en cada carga de la página — ver el bloque PHP del principio del archivo.
        const ID_CENTRO = <?= json_encode($idCentro) ?>;

        const LABELS_TIPO_MAQUINARIA = {
            compactadora: I18N.tipoCompactadora,
            cinta: I18N.tipoCinta,
            pala: I18N.tipoPala,
            bascula: I18N.tipoBascula,
            herramienta: I18N.tipoHerramienta,
            otro: I18N.tipoOtro
        };
        const LABELS_ESTADO_MAQUINARIA = {
            operativa: I18N.estadoOperativa,
            en_mantenimiento: I18N.estadoEnMantenimiento,
            fuera_servicio: I18N.estadoFueraServicio
        };
        const BADGE_ESTADO_MAQUINARIA = {
            operativa: 'badge-ok',
            en_mantenimiento: 'badge-warning',
            fuera_servicio: 'badge-danger'
        };

        const maquinariaList = document.getElementById('maquinaria-list');

        function filaMaquinaria(m) {
            const badge = BADGE_ESTADO_MAQUINARIA[m.estado] || 'badge-neutral';
            const row = document.createElement('div');
            row.className = 'maquinaria-row';
            row.innerHTML = `
                <div class="maquinaria-info">
                    <span class="maquinaria-nombre">${m.nombre}</span>
                    <span class="maquinaria-meta">${LABELS_TIPO_MAQUINARIA[m.tipo] || m.tipo} — ${I18N.adquiridaLabel} ${m.fecha_adquisicion}</span>
                </div>
                <select class="maquinaria-estado ${badge}" data-id="${m.id_maquinaria}" data-maquinaria="${m.nombre}">
                    <option value="operativa"${m.estado === 'operativa' ? ' selected' : ''}>${I18N.estadoOperativa}</option>
                    <option value="en_mantenimiento"${m.estado === 'en_mantenimiento' ? ' selected' : ''}>${I18N.estadoEnMantenimiento}</option>
                    <option value="fuera_servicio"${m.estado === 'fuera_servicio' ? ' selected' : ''}>${I18N.estadoFueraServicio}</option>
                </select>
            `;
            row.querySelector('.maquinaria-estado').addEventListener('change', onCambiarEstadoMaquinaria);
            return row;
        }

        async function cargarMaquinaria() {
            maquinariaList.innerHTML = '<p class="maquinaria-meta">' + I18N.maquinariaCargando + '</p>';
            try {
                const res = await fetch(`${API_BASE_GESTION}/maquinaria/obtenerPorCentro/${ID_CENTRO}`);
                const json = await res.json();
                const maquinas = json.status === 'success' ? json.data : [];
                maquinariaList.innerHTML = '';
                if (!maquinas.length) {
                    maquinariaList.innerHTML = '<p class="maquinaria-meta">' + I18N.maquinariaVacia + '</p>';
                    return;
                }
                maquinas.forEach(m => maquinariaList.appendChild(filaMaquinaria(m)));
            } catch {
                maquinariaList.innerHTML = '<p class="maquinaria-meta">' + I18N.maquinariaNoPudoCargar + '</p>';
            }
        }

        async function onCambiarEstadoMaquinaria(e) {
            const select = e.target;
            const idMaquinaria = select.getAttribute('data-id');
            const nombre = select.getAttribute('data-maquinaria');
            const nuevoEstado = select.value;
            const estadoAnterior = select.dataset.estadoAnterior || nuevoEstado;

            try {
                const res = await fetch(`${API_BASE_GESTION}/maquinaria/cambiarEstado/${idMaquinaria}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nuevoEstado })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    select.classList.remove('badge-ok', 'badge-warning', 'badge-danger', 'badge-neutral');
                    select.classList.add(BADGE_ESTADO_MAQUINARIA[nuevoEstado] || 'badge-neutral');
                    select.dataset.estadoAnterior = nuevoEstado;
                    mostrarToast(I18N.maquinariaActualizada + ' "' + nombre + '": "' + LABELS_ESTADO_MAQUINARIA[nuevoEstado] + '".');
                } else {
                    select.value = estadoAnterior;
                    mostrarToast(json.message || I18N.noPudoActualizarEstado, 'warning');
                }
            } catch {
                select.value = estadoAnterior;
                mostrarToast(I18N.errorConectarApi, 'warning');
            }
        }

        init();

        function toggleMobileMenu() {
            document.getElementById('navMobile').classList.toggle('nav-mobile-open');
        }
    </script>

</body>
</html>