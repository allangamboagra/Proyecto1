<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/categorias.php';

requireRole(['admin']);

validarCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/categorias.php');
    exit;
}

$id = $_POST['id'] ?? '';

if (!ctype_digit((string) $id)) {
    header('Location: /pages/categorias.php?error=no_encontrado');
    exit;
}

deleteCategoria($conn, (int) $id);

header('Location: /pages/categorias.php?ok=categoria_eliminada');
exit;
