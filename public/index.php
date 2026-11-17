<?php
require_once __DIR__ . '/../includes/auth.php';

require_once __DIR__ . '/../includes/helpers.php';

require_once __DIR__ . '/../config/database.php';

startSession();

if (isset($_SESSION['usuario_id']) || restaurarSesionRecordada()) {
    header('Location: /pages/dashboard.php');
    exit;
}

$mensajes = [
    'datos' => 'Escriba el correo y la contraseña.',
    'credenciales' => 'Correo o contraseña incorrectos.',
    'sesion' => 'Debe iniciar sesión para continuar.',
    'expirada' => 'La sesión se cerró por inactividad. Inicie sesión de nuevo.',
    'bloqueada' => 'La cuenta está bloqueada temporalmente por demasiados intentos fallidos. Intente más tarde.',
];

$mensaje = $mensajes[$_GET['error'] ?? ''] ?? '';

$titulo = 'Iniciar sesión';
require __DIR__ . '/pages/common/header.php';
?>
<main class="login">
  <div class="card p-4 w-100" style="max-width: 380px;">
    <h1 class="h4 mb-1">Iniciar sesión</h1>
    <p class="text-muted small mb-4">Inventario y préstamos del laboratorio</p>

    <?php if ($mensaje !== ''): ?>
      <div class="alert alert-danger" role="alert"><?= e($mensaje) ?></div>
    <?php endif; ?>

    <form action="/actions/login.php" method="POST">
      <?= campoCsrf() ?>
      <div class="form-group">
        <label for="correo">Correo</label>
        <input type="email" class="form-control" id="correo" name="correo" required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="recordarme" name="recordarme" value="1">
        <label class="form-check-label" for="recordarme">Recordarme</label>
      </div>
      <button type="submit" class="btn btn-verde btn-block">Entrar</button>
    </form>
  </div>
</main>
<?php require __DIR__ . '/pages/common/footer.php'; ?>
