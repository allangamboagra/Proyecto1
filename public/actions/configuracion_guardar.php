<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/configuracion.php';

requireRole(['admin']);

validarCsrf();

$dias = $_POST['dias_anticipacion'] ?? '';

if (!is_string($dias) || !ctype_digit($dias) || (int) $dias > 365) {
    header('Location: /pages/configuracion.php?error=datos_invalidos');
    exit;
}

setConfiguracion($conn, 'anticipacion_max_dias', (string) (int) $dias);

header('Location: /pages/configuracion.php?ok=configuracion_guardada');
exit;
