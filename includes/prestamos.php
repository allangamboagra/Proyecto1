<?php
function getPrestamosActivos(PDO $conn, string $busqueda = '')
{
    $sql = "SELECT p.id, u.nombre AS solicitante, p.estado, p.fecha_inicio, p.fecha_fin,
                   GROUP_CONCAT(e.nombre SEPARATOR ', ') AS equipos
            FROM prestamos p
            JOIN usuarios u ON u.id = p.usuario_id
            LEFT JOIN prestamo_detalle d ON d.prestamo_id = p.id
            LEFT JOIN equipos e ON e.id = d.equipo_id
            WHERE p.estado IN ('solicitado', 'aprobado', 'entregado')";
    $parametros = [];

    if ($busqueda !== '') {
        $sql .= ' AND u.nombre LIKE :busqueda';
        $parametros['busqueda'] = patronBusqueda($busqueda);
    }

    $statement = $conn->prepare($sql . ' GROUP BY p.id ORDER BY p.fecha_inicio');
    $statement->execute($parametros);
    return $statement->fetchAll();
}

function getEquiposDisponibles(PDO $conn)
{
    return $conn->query(
        "SELECT e.id, e.codigo, e.nombre, c.nombre AS categoria, c.dias_max_prestamo
         FROM equipos e
         JOIN categorias c ON c.id = e.categoria_id
         WHERE e.eliminado_en IS NULL AND e.estado = 'disponible' AND c.eliminado_en IS NULL
         ORDER BY e.codigo"
    )->fetchAll();
}

function getPrestamosDevueltos(PDO $conn)
{
    return $conn->query(
        "SELECT p.id, u.nombre AS solicitante, p.fecha_inicio, p.fecha_fin,
                GROUP_CONCAT(e.nombre SEPARATOR ', ') AS equipos,
                MAX(d.fecha_devolucion) AS fecha_devolucion
         FROM prestamos p
         JOIN usuarios u ON u.id = p.usuario_id
         LEFT JOIN prestamo_detalle d ON d.prestamo_id = p.id
         LEFT JOIN equipos e ON e.id = d.equipo_id
         WHERE p.estado = 'devuelto'
         GROUP BY p.id
         ORDER BY fecha_devolucion DESC, p.id DESC"
    )->fetchAll();
}

function fechaValida($texto)
{
    if (!is_string($texto)) {
        return null;
    }

    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $texto);

    return ($fecha !== false && $fecha->format('Y-m-d') === $texto) ? $fecha : null;
}

