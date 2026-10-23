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

function mostrarTexto(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

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
        $nombre = mostrarTexto(trim($nombre));

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
        } else if ($edad >= 105) {
            throw new Exception('La edad no puede ser mayor de 105 años');
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
<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 500px;
        margin: 30px auto;
        padding: 0 15px;
        background: #f4f4f4;
        color: #222;
    }

    h1 {
        text-align: center;
    }

    form,
    section {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    form div {
        margin-bottom: 12px;
    }

    label {
        display: block;
        margin-bottom: 4px;
    }

    fieldset label {
        display: inline;
    }

    input[type="text"],
    input[type="number"],
    select {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
    }

    button {
        width: 100%;
        padding: 10px;
        background: #6a1b9a;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background: #4a148c;
    }
</style>

<body>

    <h1>Inscripción Comic-Con</h1>

    <?php // Mensaje de error si lo hay
    if ($mensajeError !== ''): ?>
        <p style="color: red;">
            <?= mostrarTexto($mensajeError) ?>
        </p>
    <?php endif; ?>

    <form method="post">

        <div>
            <label for="nombre">Nombre completo:</label>
            <input type="text" id="nombre" name="nombre" required value="<?= mostrarTexto($_POST['nombre'] ?? '') ?>">
        </div>

        <div>
            <label for="edad">Edad:</label>
            <input type="number" id="edad" name="edad" min="1" step="1" required
                value="<?= mostrarTexto($_POST['edad'] ?? '') ?>">
        </div>

        <div>
            <label for="tipo">Tipo de Entrada:</label>
            <select id="tipo" name="tipo">
                <?php
                $tipoSeleccionado = $_POST['tipo'] ?? '';
                $opciones = ['General', 'VIP', 'Infantil'];
                foreach ($opciones as $opcion):
                    ?>
                    <option value="<?= mostrarTexto($opcion) ?>" <?= $tipoSeleccionado === $opcion ? 'selected' : '' ?>>
                        <?= mostrarTexto($opcion) ?>
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
                    <input type="checkbox" id="taller_<?= mostrarTexto($clave) ?>" name="talleres[]"
                        value="<?= mostrarTexto($clave) ?>" <?= in_array($clave, $talleresSeleccionados, true) ? 'checked' : '' ?>>
                    <label for="taller_<?= mostrarTexto($clave) ?>">
                        <?= mostrarTexto($info['nombre']) ?>
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
                <?= mostrarTexto($resultado['tipo']) ?>
                (
                <?= $resultado['precioBase'] ?> €)
            </p>

            <h3>Talleres</h3>
            <?php if (count($resultado['talleres']) > 0): ?>
                <ul>
                    <?php foreach ($resultado['talleres'] as $taller): ?>
                        <li>
                            <?= mostrarTexto($taller['nombre']) ?>:
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