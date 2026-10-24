<h1>Mi Kardex</h1>

<nav class="tabs">
    <a href="/estudiante/dashboard" class="tab">Resumen</a>
    <a href="/estudiante/kardex" class="tab active">Kardex</a>
    <a href="/estudiante/mensajes" class="tab">Mensajes</a>
</nav>

<form method="get" action="/estudiante/kardex" class="form-filtros">
    <label>Desde <input type="date" name="desde" value="<?= htmlspecialchars($filtros['desde'] ?? '') ?>"></label>
    <label>Hasta <input type="date" name="hasta" value="<?= htmlspecialchars($filtros['hasta'] ?? '') ?>"></label>
    <label>Categoría
        <select name="categoria">
            <option value="todas">Todas</option>
        </select>
    </label>
    <label>
        <select name="terminados">
            <option value="todos">Terminados o no</option>
            <option value="si">Solo terminados</option>
        </select>
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
        <tr>
            <th>Curso</th>
            <th>Fecha inscripción</th>
            <th>Última actividad</th>
            <th>Fecha terminación</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($registros)): ?>
            <tr><td colspan="5"><em>Sin datos aún (se conecta en la 3ra entrega).</em></td></tr>
        <?php else: foreach ($registros as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['curso']) ?></td>
                <td><?= htmlspecialchars($r['fecha_inscripcion']) ?></td>
                <td><?= htmlspecialchars($r['ultima_actividad']) ?></td>
                <td><?= htmlspecialchars($r['fecha_terminacion'] ?? '—') ?></td>
                <td><?= htmlspecialchars($r['estado']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
