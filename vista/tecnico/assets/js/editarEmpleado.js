/**
 * Función para mostrar la modal de editar el empleado
 */
async function editarEmpleado(idEmpleado) {
  try {
    // Ocultar la modal si está abierta
    const existingModal = document.getElementById("editarEmpleadoModal");
    if (existingModal) {
      const modal = bootstrap.Modal.getInstance(existingModal);
      if (modal) {
        modal.hide();
      }
      existingModal.remove(); // Eliminar la modal existente
    }

    const response = await fetch("modales/modalEditar.php");
    if (!response.ok) {
      throw new Error("Error al cargar la modal de editar el empleado");
    }
    const modalHTML = await response.text();

    // Crear un elemento div para almacenar el contenido de la modal
    const modalContainer = document.createElement("div");
    modalContainer.innerHTML = modalHTML;

    // Agregar la modal al documento actual
    document.body.appendChild(modalContainer);

    // Mostrar la modal
    const myModal = new bootstrap.Modal(
      modalContainer.querySelector("#editarEmpleadoModal")
    );
    myModal.show();

    await cargarDatosEmpleadoEditar(idEmpleado);
  } catch (error) {
    console.error(error);
  }
}

/**
 * Función buscar información del empleado seleccionado y cargarla en la modal
 */
async function cargarDatosEmpleadoEditar(idEmpleado) {
  try {
    const response = await axios.get(
      `acciones/detallesEmpleado.php?id=${idEmpleado}`
    );
    if (response.status === 200) {
      const { id_user, username, pass, name, birthday, cedula, sexo, phone, email, avatar, id_cargo, cargo } =
        response.data;

      console.log(id_user, username, pass, name, birthday, cedula, sexo, phone, email, avatar, id_cargo, cargo);
      document.querySelector("#idempleado").value = id_user;
      document.querySelector("#nombre").value = name;
      document.querySelector("#birthday").value = birthday;
      document.querySelector("#cedula").value = cedula;
      document.querySelector("#telefono").value = phone;
      document.querySelector("#pass").value = pass;
      document.querySelector("#username").value = username;
      document.querySelector("#email").value = email;

      // Seleccionar el sexo correspondiente
      seleccionarSexo(sexo);

      // Obtener el elemento <select> de cargo
      seleccionarCargo(id_cargo);

      document.querySelector("#avatar").value = avatar;
      let elementAvatar = document.querySelector("#avatar");
      if (avatar) {
        elementAvatar.src = `acciones/fotos_empleados/${avatar}`;
      } else {
        elementAvatar.src = "assets/imgs/sin-foto.jpg";
      }
    } else {
      console.log("Error al cargar el empleado a editar");
    }
  } catch (error) {
    console.error(error);
    alert("Hubo un problema al cargar los detalles del empleado");
  }
}

/**
 * Función para seleccionar el sexo del empleado de acuedo al sexo actual
 */
function seleccionarSexo(sexoEmpleado) {
  // Obtener los elementos de radio para "Masculino" y "Femenino"
  const radioMasculino = document.querySelector("#sexo_m");
  const radioFemenino = document.querySelector("#sexo_f");

  // Verificar el valor del sexo del empleado y establecer el atributo checked en el radio correspondiente
  if (sexoEmpleado === "Masculino") {
    radioMasculino.checked = true;
  } else if (sexoEmpleado === "Femenino") {
    radioFemenino.checked = true;
  }
}

/**
 * Función para seleccionar el cargo del empleado de acuedo al cargo actual
 */
function seleccionarCargo(cargoEmpleado) {
  const selectCargo = document.querySelector("#cargo");
  selectCargo.value = cargoEmpleado;
}

