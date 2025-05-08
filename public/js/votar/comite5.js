const comite5 = document.getElementById('comite5');
const cb5_validacion = document.getElementById('cb5_validacion');
const comite5_5 = document.querySelector('#comite5_5');

comite5_5.addEventListener("change", () =>{
    console.log('funciona');
        
        })
comite5.addEventListener('submit', e =>{
    const inputs5 = document.querySelectorAll('#comite5 label input:checked');
    
    if(inputs5.length < 1 || inputs5.length > 1){
        e.preventDefault();
        console.log(inputs5);
        cb5_validacion.innerHTML = `Se requiere que seleccione a un vocero`;
    }

})