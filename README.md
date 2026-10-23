# Comic-Con — Formulario de inscripción

Actividad de PHP: formulario de inscripción a un Comic-Con que valida los datos, calcula el precio de la entrada y de los talleres, y muestra un resguardo con el total a pagar. Todo está en un único archivo, `comicon.php`.

## Funcionamiento

El formulario se envía por **POST** al mismo archivo. Si los datos son válidos, debajo del formulario aparece el resguardo con el nombre del asistente, el tipo de entrada, los talleres elegidos y el total.

### Campos del formulario

| Campo               | Tipo                | Detalle                                               |
| ------------------- | ------------------- | ----------------------------------------------------- |
| Nombre completo     | Texto               | Obligatorio                                           |
| Edad                | Número              | Entero positivo                                       |
| Tipo de entrada     | Desplegable         | General, VIP o Infantil                               |
| Talleres especiales | Casillas (checkbox) | Independientes, se puede elegir ninguno, uno o varios |

### Precios

| Entrada  | Precio |
| -------- | ------ |
| General  | 30 €   |
| VIP      | 60 €   |
| Infantil | 10 €   |

| Taller                | Precio |
| --------------------- | ------ |
| Torneo de Videojuegos | 10 €   |
| Taller de Cosplay     | 15 €   |
| Zona de Rol en Vivo   | 5 €    |

**Total = precio de la entrada + suma de los talleres seleccionados.**

### Validaciones

| Caso                               | Mensaje                                                   |
| ---------------------------------- | --------------------------------------------------------- |
| Nombre vacío                       | El nombre es obligatorio                                  |
| Edad no entera o menor que 1       | La edad debe ser un número entero positivo                |
| Menor de 18 con entrada VIP        | La entrada VIP está restringida a mayores de edad         |
| 16 años o más con entrada Infantil | La entrada Infantil está restringida a menores de 16 años |
| Tipo de entrada manipulado         | Tipo de entrada no válido                                 |
| Taller manipulado                  | Taller no válido                                          |

## Estructura del código

### Bloque 1: Configuración y control de errores

- Muestra todos los errores de PHP en pantalla (`display_errors` y `error_reporting(E_ALL)`).
- Todo el procesamiento va dentro de un `try / catch / finally`.
- Los errores de validación se notifican lanzando una `Exception`; el `catch` guarda el mensaje para mostrárselo al usuario.
- El `finally` registra con `error_log` la fecha y si el resultado fue `EXITOSO` o `FALLIDO`.

Ejemplo de línea en el log del servidor:

```
[2026-10-23 10:18:10] Procesamiento de inscripción Comic-Con: EXITOSO
```

### Bloque 2: Formulario HTML

- Método POST al mismo archivo.
- Las opciones del desplegable y los talleres se generan con `foreach` a partir de arrays.
- El formulario conserva los datos enviados (`value`, `selected`, `checked`) cuando hay un error.

### Bloque 3: Procesamiento en PHP

- Detecta el envío con `$_SERVER['REQUEST_METHOD'] === 'POST'`.
- Recupera los datos con el operador de fusión de nulos (`??`), para evitar avisos si no se marca ningún taller.
- Sanea el nombre con `trim()` y `htmlspecialchars()` (protección XSS).
- Valida la edad con `filter_var(..., FILTER_VALIDATE_INT)`.
- Funciones propias:
  - `calcularPrecioBase(string $tipo): int`: usa un `switch` para asignar el precio de la entrada.
  - `procesarInscripcion(...)`: recorre los talleres con `foreach`, suma sus costes y devuelve un array con los datos del resguardo.
  - `mostrarTexto(string $texto): string`: escapa el texto con `htmlspecialchars` para imprimirlo en HTML.

### Bloque 4: Resguardo

Se muestra solo si el formulario se ha procesado con éxito, usando la sintaxis alternativa de plantillas de PHP (`if: ... endif;`, `foreach: ... endforeach;`). Incluye el nombre del asistente, el tipo de entrada con su precio base, la lista de talleres con el coste de cada uno y el total a pagar.

## Tecnologías

- PHP
- HTML5
- CSS
