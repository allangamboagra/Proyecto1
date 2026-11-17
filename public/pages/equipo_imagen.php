<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/imagenes.php';

requireLogin();

$id = $_GET['id'] ?? '';

if (!ctype_digit((string) $id)) {
    http_response_code(404);
    exit;
}

$imagen = getImagenPorId($conn, (int) $id);
$ruta = $imagen === false ? null : __DIR__ . '/../../storage/uploads/' . basename($imagen['ruta']);

if ($ruta === null || !is_file($ruta)) {
    http_response_code(404);
    exit;
}

$tipo = (new finfo(FILEINFO_MIME_TYPE))->file($ruta);

header('Content-Type: ' . $tipo);
header('Content-Length: ' . filesize($ruta));
header('X-Content-Type-Options: nosniff');

readfile($ruta);
exit;
