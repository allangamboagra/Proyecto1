<?php
date_default_timezone_set('America/Costa_Rica');

const DB_DSN = 'mysql:host=127.0.0.1;dbname=Proyecto1AllanG;charset=utf8mb4';
const DB_USER = 'root';
const DB_PASSWORD = '';

try {
    $conn = new PDO(DB_DSN, DB_USER, DB_PASSWORD, [

        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    error_log('Error de conexión: ' . $exception->getMessage());

    http_response_code(500);
    exit('El servicio no está disponible en este momento.');
}
