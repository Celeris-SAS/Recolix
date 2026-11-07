

const API = '/recolix/API_Usuarios/api.php/';
document.addEventListener('DOMContentLoaded', function () {
  cargarSolicitudes();

  document.getElementById('btnAprobarTodas').addEventListener('click', aprobarTodas);
  document.getElementById('btnRechazarTodas').addEventListener('click', rechazarTodas);

  document.getElementById('formAprobarCi').addEventListener('submit', function (e) {
    e.preventDefault();
    aprobarPorCi();
  });
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
  if (!await pedirConfirmacion(I18N.confirmarAprobar)) return;

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
  if (!await pedirConfirmacion(I18N.confirmarRechazar)) return;

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


async function aprobarTodas() {
  if (!await pedirConfirmacion(I18N.confirmarAprobarTodas)) return;

  try {
    const resp = await fetch(`${API}/solicitudes/aprobarTodas`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({})
    });
    const json = await resp.json();

    if (json.status !== 'success') {
      mostrarFeedback(json.message || I18N.errorAprobar, false);
      return;
    }

    const resultados = json.data || [];
    if (!resultados.length) {
      mostrarFeedback(I18N.sinPendientes, true);
    } else {
      const ok = resultados.filter(r => r.status === 'ok').length;
      mostrarFeedback(
        I18N.resultadoAprobarTodas.replace('{ok}', ok).replace('{total}', resultados.length),
        true
      );
    }
    cargarSolicitudes();

  } catch (e) {
    mostrarFeedback(I18N.errorConexion, false);
  }
}


async function rechazarTodas() {
  if (!await pedirConfirmacion(I18N.confirmarRechazarTodas)) return;

  try {
    const resp = await fetch(`${API}/solicitudes/rechazarTodas`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({})
    });
    const json = await resp.json();

    if (json.status !== 'success') {
      mostrarFeedback(json.message || I18N.errorRechazar, false);
      return;
    }

    const resultados = json.data || [];
    if (!resultados.length) {
      mostrarFeedback(I18N.sinPendientes, true);
    } else {
      const ok = resultados.filter(r => r.status === 'ok').length;
      mostrarFeedback(
        I18N.resultadoRechazarTodas.replace('{ok}', ok).replace('{total}', resultados.length),
        true
      );
    }
    cargarSolicitudes();

  } catch (e) {
    mostrarFeedback(I18N.errorConexion, false);
  }
}


async function aprobarPorCi() {
  const input = document.getElementById('inputAprobarCi');
  const ci = input.value.trim();

  if (!/^\d{7,8}$/.test(ci)) {
    mostrarFeedback(I18N.ciInvalida, false);
    return;
  }

  if (!await pedirConfirmacion(I18N.confirmarAprobar)) return;

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

    input.value = '';
    eliminarFila(Number(ci));
    mostrarFeedback(I18N.aprobadaOk, true);

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

let toastTimeout;

function mostrarFeedback(mensaje, esExito) {
  const toast = document.getElementById('toast');
  clearTimeout(toastTimeout);
  toast.textContent = mensaje;
  toast.className = 'toast ' + (esExito ? 'toast-success' : 'toast-error') + ' toast-show';
  toastTimeout = setTimeout(() => toast.classList.remove('toast-show'), 3500);
}

function pedirConfirmacion(mensaje) {
  return new Promise(resolve => {
    const modal = document.getElementById('modalConfirmar');
    const aceptar = document.getElementById('modalAceptar');
    const cancelar = document.getElementById('modalCancelar');
    document.getElementById('modalConfirmarMensaje').textContent = mensaje;
    modal.hidden = false;

    function cerrar(valor) {
      modal.hidden = true;
      aceptar.removeEventListener('click', onAceptar);
      cancelar.removeEventListener('click', onCancelar);
      resolve(valor);
    }
    function onAceptar() { cerrar(true); }
    function onCancelar() { cerrar(false); }

    aceptar.addEventListener('click', onAceptar);
    cancelar.addEventListener('click', onCancelar);
  });
}
