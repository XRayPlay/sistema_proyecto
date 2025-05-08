const comite1 = document.getElementById('comite1');
const cb1_validacion = document.getElementById('cb1_validacion');
const comite1_1 = document.querySelector('#comite1_1');  

comite1_1.addEventListener("change", () =>{
console.log('funciona');
    
    })

comite1.addEventListener('submit', e =>{

const inputs1 = document.querySelectorAll('#comite1 label input:checked');
    
    if(inputs1.length < 1 || inputs1.length > 1){
        e.preventDefault();
        console.log(inputs1);
        cb1_validacion.innerHTML = `Seleccione solo a un vocero`
    }
})