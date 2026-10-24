<h1>Mi panel — Instructor</h1>

<nav class="tabs">
    <a href="/instructor/dashboard" class="tab active">Resumen</a>
    <a href="/instructor/cursos" class="tab">Mis cursos</a>
    <a href="/instructor/ventas" class="tab">Ventas</a>
</nav>

<a class="btn btn-primary" href="/instructor/cursos/nuevo">+ Nuevo curso</a>

<section class="stat-cards">
    <div class="stat-card">
        <span class="stat-number"><?= (int)$resumen['cursos_publicados'] ?></span>
        <span class="stat-label">Cursos publicados</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= (int)$resumen['alumnos_totales'] ?></span>
        <span class="stat-label">Alumnos totales</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= htmlspecialchars($resumen['ingresos_totales']) ?></span>
        <span class="stat-label">Ingresos totales</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= htmlspecialchars($resumen['calificacion_prom']) ?></span>
        <span class="stat-label">Calificación promedio</span>
    </div>
</section>
