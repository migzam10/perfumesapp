<div id="view-dashboard" class="view">
    <div class="card-hdr">
        <div class="card-tittle">Dashboard y Estadísticas</div> 
        <button class="bsm bsm-g" onclick="nav('informe-financiero')">Reportes</button>
    </div>

    <div class="dashboard-grid" style="display: grid; gap: 20px; padding: 20px;">
        <!-- Gráfica de línea: Ventas del mes -->
        <div style="background: #000; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3>Ventas Diarias (Mes Actual)</h3>
            <canvas id="graficoVentasDia"></canvas>
        </div>

        <!-- Gráfica de barras: Productos más vendidos -->
        <div
            style="grid-column: 1 / -1; background: #000; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3>Top 10 Productos Más Vendidos</h3>
            <canvas id="graficoTopProductos"></canvas>
        </div>
    </div>

</div>


<script>
// Función para cargar y pintar el dashboard
async function cargarDashboard() {
    try {
        // Hacemos la petición a la nueva ruta que creamos
        const res = await fetch('api/index.php?action=reportes_dashboard');
        const data = await res.json();

        if (data.error) {
            console.error(data.error);
            return;
        }

        // 1. GRAFICAR VENTAS POR DÍA (Línea)
        const labelsDia = data.ventas_dias.map(v => v.fecha);
        const dataDia = data.ventas_dias.map(v => v.total);

        new Chart(document.getElementById('graficoVentasDia'), {
            type: 'line',
            data: {
                labels: labelsDia,
                datasets: [{
                    label: 'Total Vendido ($)',
                    data: dataDia,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.2)',
                    fill: true,
                    tension: 0.3 // Curva suave
                }]
            }
        });

        // 2. GRAFICAR TOP PRODUCTOS (Barras)
        const labelsTop = data.top_productos.map(p => p.descripcion);
        const dataTop = data.top_productos.map(p => p.total_vendidos);

        new Chart(document.getElementById('graficoTopProductos'), {
            type: 'bar',
            data: {
                labels: labelsTop,
                datasets: [{
                    label: 'Unidades Vendidas',
                    data: dataTop,
                    backgroundColor: '#2196F3',
                    borderRadius: 5
                }]
            }
        });

        // 3. GRAFICAR VENTAS POR TIPO (Dona)
        const labelsTipo = data.ventas_tipos.map(t => t.tipo);
        const dataTipo = data.ventas_tipos.map(t => t.total_vendidos);

        new Chart(document.getElementById('graficoVentasTipo'), {
            type: 'doughnut',
            data: {
                labels: labelsTipo,
                datasets: [{
                    data: dataTipo,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            }
        });

    } catch (e) {
        console.error("Error cargando dashboard:", e);
    }
}

// Llamar a la función cuando esta vista se renderice
cargarDashboard();
</script>