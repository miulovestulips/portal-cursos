<?php
// public/index.php
// Front Controller: única puerta de entrada de la aplicación.
// Todas las peticiones (portal y API) pasan por aquí y se enrutan.

require_once __DIR__ . '/../config/config.php';

set_exception_handler(function (Throwable $e) {
    error_log('[portal] ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    if (Http::esApi()) {
        Http::error(500, 'Error interno del servidor.');
    }
    http_response_code(500);
      echo '<h1>Error interno</h1>';
    echo '<p><strong>Mensaje:</strong> ' . $e->getMessage() . '</p>';
    echo '<p><strong>Archivo:</strong> ' . $e->getFile() . ' en línea ' . $e->getLine() . '</p>';
    exit;
});

$router = new Router();

$router->before('/api', CorsMiddleware::class);

$auth          = [AuthMiddleware::class];
$soloEstud     = [AuthMiddleware::class, RoleMiddleware::para('estudiante')];
$soloInstr     = [AuthMiddleware::class, RoleMiddleware::para('instructor')];
$soloAdmin     = [AuthMiddleware::class, RoleMiddleware::para('administrador')];
$instrOAdmin   = [AuthMiddleware::class, RoleMiddleware::para('instructor', 'administrador')];

// ---------- PÚBLICO (sin login) ----------
$router->get('/', function ($p) { (new PublicController())->home($p); });
$router->get('/buscador', function ($p) { (new PublicController())->buscador($p); });
$router->get('/curso/{id}', function ($p) { (new PublicController())->detalleCurso($p); });

// ---------- AUTENTICACIÓN Y PERFIL ----------
$router->get('/login', function ($p) { (new AuthController())->mostrarLogin(); });
$router->post('/login', function ($p) { (new AuthController())->procesarLogin(); });
$router->get('/registro', function ($p) { (new AuthController())->mostrarRegistro(); });
$router->post('/registro', function ($p) { (new AuthController())->procesarRegistro(); });
$router->get('/logout', function ($p) { (new AuthController())->logout(); });
$router->get('/perfil', function ($p) { (new AuthController())->mostrarPerfil(); }, $auth);
$router->post('/perfil', function ($p) { (new AuthController())->actualizarPerfil(); }, $auth);

// ---------- ESTUDIANTE (sesión + rol estudiante) ----------
$router->get('/estudiante/dashboard', function ($p) { (new EstudianteController())->dashboard(); }, $soloEstud);
$router->get('/estudiante/kardex', function ($p) { (new EstudianteController())->kardex(); }, $soloEstud);

// Mensajería (AQUÍ VAN, UNA SOLA VEZ)
$router->get('/estudiante/mensajes', function ($p) { (new EstudianteController())->mensajes(); }, $soloEstud);
$router->get('/estudiante/chat/{id}', function ($p) { (new EstudianteController())->chat($p); }, $soloEstud);
$router->post('/estudiante/chat/{id}', function ($p) { (new EstudianteController())->chat($p); }, $soloEstud);

// ---------- CHECKOUT Y COMPRAS (sesión + rol estudiante) ----------
$router->get('/checkout/{id}', function ($p) { (new CheckoutController())->mostrarCheckout($p); }, $soloEstud);
$router->post('/checkout/{id}/pagar', function ($p) { (new CheckoutController())->procesarPago($p); }, $soloEstud);
$router->post('/checkout/{id}/paypal', function ($p) { (new CheckoutController())->pagarConPayPal($p); }, $soloEstud);
$router->get('/checkout/{id}/capturar', function ($p) { (new CheckoutController())->capturarPago($p); }, $soloEstud);

// ---------- INSTRUCTOR (sesión + rol instructor) ----------
$router->get('/instructor/dashboard', function ($p) { (new InstructorController())->dashboard(); }, $soloInstr);
$router->get('/instructor/cursos', function ($p) { (new InstructorController())->misCursos(); }, $soloInstr);
$router->get('/instructor/cursos/nuevo', function ($p) { (new InstructorController())->nuevoCurso(); }, $soloInstr);
$router->post('/instructor/cursos', function ($p) { (new InstructorController())->guardarCurso(); }, $soloInstr);
$router->get('/instructor/ventas', function ($p) { (new InstructorController())->ventasResumen(); }, $soloInstr);
$router->get('/instructor/ventas/{id}', function ($p) { (new InstructorController())->ventasDetalle($p); }, $soloInstr);

// ---------- ADMINISTRADOR (sesión + rol administrador) ----------
$router->get('/admin/dashboard', function ($p) { (new AdminController())->dashboard(); }, $soloAdmin);
$router->get('/admin/categorias', function ($p) { (new AdminController())->categorias(); }, $soloAdmin);
$router->get('/admin/usuarios', function ($p) { (new AdminController())->usuarios(); }, $soloAdmin);
$router->get('/admin/reportes/usuarios', function ($p) { (new AdminController())->reporteUsuarios(); }, $soloAdmin);

$router->get('/instructor/mensajes', function ($p) { (new InstructorController())->mensajes(); }, $soloInstr);
$router->get('/instructor/chat/{id}/{estudiante_id}', function ($p) { (new InstructorController())->chat($p); }, $soloInstr);
$router->post('/instructor/chat/{id}/{estudiante_id}', function ($p) { (new InstructorController())->chat($p); }, $soloInstr);

// ---------- API PROPIA (REST, JSON) ----------
$router->post('/api/sesiones', function ($p) { (new ApiSesionesController())->crear($p); });
$router->delete('/api/sesiones', function ($p) { (new ApiSesionesController())->eliminar($p); });

$router->get('/api/categorias', function ($p) { (new ApiCategoriasController())->listar($p); });

$router->get('/api/cursos', function ($p) { (new ApiCursosController())->listar($p); });
$router->get('/api/cursos/{id}', function ($p) { (new ApiCursosController())->obtener($p); });
$router->post('/api/cursos', function ($p) { (new ApiCursosController())->crear($p); }, $soloInstr);
$router->put('/api/cursos/{id}', function ($p) { (new ApiCursosController())->actualizar($p); }, $instrOAdmin);
$router->delete('/api/cursos/{id}', function ($p) { (new ApiCursosController())->eliminar($p); }, $instrOAdmin);

// ---------- EJECUTAR EL ROUTER (SIEMPRE AL FINAL) ----------
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);