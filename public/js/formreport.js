
document.getElementById('selectCargo').addEventListener('change', function() {
    const cargoId = this.value;
    fetch('../../php/get_subcargos.php?cargo_id=' + cargoId)
        .then(response => response.json())
        .then(data => {
            const subcargoSelect = document.getElementById('selectSubcargo');
            subcargoSelect.innerHTML = '<option selected disabled>Seleccionar Subcategoría</option>';
            data.forEach(item => {
                subcargoSelect.innerHTML += `<option value="${item.id_subcargo}">${item.descripcion}</option>`;
            });
            subcargoSelect.disabled = false;
        });
});

document.getElementById('selectSubcargo').addEventListener('change', function() {
    const subcargoId = this.value;
    fetch('../../php/get_especialidades.php?subcargo_id=' + subcargoId)
        .then(response => response.json())
        .then(data => {
            const especialidadSelect = document.getElementById('selectEspecialidad');
            especialidadSelect.innerHTML = '<option selected disabled>Seleccionar Especialidad</option>';
            data.forEach(item => {
                especialidadSelect.innerHTML += `<option value="${item.id_especialidad}">${item.descripcion}</option>`;
            });
            especialidadSelect.disabled = false;
        });
});
