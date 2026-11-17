<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/prestamos.php';

requireRole(['admin']);

$resultado = paginar(getPrestamosDevueltos($conn));

$titulo = 'Reportes';
require __DIR__ . '/common/header.php';
?>
<div class="app">
  <?php require __DIR__ . '/common/sidebar.php'; ?>

  <main class="contenido">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="mb-0">Préstamos devueltos</h1>
      <div></div>
      <a href="/actions/reporte_devueltos_csv.php" class="btn btn-verde">Exportar CSV</a>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="thead-verde">
          <tr>
            <th>ID</th>
            <th>Solicitante</th>
            <th>Equipos</th>
            <th>Inicio</th>
            <th>Fin</th>
            <th>Devuelto el</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultado['filas'] as $prestamo): ?>
            <tr>
              <td><?= (int) $prestamo['id'] ?></td>
              <td><?= e($prestamo['solicitante']) ?></td>
              <td><?= e($prestamo['equipos']) ?></td>
              <td><?= e($prestamo['fecha_inicio']) ?></td>
              <td><?= e($prestamo['fecha_fin']) ?></td>
              <td><?= e($prestamo['fecha_devolucion']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if ($resultado['total'] === 0): ?>
            <tr><td colspan="6" class="text-center text-muted">No hay préstamos devueltos.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php renderPaginacion($resultado); ?>
  </main>
</div>
<?php require __DIR__ . '/common/footer.php'; ?>
