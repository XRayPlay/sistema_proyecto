const comite3 = document.getElementById('comite3');
const cb3_validacion = document.getElementById('cb3_validacion');
const comite3_3 = document.querySelector('#comite3_3');

comite3_3.addEventListener("change", () =>{
    console.log('funciona');
        
        })
comite3.addEventListener('submit', e =>{
    const inputs3 = document.querySelectorAll('#comite3 label input:checked');
    
    if(inputs3.length < 1 || inputs3.length > 1){
        e.preventDefault();
        console.log(inputs3);
        cb3_validacion.innerHTML = `Se requiere que seleccione a un vocero`;
    }

})