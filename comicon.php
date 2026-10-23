<?php
// Mostrar todos los errores
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Variables de estado
$procesado = false;
$exito = false;
$mensajeError = '';
$resultado = [];

$talleres = [
    'torneo_videojuegos' => ['nombre' => 'Torneo de Videojuegos', 'precio' => 10],
    'taller_cosplay' => ['nombre' => 'Taller de Cosplay', 'precio' => 15],
    'rol_en_vivo' => ['nombre' => 'Zona de Rol en Vivo', 'precio' => 5],
];

// Precio base según tipo de entrada
function calcularPrecioBase(string $tipo): int
{
    switch ($tipo) {
        case 'General':
            return 30;
        case 'VIP':
            return 60;
        case 'Infantil':
            return 10;
        default:
            throw new Exception('Tipo de entrada no válido');
    }
}

// Procesa la inscripción y devuelve los datos calculados
function procesarInscripcion(string $nombre, int $edad, string $tipo, array $seleccionados, array $talleres): array
{
    $precioBase = calcularPrecioBase($tipo);
    $total = $precioBase;
    $talleresElegidos = [];

    foreach ($seleccionados as $clave) {
        if (!isset($talleres[$clave])) {
            throw new Exception('Taller no válido');
        }
        $talleresElegidos[] = [
            'nombre' => $talleres[$clave]['nombre'],
            'precio' => $talleres[$clave]['precio'],
        ];
        $total += $talleres[$clave]['precio'];
    }

    return [
        'nombre' => $nombre,
        'tipo' => $tipo,
        'precioBase' => $precioBase,
        'talleres' => $talleresElegidos,
        'total' => $total,
    ];
}

// Ciclo de vida: procesar POST, registrar resultado
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $procesado = true;

        //Recuperar datos del formulario
        $nombre = $_POST['nombre'] ?? '';
        $edad = $_POST['edad'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $seleccionados = $_POST['talleres'] ?? [];

        // Sanear nombre 
        $nombre = htmlspecialchars(trim($nombre), ENT_QUOTES, 'UTF-8');

        // Validar nombre
        if ($nombre === '') {
            throw new Exception('El nombre es obligatorio');
        }

        // Validar edad
        $edadInt = filter_var($edad, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($edadInt === false) {
            throw new Exception('La edad debe ser un número entero positivo');
        }
        $edad = $edadInt;

        // Validar tipos
        if ($edad < 18 && $tipo === 'VIP') {
            throw new Exception('La entrada VIP está restringida a mayores de edad');
        } else if ($edad >= 16 && $tipo === 'Infantil') {
            throw new Exception('La entrada Infantil está restringida a menores de 16 años');
        }
        // Calcular y guardar resultado
        $resultado = procesarInscripcion($nombre, $edad, $tipo, $seleccionados, $talleres);

        $exito = true;
    }
} catch (Exception $e) {
    $mensajeError = $e->getMessage();
    $exito = false;
} finally {
    if ($procesado) {
        $logEstado = $exito ? 'EXITOSO' : 'FALLIDO';
        $fecha = date('Y-m-d H:i:s');
        error_log("[{$fecha}] Procesamiento de inscripción Comic-Con: {$logEstado}");
    }
}


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
                        (<?= $info['precio'] ?> €)
                    </label>
                </div>
            <?php endforeach; ?>
        </fieldset>
        <div>
            <button type="submit">Inscribirse</button>
        </div>

    </form>

    <?php
    if ($exito): ?>
        <section>
            <h2>Resguardo de inscripción</h2>

            <p><strong>Asistente:</strong>
                <?= $resultado['nombre'] ?>
            </p>
            <p>
                <strong>Entrada:</strong>
                <?= htmlspecialchars($resultado['tipo'], ENT_QUOTES, 'UTF-8') ?>
                (
                <?= $resultado['precioBase'] ?> €)
            </p>

            <h3>Talleres</h3>
            <?php if (count($resultado['talleres']) > 0): ?>
                <ul>
                    <?php foreach ($resultado['talleres'] as $taller): ?>
                        <li>
                            <?= htmlspecialchars($taller['nombre'], ENT_QUOTES, 'UTF-8') ?>:
                            <?= $taller['precio'] ?> €
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Sin talleres seleccionados.</p>
            <?php endif; ?>

            <p><strong>Total a pagar:
                    <?= $resultado['total'] ?> €
                </strong></p>
        </section>
    <?php endif; ?>

</body>

</html>