

const API = '/recolix/API_Usuarios/api.php';
const API_GESTION = '/recolix/API_Gestion';
document.addEventListener('DOMContentLoaded', function () {

    let perfilActual = {};

    cargarPerfil();
    cargarIncidencias();


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
        document.getElementById('editNombre').value = perfilActual.primer_nombre || '';
        document.getElementById('editApellido').value = perfilActual.p_apellido || '';
        document.getElementById('editEmail').value = perfilActual.email || '';
        document.getElementById('editTel').value = perfilActual.tel || '';
    });

    document.getElementById('cancelarPerfilBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('perfilForm').hidden = true;
        limpiarFeedback('perfilFeedback');
    });


    document.getElementById('guardarPerfilBtn').addEventListener('click', async () => {
        const primerNombre = document.getElementById('editNombre').value.trim();
        const pApellido = document.getElementById('editApellido').value.trim();
        const email = document.getElementById('editEmail').value.trim();
        const tel = document.getElementById('editTel').value.trim();

        const regexNombre = /^[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]{2,50}$/;
        const reglas = {
            primerNombre: [regexNombre, I18N.msgNombreInvalido, 'editNombreError'],
            pApellido: [regexNombre, I18N.msgNombreInvalido, 'editApellidoError'],
            email: [/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/, I18N.msgEmailInvalido, 'editEmailError'],
            tel: [/^\d{9}$/, I18N.msgTelInvalido, 'editTelError']
        };
        const valores = { primerNombre, pApellido, email, tel };

        let esValido = true;
        for (const campo in reglas) {
            const [regex, mensaje, idError] = reglas[campo];
            const valor = valores[campo];
            const ok = regex.test(valor);
            document.getElementById(idError).textContent = ok ? '' : mensaje;
            if (!ok) esValido = false;
        }
        if (!esValido) return;

        const resp = await fetch(`${API}/usuarios/${CI}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, tel, primerNombre, pApellido })
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

        const estadosIncidencia = {
            abierta: I18N.estadoIncAbierta,
            asignada: I18N.estadoIncAsignada,
            en_curso: I18N.estadoIncEnCurso,
            resuelta: I18N.estadoIncResuelta
        };

        const tarjeta = inc => `
        <div class="incidencia-card" data-estado="${inc.estado}">
            <div class="incidencia-info">
                <p class="incidencia-tipo">${inc.tipo.replace('_', ' ')}</p>
                <p class="incidencia-fecha">${formatearFecha(inc.fecha_apertura)}</p>
                ${inc.descripcion ? `<p class="incidencia-desc">${inc.descripcion}</p>` : ''}
            </div>
            <span class="badge badge-${inc.estado}">${estadosIncidencia[inc.estado] || inc.estado}</span>
        </div>
    `;

        const activas = lista.filter(inc => inc.estado !== 'resuelta');
        const resueltas = lista.filter(inc => inc.estado === 'resuelta');

        contenedor.innerHTML = activas.length
            ? activas.map(tarjeta).join('')
            : `<div class="estado-vacio">${I18N.sinIncidenciasActivas}</div>`;

        const toggle = document.getElementById('incidenciasResueltasToggle');
        const wrapResueltas = document.getElementById('incidenciasResueltasWrap');
        const boton = document.getElementById('verResueltasBtn');
        document.getElementById('incidenciasResueltasLista').innerHTML = resueltas.map(tarjeta).join('');
        toggle.hidden = resueltas.length === 0;
        if (!resueltas.length) {
            wrapResueltas.hidden = true;
            wrapResueltas.style.display = 'none';
        }
        boton.textContent = wrapResueltas.hidden ? I18N.btnVerResueltas : I18N.btnOcultarResueltas;
        boton.setAttribute('aria-expanded', String(!wrapResueltas.hidden));
    }

    document.getElementById('verResueltasBtn').addEventListener('click', () => {
        const wrapResueltas = document.getElementById('incidenciasResueltasWrap');
        const boton = document.getElementById('verResueltasBtn');
        wrapResueltas.hidden = !wrapResueltas.hidden;
        wrapResueltas.style.display = wrapResueltas.hidden ? 'none' : '';
        boton.textContent = wrapResueltas.hidden ? I18N.btnVerResueltas : I18N.btnOcultarResueltas;
        boton.setAttribute('aria-expanded', String(!wrapResueltas.hidden));
    });


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
        seleccionado.textContent = `${I18N.contenedorSeleccionado}: #${c.id_contenedor} — ${[c.calle, c.num_calle].filter(Boolean).join(' ')}`;

        if (incMarcadorSeleccionado) incMarcadorSeleccionado.setStyle({ weight: 1.5, radius: 7 });
        marker.setStyle({ weight: 3, radius: 10 });
        incMarcadorSeleccionado = marker;
    }

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
                tooltip: (c) => `#${c.id_contenedor} — ${[c.calle, c.num_calle].filter(Boolean).join(' ')}`,
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
