<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;700&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
<header class="navbar">
    <a class="navbar-brand" href="/">🌸 Portal de Cursos</a>
    <nav class="navbar-links">
        <a href="/buscador">Buscar</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['user_role'] === 'estudiante'): ?>
                <a href="/estudiante/dashboard">Mi panel</a>
                <a href="/estudiante/kardex">Kardex</a>
                <a href="/estudiante/mensajes">Mensajes 💬</a>
            <?php elseif ($_SESSION['user_role'] === 'instructor'): ?>
                <a href="/instructor/dashboard">Mi panel</a>
                <a href="/instructor/cursos">Mis cursos</a>
                <a href="/instructor/ventas">Ventas</a>
                <a href="/instructor/mensajes">Mensajes 💬</a> <!-- ENLACE AGREGADO AQUÍ -->
            <?php elseif ($_SESSION['user_role'] === 'administrador'): ?>
                <a href="/admin/dashboard">Panel admin</a>
                <a href="/admin/usuarios">Usuarios</a>
                <a href="/admin/categorias">Categorías</a>
            <?php endif; ?>
            <a href="/perfil">Mi perfil</a>
            <span class="navbar-user">Hola, <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
            <a href="/logout">Salir</a>
        <?php else: ?>
            <a href="/login">Iniciar sesión</a>
            <a href="/registro">Registrarse</a>
        <?php endif; ?>
    </nav>
</header>
<main class="main-content">