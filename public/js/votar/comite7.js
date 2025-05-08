const comite7 = document.getElementById('comite7');
const cb7_validacion = document.getElementById('cb7_validacion');
const comite7_7 = document.querySelector('#comite7_7');

comite7_7.addEventListener("change", () =>{
    console.log('funciona');
        
        })
comite7.addEventListener('submit', e =>{
    const inputs7 = document.querySelectorAll('#comite7 label input:checked');
    
    if(inputs7.length < 1 || inputs7.length > 1){
        e.preventDefault();
        console.log(inputs7);
        cb7_validacion.innerHTML = `Se requiere que seleccione a un vocero`;
    }

})