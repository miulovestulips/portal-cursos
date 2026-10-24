<?php
$errores = $errores ?? [];
$old = $old ?? [];
$val = fn(string $k) => htmlspecialchars((string) ($old[$k] ?? ''));
$err = fn(string $k) => isset($errores[$k]) ? '<small class="field-error" style="color:#dc2626">' . htmlspecialchars($errores[$k]) . '</small>' : '';
?>
<h1>Nuevo curso</h1>

<nav class="tabs">
    <a href="/instructor/dashboard" class="tab">Resumen</a>
    <a href="/instructor/cursos" class="tab">Mis cursos</a>
    <a href="/instructor/ventas" class="tab">Ventas</a>
</nav>

<?php if ($errores): ?>
    <div class="alert alert-error">Revisa los campos marcados e inténtalo de nuevo.</div>
<?php endif; ?>

<form method="post" action="/instructor/cursos" enctype="multipart/form-data" id="form-curso" novalidate>
    <label for="titulo">Título</label>
    <input type="text" id="titulo" name="titulo" maxlength="150" value="<?= $val('titulo') ?>" required>
    <?= $err('titulo') ?>

    <label for="descripcion">Descripción</label>
    <textarea id="descripcion" name="descripcion" required><?= $val('descripcion') ?></textarea>
    <?= $err('descripcion') ?>

    <label for="categoria_id">Categoría</label>
    <select id="categoria_id" name="categoria_id" required>
        <option value="">Selecciona...</option>
        <?php foreach ($categorias as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= ($old['categoria_id'] ?? '') === (string) $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?= $err('categoria_id') ?>

    <label for="imagen">Imagen del curso</label>
    <input type="file" id="imagen" name="imagen" accept="image/*" required>
    <?= $err('imagen') ?>

    <label for="costo">Costo del curso completo (0 = gratis)</label>
    <input type="number" id="costo" name="costo" min="0" step="0.01" value="<?= $val('costo') !== '' ? $val('costo') : '0' ?>" required>
    <?= $err('costo') ?>

    <label for="total_niveles">Cantidad de niveles</label>
    <input type="number" id="total_niveles" name="total_niveles" min="1" max="100" value="<?= $val('total_niveles') ?>" required>
    <?= $err('total_niveles') ?>

    <button type="submit" class="btn btn-primary">Guardar curso</button>
</form>

<p><em>Nota: la carga de niveles, videos y contenido se implementa en la 3ra entrega.</em></p>
