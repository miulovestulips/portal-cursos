<?php
// app/core/RoleMiddleware.php
// Middleware OBLIGATORIO de control de acceso, parte 2: valida el ROL.
// Se configura por ruta:  RoleMiddleware::para('administrador')
//                         RoleMiddleware::para('instructor', 'administrador')
// Se ejecuta DESPUÉS de AuthMiddleware (que garantiza sesión y rol vigentes).

class RoleMiddleware implements Middleware
{
    private array $roles;

    public function __construct(string ...$roles)
    {
        $this->roles = $roles;
    }

    public static function para(string ...$roles): self
    {
        return new self(...$roles);
    }

    public function handle(): void
    {
        $rol = $_SESSION['user_role'] ?? null;
        if ($rol !== null && in_array($rol, $this->roles, true)) {
            return;
        }

        if (Http::esApi()) {
            Http::error(403, 'No tienes permiso para realizar esta operación.');
        }
        http_response_code(403);
        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/public/403.php';
        require __DIR__ . '/../views/layout/footer.php';
        exit;
    }
}
