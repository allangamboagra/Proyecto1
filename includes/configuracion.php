<?php
function getConfiguracion(PDO $conn, string $clave)
{
    $statement = $conn->prepare('SELECT valor FROM configuracion WHERE clave = :clave LIMIT 1');
    $statement->execute(['clave' => $clave]);
    $fila = $statement->fetch();

    return $fila === false ? null : $fila['valor'];
}

function setConfiguracion(PDO $conn, string $clave, string $valor)
{
    $statement = $conn->prepare('UPDATE configuracion SET valor = :valor WHERE clave = :clave');
    $statement->execute(['valor' => $valor, 'clave' => $clave]);
}
