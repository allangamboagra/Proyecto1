<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/usuarios.php';

requireRole(['admin']);

$busqueda = busquedaDesdeUrl();
$resultado = paginar(getUsuarios($conn, $busqueda));

$titulo = 'Usuarios';
require __DIR__ . '/common/header.php';
?>
<div class="app">
  <?php require __DIR__ . '/common/sidebar.php'; ?>

  <main class="contenido">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="mb-0">Usuarios</h1>
      <?php renderBuscador($busqueda, 'Buscar por nombre'); ?>
      <a href="/pages/usuario_form.php" class="btn btn-verde">Nuevo usuario</a>
    </div>

    <?php renderAlerta(); ?>

    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="thead-verde">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Activo</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultado['filas'] as $usuario): ?>
            <tr>
              <td><?= (int) $usuario['id'] ?></td>
              <td><?= e($usuario['nombre']) ?></td>
              <td><?= e($usuario['correo']) ?></td>
              <td><?= e(nombreRol($usuario['rol'])) ?></td>
              <td><?= $usuario['activo'] ? 'Sí' : 'No' ?></td>
              <td class="text-nowrap">
                <a href="/pages/usuario_form.php?id=<?= (int) $usuario['id'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                <form action="/actions/usuario_eliminar.php" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea eliminar este usuario?');">
                  <?= campoCsrf() ?>
                  <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($resultado['total'] === 0): ?>
            <tr><td colspan="6" class="text-center text-muted"><?= $busqueda !== '' ? 'No se encontraron resultados.' : 'No hay usuarios registrados.' ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php renderPaginacion($resultado); ?>
  </main>
</div>
<?php require __DIR__ . '/common/footer.php'; ?>
