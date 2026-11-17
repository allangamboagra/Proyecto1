<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/categorias.php';

requireRole(['admin']);

validarCsrf();

$id = $_POST['id'] ?? null;
$editando = $id !== null && ctype_digit((string) $id);
$nombre = trim($_POST['nombre'] ?? '');
$dias = $_POST['dias_max_prestamo'] ?? '';

$base = '/pages/categoria_form.php' . ($editando ? '?id=' . (int) $id . '&' : '?');

if (
    $nombre === ''
    || mb_strlen($nombre) > 100
    || !ctype_digit((string) $dias)
    || (int) $dias < 1
    || (int) $dias > 365
) {
    header('Location: ' . $base . 'error=datos_invalidos');
    exit;
}

if ($editando && findCategoria($conn, (int) $id) === false) {
    header('Location: /pages/categorias.php?error=no_encontrado');
    exit;
}

$datos = [
    'nombre' => $nombre,
    'dias_max_prestamo' => (int) $dias,
];

try {
    if ($editando) {
        updateCategoria($conn, (int) $id, $datos);
    } else {
        createCategoria($conn, $datos);
    }
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        header('Location: ' . $base . 'error=nombre_repetido');
        exit;
    }

    error_log('Error al guardar categoría: ' . $exception->getMessage());
    header('Location: ' . $base . 'error=error_servidor');
    exit;
}

header('Location: /pages/categorias.php?ok=categoria_guardada');
exit;
