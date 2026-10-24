<?php
// app/controllers/InstructorController.php
// Pantallas del rol "instructor": dashboard, gestión de cursos, ventas (2 vistas).

class InstructorController extends Controller
{
    private CursoModel $cursos;
    private CategoriaModel $categorias;
    private ImagenService $imagenes;
    private InscripcionModel $inscripciones; // <-- AGREGADO

    public function __construct(?CursoModel $cursos = null, ?CategoriaModel $categorias = null, ?ImagenService $imagenes = null)
    {
        $this->cursos = $cursos ?? new CursoModel();
        $this->categorias = $categorias ?? new CategoriaModel();
        $this->imagenes = $imagenes ?? new ImagenService();
        $this->inscripciones = new InscripcionModel(); // <-- AGREGADO
    }

    public function dashboard(): void
    {
        $resumen = [
            'cursos_publicados' => 3,
            'alumnos_totales'   => 128,
            'ingresos_totales'  => '$18,450.00',
            'calificacion_prom' => '4.6 / 5',
        ];
        $this->view('instructor/dashboard', compact('resumen'));
    }

    public function misCursos(): void
    {
        $cursos = $this->cursos->listarPorInstructor((int) $_SESSION['user_id']);
        $this->view('instructor/mis_cursos', ['cursos' => $cursos, 'ok' => $_GET['ok'] ?? '']);
    }

    public function nuevoCurso(): void
    {
        $this->view('instructor/curso_form', [
            'categorias' => $this->categorias->listar(),
            'errores'    => [],
            'old'        => [],
        ]);
    }

    public function guardarCurso(): void
    {
        $d = [
            'titulo'        => trim((string) $this->input('titulo', '')),
            'descripcion'   => trim((string) $this->input('descripcion', '')),
            'categoria_id'  => (string) $this->input('categoria_id', ''),
            'costo'         => (string) $this->input('costo', '0'),
            'total_niveles' => (string) $this->input('total_niveles', ''),
        ];

        $errores = [];
        if (!Validator::longitud($d['titulo'], 3, 150)) {
            $errores['titulo'] = 'El título es obligatorio (3 a 150 caracteres).';
        }
        if (!Validator::longitud($d['descripcion'], 10, 5000)) {
            $errores['descripcion'] = 'La descripción es obligatoria (10 a 5000 caracteres).';
        }
        if (!ctype_digit($d['categoria_id']) || !$this->categorias->existe((int) $d['categoria_id'])) {
            $errores['categoria_id'] = 'Selecciona una categoría válida.';
        }
        if (!is_numeric($d['costo']) || (float) $d['costo'] < 0 || (float) $d['costo'] > 100000) {
            $errores['costo'] = 'El costo debe ser un número entre 0 y 100000.';
        }
        if (!ctype_digit($d['total_niveles']) || (int) $d['total_niveles'] < 1 || (int) $d['total_niveles'] > 100) {
            $errores['total_niveles'] = 'La cantidad de niveles debe estar entre 1 y 100.';
        }
        if (empty($_FILES['imagen']['name'])) {
            $errores['imagen'] = 'La imagen del curso es obligatoria.';
        }

        $imagen = null;
        if (!$errores) {
            try {
                $imagen = $this->imagenes->guardar($_FILES['imagen'], 'cursos');
            } catch (InvalidArgumentException $e) {
                $errores['imagen'] = $e->getMessage();
            } catch (Throwable $e) {
                error_log('Error al subir imagen de curso: ' . $e->getMessage());
                $errores['imagen'] = 'No se pudo subir la imagen. Inténtalo de nuevo.';
            }
        }

        if ($errores) {
            http_response_code(422);
            $this->view('instructor/curso_form', [
                'categorias' => $this->categorias->listar(),
                'errores'    => $errores,
                'old'        => $d,
            ]);
            return;
        }

        $costo = round((float) $d['costo'], 2);
        $this->cursos->crear([
            'instructor_id' => (int) $_SESSION['user_id'],
            'categoria_id'  => (int) $d['categoria_id'],
            'titulo'        => $d['titulo'],
            'descripcion'   => $d['descripcion'],
            'imagen'        => $imagen,
            'costo'         => $costo,
            'gratis'        => $costo == 0.0,
            'total_niveles' => (int) $d['total_niveles'],
        ]);
        $this->redirect('instructor/cursos?ok=creado');
    }

