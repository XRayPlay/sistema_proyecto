const comite11 = document.getElementById('comite11');
const cb11_validacion = document.getElementById('cb11_validacion');
const comite11_11 = document.querySelector('#comite11_11');

comite11_11.addEventListener("change", () =>{
    console.log('funciona');
        
        })
comite11.addEventListener('submit', e =>{
    const inputs11 = document.querySelectorAll('#comite11 label input:checked');
    
    if(inputs11.length < 1 || inputs11.length > 1){
        e.preventDefault();
        console.log(inputs11);
        cb11_validacion.innerHTML = `Se requiere que seleccione a un vocero`;
    }

})