function createPrestamo(PDO $conn, array $datos)
{
    $inicio = fechaValida($datos['fecha_inicio']);
    $fin = fechaValida($datos['fecha_fin']);
    $equipos = $datos['equipos'];

    if ($inicio === null || $fin === null || $fin < $inicio) {
        return 'fechas_invalidas';
    }

    if (count($equipos) === 0) {
        return 'sin_equipos';
    }

    $hoy = new DateTimeImmutable('today');

    if ($inicio < $hoy) {
        return 'fechas_invalidas';
    }

    $diasAnticipacion = (int) (getConfiguracion($conn, 'anticipacion_max_dias') ?? 0);

    if ($inicio > $hoy->modify('+' . $diasAnticipacion . ' days')) {
        return 'anticipacion_excedida';
    }

    $marcadores = implode(',', array_fill(0, count($equipos), '?'));

    $conn->beginTransaction();

    try {
        $statement = $conn->prepare(
            "SELECT e.id, e.estado, c.dias_max_prestamo
             FROM equipos e
             JOIN categorias c ON c.id = e.categoria_id
             WHERE e.id IN ($marcadores) AND e.eliminado_en IS NULL
             FOR UPDATE"
        );
        $statement->execute($equipos);
        $encontrados = $statement->fetchAll();

        if (count($encontrados) !== count($equipos)) {
            throw new RuntimeException('equipo_no_disponible');
        }

        $duracion = $inicio->diff($fin)->days;

        foreach ($encontrados as $equipo) {
            if ($equipo['estado'] !== 'disponible') {
                throw new RuntimeException('equipo_no_disponible');
            }

            if ($duracion > (int) $equipo['dias_max_prestamo']) {
                throw new RuntimeException('duracion_excedida');
            }
        }

        $traslape = $conn->prepare(
            "SELECT COUNT(*)
             FROM prestamo_detalle d
             JOIN prestamos p ON p.id = d.prestamo_id
             WHERE d.equipo_id = :equipo_id
               AND p.estado IN ('solicitado', 'aprobado', 'entregado')
               AND p.fecha_inicio < :fin
               AND p.fecha_fin > :inicio"
        );

        foreach ($equipos as $equipoId) {
            $traslape->execute([
                'equipo_id' => $equipoId,
                'fin' => $fin->format('Y-m-d'),
                'inicio' => $inicio->format('Y-m-d'),
            ]);

            if ((int) $traslape->fetchColumn() > 0) {
                throw new RuntimeException('traslape');
            }
        }

        $prestamo = $conn->prepare(
            "INSERT INTO prestamos (usuario_id, operador_id, estado, fecha_inicio, fecha_fin)
             VALUES (:usuario_id, :operador_id, 'solicitado', :fecha_inicio, :fecha_fin)"
        );
        $prestamo->execute([
            'usuario_id' => $datos['usuario_id'],
            'operador_id' => $datos['operador_id'],
            'fecha_inicio' => $inicio->format('Y-m-d'),
            'fecha_fin' => $fin->format('Y-m-d'),
        ]);

        $prestamoId = (int) $conn->lastInsertId();

        $detalle = $conn->prepare(
            'INSERT INTO prestamo_detalle (prestamo_id, equipo_id) VALUES (:prestamo_id, :equipo_id)'
        );
        foreach ($equipos as $equipoId) {
            $detalle->execute(['prestamo_id' => $prestamoId, 'equipo_id' => $equipoId]);
        }

        $conn->commit();

        return null;
    } catch (RuntimeException $exception) {
        $conn->rollBack();
        return $exception->getMessage();
    } catch (PDOException $exception) {
        $conn->rollBack();
        throw $exception;
    }
}

function transicionesPermitidas()
{
    return [
        'aprobado' => ['solicitado'],
        'entregado' => ['aprobado'],
        'devuelto' => ['entregado'],
        'cancelado' => ['solicitado', 'aprobado'],
    ];
}

function cambiarEstadoPrestamo(PDO $conn, int $id, string $nuevoEstado)
{
    $transiciones = transicionesPermitidas();

    if (!isset($transiciones[$nuevoEstado])) {
        return 'transicion_invalida';
    }

    $origenes = $transiciones[$nuevoEstado];
    $marcadores = implode(',', array_fill(0, count($origenes), '?'));

    $conn->beginTransaction();

    try {
        if ($nuevoEstado === 'cancelado') {
            $statement = $conn->prepare("DELETE FROM prestamos WHERE id = ? AND estado IN ($marcadores)");
            $statement->execute(array_merge([$id], $origenes));

            if ($statement->rowCount() !== 1) {
                throw new RuntimeException('transicion_invalida');
            }

            $conn->commit();

            return null;
        }

        $statement = $conn->prepare("UPDATE prestamos SET estado = ? WHERE id = ? AND estado IN ($marcadores)");
        $statement->execute(array_merge([$nuevoEstado, $id], $origenes));

        if ($statement->rowCount() !== 1) {
            throw new RuntimeException('transicion_invalida');
        }

        if ($nuevoEstado === 'devuelto') {
            $devolucion = $conn->prepare(
                'UPDATE prestamo_detalle
                 SET fecha_devolucion = :hoy
                 WHERE prestamo_id = :id AND fecha_devolucion IS NULL'
            );
            $devolucion->execute([
                'hoy' => (new DateTimeImmutable('today'))->format('Y-m-d'),
                'id' => $id,
            ]);
        }

        $conn->commit();

        return null;
    } catch (RuntimeException $exception) {
        $conn->rollBack();
        return $exception->getMessage();
    } catch (PDOException $exception) {
        $conn->rollBack();
        throw $exception;
    }
}
