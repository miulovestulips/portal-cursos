<?php
// app/controllers/ApiCursosController.php
// API propia REST — recurso "cursos" (módulo completo: consulta, alta,
// modificación y baja lógica). Las rutas identifican el recurso; la operación
// la define el método HTTP. Se puede probar completa desde Postman.
//
//   GET    /api/cursos         público      200 | 400
//   GET    /api/cursos/{id}    público      200 | 400 | 404
//   POST   /api/cursos         instructor   201 | 400 | 401 | 403
//   PUT    /api/cursos/{id}    dueño/admin  200 | 400 | 401 | 403 | 404
//   DELETE /api/cursos/{id}    dueño/admin  200 | 400 | 401 | 403 | 404   (baja lógica)
//
// Los errores nunca exponen SQL, rutas internas ni trazas: se registran con
// error_log y el cliente recibe un mensaje genérico (500).

class ApiCursosController extends Controller
{
    private CursoModel $cursos;
    private CategoriaModel $categorias;

    public function __construct(?CursoModel $cursos = null, ?CategoriaModel $categorias = null)
    {
        $this->cursos = $cursos ?? new CursoModel();
        $this->categorias = $categorias ?? new CategoriaModel();
    }

    public function listar(array $params = []): void
    {
        try {
            $errores = [];
            $f = [];

            foreach ($_GET as $clave => $valor) {
                if (!is_string($valor)) {
                    $errores[(string) $clave] = 'El parámetro debe ser un valor simple, no un arreglo.';
                }
            }
            if ($errores) {
                $this->jsonError(400, 'Parámetros de consulta inválidos.', $errores);
            }

            $q = $_GET['q'] ?? '';
            if (!is_string($q) || mb_strlen($q) > 100) {
                $errores['q'] = 'q debe ser texto de máximo 100 caracteres.';
            } else {
                $f['q'] = trim($q);
            }

            $instructor = $_GET['instructor'] ?? '';
            if (!is_string($instructor) || mb_strlen($instructor) > 100) {
                $errores['instructor'] = 'instructor debe ser texto de máximo 100 caracteres.';
            } else {
                $f['instructor'] = trim($instructor);
            }

            if (isset($_GET['categoria_id']) && $_GET['categoria_id'] !== '') {
                if (!ctype_digit((string) $_GET['categoria_id']) || (int) $_GET['categoria_id'] < 1) {
                    $errores['categoria_id'] = 'categoria_id debe ser un entero positivo.';
                } else {
                    $f['categoria_id'] = (int) $_GET['categoria_id'];
                }
            }
            foreach (['desde', 'hasta'] as $campo) {
                if (isset($_GET[$campo]) && $_GET[$campo] !== '') {
                    if (!is_string($_GET[$campo]) || !Validator::fecha($_GET[$campo])) {
                        $errores[$campo] = "$campo debe tener formato AAAA-MM-DD.";
                    } else {
                        $f[$campo] = $_GET[$campo];
                    }
                }
            }
            if (isset($_GET['orden']) && $_GET['orden'] !== '') {
                if (!in_array($_GET['orden'], ['recientes', 'mejor_calificados', 'mas_vendidos'], true)) {
                    $errores['orden'] = 'orden debe ser recientes, mejor_calificados o mas_vendidos.';
                } else {
                    $f['orden'] = $_GET['orden'];
                }
            }
            foreach (['limite' => 50, 'pagina' => 100000] as $campo => $max) {
                if (isset($_GET[$campo]) && $_GET[$campo] !== '') {
                    if (!ctype_digit((string) $_GET[$campo]) || (int) $_GET[$campo] < 1 || (int) $_GET[$campo] > $max) {
                        $errores[$campo] = "$campo debe ser un entero entre 1 y $max.";
                    } else {
                        $f[$campo] = (int) $_GET[$campo];
                    }
                }
            }
            if (!empty($f['desde']) && !empty($f['hasta']) && $f['desde'] > $f['hasta']) {
                $errores['desde'] = 'desde no puede ser posterior a hasta.';
            }
            if ($errores) {
                $this->jsonError(400, 'Parámetros de consulta inválidos.', $errores);
            }

            $lista = array_map([$this, 'presentar'], $this->cursos->listarActivos($f));
            $this->json(['data' => $lista], 200);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->jsonError(500, 'Error interno del servidor.');
        }
    }

    public function obtener(array $params): void
    {
        try {
            $id = $this->idDeRuta($params);
            $curso = $this->cursos->buscarPorId($id);
            if (!$curso) {
                $this->jsonError(404, 'Curso no encontrado.');
            }
            $this->json(['data' => $this->presentar($curso)], 200);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->jsonError(500, 'Error interno del servidor.');
        }
    }

    public function crear(array $params = []): void
    {
        try {
            [$datos, $errores] = $this->validar($this->body());
            if ($errores) {
                $this->jsonError(400, 'Datos inválidos.', $errores);
            }
            $datos['instructor_id'] = (int) $_SESSION['user_id'];
            $id = $this->cursos->crear($datos);

            header('Location: /api/cursos/' . $id);
            $this->json(['data' => $this->presentar($this->cursos->buscarPorId($id))], 201);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->jsonError(500, 'Error interno del servidor.');
        }
    }

