<h1>Iniciar sesión</h1>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-error">
        <?php
        $errores = [
            'campos_requeridos'    => 'Correo y contraseña son obligatorios.',
            'credenciales_invalidas' => 'Correo o contraseña incorrectos.',
            'cuenta_bloqueada'     => 'Tu cuenta fue deshabilitada por intentos fallidos. Contacta al administrador.',
        ];
        echo htmlspecialchars($errores[$_GET['error']] ?? 'Ocurrió un error.');
        ?>
    </div>
<?php endif; ?>
<?php if (!empty($_GET['ok'])): ?>
    <div class="alert alert-success">Registro exitoso, ya puedes iniciar sesión.</div>
<?php endif; ?>

<form method="post" action="/login" id="form-login" novalidate>
    <label for="email">Correo electrónico</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Contraseña</label>
    <input type="password" id="password" name="password" required>

    <button type="submit" class="btn btn-primary">Entrar</button>
</form>

<p>¿No tienes cuenta? <a href="/registro">Regístrate aquí</a></p>
