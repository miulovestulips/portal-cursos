<?php /** @var array $curso */ ?>
<article class="curso-detalle">
    <?php if (!empty($curso['imagen'])): ?>
        <img class="curso-detalle-img" src="<?= htmlspecialchars($curso['imagen']) ?>" alt="Portada del curso <?= htmlspecialchars($curso['titulo']) ?>">
    <?php endif; ?>

    <h1><?= htmlspecialchars($curso['titulo']) ?></h1>
    <p class="card-meta">
        <?= htmlspecialchars($curso['categoria']) ?> ·
        Instructor: <?= htmlspecialchars($curso['instructor']) ?> ·
        Publicado el <?= htmlspecialchars(Formato::fecha($curso['fecha_publicacion'])) ?>
    </p>
    <p><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>

    <ul class="detalle-datos">
        <li><strong>Niveles:</strong> <?= (int) $curso['total_niveles'] ?></li>
        <li><strong>Costo:</strong> <?= $curso['gratis'] ? 'Gratis' : htmlspecialchars(Formato::moneda($curso['costo'])) ?></li>
        <li><strong>Calificación:</strong> <?= $curso['calificacion_promedio'] !== null ? htmlspecialchars((string) $curso['calificacion_promedio']) . ' / 5' : 'Aún sin calificaciones' ?></li>
        <li><strong>Alumnos inscritos:</strong> <?= (int) $curso['alumnos'] ?></li>
    </ul>
</article>

<!-- Lógica para mostrar el botón correcto según el rol y sesión -->
<div class="curso-acciones" style="margin-top: 2rem;">
    <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- Si no ha iniciado sesión -->
        <a href="/login" class="btn btn-primary">Inicia sesión para inscribirte</a>
        
    <?php elseif ($_SESSION['user_role'] === 'estudiante'): ?>
        <!-- Si es estudiante, lo mandamos al checkout -->
        <a href="/checkout/<?= $curso['id'] ?>" class="btn btn-primary">Inscribirme a este curso</a>
        
    <?php elseif ($_SESSION['user_role'] === 'instructor' && $_SESSION['user_id'] == $curso['instructor_id']): ?>
        <!-- Si es el instructor dueño del curso -->
        <div class="alert alert-info">Eres el instructor de este curso.</div>
        <a href="/instructor/cursos" class="btn btn-secondary">Volver a mis cursos</a>
        
    <?php elseif ($_SESSION['user_role'] === 'administrador'): ?>
        <!-- Si es administrador -->
        <a href="/admin/dashboard" class="btn btn-secondary">Volver al panel de administrador</a>
        
    <?php else: ?>
        <!-- Si es un instructor viendo el curso de otro instructor -->
        <div class="alert alert-error">Solo los estudiantes pueden inscribirse a los cursos.</div>
    <?php endif; ?>
</div>