/* =========================================================
   RECOLIX — Lógica de solicitudes de registro
   RF84 — Aprobar solicitud
   RF85 — Rechazar solicitud
========================================================= */

const API = '/recolix/API_Usuarios/api.php';
document.addEventListener('DOMContentLoaded', function () {
  cargarSolicitudes();
});

// ── Cargar solicitudes pendientes ────────────────────────
async function cargarSolicitudes() {
  const contenedor = document.getElementById('solicitudesContenedor');

  try {
    const resp = await fetch(`${API}/solicitudes/getSolicitudesPendientes`);
    const json = await resp.json();

    if (json.status !== 'success') {
      contenedor.innerHTML = '<p class="estado-carga">No se pudieron cargar las solicitudes.</p>';
      return;
    }

    const lista = json.data;

    if (!lista.length) {
      contenedor.innerHTML = `
        <div class="estado-vacio">
          <strong>No hay solicitudes pendientes</strong>
        </div>`;
      return;
    }

    contenedor.innerHTML = `
      <table class="tabla-solicitudes">
        <thead>
          <tr>
            <th>Cédula</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tablaCuerpo"></tbody>
      </table>`;

    const tbody = document.getElementById('tablaCuerpo');
    tbody.innerHTML = lista.map(s => `
      <tr id="fila-${s.ci_usuario}">
        <td data-label="Cédula">${s.ci_usuario}</td>
        <td data-label="Nombre">${s.primer_nombre}</td>
        <td data-label="Apellido">${s.p_apellido}</td>
        <td data-label="Email">${s.email}</td>
        <td data-label="Teléfono">${s.tel}</td>
        <td data-label="Acciones">
          <div class="col-acciones">
            <button class="btn-aprobar" onclick="aprobar(${s.ci_usuario})">Aprobar</button>
            <button class="btn-rechazar" onclick="rechazar(${s.ci_usuario})">Rechazar</button>
          </div>
        </td>
      </tr>
    `).join('');

  } catch (e) {
    contenedor.innerHTML = '<p class="estado-carga">No se pudo conectar con el servidor.</p>';
  }
}

// ── RF84 — Aprobar solicitud ─────────────────────────────
async function aprobar(ci) {
  if (!confirm('¿Confirmás que querés aprobar esta solicitud?')) return;

  try {
    const resp = await fetch(`${API}/solicitudes/aprobar`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ci })
    });
    const json = await resp.json();

    if (json.status !== 'success') {
      mostrarFeedback(json.message || 'No se pudo aprobar la solicitud.', false);
      return;
    }

    eliminarFila(ci);
    mostrarFeedback('Solicitud aprobada correctamente.', true);

  } catch (e) {
    mostrarFeedback('No se pudo conectar con el servidor.', false);
  }
}

// ── RF85 — Rechazar solicitud ────────────────────────────
async function rechazar(ci) {
  if (!confirm('¿Confirmás que querés rechazar esta solicitud?')) return;

  try {
    const resp = await fetch(`${API}/solicitudes/rechazar`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ci })
    });
    const json = await resp.json();

    if (json.status !== 'success') {
      mostrarFeedback(json.message || 'No se pudo rechazar la solicitud.', false);
      return;
    }

    eliminarFila(ci);
    mostrarFeedback('Solicitud rechazada.', true);

  } catch (e) {
    mostrarFeedback('No se pudo conectar con el servidor.', false);
  }
}

// ── Utilidades ───────────────────────────────────────────

// Elimina la fila de la tabla sin recargar la página.
function eliminarFila(ci) {
  const fila = document.getElementById(`fila-${ci}`);
  if (fila) fila.remove();

  // Si ya no quedan filas, mostrar estado vacío.
  const tbody = document.getElementById('tablaCuerpo');
  if (tbody && tbody.children.length === 0) {
    document.getElementById('solicitudesContenedor').innerHTML = `
      <div class="estado-vacio">
        <strong>No hay solicitudes pendientes</strong>
        <p>Cuando un vecino se registre desde la landing page, aparecerá acá.</p>
      </div>`;
  }
}

function mostrarFeedback(mensaje, esExito) {
  const el = document.getElementById('feedbackGlobal');
  el.textContent = mensaje;
  el.className = 'form-feedback ' + (esExito ? 'is-success' : 'is-error');
  // Ocultar automáticamente después de 4 segundos.
  setTimeout(() => {
    el.textContent = '';
    el.className = 'form-feedback';
  }, 4000);
}
