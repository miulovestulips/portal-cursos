<h1>Mis Mensajes</h1>
<p class="card-meta">Selecciona un curso para chatear con tu instructor.</p>

<div class="lista-chats" style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
    <?php if (empty($inscripciones)): ?>
        <div class="alert alert-info">Aún no estás inscrito en ningún curso.</div>
    <?php else: ?>
        <?php foreach ($inscripciones as $ins): ?>
            <a href="/estudiante/chat/<?= $ins['curso_id'] ?>" style="text-decoration: none; color: inherit;">
                <div class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border: 1px solid #ffb6c1; border-radius: 10px; background: white;">
                    <div>
                        <h3 style="margin: 0; color: #d63384;"><?= htmlspecialchars($ins['titulo_curso']) ?></h3>
                        <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9em;">
                            Instructor: <strong><?= htmlspecialchars($ins['nombre_instructor']) ?></strong>
                        </p>
                    </div>
                    <div style="background: #d63384; color: white; padding: 8px 15px; border-radius: 20px; font-size: 0.9em;">
                        Abrir Chat
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>