const API = '/recolix/API_Usuarios';

document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('logoutBtn').addEventListener('click', () => {
        window.location.href = '../Backoffice/logout_backoffice.php';
    });

    async function cargarPerfil() {
        const resp  = await fetch(`${API}/usuarios/${CI}`);
        const json  = await resp.json();
        const perfil = json.data;
        document.getElementById('perfilNombre').textContent = perfil.primer_nombre + ' ' + perfil.p_apellido;
        document.getElementById('perfilCI').textContent = perfil.ci_usuario;
        document.getElementById('perfilEmail').textContent = perfil.email || '—';
        document.getElementById('perfilTel').textContent = perfil.tel || '—';
        document.getElementById('editEmail').value = perfil.email || '';
        document.getElementById('editTel').value = perfil.tel || '';
    }

    cargarPerfil();

    document.getElementById('editarPerfilBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = true;
        document.getElementById('perfilForm').hidden = false;
        document.getElementById('contraseniaForm').hidden = true;
    });

    document.getElementById('cancelarPerfilBtn').addEventListener('click', () => {
        document.getElementById('perfilVista').hidden = false;
        document.getElementById('perfilForm').hidden = true;
        limpiarFeedback('perfilFeedback');
    });

    document.getElementById('guardarPerfilBtn').addEventListener('click', async () => {
        const email = document.getElementById('editEmail').value.trim();
        const tel = document.getElementById('editTel').value.trim();

        const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);
        const telOk = /^\d{9}$/.test(tel);

        document.getElementById('editEmailError').textContent = emailOk ? '' : 'Ingresá un correo válido.';
        document.getElementById('editTelError').textContent = telOk ? '' : 'El teléfono debe tener 9 dígitos.';

        if (!emailOk || !telOk) return;

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
        mostrarFeedback('perfilFeedback', 'Datos actualizados correctamente.', true);
    });

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
