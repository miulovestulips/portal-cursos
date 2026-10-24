<?php
$errores = $errores ?? [];
$old = $old ?? [];
$val = fn(string $k) => htmlspecialchars((string) ($old[$k] ?? ''));
$err = fn(string $k) => isset($errores[$k]) ? '<small class="field-error" style="color:#dc2626">' . htmlspecialchars($errores[$k]) . '</small>' : '';
$sel = fn(string $k, string $v) => (($old[$k] ?? '') === $v) ? 'selected' : '';
?>
<h1>Crear cuenta</h1>

<?php if ($errores): ?>
    <div class="alert alert-error">Revisa los campos marcados e inténtalo de nuevo.</div>
<?php endif; ?>

<form method="post" action="/registro" enctype="multipart/form-data" id="form-registro" novalidate>
    <label for="nombre_completo">Nombre completo</label>
    <input type="text" id="nombre_completo" name="nombre_completo" maxlength="150" value="<?= $val('nombre_completo') ?>" required>
    <?= $err('nombre_completo') ?>

    <label for="genero">Género</label>
    <select id="genero" name="genero" required>
        <option value="">Selecciona...</option>
        <option value="F" <?= $sel('genero', 'F') ?>>Femenino</option>
        <option value="M" <?= $sel('genero', 'M') ?>>Masculino</option>
        <option value="O" <?= $sel('genero', 'O') ?>>Otro</option>
    </select>
    <?= $err('genero') ?>

    <label for="fecha_nacimiento">Fecha de nacimiento</label>
    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= $val('fecha_nacimiento') ?>" required>
    <?= $err('fecha_nacimiento') ?>

    <label for="email">Correo electrónico</label>
    <input type="email" id="email" name="email" maxlength="150" value="<?= $val('email') ?>" required>
    <?= $err('email') ?>

    <label for="password">Contraseña</label>
    <input type="password" id="password" name="password" required minlength="8"
           title="Mínimo 8 caracteres, una mayúscula, un número y un carácter especial">
    <small id="password-hint">Mínimo 8 caracteres, con al menos una mayúscula, un número y un carácter especial.</small>
    <?= $err('password') ?>

    <label for="rol">Quiero registrarme como</label>
    <select id="rol" name="rol" required>
        <option value="estudiante" <?= $sel('rol', 'estudiante') ?>>Estudiante</option>
        <option value="instructor" <?= $sel('rol', 'instructor') ?>>Instructor</option>
    </select>
    <?= $err('rol') ?>

    <label for="avatar">Foto de perfil (avatar)</label>
    <input type="file" id="avatar" name="avatar" accept="image/*" required>
    <?= $err('avatar') ?>

    <button type="submit" class="btn btn-primary">Crear cuenta</button>
</form>
