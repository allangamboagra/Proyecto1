<?php
require_once __DIR__ . '/auth.php';

const COOKIE_RECIENTES = 'equipos_recientes';
const MAX_RECIENTES = 5;

function leerEquiposRecientes()
{
    $valor = $_COOKIE[COOKIE_RECIENTES] ?? '';

    if (!is_string($valor) || $valor === '') {
        return [];
    }

    $ids = [];

    foreach (explode(',', $valor) as $parte) {
        $id = filter_var($parte, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($id !== false && !in_array($id, $ids, true)) {
            $ids[] = $id;
        }
    }

    return array_slice($ids, 0, MAX_RECIENTES);
}

function registrarEquipoReciente(int $equipoId)
{
    $ids = array_values(array_filter(leerEquiposRecientes(), fn($id) => $id !== $equipoId));
    array_unshift($ids, $equipoId);

    $ids = array_slice($ids, 0, MAX_RECIENTES);

    $valor = implode(',', $ids);

    setcookie(COOKIE_RECIENTES, $valor, opcionesCookie(time() + 30 * 24 * 60 * 60));

    $_COOKIE[COOKIE_RECIENTES] = $valor;
}

function getEquiposRecientes(PDO $conn)
{
    $ids = leerEquiposRecientes();

    if (count($ids) === 0) {
        return [];
    }

    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $statement = $conn->prepare(
        "SELECT id, codigo, nombre FROM equipos WHERE id IN ($marcadores) AND eliminado_en IS NULL"
    );
    $statement->execute($ids);

    $porId = [];

    foreach ($statement->fetchAll() as $fila) {
        $porId[(int) $fila['id']] = $fila;
    }

    $resultado = [];

    foreach ($ids as $id) {
        if (isset($porId[$id])) {
            $resultado[] = $porId[$id];
        }
    }

    return $resultado;
}
