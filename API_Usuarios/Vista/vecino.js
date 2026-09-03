

const API = '/recolix/API_Usuarios/api.php';
const API_GESTION = '/recolix/API_Gestion';
document.addEventListener('DOMContentLoaded', function () {

    let perfilActual = {};

    cargarPerfil();
    cargarIncidencias();
    cargarNotificaciones();


    document.getElementById('logoutBtn').addEventListener('click', () => {
        window.location.href = '/recolix/API_Usuarios/logout.php';
    });


    async function cargarPerfil() {
        const resp = await fetch(`${API}/usuarios/${CI}`);
        const json = await resp.json();
        const perfil = json.data;
        perfilActual = perfil;
        document.getElementById('perfilNombre').textContent = perfil.primer_nombre + ' ' + perfil.p_apellido;
        document.getElementById('perfilCI').textContent = perfil.ci_usuario;
        document.getElementById('perfilEmail').textContent = perfil.email || '—';
        document.getElementById('perfilTel').textContent = perfil.tel || '—';
        document.getElementById('perfilMunicipio').textContent = perfil.municipio || I18N.sinAsignar;
        const btnMunicipio = document.getElementById('asignarMunicipioBtn');
        btnMunicipio.hidden = false;
        btnMunicipio.textContent = perfil.id_municipio ? I18N.btnCambiarMunicipio : I18N.btnAgregarMunicipio;
    }


    document.getElementById('editarPerfilBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = true;
        document.getElementById('perfilForm').hidden = false;
        document.getElementById('contraseniaForm').hidden = true;
        document.getElementById('municipioForm').hidden = true;
        document.getElementById('editEmail').value = perfilActual.email || '';
        document.getElementById('editTel').value = perfilActual.tel || '';
    });

    document.getElementById('cancelarPerfilBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('perfilForm').hidden = true;
        limpiarFeedback('perfilFeedback');
    });


    document.getElementById('guardarPerfilBtn').addEventListener('click', async () => {
        const email = document.getElementById('editEmail').value.trim();
        const tel = document.getElementById('editTel').value.trim();

        const reglas = {
            email: [/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/, I18N.msgEmailInvalido, 'editEmailError'],
            tel: [/^\d{9}$/, I18N.msgTelInvalido, 'editTelError']
        };

        let esValido = true;
        for (const campo in reglas) {
            const [regex, mensaje, idError] = reglas[campo];
            const valor = campo === 'email' ? email : tel;
            const ok = regex.test(valor);
            document.getElementById(idError).textContent = ok ? '' : mensaje;
            if (!ok) esValido = false;
        }
        if (!esValido) return;

        const resp = await fetch(`${API}/usuarios/${CI}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, tel })
        });
        const json = await resp.json();

        if (json.status !== 'success') {
            mostrarFeedback('perfilFeedback', json.message || I18N.msgNoPudoGuardar, false);
            return;
        }

        await cargarPerfil();
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('perfilForm').hidden = true;
        mostrarFeedback('perfilFeedback', I18N.msgDatosActualizados, true);
    });


    document.getElementById('cambiarContraseniaBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = true;
        document.getElementById('contraseniaForm').hidden = false;
        document.getElementById('perfilForm').hidden = true;
        document.getElementById('municipioForm').hidden = true;
    });


    document.getElementById('asignarMunicipioBtn').addEventListener('click', () => {
        const esCambio = !!perfilActual.id_municipio;
        document.getElementById('perfilVista').hidden = true;
        document.getElementById('municipioForm').hidden = false;
        document.getElementById('perfilForm').hidden = true;
        document.getElementById('contraseniaForm').hidden = true;
        document.getElementById('municipioFormTitle').textContent = esCambio ? I18N.btnCambiarMunicipio : I18N.btnAgregarMunicipio;
        document.getElementById('municipioFormNota').textContent = esCambio
            ? I18N.municipioFormNotaCambiar
            : I18N.municipioFormNotaAgregar;
        document.getElementById('municipioSelect').value = esCambio ? perfilActual.municipio : '';
        limpiarFeedback('municipioFeedback');
    });

    document.getElementById('cancelarMunicipioBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('municipioForm').hidden = true;
        limpiarFeedback('municipioFeedback');
    });

    document.getElementById('guardarMunicipioBtn').addEventListener('click', async () => {
        const municipio = document.getElementById('municipioSelect').value;
        document.getElementById('municipioSelectError').textContent =
            municipio ? '' : I18N.msgSeleccionaMunicipioPunto;
        if (!municipio) return;

        const esCambio = !!perfilActual.id_municipio;

        const resp = await fetch(`${API}/usuarios/setMunicipio/${CI}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ municipio })
        });
        const json = await resp.json();

        if (json.status !== 'success') {
            mostrarFeedback('municipioFeedback', json.message || I18N.msgNoPudoGuardarMunicipio, false);
            return;
        }

        await cargarPerfil();
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('municipioForm').hidden = true;
        mostrarFeedback('perfilFeedback', esCambio ? I18N.msgMunicipioActualizado : I18N.msgMunicipioAsignado, true);
    });

    document.getElementById('cancelarContraBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('contraseniaForm').hidden = true;
        limpiarFeedback('contraFeedback');
    });


    document.getElementById('guardarContraBtn').addEventListener('click', async () => {
        const actual = document.getElementById('contraActual').value;
        const nueva = document.getElementById('contraNueva').value;

        document.getElementById('contraActualError').textContent =
            actual.length < 1 ? I18N.msgContraActualRequerida : '';
        document.getElementById('contraNuevaError').textContent =
            nueva.length < 8 ? I18N.msgContraNuevaMin : '';

        if (actual.length < 1 || nueva.length < 8) return;

        const resp = await fetch(`${API}/usuarios/cambiarContrasenia`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ci: CI, contraseniaActual: actual, nuevaContrasenia: nueva })
        });
        const json = await resp.json();

        if (json.status !== 'success') {
            mostrarFeedback('contraFeedback', json.message || I18N.msgNoPudoCambiarContra, false);
            return;
        }

        document.getElementById('contraActual').value = '';
        document.getElementById('contraNueva').value = '';
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('contraseniaForm').hidden = true;
        mostrarFeedback('perfilFeedback', I18N.msgContraActualizada, true);
    });


    async function cargarIncidencias() {
        const contenedor = document.getElementById('incidenciasLista');
        const resp = await fetch(`${API}/usuarios/getIncidencias?ci=${CI}`);
        const json = await resp.json();
        const lista = json.data;

        if (!lista.length) {
            contenedor.innerHTML = `<div class="estado-vacio">${I18N.sinIncidencias}</div>`;
            return;
        }

        contenedor.innerHTML = lista.map(inc => `
        <div class="incidencia-card" data-estado="${inc.estado}">
            <div class="incidencia-info">
                <p class="incidencia-tipo">${inc.tipo.replace('_', ' ')}</p>
                <p class="incidencia-fecha">${formatearFecha(inc.fecha_apertura)}</p>
                ${inc.descripcion ? `<p class="incidencia-desc">${inc.descripcion}</p>` : ''}
            </div>
            <span class="badge badge-${inc.estado}">${inc.estado}</span>
        </div>
    `).join('');
    }


    // RF05/RF06 — mapa interactivo: muestra la ubicación del usuario y permite elegir el
    // contenedor de la incidencia tocándolo directamente en el mapa. Los contenedores y sus
    // colores salen de cargarContenedoresEnMapa() (mapaContenedoresBase.js), la misma fuente
    // que usan la Landing Page y el mapa público.
    let incMapa = null;
    let incMapaMarcadores = null;
    let incMarcadorUsuario = null;
    let incMarcadorSeleccionado = null;

    function distanciaKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2 +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function seleccionarIncContenedorEnMapa(marker, c) {
        document.getElementById('incContenedor').value = c.id_contenedor;
        document.getElementById('incContenedorError').textContent = '';
        const seleccionado = document.getElementById('incContenedorSeleccionado');
        seleccionado.style.display = '';
        seleccionado.textContent = `${I18N.contenedorSeleccionado}: #${c.id_contenedor} — ${c.calle} ${c.num_calle}`;

        if (incMarcadorSeleccionado) incMarcadorSeleccionado.setStyle({ weight: 1.5, radius: 7 });
        marker.setStyle({ weight: 3, radius: 10 });
        incMarcadorSeleccionado = marker;
    }

    // Si el usuario no toca ningún contenedor en el mapa, se preselecciona el más cercano
    // a su ubicación detectada — nunca queda sin elegir uno.
    function incPreseleccionarMasCercano(latitude, longitude) {
        if (!incMapaMarcadores || !incMapaMarcadores.length) return;
        let masCercano = null;
        let distanciaMin = Infinity;
        incMapaMarcadores.forEach((m) => {
            const c = m.contenedor;
            const d = distanciaKm(latitude, longitude, parseFloat(c.latitud), parseFloat(c.longitud));
            if (d < distanciaMin) {
                distanciaMin = d;
                masCercano = m;
            }
        });
        if (masCercano) seleccionarIncContenedorEnMapa(masCercano, masCercano.contenedor);
    }

    async function iniciarIncMapaReportar() {
        const status = document.getElementById('incUbicacionStatus');
        status.style.display = '';
        status.textContent = I18N.detectandoUbicacion;
        document.getElementById('incContenedorSeleccionado').style.display = 'none';
        incMarcadorSeleccionado = null;

        if (!incMapa) {
            incMapa = L.map('incMapaContenedor', { zoomControl: true }).setView([-34.901, -56.165], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(incMapa);

            incMapaMarcadores = await cargarContenedoresEnMapa(incMapa, {
                apiBase: API_GESTION,
                radius: 7,
                tooltip: (c) => `#${c.id_contenedor} — ${c.calle} ${c.num_calle}`,
                onClick: seleccionarIncContenedorEnMapa
            });
        }
        setTimeout(() => incMapa.invalidateSize(), 200);

        if (!navigator.geolocation) {
            status.textContent = I18N.ubicacionNoDisponible;
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const { latitude, longitude } = pos.coords;
                status.textContent = I18N.ubicacionDetectada;
                if (incMarcadorUsuario) incMapa.removeLayer(incMarcadorUsuario);
                const icono = L.divIcon({
                    className: 'mapa-marcador-usuario-wrap',
                    html: '<span class="mapa-marcador-usuario"></span>',
                    iconSize: [22, 22],
                    iconAnchor: [11, 22]
                });
                incMarcadorUsuario = L.marker([latitude, longitude], { icon: icono })
                    .addTo(incMapa)
                    .bindTooltip(I18N.tuUbicacion);
                incMapa.setView([latitude, longitude], 15);
                incPreseleccionarMasCercano(latitude, longitude);
            },
            () => {
                status.textContent = I18N.ubicacionDenegada;
            },
            { timeout: 8000 }
        );
    }

    // RF04 — registro de incidencia desde el perfil del vecino.
    document.getElementById('nuevaIncidenciaBtn').addEventListener('click', () => {
        const form = document.getElementById('nuevaIncidenciaForm');
        form.hidden = !form.hidden;
        if (!form.hidden) {
            document.getElementById('incTipo').value = 'rotura';
            document.getElementById('incContenedor').value = '';
            document.getElementById('incDescripcion').value = '';
            document.getElementById('incFoto').value = '';
            document.getElementById('incContenedorError').textContent = '';
            document.getElementById('incFotoError').textContent = '';
            limpiarFeedback('incidenciaFeedback');
            iniciarIncMapaReportar();
        }
    });

    document.getElementById('cancelarIncidenciaBtn').addEventListener('click', () => {
        document.getElementById('nuevaIncidenciaForm').hidden = true;
    });

    // RF07: adjunto de foto (JPG/PNG, máx 5 MB) — se manda como data URL base64 dentro
    // del mismo JSON del reporte, ya que el endpoint de incidencias solo acepta JSON.
    const MAX_FOTO_BYTES = 5 * 1024 * 1024;
    function leerFotoComoBase64(input, errorEl) {
        errorEl.textContent = '';
        const file = input.files && input.files[0];
        if (!file) return Promise.resolve(null);
        if (!['image/jpeg', 'image/png'].includes(file.type)) {
            errorEl.textContent = I18N.msgFotoTipoInvalido;
            return Promise.reject(new Error('tipo'));
        }
        if (file.size > MAX_FOTO_BYTES) {
            errorEl.textContent = I18N.msgFotoTamanoInvalido;
            return Promise.reject(new Error('tamano'));
        }
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    document.getElementById('guardarIncidenciaBtn').addEventListener('click', async () => {
        const idContenedor = parseInt(document.getElementById('incContenedor').value, 10);
        const tipo = document.getElementById('incTipo').value;
        const descripcion = document.getElementById('incDescripcion').value.trim();
        const contenedorError = document.getElementById('incContenedorError');

        if (!idContenedor || idContenedor < 1) {
            contenedorError.textContent = I18N.msgContenedorInvalido;
            return;
        }
        contenedorError.textContent = '';

        let imagen;
        try {
            imagen = await leerFotoComoBase64(document.getElementById('incFoto'), document.getElementById('incFotoError'));
        } catch {
            return;
        }

        // La identidad del usuario la resuelve el servidor a partir de la sesión.
        const resp = await fetch(`${API_GESTION}/incidencias`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idContenedor, tipo, descripcion: descripcion || null, imagen })
        });
        const json = await resp.json();

        if (json.status !== 'success') {
            mostrarFeedback('incidenciaFeedback', json.message || I18N.msgErrorReportarIncidencia, false);
            return;
        }

        mostrarFeedback('incidenciaFeedback', I18N.msgIncidenciaReportada, true);
        await cargarIncidencias();
        setTimeout(() => {
            document.getElementById('nuevaIncidenciaForm').hidden = true;
        }, 1500);
    });


    async function cargarNotificaciones() {
        const contenedor = document.getElementById('notificacionesLista');
        const resp = await fetch(`${API}/usuarios/getNotificaciones?ci=${CI}`);
        const json = await resp.json();
        const lista = json.data;

        if (!lista.length) {
            contenedor.innerHTML = `<div class="estado-vacio">${I18N.sinNotificaciones}</div>`;
            return;
        } contenedor.innerHTML = lista.map(n => `
        <div class="notif-card ${n.estado === 'leida' ? 'leida' : 'no-leida'}" id="notif-${n.id_notificacion}">
            <div class="notif-dot"></div>
            <div class="notif-cuerpo">
                <p class="notif-mensaje">${n.mensaje}</p>
                <div class="notif-meta">
                    <span>${n.tipo_origen}</span>
                    <span>${formatearFecha(n.fecha_creacion)}</span>
                </div>
            </div>
            ${n.estado !== 'leida' ? `<button class="btn-marcar-leida" data-id="${n.id_notificacion}">${I18N.btnMarcarLeida}</button>` : ''}
        </div>
    `).join(''); contenedor.querySelectorAll('.btn-marcar-leida').forEach(btn => {
            btn.addEventListener('click', () => marcarLeida(btn.dataset.id));
        });
    }

    async function marcarLeida(idNotificacion) {
        await fetch(`${API}/usuarios/marcarNotificacionLeida`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idNotificacion })
        });
        const tarjeta = document.getElementById(`notif-${idNotificacion}`);
        if (tarjeta) {
            tarjeta.classList.remove('no-leida');
            tarjeta.classList.add('leida');
            tarjeta.querySelector('.notif-dot').style.background = '#ccc';
            const btn = tarjeta.querySelector('.btn-marcar-leida');
            if (btn) btn.remove();
        }
    }


    function formatearFecha(fechaStr) {
        if (!fechaStr) return '—';
        const fecha = new Date(fechaStr);
        return fecha.toLocaleDateString(LOCALE, { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function mostrarFeedback(idElemento, mensaje, esExito) {
        const el = document.getElementById(idElemento);
        el.textContent = mensaje;
        el.className = 'form-feedback ' + (esExito ? 'is-success' : 'is-error');
    }

    function limpiarFeedback(idElemento) {
        const el = document.getElementById(idElemento);
        el.textContent = '';
        el.className = 'form-feedback';
    }

});
