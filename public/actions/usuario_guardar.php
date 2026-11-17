<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/usuarios.php';

requireRole(['admin']);

validarCsrf();

$id = $_POST['id'] ?? null;

$editando = $id !== null && ctype_digit((string) $id);
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$rolId = $_POST['rol_id'] ?? '';
$password = $_POST['password'] ?? '';

$activo = isset($_POST['activo']) ? 1 : 0;

$base = '/pages/usuario_form.php' . ($editando ? '?id=' . (int) $id . '&' : '?');

$contraseñaValida = ($editando && $password === '') || strlen($password) >= 8;

if (
    $nombre === ''
    || mb_strlen($nombre) > 100
    || filter_var($correo, FILTER_VALIDATE_EMAIL) === false
    || mb_strlen($correo) > 150
    || !ctype_digit((string) $rolId)
    || !$contraseñaValida
) {
    header('Location: ' . $base . 'error=datos_invalidos');
    exit;
}

if (findRol($conn, (int) $rolId) === false) {
    header('Location: ' . $base . 'error=datos_invalidos');
    exit;
}

if ($editando && findUsuario($conn, (int) $id) === false) {
    header('Location: /pages/usuarios.php?error=no_encontrado');
    exit;
}

$datos = [
    'nombre' => $nombre,
    'correo' => $correo,
    'rol_id' => (int) $rolId,
    'activo' => $activo,
];

if ($password !== '') {
    $datos['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
}

try {
    if ($editando) {
        updateUsuario($conn, (int) $id, $datos);
    } else {
        createUsuario($conn, $datos);
    }
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        header('Location: ' . $base . 'error=correo_repetido');
        exit;
    }

    error_log('Error al guardar usuario: ' . $exception->getMessage());
    header('Location: ' . $base . 'error=error_servidor');
    exit;
}

header('Location: /pages/usuarios.php?ok=usuario_guardado');
exit;
