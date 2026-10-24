<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0; font-size: 1.5rem;">Chat: <?= htmlspecialchars($curso['titulo']) ?></h1>
        <a href="/estudiante/mensajes" class="btn btn-secondary" style="padding: 5px 15px; font-size: 0.9em;">← Volver</a>
    </div>

    <!-- Caja de mensajes -->
    <div style="border: 1px solid #ddd; border-radius: 10px; padding: 20px; height: 400px; overflow-y: scroll; background: #fafafa; display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px;">
        
        <?php if (empty($mensajes)): ?>
            <div style="text-align: center; color: #888; margin-top: 50%; transform: translateY(-50%);">
                Aún no hay mensajes. ¡Envía el primero para iniciar la conversación!
            </div>
        <?php endif; ?>

        <?php foreach ($mensajes as $msg): ?>
            <?php $esMio = ($msg['emisor_id'] == $_SESSION['user_id']); ?>
            
            <div style="display: flex; flex-direction: column; align-items: <?= $esMio ? 'flex-end' : 'flex-start' ?>;">
                <small style="color: #888; margin-bottom: 3px; font-size: 0.8em;">
                    <?= $esMio ? 'Tú' : htmlspecialchars($msg['emisor_nombre']) ?> 
                    - <?= date('d/m/Y H:i', strtotime($msg['fecha_envio'])) ?>
                </small>
                <div style="padding: 10px 15px; border-radius: 15px; max-width: 70%; 
                            background: <?= $esMio ? '#d63384' : '#e9ecef' ?>; 
                            color: <?= $esMio ? 'white' : '#333' ?>;">
                    <?= nl2br(htmlspecialchars($msg['texto'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Formulario para enviar mensaje -->
    <form method="post" action="/estudiante/chat/<?= $curso['id'] ?>" style="display: flex; gap: 10px;">
        <input type="text" name="texto" placeholder="Escribe un mensaje..." required 
               style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 20px; outline: none;">
        <button type="submit" class="btn btn-primary" style="border-radius: 20px; padding: 0 20px;">
            Enviar
        </button>
    </form>
</div>