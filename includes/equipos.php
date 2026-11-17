<?php
function getEquipos(PDO $conn, string $busqueda = '')
{
    $sql = 'SELECT e.id, e.codigo, e.nombre, e.estado, c.nombre AS categoria
            FROM equipos e
            JOIN categorias c ON c.id = e.categoria_id
            WHERE e.eliminado_en IS NULL';
    $parametros = [];

    if ($busqueda !== '') {
        $sql .= ' AND e.nombre LIKE :busqueda';
        $parametros['busqueda'] = patronBusqueda($busqueda);
    }

    $statement = $conn->prepare($sql . ' ORDER BY e.codigo');
    $statement->execute($parametros);
    return $statement->fetchAll();
}

function findEquipo(PDO $conn, int $id)
{
    $statement = $conn->prepare(
        'SELECT id, codigo, nombre, categoria_id, estado
         FROM equipos
         WHERE id = :id AND eliminado_en IS NULL
         LIMIT 1'
    );
    $statement->execute(['id' => $id]);
    return $statement->fetch();
}

function createEquipo(PDO $conn, array $datos)
{
    $statement = $conn->prepare(
        'INSERT INTO equipos (codigo, nombre, categoria_id, estado)
         VALUES (:codigo, :nombre, :categoria_id, :estado)'
    );
    $statement->execute($datos);
}

function updateEquipo(PDO $conn, int $id, array $datos)
{
    $statement = $conn->prepare(
        'UPDATE equipos
         SET codigo = :codigo, nombre = :nombre, categoria_id = :categoria_id, estado = :estado
         WHERE id = :id AND eliminado_en IS NULL'
    );
    $statement->execute([
        'codigo' => $datos['codigo'],
        'nombre' => $datos['nombre'],
        'categoria_id' => $datos['categoria_id'],
        'estado' => $datos['estado'],
        'id' => $id,
    ]);
}

function deleteEquipo(PDO $conn, int $id)
{
    $statement = $conn->prepare(
        'UPDATE equipos SET eliminado_en = NOW() WHERE id = :id AND eliminado_en IS NULL'
    );
    $statement->execute(['id' => $id]);
}
