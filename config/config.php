<?php
// config/config.php
// Configuración global. Los valores sensibles (BD, Cloudinary) se leen del
// archivo .env, que está en .gitignore y NO se sube al repositorio.
// Copia .env.example a .env y ajusta los valores.

require_once __DIR__ . '/../app/core/Env.php';
Env::load(__DIR__ . '/../.env');

define('APP_NAME', 'Portal de Cursos');
define('BASE_URL', '/');// Ajustar si el proyecto corre dentro de una subcarpeta
define('APP_DEBUG', Env::get('APP_DEBUG', '0') === '1');

// --- Base de datos ---
define('DB_HOST', Env::get('DB_HOST', 'localhost'));
define('DB_NAME', Env::get('DB_NAME', 'portal_cursos'));
define('DB_USER', Env::get('DB_USER', 'root'));
define('DB_PASS', Env::get('DB_PASS', ''));

// --- Sesión ---
define('SESSION_NAME', 'portal_cursos_sid');

// --- Intentos fallidos de login ---
define('MAX_LOGIN_ATTEMPTS', 3);

// --- CORS: orígenes autorizados para consumir la API propia (separados por coma) ---
define('CORS_ALLOWED_ORIGINS', Env::get('CORS_ALLOWED_ORIGINS', 'http://localhost:8000,http://127.0.0.1:8000'));

// --- API de terceros: Cloudinary (imágenes) ---
define('CLOUDINARY_CLOUD_NAME', Env::get('CLOUDINARY_CLOUD_NAME', ''));
define('CLOUDINARY_API_KEY', Env::get('CLOUDINARY_API_KEY', ''));
define('CLOUDINARY_API_SECRET', Env::get('CLOUDINARY_API_SECRET', ''));

// --- Subida de archivos ---
define('UPLOAD_MAX_BYTES', 5 * 1024 * 1024);

session_name(SESSION_NAME);
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Autoload simple de clases (core, controllers, models, services)
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/core/' . $class . '.php',
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
        __DIR__ . '/../app/services/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
