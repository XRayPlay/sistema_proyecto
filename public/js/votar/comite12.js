const comite12 = document.getElementById('comite12');
const cb12_validacion = document.getElementById('cb12_validacion');
const comite12_12 = document.querySelector('#comite12_12');

comite12_12.addEventListener("change", () =>{
    console.log('funciona');
        
        })
comite12.addEventListener('submit', e =>{
    const inputs12 = document.querySelectorAll('#comite12 label input:checked');
    
    if(inputs12.length < 1 || inputs12.length > 1){
        e.preventDefault();
        console.log(inputs12);
        cb12_validacion.innerHTML = `Se requiere que seleccione a un vocero`;
    }

})