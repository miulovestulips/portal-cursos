<?php
// app/controllers/AuthController.php
// Registro, login (con bloqueo al 3er intento fallido), perfil y logout.
// Toda validación se hace en el SERVIDOR (el JS del cliente es solo UX).

class AuthController extends Controller
{
    private UsuarioModel $usuarios;
    private AuthService $auth;
    private ImagenService $imagenes;

    public function __construct(?UsuarioModel $usuarios = null, ?AuthService $auth = null, ?ImagenService $imagenes = null)
    {
        $this->usuarios = $usuarios ?? new UsuarioModel();
        $this->auth     = $auth ?? new AuthService($this->usuarios);
        $this->imagenes = $imagenes ?? new ImagenService();
    }

    public function mostrarLogin(): void
    {
        $this->view('auth/login');
    }

    public function procesarLogin(): void
    {
        $email = trim((string) $this->input('email', ''));
        $password = (string) $this->input('password', '');

        if ($email === '' || $password === '') {
            $this->redirect('login?error=campos_requeridos');
        }

        $r = $this->auth->autenticar($email, $password);
        if (!$r['ok']) {
            $this->redirect('login?error=' . $r['codigo']);
        }

        AuthService::iniciarSesion($r['usuario']);
        $this->redirect(AuthService::rutaInicio($r['usuario']['rol']));
    }

    public function mostrarRegistro(): void
    {
        $this->view('auth/registro', ['errores' => [], 'old' => []]);
    }

    public function procesarRegistro(): void
    {
        $datos = [
            'nombre_completo'  => trim((string) $this->input('nombre_completo', '')),
            'genero'           => (string) $this->input('genero', ''),
            'fecha_nacimiento' => (string) $this->input('fecha_nacimiento', ''),
            'email'            => mb_strtolower(trim((string) $this->input('email', ''))),
            'rol'              => (string) $this->input('rol', ''),
        ];
        $password = (string) $this->input('password', '');

        $errores = $this->validarDatosPersona($datos);
        if (!in_array($datos['rol'], ['estudiante', 'instructor'], true)) {
            $errores['rol'] = 'Selecciona un rol válido.'; // el rol administrador NO se puede auto-registrar
        }
        if (!Validator::password($password)) {
            $errores['password'] = 'Debe tener 8+ caracteres, una mayúscula, un número y un carácter especial.';
        }
        if (!isset($errores['email']) && $this->usuarios->emailExiste($datos['email'])) {
            $errores['email'] = 'Ese correo ya está registrado.';
        }
        if (empty($_FILES['avatar']['name'])) {
            $errores['avatar'] = 'La imagen de avatar es obligatoria.';
        }

        if (!$errores) {
            try {
                $datos['avatar'] = $this->imagenes->guardar($_FILES['avatar'], 'avatares');
            } catch (InvalidArgumentException $e) {
                $errores['avatar'] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('Error al subir avatar: ' . $e->getMessage());
                $errores['avatar'] = 'No se pudo subir la imagen. Inténtalo de nuevo.';
            }
        }

        if ($errores) {
            http_response_code(422);
            $this->view('auth/registro', ['errores' => $errores, 'old' => $datos]);
            return;
        }

        $datos['password'] = $password;
        $this->usuarios->crear($datos);
        $this->redirect('login?ok=registrado');
    }

    // ---------- Perfil (los 3 roles) ----------

    public function mostrarPerfil(): void
    {
        $usuario = $this->usuarios->buscarPorId((int) $_SESSION['user_id']);
        $this->view('auth/perfil', ['usuario' => $usuario, 'errores' => [], 'ok' => !empty($_GET['ok'])]);
    }

    public function actualizarPerfil(): void
    {
        $id = (int) $_SESSION['user_id']; // el id sale de la sesión, nunca del formulario
        $actual = $this->usuarios->buscarPorId($id);

        $datos = [
            'nombre_completo'  => trim((string) $this->input('nombre_completo', '')),
            'genero'           => (string) $this->input('genero', ''),
            'fecha_nacimiento' => (string) $this->input('fecha_nacimiento', ''),
            'email'            => mb_strtolower(trim((string) $this->input('email', ''))),
        ];
        $errores = $this->validarDatosPersona($datos);
        if (!isset($errores['email']) && $this->usuarios->emailExiste($datos['email'], $id)) {
            $errores['email'] = 'Ese correo ya pertenece a otra cuenta.';
        }

        // Cambio de contraseña (opcional): exige la actual
        $nueva = (string) $this->input('password', '');
        if ($nueva !== '') {
            $actualIngresada = (string) $this->input('password_actual', '');
            if (!password_verify($actualIngresada, $actual['password_hash'])) {
                $errores['password_actual'] = 'La contraseña actual no es correcta.';
            }
            if (!Validator::password($nueva)) {
                $errores['password'] = 'Debe tener 8+ caracteres, una mayúscula, un número y un carácter especial.';
            }
            $datos['password'] = $nueva;
        }

        if (!$errores && !empty($_FILES['avatar']['name'])) {
            try {
                $datos['avatar'] = $this->imagenes->guardar($_FILES['avatar'], 'avatares');
            } catch (InvalidArgumentException $e) {
                $errores['avatar'] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('Error al subir avatar: ' . $e->getMessage());
                $errores['avatar'] = 'No se pudo subir la imagen. Inténtalo de nuevo.';
            }
        }

        if ($errores) {
            http_response_code(422);
            $this->view('auth/perfil', ['usuario' => array_merge($actual, $datos), 'errores' => $errores, 'ok' => false]);
            return;
        }

        $this->usuarios->actualizarPerfil($id, $datos);
        $_SESSION['user_name'] = $datos['nombre_completo'];
        $this->redirect('perfil?ok=1');
    }

    public function logout(): void
    {
        AuthService::cerrarSesion();
        $this->redirect('login');
    }

    private function validarDatosPersona(array $d): array
    {
        $e = [];
        if (!Validator::longitud($d['nombre_completo'], 3, 150)) {
            $e['nombre_completo'] = 'El nombre debe tener entre 3 y 150 caracteres.';
        }
        if (!in_array($d['genero'], ['F', 'M', 'O'], true)) {
            $e['genero'] = 'Selecciona un género.';
        }
        if (!Validator::fechaNacimiento($d['fecha_nacimiento'])) {
            $e['fecha_nacimiento'] = 'Fecha de nacimiento no válida.';
        }
        if (!Validator::email($d['email'])) {
            $e['email'] = 'Correo electrónico no válido.';
        }
        return $e;
    }
}
