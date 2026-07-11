const API = '/recolix/API_Usuarios';

document.addEventListener('DOMContentLoaded', async function () {

  // Verificar sesión
  const sesion = JSON.parse(sessionStorage.getItem('sesion'));
  if (!sesion || sesion.rol !== 'vecino') {
    window.location.href = '../../Landing_Page/login.html';
    return;
  }

  const CI = sesion.ci_usuario;

  // ── Cargar perfil (RF90) ─────────────────────────────────
  async function cargarPerfil() {
    const resp  = await fetch(`${API}/usuarios/${CI}`);
    const json  = await resp.json();
    const datos = json.data;

    document.getElementById('perfilNombre').textContent = datos.primer_nombre + ' ' + datos.p_apellido;
    document.getElementById('perfilCI').textContent     = datos.ci_usuario;
    document.getElementById('perfilEmail').textContent  = datos.email || '—';
    document.getElementById('perfilTel').textContent    = datos.tel   || '—';

    // Pre-rellenar los inputs por si el usuario abre el form
    document.getElementById('editEmail').value = datos.email || '';
    document.getElementById('editTel').value   = datos.tel   || '';
  }

  await cargarPerfil();

  // ── Mostrar / ocultar bloques ────────────────────────────
  function mostrarSolo(idVisible) {
    ['perfilVista', 'perfilForm', 'contraForm'].forEach(id => {
      document.getElementById(id).hidden = (id !== idVisible);
    });
    limpiarFeedbacks();
  }

  document.getElementById('btnEditar')
    .addEventListener('click', () => mostrarSolo('perfilForm'));

  document.getElementById('btnCambiarContra')
    .addEventListener('click', () => mostrarSolo('contraForm'));

  document.getElementById('btnCancelarEditar')
    .addEventListener('click', () => mostrarSolo('perfilVista'));

  document.getElementById('btnCancelarContra')
    .addEventListener('click', () => mostrarSolo('perfilVista'));

  // ── Guardar cambios de perfil (RF91) ────────────────────
  document.getElementById('btnGuardar').addEventListener('click', async () => {
    const email = document.getElementById('editEmail').value.trim();
    const tel   = document.getElementById('editTel').value.trim();

    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);
    const telOk   = /^\d{9}$/.test(tel);

    document.getElementById('editEmailError').textContent = emailOk ? '' : 'Ingresá un correo válido.';
    document.getElementById('editTelError').textContent   = telOk   ? '' : 'El teléfono debe tener 9 dígitos.';

    if (!emailOk || !telOk) return;

    try {
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

      // Actualizar sesión con los nuevos datos
      sesion.email = email;
      sesion.tel   = tel;
      sessionStorage.setItem('sesion', JSON.stringify(sesion));

      await cargarPerfil();
      mostrarSolo('perfilVista');
      mostrarFeedback('perfilFeedback', 'Datos actualizados correctamente.', true);
    } catch (e) {
      mostrarFeedback('perfilFeedback', 'No se pudo conectar con el servidor.', false);
    }
  });

  // ── Cambiar contraseña (RF92) ────────────────────────────
  document.getElementById('btnGuardarContra').addEventListener('click', async () => {
    const actual  = document.getElementById('contraActual').value;
    const nueva   = document.getElementById('contraNueva').value;
    const repetir = document.getElementById('contraRepetir').value;

    document.getElementById('contraActualError').textContent  = actual.length < 1  ? 'Ingresá tu contraseña actual.'      : '';
    document.getElementById('contraNuevaError').textContent   = nueva.length  < 8  ? 'Mínimo 8 caracteres.'               : '';
    document.getElementById('contraRepetirError').textContent = nueva !== repetir   ? 'Las contraseñas no coinciden.'      : '';

    if (actual.length < 1 || nueva.length < 8 || nueva !== repetir) return;

    try {
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

      document.getElementById('contraActual').value  = '';
      document.getElementById('contraNueva').value   = '';
      document.getElementById('contraRepetir').value = '';
      mostrarSolo('perfilVista');
      mostrarFeedback('perfilFeedback', 'Contraseña actualizada correctamente.', true);
    } catch (e) {
      mostrarFeedback('contraFeedback', 'No se pudo conectar con el servidor.', false);
    }
  });

  // ── Utilidades ───────────────────────────────────────────
  function mostrarFeedback(id, mensaje, esExito) {
    const el = document.getElementById(id);
    el.textContent = mensaje;
    el.className = 'form-feedback ' + (esExito ? 'is-success' : 'is-error');
  }

  function limpiarFeedbacks() {
    ['perfilFeedback', 'contraFeedback'].forEach(id => {
      const el = document.getElementById(id);
      el.textContent = '';
      el.className = 'form-feedback';
    });
  }

});