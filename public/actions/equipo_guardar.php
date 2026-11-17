<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/equipos.php';
require_once __DIR__ . '/../../includes/categorias.php';

requireRole(['admin']);

validarCsrf();

$id = $_POST['id'] ?? null;
$editando = $id !== null && ctype_digit((string) $id);
$codigo = trim($_POST['codigo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$categoriaId = $_POST['categoria_id'] ?? '';
$estado = $_POST['estado'] ?? '';

$base = '/pages/equipo_form.php' . ($editando ? '?id=' . (int) $id . '&' : '?');

if (
    $codigo === ''
    || mb_strlen($codigo) > 30
    || $nombre === ''
    || mb_strlen($nombre) > 100
    || !ctype_digit((string) $categoriaId)
    || !in_array($estado, ['disponible', 'mantenimiento', 'baja'], true)
) {
    header('Location: ' . $base . 'error=datos_invalidos');
    exit;
}

if (findCategoria($conn, (int) $categoriaId) === false) {
    header('Location: ' . $base . 'error=datos_invalidos');
    exit;
}

if ($editando && findEquipo($conn, (int) $id) === false) {
    header('Location: /pages/equipos.php?error=no_encontrado');
    exit;
}

$datos = [
    'codigo' => $codigo,
    'nombre' => $nombre,
    'categoria_id' => (int) $categoriaId,
    'estado' => $estado,
];

try {
    if ($editando) {
        updateEquipo($conn, (int) $id, $datos);
    } else {
        createEquipo($conn, $datos);
    }
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        header('Location: ' . $base . 'error=codigo_repetido');
        exit;
    }

    error_log('Error al guardar equipo: ' . $exception->getMessage());
    header('Location: ' . $base . 'error=error_servidor');
    exit;
}

header('Location: /pages/equipos.php?ok=equipo_guardado');
exit;
