<?php
require_once __DIR__ . '/../../includes/auth.php';

require_once __DIR__ . '/../../config/database.php';

startSession();
validarCsrf();

olvidarRecordarme($conn);

cerrarSesion();

header('Location: /index.php');
exit;
