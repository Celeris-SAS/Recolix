/* =========================================================
   RF81, RF82, RF83, RF90, RF91, RF92, RF95, RF96
   Archivo organizado en secciones
========================================================= */

const API = '/recolix/api.php'; // TODO: cambiar a la URL real del backend cuando esté disponible.

document.addEventListener('DOMContentLoaded', function () {

    // ===================== SESIÓN =====================
    // Crear sesión falsa de prueba (solo para desarrollo)
    if (!sessionStorage.getItem('vecinoSesion')) {
        sessionStorage.setItem('vecinoSesion', JSON.stringify({
            ci_usuario: '12345678',
            primer_nombre: 'Nombre',
            p_apellido: 'Apellido',
            email: 'ejemplo@ejemplo.com',
            tel: '099123456'
        }));
    }

    const sesion = JSON.parse(sessionStorage.getItem('vecinoSesion'));

    // Probar nombres
    sesion.primer_nombre = 'Ejemplo';
    sesion.p_apellido = 'Ejemplo';
    sessionStorage.setItem('vecinoSesion', JSON.stringify(sesion));
    cargarPerfil();

    // Validar que haya sesión
    if (!sesion || !sesion.ci_usuario) {
        window.location.href = '../Landing_Page/login.html';
        return;
    }

    const CI = sesion.ci_usuario;

    // ===================== LOGOUT =====================
    document.getElementById('logoutBtn').addEventListener('click', async () => {
        try {
            // TODO: reemplazar por fetch real cuando el JWT esté implementado.
        } finally {
            sessionStorage.removeItem('vecinoSesion');
            window.location.href = '../Landing_Page/login.html';
        }
    });

    // ===================== PERFIL =====================
    async function cargarPerfil() {
        const perfil = sesion; // Usando datos de sesión mientras no hay backend.
        document.getElementById('perfilNombre').textContent =
            perfil.primer_nombre + ' ' + perfil.p_apellido;
        document.getElementById('perfilCI').textContent = perfil.ci_usuario;
        document.getElementById('perfilEmail').textContent = perfil.email || '—';
        document.getElementById('perfilTel').textContent = perfil.tel || '—';
    }

    // Mostrar / ocultar formulario de edición de perfil
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

    // Guardar cambios de perfil (solo email y teléfono)
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

        sesion.email = email;
        sesion.tel = tel;
        sessionStorage.setItem('vecinoSesion', JSON.stringify(sesion));
        cargarPerfil();
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('perfilForm').hidden = true;
        mostrarFeedback('perfilFeedback', 'Datos actualizados correctamente.', true);
    });

    // Mostrar / ocultar formulario de cambio de contraseña
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

    // Guardar nueva contraseña
    document.getElementById('guardarContraBtn').addEventListener('click', async () => {
        const actual = document.getElementById('contraActual').value;
        const nueva = document.getElementById('contraNueva').value;

        document.getElementById('contraActualError').textContent =
            actual.length < 1 ? 'Ingresá tu contraseña actual.' : '';
        document.getElementById('contraNuevaError').textContent =
            nueva.length < 8 ? 'La nueva contraseña debe tener al menos 8 caracteres.' : '';

        if (actual.length < 1 || nueva.length < 8) return;

        // TODO: reemplazar por llamada real al backend
        document.getElementById('contraActual').value = '';
        document.getElementById('contraNueva').value = '';
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('contraseniaForm').hidden = true;
        mostrarFeedback('perfilFeedback', 'Contraseña actualizada correctamente.', true);
    });

    // ===================== INCIDENCIAS =====================
    async function cargarIncidencias() {
        const contenedor = document.getElementById('incidenciasLista');
        const lista = []; // Vacío hasta que el backend esté disponible.

        if (!lista.length) {
            contenedor.innerHTML = '<div class="estado-vacio">Todavía no reportaste ninguna incidencia.</div>';
            return;
        }
    }

    document.getElementById('nuevaIncidenciaBtn').addEventListener('click', () => {
        window.location.href = '../Landing_Page/incidencias.html';
    });

    // ===================== NOTIFICACIONES =====================
    async function cargarNotificaciones() {
        const contenedor = document.getElementById('notificacionesLista');
        const lista = []; // Vacío hasta que el backend esté disponible.

        if (!lista.length) {
            contenedor.innerHTML = '<div class="estado-vacio">No tenés notificaciones por el momento.</div>';
            return;
        }
    }

    async function marcarLeida(idNotificacion) {
        const tarjeta = document.getElementById(`notif-${idNotificacion}`);
        if (tarjeta) {
            tarjeta.classList.remove('no-leida');
            tarjeta.classList.add('leida');
            tarjeta.querySelector('.notif-dot').style.background = '#ccc';
            const btn = tarjeta.querySelector('.btn-marcar-leida');
            if (btn) btn.remove();
        }
    }

    // ===================== UTILIDADES =====================
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
