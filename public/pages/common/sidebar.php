<?php
$rol = $_SESSION['rol'] ?? '';

$archivo = basename($_SERVER['SCRIPT_NAME']);

$paginaActual = [
    'usuario_form.php' => 'usuarios.php',
    'categoria_form.php' => 'categorias.php',
    'equipo_form.php' => 'equipos.php',
    'prestamo_form.php' => 'dashboard.php',
][$archivo] ?? $archivo;

$paginas = [
    'Préstamos' => 'dashboard.php',
    'Usuarios' => 'usuarios.php',
    'Categorías' => 'categorias.php',
    'Equipos' => 'equipos.php',
    'Reportes' => 'reportes.php',
];

$opcionesPorRol = [
    'admin' => ['Préstamos', 'Usuarios', 'Categorías', 'Equipos', 'Reportes'],
    'operador' => ['Préstamos', 'Equipos'],
];

$opciones = $opcionesPorRol[$rol] ?? ['Préstamos'];
?>
<aside class="menu">
  <div class="menu-marca">Administrador de inventario</div>

  <nav class="nav flex-column">
    <?php foreach ($opciones as $nombre): ?>
      <a class="nav-link<?= $paginas[$nombre] === $paginaActual ? ' activo' : '' ?>" href="/pages/<?= e($paginas[$nombre]) ?>">
        <?= e($nombre) ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="menu-usuario text-center">
    <?php if ($rol === 'admin'): ?>
      <a href="/pages/configuracion.php" class="btn btn-outline-light btn-sm mb-3">Configurar días</a>
    <?php endif; ?>

    <div class="font-weight-bold"><?= e($_SESSION['nombre'] ?? '') ?></div>
    <div><?= e(nombreRol($rol)) ?></div>

    <form action="/actions/logout.php" method="POST" class="mt-2">
      <?= campoCsrf() ?>
      <button type="submit" class="btn btn-outline-light btn-sm">Cerrar sesión</button>
    </form>
  </div>
</aside>
