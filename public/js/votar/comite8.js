const comite8 = document.getElementById('comite8');
const cb8_validacion = document.getElementById('cb8_validacion');
const comite8_8 = document.querySelector('#comite8_8');

comite8_8.addEventListener("change", () =>{
    console.log('funciona');
        
        })
comite8.addEventListener('submit', e =>{
    const inputs8 = document.querySelectorAll('#comite8 label input:checked');
    
    if(inputs8.length < 1 || inputs8.length > 1){
        e.preventDefault();
        console.log(inputs8);
        cb8_validacion.innerHTML = `Se requiere que seleccione a un vocero`;
    }

})