<h1>Mi panel — Estudiante</h1>

<nav class="tabs">
    <a href="/estudiante/dashboard" class="tab active">Resumen</a>
    <a href="/estudiante/kardex" class="tab">Kardex</a>
    <a href="/estudiante/mensajes" class="tab">Mensajes</a>
</nav>

<section class="stat-cards">
    <div class="stat-card">
        <span class="stat-number"><?= (int)$resumen['cursos_inscritos'] ?></span>
        <span class="stat-label">Cursos inscritos</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= (int)$resumen['en_progreso'] ?></span>
        <span class="stat-label">En progreso</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= (int)$resumen['cursos_terminados'] ?></span>
        <span class="stat-label">Terminados</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= (int)$resumen['certificados'] ?></span>
        <span class="stat-label">Certificados</span>
    </div>
</section>

<h2>Mis cursos en progreso</h2>
<div class="progress-list">
    <?php foreach ($cursosActivos as $c): ?>
        <div class="progress-item">
            <div class="progress-item-header">
                <span><?= htmlspecialchars($c['titulo']) ?></span>
                <span><?= (int)$c['progreso'] ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-bar-fill" style="width: <?= (int)$c['progreso'] ?>%"></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
