<?php
require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../includes/auth.php';

function registrarIntentoFallido(PDO $conn, int $id, int $intentos)
{
    $intentos++;
    $bloqueadoHasta = null;

    if ($intentos >= BLOQUEO_INTENTOS) {
        $bloqueadoHasta = date('Y-m-d H:i:s', time() + BLOQUEO_MINUTOS * 60);
        $intentos = 0;
    }

    $statement = $conn->prepare(
        'UPDATE usuarios SET intentos_fallidos = :intentos, bloqueado_hasta = :bloqueado_hasta WHERE id = :id'
    );
    $statement->execute([
        'intentos' => $intentos,
        'bloqueado_hasta' => $bloqueadoHasta,
        'id' => $id,
    ]);
}

startSession();
validarCsrf();

$correo = trim($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';

if ($correo === '' || $password === '') {
    header('Location: /index.php?error=datos');
    exit;
}

$statement = $conn->prepare(
    'SELECT u.id, u.nombre, u.password_hash, u.activo, u.intentos_fallidos, u.bloqueado_hasta, r.nombre AS rol
     FROM usuarios u
     JOIN roles r ON r.id = u.rol_id
     WHERE u.correo = :correo AND u.eliminado_en IS NULL
     LIMIT 1'
);
$statement->execute(['correo' => $correo]);

$usuario = $statement->fetch();

if ($usuario !== false && $usuario['bloqueado_hasta'] !== null && strtotime($usuario['bloqueado_hasta']) > time()) {
    header('Location: /index.php?error=bloqueada');
    exit;
}

if ($usuario === false || !$usuario['activo'] || !password_verify($password, $usuario['password_hash'])) {
    if ($usuario !== false) {
        registrarIntentoFallido($conn, (int) $usuario['id'], (int) $usuario['intentos_fallidos']);
    }

    header('Location: /index.php?error=credenciales');
    exit;
}

$reinicio = $conn->prepare('UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = :id');
$reinicio->execute(['id' => $usuario['id']]);

session_regenerate_id(true);

$_SESSION['usuario_id'] = (int) $usuario['id'];
$_SESSION['nombre'] = $usuario['nombre'];
$_SESSION['rol'] = $usuario['rol'];
$_SESSION['ultima_actividad'] = time();

if (isset($_POST['recordarme'])) {
    crearRecordarme($conn, (int) $usuario['id']);
}

header('Location: /pages/dashboard.php');
exit;