    public function actualizar(array $params): void
    {
        try {
            $id = $this->idDeRuta($params);
            $curso = $this->cursoModificable($id);

            [$datos, $errores] = $this->validar($this->body());
            if ($errores) {
                $this->jsonError(400, 'Datos inválidos.', $errores);
            }
            $this->cursos->actualizar((int) $curso['id'], $datos);
            $this->json(['data' => $this->presentar($this->cursos->buscarPorId($id))], 200);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->jsonError(500, 'Error interno del servidor.');
        }
    }

    public function eliminar(array $params): void
    {
        try {
            $id = $this->idDeRuta($params);
            $curso = $this->cursoModificable($id);
            $this->cursos->darDeBaja((int) $curso['id']);
            $this->json(['mensaje' => 'El curso fue dado de baja.', 'data' => ['id' => $id, 'activo' => false]], 200);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->jsonError(500, 'Error interno del servidor.');
        }
    }

    // ---------- helpers ----------

    private function idDeRuta(array $params): int
    {
        $raw = (string) ($params['id'] ?? '');
        if (!ctype_digit($raw) || (int) $raw < 1) {
            $this->jsonError(400, 'El identificador del curso no es válido.');
        }
        return (int) $raw;
    }

    /** 404 si no existe (o ya está de baja); 403 si no es el dueño ni administrador. */
    private function cursoModificable(int $id): array
    {
        $curso = $this->cursos->buscarPorId($id);
        if (!$curso) {
            $this->jsonError(404, 'Curso no encontrado.');
        }
        $esDueno = (int) $curso['instructor_id'] === (int) ($_SESSION['user_id'] ?? 0);
        $esAdmin = ($_SESSION['user_role'] ?? '') === 'administrador';
        if (!$esDueno && !$esAdmin) {
            $this->jsonError(403, 'Solo el instructor dueño del curso puede modificarlo.');
        }
        return $curso;
    }

    /** @return array{0:array,1:array} [datos limpios, errores por campo] */
    private function validar(array $b): array
    {
        $e = [];
        $d = [];

        $titulo = is_string($b['titulo'] ?? null) ? trim($b['titulo']) : '';
        if (!Validator::longitud($titulo, 3, 150)) {
            $e['titulo'] = 'El título es obligatorio (3 a 150 caracteres).';
        }
        $d['titulo'] = $titulo;

        $desc = is_string($b['descripcion'] ?? null) ? trim($b['descripcion']) : '';
        if (!Validator::longitud($desc, 10, 5000)) {
            $e['descripcion'] = 'La descripción es obligatoria (10 a 5000 caracteres).';
        }
        $d['descripcion'] = $desc;

        $cat = $b['categoria_id'] ?? null;
        if (!is_int($cat) && !(is_string($cat) && ctype_digit($cat))) {
            $e['categoria_id'] = 'La categoría es obligatoria.';
        } elseif (!$this->categorias->existe((int) $cat)) {
            $e['categoria_id'] = 'La categoría indicada no existe.';
        }
        $d['categoria_id'] = (int) $cat;

        $costo = $b['costo'] ?? null;
        if (!is_numeric($costo) || (float) $costo < 0 || (float) $costo > 100000) {
            $e['costo'] = 'El costo debe ser un número entre 0 y 100000.';
        }
        $d['costo'] = round((float) $costo, 2);
        $d['gratis'] = $d['costo'] == 0.0;

        $niv = $b['total_niveles'] ?? null;
        if (!is_int($niv) && !(is_string($niv) && ctype_digit($niv))) {
            $e['total_niveles'] = 'La cantidad de niveles debe ser un entero entre 1 y 100.';
        } elseif ((int) $niv < 1 || (int) $niv > 100) {
            $e['total_niveles'] = 'La cantidad de niveles debe ser un entero entre 1 y 100.';
        }
        $d['total_niveles'] = (int) $niv;

        $img = $b['imagen'] ?? null;
        if ($img !== null && $img !== '') {
            if (!is_string($img) || !Validator::urlHttps($img)) {
                $e['imagen'] = 'La imagen debe ser una URL https válida.';
            } else {
                $d['imagen'] = $img;
            }
        }
        return [$d, $e];
    }

    private function presentar(array $c): array
    {
        return [
            'id'                    => (int) $c['id'],
            'titulo'                => $c['titulo'],
            'descripcion'           => $c['descripcion'],
            'imagen'                => $c['imagen'],
            'costo'                 => (float) $c['costo'],
            'gratis'                => (bool) $c['gratis'],
            'total_niveles'         => (int) $c['total_niveles'],
            'categoria_id'          => (int) $c['categoria_id'],
            'categoria'             => $c['categoria'],
            'instructor_id'         => (int) $c['instructor_id'],
            'instructor'            => $c['instructor'],
            'calificacion_promedio' => $c['calificacion_promedio'] !== null ? (float) $c['calificacion_promedio'] : null,
            'alumnos'               => (int) $c['alumnos'],
            'fecha_publicacion'     => date('c', strtotime($c['fecha_publicacion'])),
        ];
    }
}
