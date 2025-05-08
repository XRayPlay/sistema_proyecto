const comite6 = document.getElementById('comite6');
const cb6_validacion = document.getElementById('cb6_validacion');
const comite6_6 = document.querySelector('#comite6_6');

comite6_6.addEventListener("change", () =>{
    console.log('funciona');
        
        })
comite6.addEventListener('submit', e =>{
    const inputs6 = document.querySelectorAll('#comite6 label input:checked');
    
    if(inputs6.length < 1 || inputs6.length > 1){
        e.preventDefault();
        console.log(inputs6);
        cb6_validacion.innerHTML = `Se requiere que seleccione a un vocero`;
    }

})