function cargarReportes() {
  const estado = document.getElementById("filtroEstado").value;

  fetch(`../php/listarReportes.php?estado=${estado}`)
    .then(response => response.json())
    .then(data => renderTabla(data))
    .catch(error => console.error("Error al cargar reportes:", error));
}
const getEstadoClase = (estado) => {
  if (!estado) return '';
  switch (estado.toLowerCase()) {
    case 'asignado':
      return 'estado-asignado';
    case 'redirigido':
      return 'estado-reasignado';
    case 'en proceso':
      return 'estado-pendiente';
    case 'cerrado':
      return 'estado-cerrado';
    default:
      return '';
  }
};

function renderTabla(data) {
  const tabla = document.getElementById("tabla-tickets");
  tabla.innerHTML = "";

  data.forEach((item, i) => {
    let extraInfo = '';
    if (item.estado === 'Cerrado') {
      extraInfo = `
        <div class="mt-2 small text-muted">
          <strong>Descripción:</strong> ${item.problem}<br>
          <strong>Detalle técnico:</strong> ${item.detalle_tecnico || 'N/A'}
        </div>`;
    }

    const botonAsignarTexto = item.tecnico ? "Reasignar técnico" : "Asignar técnico";
    const botonAsignarClase = item.tecnico ? "btn-warning text-white" : "btn-outline-primary";

    const row = `
      <tr>
        <td>
          ${item.problem}
          ${extraInfo}
        </td>
        <td><span class="badge ${getEstadoClase(item.estado)}">${item.estado}</span></td>
        <td><span class="badge cargo_departamento">${item.cargo}</span></td>
        <td>${item.fecha_resuelto ?? 'Pendiente'}</td>
        <td class="d-flex gap-1 flex-wrap">
          <button class="btn btn-outline-success btn-sm" onclick="verDetalle(${item.id_report})">Ver</button>
          <button class="btn ${botonAsignarClase} btn-sm" onclick="abrirAsignar(${item.id_report})">${botonAsignarTexto}</button>
          <button class="btn btn-outline-warning btn-sm" onclick="abrirEditar(${item.id_report})">Editar</button>
        </td>
      </tr>`;

    tabla.insertAdjacentHTML("beforeend", row);
  });
}


async function verDetalle(id) {
  try {
    const res = await fetch(`../php/getDetalleReporte.php?id=${id}`);
    const data = await res.json();

    if (data.error) {
      alert(data.error);
      return;
    }

    document.getElementById("detalleProblema").textContent = data.problem;
    document.getElementById("detalleEstado").textContent = data.estado;
    document.getElementById("detalleCargo").textContent = data.cargo;
    document.getElementById("detalleTecnico").textContent = data.tecnico || "No asignado";
    document.getElementById("detalleFecha").textContent = data.fecha_resuelto || "Pendiente";

    new bootstrap.Modal(document.getElementById("verModal")).show();
  } catch (error) {
    console.error("Error al cargar detalle:", error);
  }
}

async function abrirAsignar(id) {
  try {
    const tecnicoSelect = document.getElementById("asignarTecnicoSelect");

    const res = await fetch(`../php/listarTecnicos.php`);
    const tecnicos = await res.json();

    tecnicoSelect.innerHTML = "";
    tecnicos.forEach(tecnico => {
      tecnicoSelect.innerHTML += `<option value="${tecnico.id_user}">${tecnico.name}</option>`;
    });

    document.getElementById("asignarId").value = id;
    new bootstrap.Modal(document.getElementById("asignarModal")).show();
  } catch (error) {
    console.error("Error al cargar técnicos:", error);
  }
}

async function enviarAsignacion() {
  const id = document.getElementById("asignarId").value;
  const tecnicoId = document.getElementById("asignarTecnicoSelect").value;

  const res = await fetch("../php/asignarTecnico.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id_report: id, id_user_tecnico: tecnicoId })
  });

  const data = await res.json();

  if (data.success) {
    bootstrap.Modal.getInstance(document.getElementById("asignarModal")).hide();
    cargarReportes();
  } else {
    alert("Error al asignar técnico.");
  }
}

async function abrirEditar(id) {
  try {
    const res = await fetch(`../php/getDetalleReporte.php?id=${id}`);
    const data = await res.json();

    if (data.error) {
      alert(data.error);
      return;
    }

    document.getElementById("editarIdReporte").value = id;
    document.getElementById("editarAsunto").value = data.problem || "";
    document.getElementById("editarDetalle").value = data.detalleTec || "N/A";

    // Cargar departamentos
    const cargoSelect = document.getElementById("editarCargo");

    const resCargo = await fetch(`../php/listarCargos.php`);
    const cargos = await resCargo.json();

    cargos.forEach(cargo => {
      cargoSelect.innerHTML = `<option value="${data.id_cargo}" ${cargo.id_cargo === data.id_cargo ? "selected" : ""}>${data.cargo}</option>`;
    });

    new bootstrap.Modal(document.getElementById("modalEditar")).show();
  } catch (error) {
    console.error("Error al editar reporte:", error);
  }
}

async function enviarEdicion() {
  const id = document.getElementById("editarId").value;
  const problem = document.getElementById("editarProblema").value;
  const id_cargo = document.getElementById("editarCargo").value;

  const res = await fetch("../php/editarReporte.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id_report: id, problem, id_cargo })
  });

  const data = await res.json();

  if (data.success) {
    bootstrap.Modal.getInstance(document.getElementById("editarModal")).hide();
    cargarReportes();
  } else {
    alert("Error al editar reporte.");
  }
}
