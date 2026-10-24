<?php
// app/controllers/ApiSesionesController.php
// API propia — recurso "sesiones" (permite autenticarse desde Postman/fetch).
// POST   /api/sesiones  -> iniciar sesión   (201 | 400 | 401 | 403)
// DELETE /api/sesiones  -> cerrar sesión    (200)

class ApiSesionesController extends Controller
{
    private AuthService $auth;

    public function __construct(?AuthService $auth = null)
    {
        $this->auth = $auth ?? new AuthService(new UsuarioModel());
    }

    public function crear(array $params = []): void
    {
        try {
            $b = $this->body();
            $email = is_string($b['email'] ?? null) ? trim($b['email']) : '';
            $password = is_string($b['password'] ?? null) ? $b['password'] : '';

            $errores = [];
            if ($email === '') {
                $errores['email'] = 'El correo es obligatorio.';
            }
            if ($password === '') {
                $errores['password'] = 'La contraseña es obligatoria.';
            }
            if ($errores) {
                $this->jsonError(400, 'Datos inválidos.', $errores);
            }

            $r = $this->auth->autenticar($email, $password);
            if (!$r['ok']) {
                if ($r['codigo'] === 'cuenta_bloqueada') {
                    $this->jsonError(403, 'La cuenta está deshabilitada. Contacta al administrador.');
                }
                $this->jsonError(401, 'Correo o contraseña incorrectos.');
            }

            AuthService::iniciarSesion($r['usuario']);
            $this->json(['data' => [
                'id'     => (int) $r['usuario']['id'],
                'nombre' => $r['usuario']['nombre_completo'],
                'rol'    => $r['usuario']['rol'],
            ]], 201);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->jsonError(500, 'Error interno del servidor.');
        }
    }

    public function eliminar(array $params = []): void
    {
        AuthService::cerrarSesion();
        $this->json(['mensaje' => 'Sesión cerrada.'], 200);
    }
}
