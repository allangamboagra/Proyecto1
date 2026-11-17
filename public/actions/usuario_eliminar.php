<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/usuarios.php';

requireRole(['admin']);

validarCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/usuarios.php');
    exit;
}

$id = $_POST['id'] ?? '';

if (!ctype_digit((string) $id)) {
    header('Location: /pages/usuarios.php?error=no_encontrado');
    exit;
}

if ((int) $id === (int) $_SESSION['usuario_id']) {
    header('Location: /pages/usuarios.php?error=no_puede_eliminarse');
    exit;
}

deleteUsuario($conn, (int) $id);

header('Location: /pages/usuarios.php?ok=usuario_eliminado');
exit;
