// Fecha de Nacimiento a edad

const fechanacimiento=document.getElementById("fecha__fecha");
const edad=document.getElementById("fecha_fecha");

const calcularedad = (fechanacimiento) => {
    const fechaactual= new Date();
    const annoactual=parseInt(fechaactual.getFullYear());
    const mesactual=parseInt(fechaactual.getMonth()) + 1;
    const diaactual=parseInt(fechaactual.getDay());

    // 1943-01-01   2008-12-31
    const annonacimiento = parseInt(String(fechanacimiento).substring(0, 4));
    const mesnacimiento = parseInt(String(fechanacimiento).substring(5, 7));
    const dianacimiento = parseInt(String(fechanacimiento).substring(8, 10));

    let edad= annoactual-annonacimiento;
    if(mesactual<mesnacimiento){
        edad--;
    } else if (mesactual==mesnacimiento){
        if(diaactual<dianacimiento){
            edad--;

        }
    }
    return edad;

};