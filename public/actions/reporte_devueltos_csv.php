<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/prestamos.php';

requireRole(['admin']);

$nombreArchivo = 'prestamos_devueltos_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

$salida = fopen('php://output', 'w');

fwrite($salida, "\xEF\xBB\xBF");

fputcsv($salida, ['ID', 'Solicitante', 'Equipos', 'Fecha de inicio', 'Fecha de fin', 'Fecha de devolución']);

foreach (getPrestamosDevueltos($conn) as $fila) {
    fputcsv($salida, [
        $fila['id'],
        $fila['solicitante'],
        $fila['equipos'],
        $fila['fecha_inicio'],
        $fila['fecha_fin'],
        $fila['fecha_devolucion'],
    ]);
}

fclose($salida);

exit;
