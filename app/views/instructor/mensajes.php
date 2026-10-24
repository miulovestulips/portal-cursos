<h1>Mensajes de mis Alumnos</h1>
<p class="card-meta">Selecciona un alumno para responder sus dudas.</p>

<div class="lista-chats" style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
    <?php if (empty($alumnos)): ?>
        <div class="alert alert-info">Aún no tienes alumnos inscritos en tus cursos.</div>
    <?php else: ?>
        <?php foreach ($alumnos as $al): ?>
            <a href="/instructor/chat/<?= $al['curso_id'] ?>/<?= $al['estudiante_id'] ?>" style="text-decoration: none; color: inherit;">
                <div class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border: 1px solid #b6d4ff; border-radius: 10px; background: white;">
                    <div>
                        <h3 style="margin: 0; color: #0056b3;"><?= htmlspecialchars($al['nombre_estudiante']) ?></h3>
                        <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9em;">
                            Curso: <strong><?= htmlspecialchars($al['titulo_curso']) ?></strong>
                        </p>
                    </div>
                    <div style="background: #0056b3; color: white; padding: 8px 15px; border-radius: 20px; font-size: 0.9em;">
                        Responder
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>