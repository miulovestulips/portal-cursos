<h1>Ventas — Resumen por curso</h1>

<nav class="tabs">
    <a href="/instructor/dashboard" class="tab">Resumen</a>
    <a href="/instructor/cursos" class="tab">Mis cursos</a>
    <a href="/instructor/ventas" class="tab active">Ventas</a>
</nav>

<form method="get" action="/instructor/ventas" class="form-filtros">
    <label>Desde <input type="date" name="desde" value="<?= htmlspecialchars($filtros['desde'] ?? '') ?>"></label>
    <label>Hasta <input type="date" name="hasta" value="<?= htmlspecialchars($filtros['hasta'] ?? '') ?>"></label>
    <label>Categoría
        <select name="categoria"><option value="todas">Todas</option></select>
    </label>
    <label>
        <select name="activos">
            <option value="todos">Activos o no</option>
            <option value="si">Solo activos</option>
        </select>
    </label>
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>

<table class="table">
    <thead>
        <tr><th>Curso</th><th>Alumnos inscritos</th><th>Nivel promedio</th><th>Ingresos</th></tr>
    </thead>
    <tbody>
        <?php if (empty($reporte)): ?>
            <tr><td colspan="4"><em>Reporte se conecta en la 3ra entrega.</em></td></tr>
        <?php else: foreach ($reporte as $r): ?>
            <tr>
                <td><a href="/instructor/ventas/<?= (int)$r['curso_id'] ?>"><?= htmlspecialchars($r['titulo']) ?></a></td>
                <td><?= (int)$r['alumnos'] ?></td>
                <td><?= htmlspecialchars($r['nivel_promedio']) ?></td>
                <td><?= htmlspecialchars($r['ingresos']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
