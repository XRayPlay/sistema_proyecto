const comite10 = document.getElementById('comite10');
const cb10_validacion = document.getElementById('cb10_validacion');
const comite10_10 = document.querySelector('#comite10_10');

comite10_10.addEventListener("change", () =>{
    console.log('funciona');
        
        })
comite10.addEventListener('submit', e =>{
    const inputs10 = document.querySelectorAll('#comite10 label input:checked');
    
    if(inputs10.length < 1 || inputs10.length > 1){
        e.preventDefault();
        console.log(inputs10);
        cb10_validacion.innerHTML = `Se requiere que seleccione a un vocero`;
    }

})