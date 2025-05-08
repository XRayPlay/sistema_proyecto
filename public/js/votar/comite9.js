const comite9 = document.getElementById('comite9');
const cb9_validacion = document.getElementById('cb9_validacion');
const comite9_9 = document.querySelector('#comite9_9');

comite9_9.addEventListener("change", () =>{
    console.log('funciona');
        
        })
comite9.addEventListener('submit', e =>{
    const inputs9 = document.querySelectorAll('#comite9 label input:checked');
    
    if(inputs9.length < 1 || inputs9.length > 1){
        e.preventDefault();
        console.log(inputs9);
        cb9_validacion.innerHTML = `Se requiere que seleccione a un vocero`;
    }

})