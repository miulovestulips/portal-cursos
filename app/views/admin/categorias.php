<h1>Categorías</h1>

<nav class="tabs">
    <a href="/admin/dashboard" class="tab">Resumen</a>
    <a href="/admin/usuarios" class="tab">Usuarios</a>
    <a href="/admin/categorias" class="tab active">Categorías</a>
    <a href="/admin/reportes/usuarios" class="tab">Reportes</a>
</nav>

<form method="post" action="/admin/categorias" class="form-inline" id="form-categoria" novalidate>
    <input type="text" name="nombre" placeholder="Nombre de la categoría" required>
    <input type="text" name="descripcion" placeholder="Descripción">
    <button type="submit" class="btn btn-primary">Agregar</button>
</form>

<table class="table">
    <thead>
        <tr><th>Nombre</th><th>Descripción</th><th>Creada por</th><th>Fecha</th><th>Acciones</th></tr>
    </thead>
    <tbody>
        <?php if (empty($categorias)): ?>
            <tr><td colspan="5"><em>CRUD de categorías se conecta en la 2da entrega.</em></td></tr>
        <?php else: foreach ($categorias as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['nombre']) ?></td>
                <td><?= htmlspecialchars($cat['descripcion']) ?></td>
                <td><?= htmlspecialchars($cat['creado_por']) ?></td>
                <td><?= htmlspecialchars($cat['fecha_creacion']) ?></td>
                <td>Editar | Eliminar</td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
