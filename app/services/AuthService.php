<?php
// app/services/AuthService.php
// Lógica de autenticación compartida por el portal (formulario) y la API
// (POST /api/sesiones). Recibe el modelo por constructor (inyección).

class AuthService
{
    private UsuarioModel $usuarios;

    public function __construct(UsuarioModel $usuarios)
    {
        $this->usuarios = $usuarios;
    }

    /**
     * @return array{ok:bool, codigo:string, usuario?:array}
     * codigo: ok | credenciales_invalidas | cuenta_bloqueada
     */
    public function autenticar(string $email, string $password): array
    {
        $u = $this->usuarios->buscarPorEmail($email);
        if (!$u) {
            return ['ok' => false, 'codigo' => 'credenciales_invalidas'];
        }
        if (!(int) $u['activo']) {
            return ['ok' => false, 'codigo' => 'cuenta_bloqueada'];
        }

        if (!password_verify($password, $u['password_hash'])) {
            $this->usuarios->incrementarIntentosFallidos((int) $u['id']);
            // Tercer intento fallido CONSECUTIVO => cuenta deshabilitada
            if (((int) $u['intentos_fallidos'] + 1) >= MAX_LOGIN_ATTEMPTS) {
                $this->usuarios->deshabilitar((int) $u['id']);
                return ['ok' => false, 'codigo' => 'cuenta_bloqueada'];
            }
            return ['ok' => false, 'codigo' => 'credenciales_invalidas'];
        }

        if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
            $this->usuarios->actualizarHash((int) $u['id'], password_hash($password, PASSWORD_DEFAULT));
        }
        $this->usuarios->resetearIntentosFallidos((int) $u['id']);

        return ['ok' => true, 'codigo' => 'ok', 'usuario' => $u];
    }

    public static function iniciarSesion(array $usuario): void
    {
        session_regenerate_id(true); // evita fijación de sesión
        $_SESSION['user_id']   = (int) $usuario['id'];
        $_SESSION['user_role'] = $usuario['rol'];
        $_SESSION['user_name'] = $usuario['nombre_completo'];
    }

    public static function cerrarSesion(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function rutaInicio(string $rol): string
    {
        return match ($rol) {
            'administrador' => 'admin/dashboard',
            'instructor'    => 'instructor/dashboard',
            default         => 'estudiante/dashboard',
        };
    }
}
