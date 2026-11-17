<?php
const MAX_FOTOS_EQUIPO = 5;

function getImagenesEquipo(PDO $conn, int $equipoId)
{
    $statement = $conn->prepare(
        'SELECT id, equipo_id, ruta
         FROM equipo_imagenes
         WHERE equipo_id = :equipo_id
         ORDER BY id'
    );
    $statement->execute(['equipo_id' => $equipoId]);
    return $statement->fetchAll();
}

function getImagenPorId(PDO $conn, int $imagenId)
{
    $statement = $conn->prepare('SELECT id, equipo_id, ruta FROM equipo_imagenes WHERE id = :id');
    $statement->execute(['id' => $imagenId]);
    return $statement->fetch();
}

function agregarImagenEquipo(PDO $conn, int $equipoId, string $ruta)
{
    $statement = $conn->prepare('INSERT INTO equipo_imagenes (equipo_id, ruta) VALUES (:equipo_id, :ruta)');
    $statement->execute(['equipo_id' => $equipoId, 'ruta' => $ruta]);
}

function eliminarImagenEquipo(PDO $conn, int $imagenId, int $equipoId)
{
    $imagen = getImagenPorId($conn, $imagenId);

    if ($imagen === false || (int) $imagen['equipo_id'] !== $equipoId) {
        return null;
    }

    $statement = $conn->prepare('DELETE FROM equipo_imagenes WHERE id = :id AND equipo_id = :equipo_id');
    $statement->execute(['id' => $imagenId, 'equipo_id' => $equipoId]);

    return $statement->rowCount() === 1 ? $imagen['ruta'] : null;
}
