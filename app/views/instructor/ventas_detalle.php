<h1>Ventas — Detalle del curso #<?= htmlspecialchars($cursoId) ?></h1>

<a href="/instructor/ventas">&larr; Volver al resumen</a>

<table class="table">
    <thead>
        <tr><th>Alumno</th><th>Fecha inscripción</th><th>Nivel de avance</th><th>Precio pagado</th><th>Forma de pago</th></tr>
    </thead>
    <tbody>
        <?php if (empty($detalle)): ?>
            <tr><td colspan="5"><em>Detalle se conecta en la 3ra entrega.</em></td></tr>
        <?php else: foreach ($detalle as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['alumno']) ?></td>
                <td><?= htmlspecialchars($d['fecha_inscripcion']) ?></td>
                <td><?= htmlspecialchars($d['nivel']) ?></td>
                <td><?= htmlspecialchars($d['precio']) ?></td>
                <td><?= htmlspecialchars($d['forma_pago']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
