// Define la función globalmente adjuntándola al objeto window
window.actualizarEmpleadoEdit = async function (idEmpleado) {
  try {
    const response = await axios.get(
      `acciones/getEmpleado.php?id=${idEmpleado}`
    );
    if (response.status === 200) {

      function calcularEdad(fechaNacimiento) {
        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();

        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
          edad--;
        }

        return edad;
      }

      const infoEmpleado = response.data; // Obtener los datos del empleado desde la respuesta

      let tr = document.querySelector(`#empleado_${idEmpleado}`);
      let tablaHTML = "";
      tablaHTML += `
          <tr id="empleado_${infoEmpleado.id_user}">
            <th class="dt-type-numeric sorting_1" scope="row">${
              infoEmpleado.id_user-1
            }</th>
            <td>${infoEmpleado.name}</td>
            <td>${calcularEdad(infoEmpleado.birthday)}</td>
            <td>${infoEmpleado.cedula}</td>
            <td>${infoEmpleado.cargo}</td>
            <td>
              <img class="rounded-circle" src="acciones/fotos_empleados/${
                infoEmpleado.avatar || "sin-foto.jpg"
              }" alt="${infoEmpleado.name}" width="50" height="50">
            </td>
            <td>
              <a title="Ver detalles del empleado" href="#" onclick="verDetallesEmpleado(${
                infoEmpleado.id_user
              })" class="btn btn-success"><i class="bi bi-binoculars"></i></a>
              <a title="Editar datos del empleado" href="#" onclick="editarEmpleado(${
                infoEmpleado.id_user
              })" class="btn btn-warning"><i class="bi bi-pencil-square"></i></a>
              <a title="Eliminar datos del empleado" href="#" onclick="eliminarEmpleado(${
                infoEmpleado.id_user
              }, '${
        infoEmpleado.avatar || ""
      }')" class="btn btn-danger"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
        `;

      // Actualizar el contenido HTML de la tabla
      tr.innerHTML = tablaHTML;
    }
  } catch (error) {
    console.error("Error al obtener la información del empleado", error);
  }
};
