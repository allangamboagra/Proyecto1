<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/equipos.php';
require_once __DIR__ . '/../../includes/imagenes.php';

requireRole(['admin']);

validarCsrf();

$id = $_POST['id'] ?? '';

if (!ctype_digit((string) $id) || findEquipo($conn, (int) $id) === false) {
    header('Location: /pages/equipos.php?error=no_encontrado');
    exit;
}

$equipoId = (int) $id;
$base = '/pages/equipo_foto.php?id=' . $equipoId . '&';

$archivos = $_FILES['fotos'] ?? null;
$errores = is_array($archivos['error'] ?? null) ? $archivos['error'] : [];

if (count($errores) === 0 || in_array(UPLOAD_ERR_NO_FILE, $errores, true)) {
    header('Location: ' . $base . 'error=foto_no_subida');
    exit;
}

if (count($errores) + count(getImagenesEquipo($conn, $equipoId)) > MAX_FOTOS_EQUIPO) {
    header('Location: ' . $base . 'error=demasiadas_fotos');
    exit;
}

$tamanoMaximo = 2 * 1024 * 1024;

$extensiones = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

$aceptadas = [];

foreach ($errores as $indice => $error) {
    if (in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        header('Location: ' . $base . 'error=foto_invalida');
        exit;
    }

    if ($error !== UPLOAD_ERR_OK) {
        header('Location: ' . $base . 'error=foto_no_subida');
        exit;
    }

    $tmp = $archivos['tmp_name'][$indice];

    if ($archivos['size'][$indice] > $tamanoMaximo) {
        header('Location: ' . $base . 'error=foto_invalida');
        exit;
    }

    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);

    if (!isset($extensiones[$tipo])) {
        header('Location: ' . $base . 'error=foto_invalida');
        exit;
    }

    $aceptadas[] = ['tmp' => $tmp, 'extension' => $extensiones[$tipo]];
}

$directorio = __DIR__ . '/../../storage/uploads';

if (!is_dir($directorio) && !mkdir($directorio, 0775, true)) {
    error_log('No se pudo crear la carpeta de fotos: ' . $directorio);
    header('Location: ' . $base . 'error=error_servidor');
    exit;
}

foreach ($aceptadas as $foto) {
    $nombre = 'equipo_' . $equipoId . '_' . bin2hex(random_bytes(12)) . '.' . $foto['extension'];
    $destino = $directorio . '/' . $nombre;

    if (!move_uploaded_file($foto['tmp'], $destino)) {
        error_log('No se pudo guardar la foto en ' . $directorio . ' (revise los permisos de la carpeta)');
        header('Location: ' . $base . 'error=error_servidor');
        exit;
    }

    try {
        agregarImagenEquipo($conn, $equipoId, $nombre);
    } catch (PDOException $exception) {
        unlink($destino);
        error_log('Error al guardar foto de equipo: ' . $exception->getMessage());
        header('Location: ' . $base . 'error=error_servidor');
        exit;
    }
}

header('Location: ' . $base . 'ok=foto_guardada');
exit;
