let chart;

function crearGrafico(data) {
    const labels = data.map(item => item.estado);
    const valores = data.map(item => item.total);

    const config = {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad de Reportes',
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                data: valores
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    };

    if (chart) chart.destroy();

    const ctx = document.getElementById('reporteChart').getContext('2d');
    chart = new Chart(ctx, config);
}

function cargarDatos() {
    const tipo = document.getElementById("tipoReporte").value;
    const area = document.getElementById("areaCargo").value;

    fetch(`acciones/getReportePorTipo.php?tipo=${tipo}&area=${area}`)
        .then(res => res.json())
        .then(data => crearGrafico(data));
}

document.addEventListener("DOMContentLoaded", () => {
    cargarDatos();
    document.getElementById("tipoReporte").addEventListener("change", cargarDatos);
    document.getElementById("areaCargo").addEventListener("change", cargarDatos);
});
