<h1>Usuarios</h1>

<nav class="tabs">
    <a href="/admin/dashboard" class="tab">Resumen</a>
    <a href="/admin/usuarios" class="tab active">Usuarios</a>
    <a href="/admin/categorias" class="tab">Categorías</a>
    <a href="/admin/reportes/usuarios" class="tab">Reportes</a>
</nav>

<table class="table">
    <thead>
        <tr><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
    </thead>
    <tbody>
        <?php if (empty($usuarios)): ?>
            <tr><td colspan="5"><em>Bloqueo/desbloqueo real se conecta en la 3ra entrega.</em></td></tr>
        <?php else: foreach ($usuarios as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                <td><?= htmlspecialchars($u['rol']) ?></td>
                <td><?= $u['activo'] ? 'Activo' : 'Bloqueado' ?></td>
                <td><?= $u['activo'] ? 'Bloquear' : 'Desbloquear' ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
