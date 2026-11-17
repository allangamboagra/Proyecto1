<?php
function getUsuarios(PDO $conn, string $busqueda = '')
{
    $sql = 'SELECT u.id, u.nombre, u.correo, r.nombre AS rol, u.activo
            FROM usuarios u
            JOIN roles r ON r.id = u.rol_id
            WHERE u.eliminado_en IS NULL';
    $parametros = [];

    if ($busqueda !== '') {
        $sql .= ' AND u.nombre LIKE :busqueda';
        $parametros['busqueda'] = patronBusqueda($busqueda);
    }

    $statement = $conn->prepare($sql . ' ORDER BY u.id');
    $statement->execute($parametros);
    return $statement->fetchAll();
}

function findUsuario(PDO $conn, int $id)
{
    $statement = $conn->prepare(
        'SELECT id, nombre, correo, rol_id, activo
         FROM usuarios
         WHERE id = :id AND eliminado_en IS NULL
         LIMIT 1'
    );
    $statement->execute(['id' => $id]);
    return $statement->fetch();
}

function getRoles(PDO $conn)
{
    return $conn->query('SELECT id, nombre FROM roles ORDER BY id')->fetchAll();
}

function findRol(PDO $conn, int $id)
{
    $statement = $conn->prepare('SELECT id FROM roles WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    return $statement->fetch();
}

function createUsuario(PDO $conn, array $datos)
{
    $statement = $conn->prepare(
        'INSERT INTO usuarios (nombre, correo, rol_id, activo, password_hash)
         VALUES (:nombre, :correo, :rol_id, :activo, :password_hash)'
    );
    $statement->execute($datos);
}

function updateUsuario(PDO $conn, int $id, array $datos)
{
    $sql = 'UPDATE usuarios SET nombre = :nombre, correo = :correo, rol_id = :rol_id, activo = :activo';
    $parametros = [
        'nombre' => $datos['nombre'],
        'correo' => $datos['correo'],
        'rol_id' => $datos['rol_id'],
        'activo' => $datos['activo'],
        'id' => $id,
    ];

    if (isset($datos['password_hash'])) {
        $sql .= ', password_hash = :password_hash';
        $parametros['password_hash'] = $datos['password_hash'];
    }

    $statement = $conn->prepare($sql . ' WHERE id = :id AND eliminado_en IS NULL');
    $statement->execute($parametros);
}

function deleteUsuario(PDO $conn, int $id)
{
    $statement = $conn->prepare(
        'UPDATE usuarios SET eliminado_en = NOW() WHERE id = :id AND eliminado_en IS NULL'
    );
    $statement->execute(['id' => $id]);
}

function getUsuariosActivos(PDO $conn)
{
    return $conn->query(
        'SELECT id, nombre, correo
         FROM usuarios
         WHERE activo = 1 AND eliminado_en IS NULL
         ORDER BY nombre'
    )->fetchAll();
}

function findUsuarioActivo(PDO $conn, int $id)
{
    $statement = $conn->prepare(
        'SELECT id FROM usuarios WHERE id = :id AND activo = 1 AND eliminado_en IS NULL LIMIT 1'
    );
    $statement->execute(['id' => $id]);
    return $statement->fetch();
}