async function actualizarEmpleado(event) {
  try {
    event.preventDefault();

    const formulario = document.querySelector("#formularioEmpleadoEdit");

    // Eliminar errores anteriores
    const erroresAntiguos = formulario.querySelectorAll(".error-text");
    erroresAntiguos.forEach(e => e.remove());

    const expresiones = {
      nombre: /^.{3,}$/,
      cedula: /^\d{5,}$/,
      username: /^.{3,}$/,
      pass: /^.{6,}$/,
      telefono: /^\d{7,}$/,
      email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
      birthday: fecha => {
        if (!fecha) return false;
        const hoy = new Date();
        const f = new Date(fecha);
        return f < hoy;
      },
      cargo: val => val !== '',
      sexo: val => val === 'Masculino' || val === 'Femenino',
      avatar: file => {
        if (!file) return true; // opcional
        const tiposValidos = ['image/jpeg', 'image/png'];
        return tiposValidos.includes(file.type);
      }
    };

    const mensajes = {
      nombre: "El nombre debe tener al menos 3 caracteres.",
      cedula: "Ingrese una cédula válida (mínimo 5 dígitos numéricos).",
      username: "El usuario debe tener al menos 3 caracteres.",
      pass: "La contraseña debe tener al menos 6 caracteres.",
      telefono: "Ingrese un teléfono válido (mínimo 7 dígitos numéricos).",
      email: "Ingrese un correo electrónico válido.",
      birthday: "Seleccione una fecha de nacimiento válida (no futura).",
      cargo: "Seleccione un cargo.",
      sexo: "Seleccione el sexo correctamente.",
      avatar: "La imagen debe ser JPG o PNG."
    };

    // Obtener campos
    const idempleado = formulario.idempleado.value;
    const nombre = formulario.nombre;
    const cedula = formulario.cedula;
    const username = formulario.username;
    const pass = formulario.pass;
    const birthday = formulario.birthday;
    const telefono = formulario.telefono;
    const email = formulario.email;
    const cargo = formulario.cargo;
    const sexo = formulario.querySelector('input[name="sexo"]:checked');
    const avatarFile = formulario.avatar.files[0] || null;

    let errores = 0;

    const mostrarError = (input, mensaje) => {
      const div = document.createElement("div");
      div.classList.add("error-text");
      div.innerText = mensaje;
      input.closest(".mb-3, .col-md-6").appendChild(div);
    };

    if (!expresiones.nombre.test(nombre.value.trim())) {
      mostrarError(nombre, mensajes.nombre);
      errores++;
    }

    if (!expresiones.cedula.test(cedula.value.trim())) {
      mostrarError(cedula, mensajes.cedula);
      errores++;
    }

    if (!expresiones.username.test(username.value.trim())) {
      mostrarError(username, mensajes.username);
      errores++;
    }

    if (!expresiones.pass.test(pass.value.trim())) {
      mostrarError(pass, mensajes.pass);
      errores++;
    }

    if (!expresiones.birthday(birthday.value)) {
      mostrarError(birthday, mensajes.birthday);
      errores++;
    }

    if (!expresiones.telefono.test(telefono.value.trim())) {
      mostrarError(telefono, mensajes.telefono);
      errores++;
    }

    if (!expresiones.email.test(email.value.trim())) {
      mostrarError(email, mensajes.email);
      errores++;
    }

    if (!expresiones.cargo(cargo.value)) {
      mostrarError(cargo, mensajes.cargo);
      errores++;
    }

    if (!sexo || !expresiones.sexo(sexo.value)) {
      mostrarError(formulario.querySelector(".form-check:last-child"), mensajes.sexo);
      errores++;
    }

    if (!expresiones.avatar(avatarFile)) {
      mostrarError(formulario.avatar, mensajes.avatar);
      errores++;
    }

    if (errores > 0) return;

    // Enviar datos si todo es válido
    const formData = new FormData(formulario);

    const response = await axios.post("acciones/updateEmpleado.php", formData);

    if (response.status === 200) {
      console.log("Empleado actualizado exitosamente");

      window.actualizarEmpleadoEdit(idempleado);

      if (window.toastrOptions) {
        toastr.options = window.toastrOptions;
        toastr.success("¡El empleado se actualizó correctamente!");
      }

      setTimeout(() => {
        $("#editarEmpleadoModal").css("opacity", "");
        $("#editarEmpleadoModal").modal("hide");
      }, 600);
    } else {
      console.error("Error al actualizar el empleado");
    }
  } catch (error) {
    console.error("Error al enviar el formulario", error);
  }
}