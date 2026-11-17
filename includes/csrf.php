<?php
function tokenCsrf()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function campoCsrf()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(tokenCsrf(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
}

function validarCsrf()
{
    $enviado = $_POST['csrf_token'] ?? '';

    $esperado = $_SESSION['csrf_token'] ?? '';

    if (!is_string($enviado) || $esperado === '' || !hash_equals($esperado, $enviado)) {
        http_response_code(403);
        exit('La solicitud no es válida. Recargue la página e intente de nuevo.');
    }
}
