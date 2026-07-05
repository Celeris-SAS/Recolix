/* =========================================================
   RF81, RF82, RF83, RF90, RF91, RF92, RF95, RF96
========================================================= */

const API = '/recolix/App_Vecino';

document.addEventListener('DOMContentLoaded', function () {

    /* --------------------------------------------------
       Verificar sesión activa.
       TODO: cuando el backend implemente JWT, validar el
       token almacenado en sessionStorage antes de cargar.
       Por ahora se trabaja con los datos guardados al hacer login.
    -------------------------------------------------- */
    const sesion = JSON.parse(sessionStorage.getItem('vecinoSesion'));
    if (!sesion || !sesion.ci_usuario) {
        window.location.href = '../Landing_Page/login.html';
        return;
    }

    const CI = sesion.ci_usuario;

    // Mostrar nombre en el header (RF82 — el rol ya viene del login).
    document.getElementById('nombreUsuario').textContent =
        sesion.primer_nombre + ' ' + sesion.p_apellido;

    // Cargar todo al arrancar.
    cargarPerfil();
    cargarIncidencias();
    cargarNotificaciones();

    // ================================================
    // RF83 — Cierre de sesión
    // ================================================
    document.getElementById('logoutBtn').addEventListener('click', async () => {
        try {
            // TODO: reemplazar por fetch real cuando el JWT esté implementado.
            // await fetch(`${API}/usuarios/logout`, { method: 'POST' });
        } finally {
            sessionStorage.removeItem('vecinoSesion');
            window.location.href = '../Landing_Page/login.html';
        }
    });

    // ================================================
    // RF90, RF91 — Perfil del vecino
    // ================================================

    async function cargarPerfil() {
        /* TODO: reemplazar por llamada real:
           const resp = await fetch(`${API}/usuarios/${CI}`);
           const json = await resp.json();
           const perfil = json.data; */
        const perfil = sesion; // Usando datos de sesión mientras no hay backend.

        document.getElementById('perfilNombre').textContent =
            perfil.primer_nombre + ' ' + perfil.p_apellido;
        document.getElementById('perfilCI').textContent = perfil.ci_usuario;
        document.getElementById('perfilEmail').textContent = perfil.email || '—';
        document.getElementById('perfilTel').textContent = perfil.tel || '—';
    }

    // Mostrar / ocultar formulario de edición de perfil.
    document.getElementById('editarPerfilBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = true;
        document.getElementById('perfilForm').hidden = false;
        document.getElementById('contraseniaForm').hidden = true;
        document.getElementById('editEmail').value = sesion.email || '';
        document.getElementById('editTel').value = sesion.tel || '';
    });

    document.getElementById('cancelarPerfilBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('perfilForm').hidden = true;
        limpiarFeedback('perfilFeedback');
    });

    // RF91 — Guardar cambios del perfil.
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

        try {
            /* TODO: reemplazar por llamada real:
               await fetch(`${API}/usuarios/${CI}`, {
                 method: 'PUT',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({ email, tel })
               }); */
            sesion.email = email;
            sesion.tel = tel;
            sessionStorage.setItem('vecinoSesion', JSON.stringify(sesion));
            cargarPerfil();
            document.getElementById('perfilVista').hidden = false;
            document.getElementById('perfilForm').hidden = true;
            mostrarFeedback('perfilFeedback', 'Datos actualizados correctamente.', true);
        } catch (e) {
            mostrarFeedback('perfilFeedback', 'No se pudo guardar. Intentá de nuevo.', false);
        }
    });

    // Mostrar formulario de cambio de contraseña.
    document.getElementById('cambiarContraseniaBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = true;
        document.getElementById('contraseniaForm').hidden = false;
        document.getElementById('perfilForm').hidden = true;
    });

    document.getElementById('cancelarContraBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('contraseniaForm').hidden = true;
        limpiarFeedback('contraFeedback');
    });

    // RF92 — Cambio de contraseña.
    document.getElementById('guardarContraBtn').addEventListener('click', async () => {
        const actual = document.getElementById('contraActual').value;
        const nueva = document.getElementById('contraNueva').value;

        document.getElementById('contraActualError').textContent =
            actual.length < 1 ? 'Ingresá tu contraseña actual.' : '';
        document.getElementById('contraNuevaError').textContent =
            nueva.length < 8 ? 'La nueva contraseña debe tener al menos 8 caracteres.' : '';

        if (actual.length < 1 || nueva.length < 8) return;

        try {
            /* TODO: reemplazar por llamada real:
               await fetch(`${API}/usuarios/cambiarContrasenia`, {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({ ci: CI, contraseniaActual: actual, nuevaContrasenia: nueva })
               }); */
            document.getElementById('contraActual').value = '';
            document.getElementById('contraNueva').value = '';
            document.getElementById('perfilVista').hidden = false;
            document.getElementById('contraseniaForm').hidden = true;
            mostrarFeedback('perfilFeedback', 'Contraseña actualizada correctamente.', true);
        } catch (e) {
            mostrarFeedback('contraFeedback', 'No se pudo cambiar la contraseña. Verificá que la contraseña actual sea correcta.', false);
        }
    });

    // ================================================
    // RF03 — Incidencias del vecino
    // ================================================

    async function cargarIncidencias() {
        const contenedor = document.getElementById('incidenciasLista');
        try {
            /* TODO: reemplazar por llamada real:
               const resp = await fetch(`${API}/usuarios/getIncidencias?ci=${CI}`);
               const json = await resp.json();
               const lista = json.data; */
            const lista = []; // Vacío hasta que el backend esté disponible.

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
        } catch (e) {
            contenedor.innerHTML = '<p class="estado-carga">No se pudieron cargar las incidencias.</p>';
        }
    }

    // Botón de nueva incidencia — redirige al formulario de incidencias.
    document.getElementById('nuevaIncidenciaBtn').addEventListener('click', () => {
        // TODO: redirigir a la página de reporte de incidencias o mostrar un modal.
        window.location.href = '../Landing_Page/incidencias.html';
    });

    // ================================================
    // RF95, RF96 — Notificaciones del vecino
    // ================================================

    async function cargarNotificaciones() {
        const contenedor = document.getElementById('notificacionesLista');
        try {
            /* TODO: reemplazar por llamada real:
               const resp = await fetch(`${API}/usuarios/getNotificaciones?ci=${CI}`);
               const json = await resp.json();
               const lista = json.data; */
            const lista = []; // Vacío hasta que el backend esté disponible.

            if (!lista.length) {
                contenedor.innerHTML = '<div class="estado-vacio">No tenés notificaciones por el momento.</div>';
                return;
            }

            contenedor.innerHTML = lista.map(n => `
        <div class="notif-card ${n.estado === 'leida' ? 'leida' : 'no-leida'}" id="notif-${n.id_notificacion}">
          <div class="notif-dot"></div>
          <div class="notif-cuerpo">
            <p class="notif-mensaje">${n.mensaje}</p>
            <div class="notif-meta">
              <span>${n.tipo_origen}</span>
              <span>${formatearFecha(n.fecha_creacion)}</span>
              <span class="badge badge-${n.prioridad === 'critica' ? 'abierta' : 'resuelta'}">${n.prioridad}</span>
            </div>
          </div>
          ${n.estado !== 'leida' ? `<button class="btn-marcar-leida" data-id="${n.id_notificacion}">Marcar leída</button>` : ''}
        </div>
      `).join('');

            // RF96 — Marcar notificación como leída al hacer clic.
            contenedor.querySelectorAll('.btn-marcar-leida').forEach(btn => {
                btn.addEventListener('click', () => marcarLeida(btn.dataset.id));
            });

        } catch (e) {
            contenedor.innerHTML = '<p class="estado-carga">No se pudieron cargar las notificaciones.</p>';
        }
    }

    // RF96 — Llama al endpoint para marcar una notificación como leída.
    async function marcarLeida(idNotificacion) {
        try {
            /* TODO: reemplazar por llamada real:
               await fetch(`${API}/usuarios/marcarNotificacionLeida`, {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify({ idNotificacion })
               }); */
            const tarjeta = document.getElementById(`notif-${idNotificacion}`);
            if (tarjeta) {
                tarjeta.classList.remove('no-leida');
                tarjeta.classList.add('leida');
                tarjeta.querySelector('.notif-dot').style.background = '#ccc';
                const btn = tarjeta.querySelector('.btn-marcar-leida');
                if (btn) btn.remove();
            }
        } catch (e) {
            console.error('No se pudo marcar la notificación como leída.', e);
        }
    }

    // ================================================
    // Utilidades
    // ================================================

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