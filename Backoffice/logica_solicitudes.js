

const API = '/recolix/API_Usuarios/api.php/';
document.addEventListener('DOMContentLoaded', function () {
  cargarSolicitudes();
});


async function cargarSolicitudes() {
  const contenedor = document.getElementById('solicitudesContenedor');

  try {
    const resp = await fetch(`${API}/solicitudes/getSolicitudesPendientes`);
    const json = await resp.json();

    if (json.status !== 'success') {
      contenedor.innerHTML = `<p class="estado-carga">${I18N.errorCargar}</p>`;
      return;
    }

    const lista = json.data;

    if (!lista.length) {
      contenedor.innerHTML = `
        <div class="estado-vacio">
          <strong>${I18N.noHay}</strong>
        </div>`;
      return;
    }

    contenedor.innerHTML = `
      <table class="tabla-solicitudes">
        <thead>
          <tr>
            <th>${I18N.colCedula}</th>
            <th>${I18N.colNombre}</th>
            <th>${I18N.colApellido}</th>
            <th>${I18N.colEmail}</th>
            <th>${I18N.colTelefono}</th>
            <th>${I18N.colAcciones}</th>
          </tr>
        </thead>
        <tbody id="tablaCuerpo"></tbody>
      </table>`;

    const tbody = document.getElementById('tablaCuerpo');
    tbody.innerHTML = lista.map(s => `
      <tr id="fila-${s.ci_usuario}">
        <td data-label="${I18N.colCedula}">${s.ci_usuario}</td>
        <td data-label="${I18N.colNombre}">${s.primer_nombre}</td>
        <td data-label="${I18N.colApellido}">${s.p_apellido}</td>
        <td data-label="${I18N.colEmail}">${s.email}</td>
        <td data-label="${I18N.colTelefono}">${s.tel}</td>
        <td data-label="${I18N.colAcciones}">
          <div class="col-acciones">
            <button class="btn-aprobar" onclick="aprobar(${s.ci_usuario})">${I18N.aprobar}</button>
            <button class="btn-rechazar" onclick="rechazar(${s.ci_usuario})">${I18N.rechazar}</button>
          </div>
        </td>
      </tr>
    `).join('');

  } catch (e) {
    contenedor.innerHTML = `<p class="estado-carga">${I18N.errorConexion}</p>`;
  }
}


async function aprobar(ci) {
  if (!confirm(I18N.confirmarAprobar)) return;

  try {
    const resp = await fetch(`${API}/solicitudes/aprobar`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ci })
    });
    const json = await resp.json();

    if (json.status !== 'success') {
      mostrarFeedback(json.message || I18N.errorAprobar, false);
      return;
    }

    eliminarFila(ci);
    mostrarFeedback(I18N.aprobadaOk, true);

  } catch (e) {
    mostrarFeedback(I18N.errorConexion, false);
  }
}


async function rechazar(ci) {
  if (!confirm(I18N.confirmarRechazar)) return;

  try {
    const resp = await fetch(`${API}/solicitudes/rechazar`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ci })
    });
    const json = await resp.json();

    if (json.status !== 'success') {
      mostrarFeedback(json.message || I18N.errorRechazar, false);
      return;
    }

    eliminarFila(ci);
    mostrarFeedback(I18N.rechazadaOk, true);

  } catch (e) {
    mostrarFeedback(I18N.errorConexion, false);
  }
}


function eliminarFila(ci) {
  const fila = document.getElementById(`fila-${ci}`);
  if (fila) fila.remove();

  const tbody = document.getElementById('tablaCuerpo');
  if (tbody && tbody.children.length === 0) {
    document.getElementById('solicitudesContenedor').innerHTML = `
      <div class="estado-vacio">
        <strong>${I18N.noHay}</strong>
        <p>${I18N.noHayDesc}</p>
      </div>`;
  }
}

function mostrarFeedback(mensaje, esExito) {
  const el = document.getElementById('feedbackGlobal');
  el.textContent = mensaje;
  el.className = 'form-feedback ' + (esExito ? 'is-success' : 'is-error');
  
  setTimeout(() => {
    el.textContent = '';
    el.className = 'form-feedback';
  }, 4000);
}
