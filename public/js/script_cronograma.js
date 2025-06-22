// Equipos Reparados
        fetch('../php/data_cronograma.php?type=equipos')
            .then(response => response.json())
            .then(data => {
                const fechas = data.map(item => item.fecha_reparacion);
                const cantidades = data.map(item => item.cantidad);

                new Chart(document.getElementById('equiposChart'), {
                    type: 'line',
                    data: {
                        labels: fechas,
                        datasets: [{
                            label: 'Equipos Reparados',
                            data: cantidades,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2
                        }]
                    }
                });
            });

        // Rendimiento Técnicos
        fetch('../php/data_cronograma.php?type=rendimiento')
            .then(response => response.json())
            .then(data => {
                const tecnicos = data.map(item => item.tecnico);
                const reparaciones = data.map(item => item.reparaciones);

                new Chart(document.getElementById('rendimientoChart'), {
                    type: 'bar',
                    data: {
                        labels: tecnicos,
                        datasets: [{
                            label: 'Reparaciones',
                            data: reparaciones,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 2
                        }]
                    }
                });
            });

        // Listado Técnicos
        fetch('../php/data_cronograma.php?type=tecnicos')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('tecnicosTable');
                data.forEach(tecnico => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${tecnico.nombre}</td><td>${tecnico.estado}</td>`;
                    tbody.appendChild(tr);
                });
            });