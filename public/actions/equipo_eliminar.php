<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/equipos.php';

requireRole(['admin']);

validarCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/equipos.php');
    exit;
}

$id = $_POST['id'] ?? '';

if (!ctype_digit((string) $id)) {
    header('Location: /pages/equipos.php?error=no_encontrado');
    exit;
}

deleteEquipo($conn, (int) $id);

header('Location: /pages/equipos.php?ok=equipo_eliminado');
exit;
