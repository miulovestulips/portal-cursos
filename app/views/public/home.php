<section class="hero">
    <h1>Aprende algo nuevo hoy</h1>
    <p>Cursos autodidactas creados por instructores de todo el mundo.</p>
    <a class="btn btn-primary" href="/buscador">Explorar cursos</a>
</section>

<section>
    <h2>Categorías</h2>
    <ul class="chips">
        <?php foreach ($categorias as $cat): ?>
            <li><a class="chip" href="/buscador?categoria_id=<?= (int) $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</section>

<section>
    <h2>Cursos</h2>
    <nav class="tabs" id="home-tabs">
        <button type="button" class="tab active" data-orden="recientes">Más recientes</button>
        <button type="button" class="tab" data-orden="mejor_calificados">Mejor calificados</button>
        <button type="button" class="tab" data-orden="mas_vendidos">Más vendidos</button>
    </nav>
    <div id="home-mensaje"></div>
    <div class="grid-cards" id="home-cursos"></div>
</section>
