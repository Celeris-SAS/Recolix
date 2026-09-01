<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recolix — Operario</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../Backoffice/styleBackoffice.css">
<link rel="stylesheet" href="styles_vecino.css">
<link rel="stylesheet" href="navExtras.css">
<link rel="stylesheet" href="operario.css">
</head>

<body>

    <header class="navbar">
        <div class="nav-inner">
            <div class="nav-left">
                <a href="operario.html" class="brand">
                    <img src="../../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                    <span class="brand-name">Recolix</span>
                </a>
                    <nav class="nav-links">
                    <a href="operario.php" class="nav-active"><?= t('operario.nav_inicio') ?></a>
                    <a href="http://localhost/recolix/Backoffice/mapa.php"><?= t('operario.nav_mapa') ?></a>
                </nav>
            </div>
            <div class="nav-right">
                <?php include __DIR__ . '/../../i18n/switcher.php'; ?>
                <a href="../perfil.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                    <img src="../../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="Menú">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
            <nav class="nav-mobile" id="navMobile">
            <a href="operario.php" class="nav-active"><?= t('operario.nav_inicio') ?></a>
            <a href="http://localhost/recolix/Backoffice/mapa.php"><?= t('operario.nav_mapa') ?></a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    <main class="container dash-main">

        <div class="page-title">
            <h1>Operario</h1>
            <p>Gestioná el centro de acopio y su maquinaria</p>
        </div>

        <!-- RF77: Listado de centros de acopio (el/los que tiene asignados este operario) -->
        <section class="dash-section">
            <div class="panel-card">
                <div class="card-header">
                    <span class="card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h1"/><path d="M14 9h1"/><path d="M9 13h1"/><path d="M14 13h1"/><path d="M9 21v-4h6v4"/></svg>
                    </span>
                    <div>
                        <h2>Mi Centro de Acopio</h2>
                        <p>Datos principales de la instalación asignada</p>
                    </div>
                </div>

                <div class="centro-row">
                    <div class="centro-titulo">
                        <h3>Ecocentro Buceo</h3>
                        <span class="badge badge-ok">Operativo</span>
                    </div>
                    <div class="centro-datos">
                        <span class="centro-item">Tipo: <strong>Ecocentro</strong></span>
                        <span class="centro-item">Dirección: <strong>Rambla República de México 5589</strong></span>
                        <span class="centro-item">Capacidad total: <strong>500 m³</strong></span>
                    </div>
                </div>

                <a class="btn btn-outline btn-full" href="http://localhost/recolix/Backoffice/mapa.php">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6v15l6-3 6 3 6-3V3l-6 3-6-3-6 3Z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>
                    Ver en mapa
                </a>
            </div>
        </section>

        <!-- RF78: Consulta de ocupación del centro de acopio -->
        <section class="dash-section">
            <div class="panel-card">
                <div class="card-header">
                    <span class="card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15v4"/><path d="M12 10v9"/><path d="M17 6v13"/></svg>
                    </span>
                    <div>
                        <h2>Ocupación del Centro</h2>
                        <p>Calculada a partir de los ingresos de residuos registrados</p>
                    </div>
                </div>

                <div class="ocupacion-list" id="ocupacion-list">
                    <div class="ocupacion-item" data-tipo="Plástico">
                        <div class="ocupacion-item-header">
                            <span class="tipo-nombre">Plástico</span>
                            <span class="tipo-valores"><span class="valor-actual">2040</span> / <span class="valor-capacidad">3000</span> kg (<span class="valor-porcentaje">68</span>%)</span>
                        </div>
                        <div class="progreso-track">
                            <div class="progreso-fill" style="width: 68%;"></div>
                        </div>
                    </div>

                    <div class="ocupacion-item" data-tipo="Papel y cartón">
                        <div class="ocupacion-item-header">
                            <span class="tipo-nombre">Papel y cartón</span>
                            <span class="tipo-valores"><span class="valor-actual">1350</span> / <span class="valor-capacidad">3000</span> kg (<span class="valor-porcentaje">45</span>%)</span>
                        </div>
                        <div class="progreso-track">
                            <div class="progreso-fill" style="width: 45%;"></div>
                        </div>
                    </div>

                    <div class="ocupacion-item" data-tipo="Vidrio">
                        <div class="ocupacion-item-header">
                            <span class="tipo-nombre">Vidrio</span>
                            <span class="tipo-valores"><span class="valor-actual">2460</span> / <span class="valor-capacidad">3000</span> kg (<span class="valor-porcentaje">82</span>%)</span>
                        </div>
                        <div class="progreso-track">
                            <div class="progreso-fill nivel-warning" style="width: 82%;"></div>
                        </div>
                    </div>

                    <div class="ocupacion-item" data-tipo="Orgánico">
                        <div class="ocupacion-item-header">
                            <span class="tipo-nombre">Orgánico</span>
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
        <section class="dash-section">
            <div class="panel-card">
                <div class="card-header">
                    <span class="card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17h4V5H2v12h3"/><path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                    </span>
                    <div>
                        <h2>Registrar Ingreso de Residuos</h2>
                        <p>Registra la llegada de un camión con residuos al centro</p>
                    </div>
                </div>

                <form id="form-ingreso" class="form-grid">
                    <div class="form-group">
                        <label for="ingreso-camion">Camión (matrícula)</label>
                        <input type="text" id="ingreso-camion" name="camion" placeholder="Ej: SBC 1234">
                    </div>

                    <div class="form-group">
                        <label for="ingreso-tipo">Tipo de residuo</label>
                        <select id="ingreso-tipo" name="tipo">
                            <option value="Plástico">Plástico</option>
                            <option value="Papel y cartón">Papel y cartón</option>
                            <option value="Vidrio">Vidrio</option>
                            <option value="Orgánico">Orgánico</option>
                            <option value="Metal">Metal</option>
                            <option value="No reciclable">No reciclable</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ingreso-peso">Peso ingresado (kg)</label>
                        <input type="number" id="ingreso-peso" name="peso" min="1" placeholder="Ej: 350">
                    </div>

                    <div class="form-group">
                        <label for="ingreso-fecha">Fecha de ingreso</label>
                        <input type="date" id="ingreso-fecha" name="fecha">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-full">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                            Registrar ingreso
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- RF80: Actualización del estado de la maquinaria -->
        <section class="dash-section">
            <div class="panel-card">
                <div class="card-header">
                    <span class="card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
                    </span>
                    <div>
                        <h2>Maquinaria</h2>
                        <p>Actualiza el estado de la maquinaria del centro</p>
                    </div>
                </div>

                <div class="maquinaria-list" id="maquinaria-list">
                    <p class="maquinaria-meta" id="maquinaria-cargando">Cargando maquinaria...</p>
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
                <p>Plataforma de gestión de residuos urbanos para Montevideo.</p>
            </div>
            <div class="footer-col">
                <h3>Navegación</h3>
                <ul>
                    <li><a href="../../LandingPage/index_LandingPage.html">Página principal</a></li>
                    <li><a href="../../LandingPage/mapa.html">Mapa público</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-hr"></div>
        <p class="footer-copy">&copy; 2026 Recolix. Todos los derechos reservados.</p>
    </footer>

    <!-- Toast de confirmación -->
    <div class="toast" id="toast"></div>

    <script>
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
                mostrarToast('Completá camión, peso y fecha para registrar el ingreso.', 'warning');
                return;
            }

            if (OCUPACION[tipo]) {
                OCUPACION[tipo].actual = Math.min(OCUPACION[tipo].capacidad, OCUPACION[tipo].actual + peso);
                actualizarBarraOcupacion(tipo);
            }

            mostrarToast('Se registró el ingreso de ' + peso + ' kg de ' + tipo.toLowerCase() + ' (camión ' + camion + ').');
            formIngreso.reset();
            ingresoFecha.valueAsDate = new Date();
        });

        // RF80 — Actualización del estado de la maquinaria
        const API_BASE_GESTION = '/recolix/API_Gestion';
        // TODO: reemplazar por el centro real asignado al operario cuando la página tenga sesión propia.
        const ID_CENTRO = parseInt(new URLSearchParams(window.location.search).get('centro'), 10) || 1;

        const LABELS_TIPO_MAQUINARIA = {
            compactadora: 'Compactadora',
            cinta: 'Cinta',
            pala: 'Pala',
            bascula: 'Báscula',
            herramienta: 'Herramienta',
            otro: 'Otro'
        };
        const LABELS_ESTADO_MAQUINARIA = {
            operativa: 'Operativa',
            en_mantenimiento: 'En mantenimiento',
            fuera_servicio: 'Fuera de servicio'
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
                    <span class="maquinaria-meta">${LABELS_TIPO_MAQUINARIA[m.tipo] || m.tipo} — adquirida ${m.fecha_adquisicion}</span>
                </div>
                <select class="maquinaria-estado ${badge}" data-id="${m.id_maquinaria}" data-maquinaria="${m.nombre}">
                    <option value="operativa"${m.estado === 'operativa' ? ' selected' : ''}>Operativa</option>
                    <option value="en_mantenimiento"${m.estado === 'en_mantenimiento' ? ' selected' : ''}>En mantenimiento</option>
                    <option value="fuera_servicio"${m.estado === 'fuera_servicio' ? ' selected' : ''}>Fuera de servicio</option>
                </select>
            `;
            row.querySelector('.maquinaria-estado').addEventListener('change', onCambiarEstadoMaquinaria);
            return row;
        }

        async function cargarMaquinaria() {
            maquinariaList.innerHTML = '<p class="maquinaria-meta">Cargando maquinaria...</p>';
            try {
                const res = await fetch(`${API_BASE_GESTION}/maquinaria/obtenerPorCentro/${ID_CENTRO}`);
                const json = await res.json();
                const maquinas = json.status === 'success' ? json.data : [];
                maquinariaList.innerHTML = '';
                if (!maquinas.length) {
                    maquinariaList.innerHTML = '<p class="maquinaria-meta">Este centro todavía no tiene maquinaria registrada.</p>';
                    return;
                }
                maquinas.forEach(m => maquinariaList.appendChild(filaMaquinaria(m)));
            } catch {
                maquinariaList.innerHTML = '<p class="maquinaria-meta">No se pudo cargar la maquinaria.</p>';
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
                    mostrarToast('Maquinaria "' + nombre + '" actualizada a estado "' + LABELS_ESTADO_MAQUINARIA[nuevoEstado] + '".');
                } else {
                    select.value = estadoAnterior;
                    mostrarToast(json.message || 'No se pudo actualizar el estado.', 'warning');
                }
            } catch {
                select.value = estadoAnterior;
                mostrarToast('Error al conectar con la API.', 'warning');
            }
        }

        cargarMaquinaria();

        function toggleMobileMenu() {
            document.getElementById('navMobile').classList.toggle('nav-mobile-open');
        }
    </script>

</body>
</html>