const comite2 = document.getElementById('comite2');
const cb2_validacion = document.getElementById('cb2_validacion');
const comite2_2 = document.querySelector('#comite2_2');

comite2_2.addEventListener("change", () =>{
    console.log('funciona');
        
        })
comite2.addEventListener('submit', e =>{
    const inputs2 = document.querySelectorAll('#comite2 label input:checked');    
    if(inputs2.length < 1 || inputs2.length > 1){
        e.preventDefault();
        console.log(inputs2);
        cb2_validacion.innerHTML = `Se requiere que seleccione a un vocero`;
    }

})