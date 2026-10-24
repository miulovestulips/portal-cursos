<?php
// app/core/AuthMiddleware.php
// Middleware OBLIGATORIO de control de acceso, parte 1: valida la SESIÓN.
// Además comprueba contra la BD que el usuario siga activo (si un
// administrador lo bloquea, su sesión deja de servir de inmediato) y
// refresca el rol desde la BD.
//
// Orden en rutas protegidas:  CORS (prefijo /api) -> Auth -> Role -> Controlador

class AuthMiddleware implements Middleware
{
    private UsuarioModel $usuarios;

    public function __construct(?UsuarioModel $usuarios = null)
    {
        $this->usuarios = $usuarios ?? new UsuarioModel();
    }

    public function handle(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $usuario = $userId > 0 ? $this->usuarios->buscarPorId($userId) : null;

        if ($usuario === null || !(int) $usuario['activo']) {
            if ($userId > 0) {
                $_SESSION = [];
                session_regenerate_id(true);
            }
            if (Http::esApi()) {
                Http::error(401, 'No autenticado. Inicia sesión para continuar.');
            }
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $_SESSION['user_role'] = $usuario['rol'];
        $_SESSION['user_name'] = $usuario['nombre_completo'];
    }
}
