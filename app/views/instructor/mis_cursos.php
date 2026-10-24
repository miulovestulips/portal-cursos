<h1>Mis cursos</h1>

<nav class="tabs">
    <a href="/instructor/dashboard" class="tab">Resumen</a>
    <a href="/instructor/cursos" class="tab active">Mis cursos</a>
    <a href="/instructor/ventas" class="tab">Ventas</a>
</nav>

<?php if (($ok ?? '') === 'creado'): ?>
    <div class="alert alert-success">El curso se guardó correctamente.</div>
<?php endif; ?>
<div id="cursos-mensaje"></div>

<a class="btn btn-primary" href="/instructor/cursos/nuevo">+ Nuevo curso</a>

<table class="table" id="tabla-mis-cursos">
    <thead>
        <tr><th>Título</th><th>Categoría</th><th>Niveles</th><th>Costo</th><th>Estado</th><th>Acciones</th></tr>
    </thead>
    <tbody>
        <?php if (empty($cursos)): ?>
            <tr><td colspan="6"><em>Aún no tienes cursos. Crea el primero con «Nuevo curso».</em></td></tr>
        <?php else: foreach ($cursos as $c): ?>
            <tr data-curso-id="<?= (int) $c['id'] ?>">
                <td><?= htmlspecialchars($c['titulo']) ?></td>
                <td><?= htmlspecialchars($c['categoria']) ?></td>
                <td><?= (int) $c['total_niveles'] ?></td>
                <td><?= $c['gratis'] ? 'Gratis' : htmlspecialchars(Formato::moneda($c['costo'])) ?></td>
                <td class="celda-estado"><?= $c['activo'] ? 'Activo' : 'Dado de baja' ?></td>
                <td class="celda-acciones">
                    <?php if ($c['activo']): ?>
                        <a href="/curso/<?= (int) $c['id'] ?>">Ver</a>
                        <button type="button" class="btn btn-link" data-baja-curso="<?= (int) $c['id'] ?>">Dar de baja</button>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
