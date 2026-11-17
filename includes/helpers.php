<?php
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function nombreRol($rol)
{
    $nombres = [
        'admin' => 'Administrador',
        'operador' => 'Operador',
    ];

    return $nombres[$rol] ?? $rol;
}

function busquedaDesdeUrl()
{
    $q = $_GET['q'] ?? '';

    return is_string($q) ? trim($q) : '';
}

const OPCIONES_POR_PAGINA = [5, 10, 25, 50];

function paginaDesdeUrl()
{
    $nombreCookie = 'pp_' . pathinfo($_SERVER['SCRIPT_NAME'] ?? '', PATHINFO_FILENAME);

    $porPagina = filter_var($_GET['pp'] ?? ($_COOKIE[$nombreCookie] ?? null), FILTER_VALIDATE_INT);

    $porPagina = in_array($porPagina, OPCIONES_POR_PAGINA, true) ? $porPagina : 10;

    if (isset($_GET['pp']) && in_array(filter_var($_GET['pp'], FILTER_VALIDATE_INT), OPCIONES_POR_PAGINA, true)) {
        setcookie($nombreCookie, (string) $porPagina, [
            'expires' => time() + 60 * 60 * 24 * 365,
            'path' => '/',
            'secure' => esHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    $pagina = (int) ($_GET['pagina'] ?? 1);

    return [
        'porPagina' => $porPagina,
        'pagina' => max(1, $pagina),
    ];
}

function parametrosLista()
{
    $parametros = $_GET;
    unset($parametros['pagina'], $parametros['pp'], $parametros['ok'], $parametros['error']);

    return $parametros;
}

function paginar(array $filas)
{
    $paginacion = paginaDesdeUrl();
    $total = count($filas);
    $porPagina = $paginacion['porPagina'];

    $paginas = max(1, (int) ceil($total / $porPagina));

    $pagina = min($paginacion['pagina'], $paginas);

    return [

        'filas' => array_slice($filas, ($pagina - 1) * $porPagina, $porPagina),
        'total' => $total,
        'porPagina' => $porPagina,
        'pagina' => $pagina,
        'paginas' => $paginas,
    ];
}

function renderPaginacion(array $resultado)
{
    if ($resultado['total'] === 0) {
        return;
    }

    $parametros = parametrosLista();
    $pagina = $resultado['pagina'];
    $paginas = $resultado['paginas'];
    $porPagina = $resultado['porPagina'];

    $enlace = function ($numero) use ($parametros, $porPagina) {
        return '?' . http_build_query(array_merge($parametros, ['pp' => $porPagina, 'pagina' => $numero]));
    };

    echo '<div class="d-flex justify-content-end align-items-center flex-wrap mt-2">';

    echo '<form method="GET" class="form-inline mr-3">';
    foreach ($parametros as $clave => $valor) {
        if (is_string($valor)) {
            echo '<input type="hidden" name="' . e($clave) . '" value="' . e($valor) . '">';
        }
    }
    echo '<label class="small mr-2 mb-0">Mostrar</label>';
    echo '<select name="pp" class="form-control form-control-sm mr-2" onchange="this.form.submit()">';
    foreach (OPCIONES_POR_PAGINA as $opcion) {
        echo '<option value="' . $opcion . '"' . ($opcion === $porPagina ? ' selected' : '') . '>' . $opcion . '</option>';
    }
    echo '</select></form>';

    echo '<span class="small text-muted mr-2">Página ' . $pagina . ' de ' . $paginas . '</span>';

    echo '<a href="' . e($enlace($pagina - 1)) . '" class="btn btn-sm btn-outline-secondary mr-1' . ($pagina <= 1 ? ' disabled' : '') . '">Anterior</a>';
    echo '<a href="' . e($enlace($pagina + 1)) . '" class="btn btn-sm btn-outline-secondary' . ($pagina >= $paginas ? ' disabled' : '') . '">Siguiente</a>';
    echo '</div>';
}

function patronBusqueda($texto)
{
    return '%' . addcslashes($texto, '%_\\') . '%';
}

function renderBuscador($valor, $placeholder)
{
    echo '<form method="GET" class="form-inline mb-0">'
        . '<input type="text" name="q" class="form-control form-control-sm mr-2" placeholder="' . e($placeholder) . '" value="' . e($valor) . '">'
        . '<button type="submit" class="btn btn-sm btn-verde mr-2">Buscar</button>'
        . '<a href="?" class="btn btn-sm btn-outline-secondary">Mostrar todo</a>'
        . '</form>';
}

function renderAlerta()
{
    $exitos = [
        'usuario_guardado' => 'El usuario se guardó correctamente.',
        'usuario_eliminado' => 'El usuario se eliminó.',
        'categoria_guardada' => 'La categoría se guardó correctamente.',
        'categoria_eliminada' => 'La categoría se eliminó.',
        'equipo_guardado' => 'El equipo se guardó correctamente.',
        'equipo_eliminado' => 'El equipo se eliminó.',
        'foto_guardada' => 'La foto se guardó correctamente.',
        'foto_eliminada' => 'La foto se eliminó.',
        'demasiadas_fotos' => 'El equipo ya tiene el máximo de fotos permitido (5). Elimine alguna para agregar otra.',
        'prestamo_guardado' => 'El préstamo se registró correctamente.',
        'configuracion_guardada' => 'La configuración se guardó correctamente.',
        'estado_actualizado' => 'El estado del préstamo se actualizó.',
        'prestamo_cancelado' => 'El préstamo se canceló y se eliminó de la base de datos.',
    ];

    $errores = [
        'datos_invalidos' => 'Revise los datos. Hay campos vacíos o con formato incorrecto.',
        'correo_repetido' => 'Ese correo ya está registrado.',
        'codigo_repetido' => 'Ese código de equipo ya está en uso.',
        'nombre_repetido' => 'Ese nombre de categoría ya existe.',
        'no_encontrado' => 'El registro no se encontró.',
        'no_puede_eliminarse' => 'No puede eliminar su propio usuario.',
        'foto_invalida' => 'La foto debe ser JPG, PNG o WEBP y pesar 2 MB o menos.',
        'foto_no_subida' => 'No se recibió ninguna foto. Seleccione un archivo.',
        'fechas_invalidas' => 'Las fechas no son válidas. El inicio no puede ser anterior a hoy y el fin no puede ser anterior al inicio.',
        'anticipacion_excedida' => 'La fecha de inicio supera la anticipación máxima permitida.',
        'duracion_excedida' => 'La duración supera los días máximos de alguna de las categorías elegidas.',
        'sin_equipos' => 'Seleccione al menos un equipo.',
        'equipo_no_disponible' => 'Algún equipo elegido ya no está disponible.',
        'traslape' => 'Algún equipo ya está prestado en esas fechas.',
        'transicion_invalida' => 'Ese cambio de estado no está permitido para este préstamo.',
        'error_servidor' => 'Ocurrió un error. Intente de nuevo.',
    ];

    $ok = $_GET['ok'] ?? '';
    $error = $_GET['error'] ?? '';

    if (is_string($ok) && isset($exitos[$ok])) {
        echo '<div class="alert alert-success">' . e($exitos[$ok]) . '</div>';
    }

    if (is_string($error) && isset($errores[$error])) {
        echo '<div class="alert alert-danger">' . e($errores[$error]) . '</div>';
    }
}
