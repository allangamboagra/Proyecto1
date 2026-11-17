<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/prestamos.php';

requireRole(['operador']);

validarCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/dashboard.php');
    exit;
}

$id = $_POST['id'] ?? '';
$estado = $_POST['estado'] ?? '';

if (!ctype_digit((string) $id) || !is_string($estado)) {
    header('Location: /pages/dashboard.php?error=datos_invalidos');
    exit;
}

try {
    $error = cambiarEstadoPrestamo($conn, (int) $id, $estado);
} catch (PDOException $exception) {
    error_log('Error al cambiar estado de préstamo: ' . $exception->getMessage());
    header('Location: /pages/dashboard.php?error=error_servidor');
    exit;
}

if ($error !== null) {
    header('Location: /pages/dashboard.php?error=' . $error);
    exit;
}

$ok = $estado === 'cancelado' ? 'prestamo_cancelado' : 'estado_actualizado';
header('Location: /pages/dashboard.php?ok=' . $ok);
exit;
