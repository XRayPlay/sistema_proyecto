const comite4 = document.getElementById('comite4');
const cb4_validacion = document.getElementById('cb4_validacion');
const comite4_4 = document.querySelector('#comite4_4');

comite4_4.addEventListener("change", () =>{
    console.log('funciona');
        
        })
comite4.addEventListener('submit', e =>{
    const inputs4 = document.querySelectorAll('#comite4 label input:checked');
    
    if(inputs4.length < 1 || inputs4.length > 1){
        e.preventDefault();
        console.log(inputs4);
        cb4_validacion.innerHTML = `Se requiere que seleccione a un vocero`;
    }

})