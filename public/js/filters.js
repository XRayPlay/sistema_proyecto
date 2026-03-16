async function loadFilters() {
    const response = await fetch('../php/get_filters.php');
    const data = await response.json();

    // Poblar piso
    const pisoSelect = document.getElementById('piso');
    pisoSelect.innerHTML = '<option value="">Todos</option>';
    data.pisos.forEach(p => {
        pisoSelect.innerHTML += `<option value="${p.id_floors}">${p.name}</option>`;
    });

    // Poblar estado
    const estadoSelect = document.getElementById('estado');
    estadoSelect.innerHTML = '<option value="">Todos</option>';
    data.estados.forEach(e => {
        estadoSelect.innerHTML += `<option value="${e.id_status_incidencia}">${e.name}</option>`;
    });

    // Poblar tecnico
    const tecnicoSelect = document.getElementById('tecnico');
    tecnicoSelect.innerHTML = '<option value="">Todos</option>';
    data.tecnicos.forEach(t => {
        tecnicoSelect.innerHTML += `<option value="${t.id_person}">${t.nombre}</option>`;
    });

    // Poblar departamento
    const departamentoSelect = document.getElementById('departamento');
    departamentoSelect.innerHTML = '<option value="">Todos</option>';
    data.departamentos.forEach(d => {
        departamentoSelect.innerHTML += `<option value="${d.id_cargo}">${d.name}</option>`;
    });
}

loadFilters();