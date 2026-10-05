<?php
include 'conexion.php';

// Lista de candidatos (se usa para mostrarlos y para validar el voto)
$candidatos = [
    1 => 'Dena María Jesus Camarena Gómez',
    2 => 'Aarón Grajeda Bustamante',
    3 => 'María Elena Robles Baldenegro',
    4 => 'Cuauhtémoc González Valdez',
    5 => 'Judith Tanori Córdova',
    6 => 'Ezequiel Rodríguez Jáuregui',
];

// Mensaje que se mostrará en el modal
$mensaje_modal = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['votar'])) {
    $expediente   = trim($_POST['expediente'] ?? '');
    $nombre       = trim($_POST['nombre'] ?? '');
    $id_candidato = (int) ($_POST['id_candidato'] ?? 0);

    if (!isset($candidatos[$id_candidato])) {
        $mensaje_modal = "Selecciona un candidato";
    } elseif (!preg_match('/^[0-9]{9}$/', $expediente)) {
        $mensaje_modal = "El expediente debe ser de 9 números.";
    } else {
        // El nombre del candidato se toma de nuestra lista, no del formulario
        $nombre_candidato = $candidatos[$id_candidato];

        // Verifica si el expediente ya ha votado
        $stmt = $conn->prepare("SELECT expediente FROM mesa2 WHERE expediente = ?");
        $stmt->bind_param("s", $expediente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $ya_voto = $resultado->num_rows > 0;
        $stmt->close();

        if ($ya_voto) {
            $mensaje_modal = "El expediente ya ha votado.";
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO mesa2 (expediente, nombre, id_candidato, nombre_candidato) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssis", $expediente, $nombre, $id_candidato, $nombre_candidato);
                $stmt->execute();
                $stmt->close();
                $mensaje_modal = "Se registró el voto por: " . $nombre_candidato;
            } catch (mysqli_sql_exception $e) {
                // Código 1062 = expediente duplicado (por si votan al mismo tiempo)
                if ($e->getCode() == 1062) {
                    $mensaje_modal = "El expediente ya ha votado.";
                } else {
                    $mensaje_modal = "Error al registrar el voto.";
                }
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votación de Candidatos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="stilos.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Votación de Candidatos</h1>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="candidates">
                <?php foreach ($candidatos as $id => $nombre_c): ?>
                    <div class="candidate" onclick="selectCandidate(<?php echo $id; ?>, this)">
                        <img src="img/candidato<?php echo $id; ?>.png" alt="Candidato <?php echo $id; ?>">
                        <h3><?php echo htmlspecialchars($nombre_c); ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
            <br>

            <div>
                <input type="text" name="nombre" class="form-control" placeholder="Introduzca su nombre:" required><br><br>
                <input type="text" name="expediente" class="form-control" placeholder="Introduzca su expediente:" maxlength="9" inputmode="numeric" pattern="[0-9]{9}" required><br><br>
                <input type="hidden" id="id_candidato" name="id_candidato">
            </div>

            <button type="submit" class="btn btn-success vote-button" name="votar">Votar</button>
            <br>
        </form>

        <a href="resultados.php" target="_blank" class="btn btn-primary vote-results">Resultados</a>
        <br>
    </div>

    <!-- Modal de Bootstrap -->
    <div class="modal fade" id="modalError" tabindex="-1" aria-labelledby="modalErrorLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalErrorLabel">Mensaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo htmlspecialchars($mensaje_modal); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        function selectCandidate(id, elemento) {
            // Marca el candidato seleccionado
            document.querySelectorAll('.candidate').forEach(c => c.classList.remove('selected'));
            elemento.classList.add('selected');

            // Guarda el id en el formulario
            document.getElementById('id_candidato').value = id;
        }

        // Mostrar el modal si hay un mensaje
        <?php if (!empty($mensaje_modal)): ?>
            var modalError = new bootstrap.Modal(document.getElementById('modalError'));
            modalError.show();
        <?php endif; ?>
    </script>
</body>
</html>