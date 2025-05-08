const formulario = document.getElementById('formulario')
const inputs = document.querySelectorAll('#formulario input');
const selects = document.querySelectorAll('#formulario select');




const campos = {

    direccion: false
}

const validarselect = (select, campo) => {
    if(select == null) {
        document.getElementById(`grupo__${campo}`).classList.remove('formulario__grupo-correcto');
        document.getElementById(`grupo__${campo}`).classList.add('formulario__grupo-incorrecto');
        document.querySelector(`#grupo__${campo} i`).classList.remove('fa-check-circle');
        document.querySelector(`#grupo__${campo} i`).classList.add('fa-times-circle');
        document.querySelector(`#grupo__${campo} .formulario__input-error`).classList.add('formulario__input-error-activo');
        campos[campo] = false;
    }else{
        document.getElementById(`grupo__${campo}`).classList.remove('formulario__grupo-incorrecto');
        document.getElementById(`grupo__${campo}`).classList.add('formulario__grupo-correcto');
        document.querySelector(`#grupo__${campo} i`).classList.remove('fa-times-circle');
        document.querySelector(`#grupo__${campo} i`).classList.add('fa-check-circle');
        document.querySelector(`#grupo__${campo} .formulario__input-error`).classList.remove('formulario__input-error-activo');
        campos[campo] = true;
    }
}

const validarFormularioS = (b) => {
    console.log(b.target.name);
    switch (b.target.name) {
        case "comuna":
            validarselect(b.target.value, 'comuna');
        break;
    }
}

selects.forEach((select) => {
    select.addEventListener('change', validarFormularioS);
    select.addEventListener('blur', validarFormularioS);
})

formulario.addEventListener('submit', (e) => {

    if(campos.comuna){

    }else{
        e.preventDefault();

    }
})