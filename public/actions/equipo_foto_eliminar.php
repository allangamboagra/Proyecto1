<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/equipos.php';
require_once __DIR__ . '/../../includes/imagenes.php';

requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/equipos.php');
    exit;
}

validarCsrf();

$equipoId = $_POST['equipo_id'] ?? '';
$imagenId = $_POST['imagen_id'] ?? '';

if (!ctype_digit((string) $equipoId) || !ctype_digit((string) $imagenId) || findEquipo($conn, (int) $equipoId) === false) {
    header('Location: /pages/equipos.php?error=no_encontrado');
    exit;
}

$base = '/pages/equipo_foto.php?id=' . (int) $equipoId . '&';

try {
    $ruta = eliminarImagenEquipo($conn, (int) $imagenId, (int) $equipoId);
} catch (PDOException $exception) {
    error_log('Error al eliminar foto de equipo: ' . $exception->getMessage());
    header('Location: ' . $base . 'error=error_servidor');
    exit;
}

if ($ruta === null) {
    header('Location: ' . $base . 'error=no_encontrado');
    exit;
}

@unlink(__DIR__ . '/../../storage/uploads/' . basename($ruta));

header('Location: ' . $base . 'ok=foto_eliminada');
exit;
