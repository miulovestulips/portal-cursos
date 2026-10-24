<h1>Panel — Administrador</h1>

<nav class="tabs">
    <a href="/admin/dashboard" class="tab active">Resumen</a>
    <a href="/admin/usuarios" class="tab">Usuarios</a>
    <a href="/admin/categorias" class="tab">Categorías</a>
    <a href="/admin/reportes/usuarios" class="tab">Reportes</a>
</nav>

<section class="stat-cards">
    <div class="stat-card">
        <span class="stat-number"><?= (int)$resumen['usuarios_totales'] ?></span>
        <span class="stat-label">Usuarios totales</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= (int)$resumen['instructores'] ?></span>
        <span class="stat-label">Instructores</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= (int)$resumen['estudiantes'] ?></span>
        <span class="stat-label">Estudiantes</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= (int)$resumen['cursos_activos'] ?></span>
        <span class="stat-label">Cursos activos</span>
    </div>
    <div class="stat-card stat-card-alert">
        <span class="stat-number"><?= (int)$resumen['comentarios_reportados'] ?></span>
        <span class="stat-label">Comentarios reportados</span>
    </div>
</section>
