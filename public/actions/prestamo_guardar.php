<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/usuarios.php';
require_once __DIR__ . '/../../includes/prestamos.php';
require_once __DIR__ . '/../../includes/configuracion.php';

requireRole(['operador']);

validarCsrf();

$usuarioId = $_POST['usuario_id'] ?? '';
$fechaInicio = $_POST['fecha_inicio'] ?? '';
$fechaFin = $_POST['fecha_fin'] ?? '';
$equipos = $_POST['equipos'] ?? [];

$base = '/pages/prestamo_form.php?error=';

if (!ctype_digit((string) $usuarioId) || !is_array($equipos)) {
    header('Location: ' . $base . 'datos_invalidos');
    exit;
}

foreach ($equipos as $equipo) {
    if (!is_string($equipo) || !ctype_digit($equipo) || (int) $equipo === 0) {
        header('Location: ' . $base . 'datos_invalidos');
        exit;
    }
}

$equipos = array_values(array_unique(array_map('intval', $equipos)));

if (findUsuarioActivo($conn, (int) $usuarioId) === false) {
    header('Location: ' . $base . 'datos_invalidos');
    exit;
}

$codigo = createPrestamo($conn, [
    'usuario_id' => (int) $usuarioId,
    'operador_id' => (int) $_SESSION['usuario_id'],
    'fecha_inicio' => $fechaInicio,
    'fecha_fin' => $fechaFin,
    'equipos' => $equipos,
]);

if ($codigo !== null) {
    header('Location: ' . $base . $codigo);
    exit;
}

header('Location: /pages/dashboard.php?ok=prestamo_guardado');
exit;
