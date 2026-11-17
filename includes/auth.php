<?php
require_once __DIR__ . '/csrf.php';

const INACTIVIDAD_SEGUNDOS = 1800;

const BLOQUEO_INTENTOS = 10;

const BLOQUEO_MINUTOS = 15;

const RECORDARME_DIAS = 14;

const COOKIE_RECORDARME = 'sigel_remember';

function esHttps()
{
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

function opcionesCookie(int $expira)
{
    return [
        'expires' => $expira,
        'path' => '/',
        'secure' => esHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function startSession()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => esHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.gc_maxlifetime', (string) INACTIVIDAD_SEGUNDOS);

    session_start();
}

function crearRecordarme(PDO $conn, int $usuarioId)
{
    $selector = bin2hex(random_bytes(12));

    $validador = bin2hex(random_bytes(32));

    $expira = time() + RECORDARME_DIAS * 24 * 60 * 60;

    $guardar = $conn->prepare(
        'INSERT INTO auth_tokens (usuario_id, selector, validator_hash, expira_en)
         VALUES (:usuario_id, :selector, :validator_hash, :expira_en)'
    );
    $guardar->execute([
        'usuario_id' => $usuarioId,
        'selector' => $selector,
        'validator_hash' => hash('sha256', $validador),
        'expira_en' => date('Y-m-d H:i:s', $expira),
    ]);

    setcookie(COOKIE_RECORDARME, $selector . ':' . $validador, opcionesCookie($expira));
}

function olvidarRecordarme(PDO $conn)
{
    $valor = $_COOKIE[COOKIE_RECORDARME] ?? '';
    $selector = is_string($valor) ? explode(':', $valor, 2)[0] : '';

    if ($selector !== '') {
        $borrar = $conn->prepare('DELETE FROM auth_tokens WHERE selector = :selector');
        $borrar->execute(['selector' => $selector]);
    }

    setcookie(COOKIE_RECORDARME, '', opcionesCookie(time() - 42000));
    unset($_COOKIE[COOKIE_RECORDARME]);
}

function restaurarSesionRecordada()
{
    global $conn;

    if (!$conn instanceof PDO) {
        return false;
    }

    $valor = $_COOKIE[COOKIE_RECORDARME] ?? '';

    if (!is_string($valor) || substr_count($valor, ':') !== 1) {
        return false;
    }

    [$selector, $validador] = explode(':', $valor, 2);

    $buscar = $conn->prepare(
        'SELECT t.id, t.usuario_id, t.validator_hash, t.expira_en, u.nombre, u.activo, r.nombre AS rol
         FROM auth_tokens t
         JOIN usuarios u ON u.id = t.usuario_id AND u.eliminado_en IS NULL
         JOIN roles r ON r.id = u.rol_id
         WHERE t.selector = :selector
         LIMIT 1'
    );
    $buscar->execute(['selector' => $selector]);
    $token = $buscar->fetch();

    $valido = $token !== false
        && (bool) $token['activo']
        && strtotime($token['expira_en']) > time()
        && hash_equals($token['validator_hash'], hash('sha256', $validador));

    if ($token !== false) {
        $borrar = $conn->prepare('DELETE FROM auth_tokens WHERE id = :id');
        $borrar->execute(['id' => $token['id']]);
    }

    if (!$valido) {
        setcookie(COOKIE_RECORDARME, '', opcionesCookie(time() - 42000));
        return false;
    }

    startSession();
    session_regenerate_id(true);

    $_SESSION['usuario_id'] = (int) $token['usuario_id'];
    $_SESSION['nombre'] = $token['nombre'];
    $_SESSION['rol'] = $token['rol'];
    $_SESSION['ultima_actividad'] = time();

    crearRecordarme($conn, (int) $token['usuario_id']);

    return true;
}

function cerrarSesion()
{
    $_SESSION = [];

    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $parametros['path'],
        'secure' => $parametros['secure'],
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_destroy();
}

function requireLogin()
{
    startSession();

    if (!isset($_SESSION['usuario_id']) && !restaurarSesionRecordada()) {
        header('Location: /index.php?error=sesion');
        exit;
    }

    if (time() - ($_SESSION['ultima_actividad'] ?? 0) > INACTIVIDAD_SEGUNDOS) {
        cerrarSesion();
        header('Location: /index.php?error=expirada');
        exit;
    }

    $_SESSION['ultima_actividad'] = time();
}

function requireRole(array $roles)
{
    requireLogin();

    if (!in_array($_SESSION['rol'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('No tiene permiso para ver esta página.');
    }
}
