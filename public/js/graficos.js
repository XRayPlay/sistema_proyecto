let chartStatus, chartFloor;

async function loadData(filters = {}) {
    const params = new URLSearchParams(filters);
    // Evitar caché en el fetch para obtener datos actualizados
    params.set('_', Date.now());
    const response = await fetch(`../php/get_dashboard_data.php?${params}`, { cache: 'no-store' });
    const data = await response.json();

    // Actualizar gráfico de departamento
    const labelsDepartamento = data.departamento.map(item => item.departamento);
    const dataDepartamento = data.departamento.map(item => item.cantidad);

    if (chartStatus) chartStatus.destroy();
    chartStatus = new Chart(document.getElementById('chart-status'), {
        type: 'doughnut',
        data: {
            labels: labelsDepartamento,
            datasets: [{
                data: dataDepartamento,
                backgroundColor: ['#ff6384', '#36a2eb', '#4bc0c0', '#ffce56'],
            }],
        },
    });

    // Actualizar gráfico de fechas (creadas y resueltas)
    const fechasData = data.fechas.map(item => ({
        fecha_creacion: item.fecha_creacion,
        fecha_resolucion: item.fecha_resolucion
    })).sort((a, b) => new Date(a.fecha_creacion) - new Date(b.fecha_creacion));

    const labelsFechas = fechasData.map(item => item.fecha_creacion);
    const dataCreacion = fechasData.map(item => new Date(item.fecha_creacion).getTime());
    const dataResolucion = fechasData.filter(item => item.fecha_resolucion).map(item => new Date(item.fecha_resolucion).getTime());

    if (chartFloor) chartFloor.destroy();
    chartFloor = new Chart(document.getElementById('chart-floor'), {
        type: 'line',
        data: {
            labels: labelsFechas,
            datasets: [{
                label: 'Fecha Creación',
                data: dataCreacion,
                borderColor: '#36a2eb',
                fill: false,
            }, {
                label: 'Fecha Resolución',
                data: dataResolucion,
                borderColor: '#ff6384',
                fill: false,
            }],
        },
        options: {
            scales: {
                x: {
                    type: 'category',
                    title: {
                        display: true,
                        text: 'Fecha de Creación'
                    }
                },
                y: {
                    display: false,
                },
            },
        },
    });
}

// Cargar datos iniciales
loadData();

// Actualizar en tiempo real cada 30 segundos
setInterval(() => {
    const currentFilters = {
        busqueda: document.getElementById('busqueda').value,
        piso: document.getElementById('piso').value,
        estado: document.getElementById('estado').value,
        tecnico: document.getElementById('tecnico').value,
        departamento: document.getElementById('departamento').value,
        fecha_inicio: document.getElementById('fecha-inicio').value,
        fecha_fin: document.getElementById('fecha-fin').value,
    };
    loadData(currentFilters);
}, 30000); // 30 segundos

// Event listeners para filtros
document.querySelectorAll('.filter-input').forEach(input => {
    input.addEventListener('change', () => {
        const busqueda = document.getElementById('busqueda').value;
        if (busqueda && (busqueda.length < 3 || busqueda.length > 40 || !/^[a-zA-Z\s]+$/.test(busqueda))) {
            alert('La búsqueda debe tener entre 3 y 40 caracteres, solo letras y espacios.');
            return;
        }
        const filters = {
            busqueda: busqueda,
            piso: document.getElementById('piso').value,
            estado: document.getElementById('estado').value,
            tecnico: document.getElementById('tecnico').value,
            departamento: document.getElementById('departamento').value,
            fecha_inicio: document.getElementById('fecha-inicio').value,
            fecha_fin: document.getElementById('fecha-fin').value,
        };
        loadData(filters);
    });
});