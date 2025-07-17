/**
 * Modal para agregar un nuevo empleado
 */
async function modalRegistrarEmpleado() {
  try {
    // Ocultar la modal si está abierta
    const existingModal = document.getElementById("detalleEmpleadoModal");
    if (existingModal) {
      const modal = bootstrap.Modal.getInstance(existingModal);
      if (modal) {
        modal.hide();
      }
      existingModal.remove(); // Eliminar la modal existente
    }

    const response = await fetch("modales/modalAdd.php");

    if (!response.ok) {
      throw new Error("Error al cargar la modal");
    }

    // response.text() es un método en programación que se utiliza para obtener el contenido de texto de una respuesta HTTP
    const data = await response.text();

    // Crear un elemento div para almacenar el contenido de la modal
    const modalContainer = document.createElement("div");
    modalContainer.innerHTML = data;

    // Agregar la modal al documento actual
    document.body.appendChild(modalContainer);

    // Mostrar la modal
    const myModal = new bootstrap.Modal(
      modalContainer.querySelector("#agregarEmpleadoModal")
    );
    myModal.show();
  } catch (error) {
    console.error(error);
  }
}


// Expresiones de validación
const expresiones = {
  nombre: /^.{3,20}$/,
  cedula: /^\d{7,9}$/,
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
    if (!file) return true;
    const tiposValidos = ['image/jpeg', 'image/png'];
    return tiposValidos.includes(file.type);
  }
};

// Mensajes de error
const mensajes = {
  nombre: "El nombre debe tener al menos 3 caracteres.",
  cedula: "Ingrese una cédula válida (solo números, mínimo 5 dígitos).",
  username: "El usuario debe tener al menos 3 caracteres.",
  pass: "La contraseña debe tener al menos 6 caracteres.",
  telefono: "Ingrese un teléfono válido (solo números, mínimo 7 dígitos).",
  email: "Ingrese un correo electrónico válido.",
  birthday: "Seleccione una fecha de nacimiento válida (no futura).",
  cargo: "Seleccione un cargo.",
  sexo: "Seleccione el sexo correctamente.",
  avatar: "La imagen debe ser JPG o PNG."
};

// Elimina errores anteriores del DOM
function limpiarErrores() {
  const errores = document.querySelectorAll(".error-text");
  errores.forEach(e => e.remove());
}

// Muestra error debajo del input
function mostrarError(input, mensaje) {
  const div = document.createElement("div");
  div.classList.add("error-text");
  div.innerText = mensaje;
  input.closest(".mb-3, .col-md-6").appendChild(div);
}

// Función principal que se ejecuta al enviar el formulario
async function registrarEmpleado(event) {
  try {
    event.preventDefault();
    limpiarErrores();

    const formulario = document.querySelector("#formularioEmpleado");
    const nombre = formulario.nombre;
    const cedula = formulario.cedula;
    const username = formulario.username;
    const pass = formulario.pass;
    const birthday = formulario.birthday;
    const telefono = formulario.telefono;
    const email = formulario.email;
    const cargo = formulario.cargo;
    const sexo = formulario.querySelector('input[name="sexo"]:checked');
    const avatar = formulario.avatar.files[0] || null;

    let errores = 0;

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

    if (!expresiones.avatar(avatar)) {
      mostrarError(formulario.avatar, mensajes.avatar);
      errores++;
    }

    if (errores > 0) return;

    const formData = new FormData(formulario);

    const response = await axios.post("acciones/acciones.php", formData);

    if (response.status === 200) {
      window.insertEmpleadoTable();

      setTimeout(() => {
        $("#agregarEmpleadoModal").css("opacity", "");
        $("#agregarEmpleadoModal").modal("hide");

        toastr.options = window.toastrOptions;
        toastr.success("¡El empleado se registró correctamente!");
      }, 600);
    } else {
      console.error("Error al registrar el empleado");
    }
  } catch (error) {
    console.error("Error al enviar el formulario", error);
  }
}
