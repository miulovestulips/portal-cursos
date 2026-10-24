<?php
$errores = $errores ?? [];
$u = $usuario;
$val = fn(string $k) => htmlspecialchars((string) ($u[$k] ?? ''));
$err = fn(string $k) => isset($errores[$k]) ? '<small class="field-error" style="color:#dc2626">' . htmlspecialchars($errores[$k]) . '</small>' : '';
$sel = fn(string $v) => (($u['genero'] ?? '') === $v) ? 'selected' : '';
?>
<h1>Mi perfil</h1>

<?php if (!empty($ok)): ?>
    <div class="alert alert-success">Tus datos se guardaron correctamente.</div>
<?php endif; ?>
<?php if ($errores): ?>
    <div class="alert alert-error">Revisa los campos marcados e inténtalo de nuevo.</div>
<?php endif; ?>

<p class="card-meta">
    Rol: <strong><?= $val('rol') ?></strong> ·
    Registrado el <?= htmlspecialchars(Formato::fecha($u['fecha_registro'] ?? null)) ?> ·
    Último cambio: <?= htmlspecialchars(Formato::fecha($u['fecha_ultimo_cambio'] ?? null)) ?>
</p>
<?php if (!empty($u['avatar'])): ?>
    <img class="avatar-grande" src="<?= $val('avatar') ?>" alt="Tu avatar">
<?php endif; ?>

<form method="post" action="/perfil" enctype="multipart/form-data" id="form-perfil" novalidate>
    <label for="nombre_completo">Nombre completo</label>
    <input type="text" id="nombre_completo" name="nombre_completo" maxlength="150" value="<?= $val('nombre_completo') ?>" required>
    <?= $err('nombre_completo') ?>

    <label for="genero">Género</label>
    <select id="genero" name="genero" required>
        <option value="F" <?= $sel('F') ?>>Femenino</option>
        <option value="M" <?= $sel('M') ?>>Masculino</option>
        <option value="O" <?= $sel('O') ?>>Otro</option>
    </select>
    <?= $err('genero') ?>

    <label for="fecha_nacimiento">Fecha de nacimiento</label>
    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= $val('fecha_nacimiento') ?>" required>
    <?= $err('fecha_nacimiento') ?>

    <label for="email">Correo electrónico (con el que inicias sesión)</label>
    <input type="email" id="email" name="email" maxlength="150" value="<?= $val('email') ?>" required>
    <?= $err('email') ?>

    <label for="avatar">Cambiar avatar (opcional)</label>
    <input type="file" id="avatar" name="avatar" accept="image/*">
    <?= $err('avatar') ?>

    <fieldset>
        <legend>Cambiar contraseña (opcional)</legend>
        <label for="password_actual">Contraseña actual</label>
        <input type="password" id="password_actual" name="password_actual" autocomplete="current-password">
        <?= $err('password_actual') ?>

        <label for="password">Nueva contraseña</label>
        <input type="password" id="password" name="password" autocomplete="new-password">
        <small>Mínimo 8 caracteres, con una mayúscula, un número y un carácter especial.</small>
        <?= $err('password') ?>
    </fieldset>

    <button type="submit" class="btn btn-primary">Guardar cambios</button>
</form>
