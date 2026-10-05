<?php
// Conexión a la base de datos
include('conexion.php');

// Obtener el conteo total de votos
$sql_total_votos = "SELECT COUNT(*) as total FROM mesa2";
$result_total = $conn->query($sql_total_votos);
$row_total = $result_total->fetch_assoc();
$total_votos = $row_total['total'];

// Obtener el conteo de votos por candidato
$sql_votos = "SELECT nombre_candidato, COUNT(*) as votos FROM mesa2 GROUP BY nombre_candidato ORDER BY votos DESC";
$result_votos = $conn->query($sql_votos);
$votos = [];
$labels = []; // Para los nombres de los candidatos
$data_votos = []; // Para los votos
$data_porcentaje = []; // Para los porcentajes

while ($row = $result_votos->fetch_assoc()) {
    $nombre = $row['nombre_candidato'];
    $cantidad = $row['votos'];
    $porcentaje = ($total_votos > 0) ? round(($cantidad / $total_votos) * 100, 2) : 0;
    $votos[$nombre] = ['cantidad' => $cantidad, 'porcentaje' => $porcentaje];
    
    // Llenar los arrays para la gráfica
    $labels[] = $nombre;        // Nombres de los candidatos
    $data_votos[] = $cantidad;  // Votos de cada candidato
    $data_porcentaje[] = $porcentaje;  // Porcentaje de votos de cada candidato
}
?>
<html>
    <meta http-equiv="refresh" content="30">
     <meta charset="UTF-8">
     <title>Resultados de Votaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <head><style>
         .grafica-container {
            border: black 7px;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
                background-color: #f5f5f5;
            position: relative; 
        }
        
        .grafica-wrapper {
            position: relative;
            width: 100%;
            min-height: 300px; 
            height: 400px; 
        }
        </style>
    </head>
 <div class="grafica-container">
    <div class="grafica-wrapper">
        <canvas id="votosChart"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script>
   document.addEventListener("DOMContentLoaded", function() {
  
    const ctx = document.getElementById('votosChart').getContext('2d');
    
    if (!ctx) {
        console.error('No se encontró el elemento canvas');
        return;
    }
    
    const data = {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
           
            label: 'Votos',
            data: <?php echo json_encode($data_votos); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.5)',
                'rgba(54, 162, 235, 0.5)',
                'rgba(255, 206, 86, 0.5)',
                'rgba(75, 192, 192, 0.5)',
                'rgba(153, 102, 255, 0.5)',
                'rgba(255, 159, 64, 0.5)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 2
        }]
    };
    
    const config = {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false, //importante
            plugins: {
                legend: {
                    display: true
                },
                datalabels: {
                    color: '#fff',
                   
                    borderRadius: 5,
                    padding: 6,
                    font: {
                        weight: 'bold',
                        size: 20
                    },
                    formatter: function(value) {
                        return value;
                    }
                }
            }
            
        },
        plugins: [ChartDataLabels]
    };
    
    //crear la gráfica
    const votosChart = new Chart(ctx, config);
    
    setTimeout(() => {
        votosChart.resize();
    }, 100);
});
</script>