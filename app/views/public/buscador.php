<h1>Buscar cursos</h1>

<form method="get" action="/buscador" class="form-inline" id="form-buscador" novalidate>
    <input type="text" name="q" placeholder="Título del curso (parcial)" maxlength="100" value="<?= htmlspecialchars($filtros['q']) ?>">

    <select name="categoria_id">
        <option value="">Todas las categorías</option>
        <?php foreach ($categorias as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= $filtros['categoria_id'] === (string) $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="instructor" placeholder="Instructor" maxlength="100" value="<?= htmlspecialchars($filtros['instructor']) ?>">

    <label>Publicado desde <input type="date" name="desde" value="<?= htmlspecialchars($filtros['desde']) ?>"></label>
    <label>hasta <input type="date" name="hasta" value="<?= htmlspecialchars($filtros['hasta']) ?>"></label>

    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<div id="buscador-mensaje"></div>
<div class="grid-cards" id="buscador-resultados"></div>
