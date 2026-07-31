

const API = '/recolix/API_Usuarios';

document.addEventListener('DOMContentLoaded', function () {

let perfilActual = {};

cargarPerfil();
cargarIncidencias();
cargarNotificaciones();


    document.getElementById('logoutBtn').addEventListener('click', () => {
        window.location.href = `${API}/logout.php`;
    });


    async function cargarPerfil() {
    const resp  = await fetch(`${API}/usuarios/${CI}`);
    const json  = await resp.json();
    const perfil = json.data;
    perfilActual = perfil;
    document.getElementById('perfilNombre').textContent = perfil.primer_nombre + ' ' + perfil.p_apellido;
    document.getElementById('perfilCI').textContent = perfil.ci_usuario;
    document.getElementById('perfilEmail').textContent = perfil.email || '—';
    document.getElementById('perfilTel').textContent = perfil.tel || '—';
    document.getElementById('perfilMunicipio').textContent = perfil.municipio || 'Sin asignar';
    const btnMunicipio = document.getElementById('asignarMunicipioBtn');
    btnMunicipio.hidden = false;
    btnMunicipio.textContent = perfil.id_municipio ? 'Cambiar municipio' : 'Agregar municipio';
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
            email: [/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/, 'Ingresá un correo válido.', 'editEmailError'],
            tel: [/^\d{9}$/, 'El teléfono debe tener 9 dígitos.', 'editTelError']
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
    mostrarFeedback('perfilFeedback', json.message || 'No se pudo guardar.', false);
    return;
}

    await cargarPerfil();
    document.getElementById('perfilVista').hidden = false;
    document.getElementById('perfilForm').hidden = true;
    mostrarFeedback('perfilFeedback', 'Datos actualizados correctamente.', true);;
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
        document.getElementById('municipioFormTitle').textContent = esCambio ? 'Cambiar municipio' : 'Agregar municipio';
        document.getElementById('municipioFormNota').textContent = esCambio
            ? 'Podés modificar el municipio que corresponde a tu domicilio.'
            : 'Todavía no tenés un municipio asignado. Seleccioná el que corresponde a tu domicilio.';
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
            municipio ? '' : 'Seleccioná un municipio.';
        if (!municipio) return;

        const esCambio = !!perfilActual.id_municipio;

        const resp = await fetch(`${API}/usuarios/setMunicipio/${CI}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ municipio })
        });
        const json = await resp.json();

        if (json.status !== 'success') {
            mostrarFeedback('municipioFeedback', json.message || 'No se pudo guardar el municipio.', false);
            return;
        }

        await cargarPerfil();
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('municipioForm').hidden = true;
        mostrarFeedback('perfilFeedback', esCambio ? 'Municipio actualizado correctamente.' : 'Municipio asignado correctamente.', true);
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
            actual.length < 1 ? 'Ingresá tu contraseña actual.' : '';
        document.getElementById('contraNuevaError').textContent =
            nueva.length < 8 ? 'La nueva contraseña debe tener al menos 8 caracteres.' : '';

        if (actual.length < 1 || nueva.length < 8) return;

        const resp = await fetch(`${API}/usuarios/cambiarContrasenia`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ ci: CI, contraseniaActual: actual, nuevaContrasenia: nueva })
});
    const json = await resp.json();

    if (json.status !== 'success') {
        mostrarFeedback('contraFeedback', json.message || 'No se pudo cambiar la contraseña.', false);
        return;
    }

    document.getElementById('contraActual').value = '';
    document.getElementById('contraNueva').value = '';
    document.getElementById('perfilVista').hidden = false;
    document.getElementById('contraseniaForm').hidden = true;
    mostrarFeedback('perfilFeedback', 'Contraseña actualizada correctamente.', true);
    });


  async function cargarIncidencias() {
    const contenedor = document.getElementById('incidenciasLista');
    const resp = await fetch(`${API}/usuarios/getIncidencias?ci=${CI}`);
    const json = await resp.json();
    const lista = json.data;

    if (!lista.length) {
        contenedor.innerHTML = '<div class="estado-vacio">Todavía no reportaste ninguna incidencia.</div>';
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


    document.getElementById('nuevaIncidenciaBtn').addEventListener('click', () => {
        window.location.href = '#';
    });


    async function cargarNotificaciones() {
    const contenedor = document.getElementById('notificacionesLista');
    const resp = await fetch(`${API}/usuarios/getNotificaciones?ci=${CI}`);
    const json = await resp.json();
    const lista = json.data;

    if (!lista.length) {
        contenedor.innerHTML = '<div class="estado-vacio">No tenés notificaciones por el momento.</div>';
        return;
    }contenedor.innerHTML = lista.map(n => `
        <div class="notif-card ${n.estado === 'leida' ? 'leida' : 'no-leida'}" id="notif-${n.id_notificacion}">
            <div class="notif-dot"></div>
            <div class="notif-cuerpo">
                <p class="notif-mensaje">${n.mensaje}</p>
                <div class="notif-meta">
                    <span>${n.tipo_origen}</span>
                    <span>${formatearFecha(n.fecha_creacion)}</span>
                </div>
            </div>
            ${n.estado !== 'leida' ? `<button class="btn-marcar-leida" data-id="${n.id_notificacion}">Marcar leída</button>` : ''}
        </div>
    `).join('');contenedor.querySelectorAll('.btn-marcar-leida').forEach(btn => {
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
        return fecha.toLocaleDateString('es-UY', { day: '2-digit', month: '2-digit', year: 'numeric' });
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
