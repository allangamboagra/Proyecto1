<?php
function getCategorias(PDO $conn, string $busqueda = '')
{
    $sql = 'SELECT id, nombre, dias_max_prestamo FROM categorias WHERE eliminado_en IS NULL';
    $parametros = [];

    if ($busqueda !== '') {
        $sql .= ' AND nombre LIKE :busqueda';
        $parametros['busqueda'] = patronBusqueda($busqueda);
    }

    $statement = $conn->prepare($sql . ' ORDER BY nombre');
    $statement->execute($parametros);
    return $statement->fetchAll();
}

function findCategoria(PDO $conn, int $id)
{
    $statement = $conn->prepare(
        'SELECT id, nombre, dias_max_prestamo
         FROM categorias
         WHERE id = :id AND eliminado_en IS NULL
         LIMIT 1'
    );
    $statement->execute(['id' => $id]);
    return $statement->fetch();
}

function createCategoria(PDO $conn, array $datos)
{
    $statement = $conn->prepare(
        'INSERT INTO categorias (nombre, dias_max_prestamo)
         VALUES (:nombre, :dias_max_prestamo)'
    );
    $statement->execute($datos);
}

function updateCategoria(PDO $conn, int $id, array $datos)
{
    $statement = $conn->prepare(
        'UPDATE categorias
         SET nombre = :nombre, dias_max_prestamo = :dias_max_prestamo
         WHERE id = :id AND eliminado_en IS NULL'
    );

    $statement->execute([
        'nombre' => $datos['nombre'],
        'dias_max_prestamo' => $datos['dias_max_prestamo'],
        'id' => $id,
    ]);
}

function deleteCategoria(PDO $conn, int $id)
{
    $statement = $conn->prepare(
        'UPDATE categorias SET eliminado_en = NOW() WHERE id = :id AND eliminado_en IS NULL'
    );
    $statement->execute(['id' => $id]);
}
