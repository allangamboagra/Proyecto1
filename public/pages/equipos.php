<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/equipos.php';
require_once __DIR__ . '/../../includes/recientes.php';

requireRole(['admin', 'operador']);

$esAdmin = $_SESSION['rol'] === 'admin';

$busqueda = busquedaDesdeUrl();
$resultado = paginar(getEquipos($conn, $busqueda));

$recientes = $esAdmin ? [] : getEquiposRecientes($conn);

$titulo = 'Equipos';
require __DIR__ . '/common/header.php';
?>
<div class="app">
  <?php require __DIR__ . '/common/sidebar.php'; ?>

  <main class="contenido">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="mb-0">Equipos</h1>
      <?php renderBuscador($busqueda, 'Buscar por nombre del dispositivo'); ?>
      <?php if ($esAdmin): ?>
        <a href="/pages/equipo_form.php" class="btn btn-verde">Nuevo equipo</a>
      <?php else: ?>
        <div></div>
      <?php endif; ?>
    </div>

    <?php if (count($recientes) > 0): ?>
      <div class="card card-body mb-3 py-2">
        <span class="small text-muted mb-1">Consultados recientemente:</span>
        <div>
          <?php foreach ($recientes as $reciente): ?>
            <a href="/pages/equipo_foto.php?id=<?= (int) $reciente['id'] ?>" class="btn btn-sm btn-outline-secondary mr-1 mb-1"><?= e($reciente['codigo']) ?> · <?= e($reciente['nombre']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php renderAlerta(); ?>

    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="thead-verde">
          <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultado['filas'] as $equipo): ?>
            <tr>
              <td><?= (int) $equipo['id'] ?></td>
              <td><?= e($equipo['codigo']) ?></td>
              <td><?= e($equipo['nombre']) ?></td>
              <td><?= e($equipo['categoria']) ?></td>
              <td><?= e($equipo['estado']) ?></td>
              <td class="text-nowrap">
                <?php if ($esAdmin): ?>
                  <a href="/pages/equipo_form.php?id=<?= (int) $equipo['id'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                <?php endif; ?>
                <a href="/pages/equipo_foto.php?id=<?= (int) $equipo['id'] ?>" class="btn btn-sm btn-outline-secondary">Foto</a>
                <?php if ($esAdmin): ?>
                  <form action="/actions/equipo_eliminar.php" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea eliminar este equipo?');">
                    <?= campoCsrf() ?>
                    <input type="hidden" name="id" value="<?= (int) $equipo['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($resultado['total'] === 0): ?>
            <tr><td colspan="6" class="text-center text-muted"><?= $busqueda !== '' ? 'No se encontraron resultados.' : 'No hay equipos registrados.' ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php renderPaginacion($resultado); ?>
  </main>
</div>
<?php require __DIR__ . '/common/footer.php'; ?>