    public function ventasResumen(): void
    {
        $filtros = [
            'desde'     => $this->input('desde'),
            'hasta'     => $this->input('hasta'),
            'categoria' => $this->input('categoria', 'todas'),
            'activos'   => $this->input('activos', 'todos'),
        ];
        $reporte = [
            ['curso_id' => 1, 'titulo' => 'Introducción a PHP', 'alumnos' => 64, 'nivel_promedio' => '3.2 de 6', 'ingresos' => '$8,320.00'],
            ['curso_id' => 2, 'titulo' => 'MySQL desde Cero', 'alumnos' => 41, 'nivel_promedio' => '2.1 de 4', 'ingresos' => '$5,330.00'],
            ['curso_id' => 3, 'titulo' => 'APIs REST con PHP', 'alumnos' => 23, 'nivel_promedio' => '4.0 de 5', 'ingresos' => '$4,800.00'],
        ];
        $this->view('instructor/ventas_resumen', compact('filtros', 'reporte'));
    }

    public function ventasDetalle(array $params): void
    {
        $cursoId = $params['id'] ?? null;
        $detalle = [
            ['alumno' => 'María López', 'fecha_inscripcion' => '12 ago 2026', 'nivel' => '5 de 6', 'precio' => '$299.00', 'forma_pago' => 'Tarjeta'],
            ['alumno' => 'Jorge Peña', 'fecha_inscripcion' => '20 ago 2026', 'nivel' => '2 de 6', 'precio' => '$299.00', 'forma_pago' => 'PayPal'],
            ['alumno' => 'Sofía Cantú', 'fecha_inscripcion' => '02 sep 2026', 'nivel' => '6 de 6', 'precio' => '$0.00 (gratis)', 'forma_pago' => '—'],
        ];
        $this->view('instructor/ventas_detalle', compact('cursoId', 'detalle'));
    }

    // --------------------------------------------------------
    // MÓDULO DE MENSAJERÍA DEL INSTRUCTOR
    // --------------------------------------------------------

    public function mensajes(): void
    {
        $sql = "SELECT DISTINCT i.estudiante_id, c.id as curso_id, c.titulo as titulo_curso, u.nombre_completo as nombre_estudiante 
                FROM inscripciones i 
                JOIN cursos c ON i.curso_id = c.id 
                JOIN usuarios u ON i.estudiante_id = u.id 
                WHERE c.instructor_id = :instructor_id";
        
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['instructor_id' => $_SESSION['user_id']]);
        $alumnos = $stmt->fetchAll();

        $this->view('instructor/mensajes', compact('alumnos'));
    }

    public function chat(array $params): void
    {
        $cursoId = (int) $params['id'];
        $estudianteId = (int) $params['estudiante_id'];
        
        $curso = $this->cursos->buscarPorId($cursoId);
        if (!$curso) { $this->notFound(); }

        $mensajeModel = new MensajeModel();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $texto = trim($_POST['texto'] ?? '');
            if ($texto !== '') {
                $mensajeModel->enviar($cursoId, $_SESSION['user_id'], $estudianteId, $texto);
                $this->redirect('instructor/chat/' . $cursoId . '/' . $estudianteId);
            }
        }

        $mensajes = $mensajeModel->obtenerConversacion($cursoId, $_SESSION['user_id'], $estudianteId);
        
        $estudiante = (new UsuarioModel())->buscarPorId($estudianteId);

        $this->view('instructor/chat', compact('curso', 'mensajes', 'estudiante'));
    }
}