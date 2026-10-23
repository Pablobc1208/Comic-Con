<?php
// Comic-Con — Inscripción
// Bloque 1: configuración, variables de estado y ciclo de vida del formulario

// Mostrar todos los errores
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Variables de estado
$procesado = false;
$exito = false;
$mensajeError = '';

// Ciclo de vida: procesar POST, registrar resultado
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $procesado = true;
        $exito = true;
    }
} catch (Exception $e) {
    $mensajeError = $e->getMessage();
    $exito = false;
} finally {
    if ($procesado) {
        $resultado = $exito ? 'EXITOSO' : 'FALLIDO';
        $fecha = date('Y-m-d H:i:s');
        error_log("[{$fecha}] Procesamiento de inscripción Comic-Con: {$resultado}");
    }
}

// Bloque 2 — Array de talleres con su precio
$talleres = [
    'torneo_videojuegos' => ['nombre' => 'Torneo de Videojuegos', 'precio' => 10],
    'taller_cosplay' => ['nombre' => 'Taller de Cosplay', 'precio' => 15],
    'rol_en_vivo' => ['nombre' => 'Zona de Rol en Vivo', 'precio' => 5],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comic-Con — Inscripción</title>
</head>

<body>

    <h1>Inscripción Comic-Con</h1>

    <?php // Mensaje de error si lo hay
    if ($mensajeError !== ''): ?>
        <p style="color: red;">
            <?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="post">

        <div>
            <label for="nombre">Nombre completo:</label>
            <input type="text" id="nombre" name="nombre" required
                value="<?= htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div>
            <label for="edad">Edad:</label>
            <input type="number" id="edad" name="edad" min="1" step="1" required
                value="<?= htmlspecialchars($_POST['edad'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div>
            <label for="tipo">Tipo de Entrada:</label>
            <select id="tipo" name="tipo">
                <?php
                $tipoSeleccionado = $_POST['tipo'] ?? '';
                $opciones = ['General', 'VIP', 'Infantil'];
                foreach ($opciones as $opcion):
                    ?>
                    <option value="<?= htmlspecialchars($opcion, ENT_QUOTES, 'UTF-8') ?>" <?= $tipoSeleccionado === $opcion ? 'selected' : '' ?>>
                        <?= htmlspecialchars($opcion, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <fieldset>
            <legend>Talleres Especiales</legend>

            <?php
            $talleresSeleccionados = $_POST['talleres'] ?? [];
            foreach ($talleres as $clave => $info):
                ?>
                <div>
                    <input type="checkbox" id="taller_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>"
                        name="talleres[]" value="<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($clave, $talleresSeleccionados, true) ? 'checked' : '' ?>>
                    <label for="taller_<?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($info['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        (<?= htmlspecialchars($info['precio'], ENT_QUOTES, 'UTF-8') ?> €)
                    </label>
                </div>
            <?php endforeach; ?>
        </fieldset>
        <div>
            <button type="submit">Inscribirse</button>
        </div>

    </form>

</body>

</html>