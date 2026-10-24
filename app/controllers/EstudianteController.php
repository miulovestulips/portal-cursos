<?php
// app/controllers/EstudianteController.php
// Pantallas del rol "estudiante": dashboard, kardex (con filtros), mensajes.

class EstudianteController extends Controller
{
    private InscripcionModel $inscripciones;
    private CursoModel $cursos;

    public function __construct()
    {
        $this->inscripciones = new InscripcionModel();
        $this->cursos = new CursoModel();
    }

    public function dashboard(): void
    {
        // TODO (3ra entrega): reemplazar por datos reales del alumno en sesión
        $resumen = [
            'cursos_inscritos'  => 4,
            'cursos_terminados' => 1,
            'en_progreso'       => 3,
            'certificados'      => 1,
        ];
        $cursosActivos = [
            ['titulo' => 'Introducción a PHP', 'progreso' => 80],
            ['titulo' => 'MySQL desde Cero', 'progreso' => 45],
            ['titulo' => 'Fundamentos de UX/UI', 'progreso' => 10],
        ];
        $this->view('student/dashboard', compact('resumen', 'cursosActivos'));
    }

    public function kardex(): void
    {
        // Filtros esperados: rango de fechas, categoría, terminado/todos, activo/todos
        $filtros = [
            'desde'       => $this->input('desde'),
            'hasta'       => $this->input('hasta'),
            'categoria'   => $this->input('categoria', 'todas'),
            'terminados'  => $this->input('terminados', 'todos'),
            'activos'     => $this->input('activos', 'todos'),
        ];
        // TODO (3ra entrega): consultar KardexModel con estos filtros
        $registros = [
            ['curso' => 'Introducción a PHP', 'fecha_inscripcion' => '12 ago 2026', 'ultima_actividad' => '15 sep 2026', 'fecha_terminacion' => null, 'estado' => 'En progreso'],
            ['curso' => 'MySQL desde Cero', 'fecha_inscripcion' => '20 ago 2026', 'ultima_actividad' => '10 sep 2026', 'fecha_terminacion' => null, 'estado' => 'En progreso'],
            ['curso' => 'Marketing Digital 101', 'fecha_inscripcion' => '02 jul 2026', 'ultima_actividad' => '30 jul 2026', 'fecha_terminacion' => '30 jul 2026', 'estado' => 'Terminado'],
            ['curso' => 'Fundamentos de UX/UI', 'fecha_inscripcion' => '01 sep 2026', 'ultima_actividad' => '05 sep 2026', 'fecha_terminacion' => null, 'estado' => 'En progreso'],
        ];
        $this->view('student/kardex', compact('filtros', 'registros'));
    }

    // --------------------------------------------------------
    // MÓDULO DE MENSAJERÍA
    // --------------------------------------------------------
    
    // Muestra la lista de cursos para elegir a quién enviarle mensaje
    public function mensajes(): void
    {
        $sql = "SELECT i.curso_id, c.titulo as titulo_curso, u.nombre_completo as nombre_instructor 
                FROM inscripciones i 
                JOIN cursos c ON i.curso_id = c.id 
                JOIN usuarios u ON c.instructor_id = u.id 
                WHERE i.estudiante_id = :id";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $inscripciones = $stmt->fetchAll();

        $this->view('student/mensajes', compact('inscripciones'));
    }

    // Muestra el chat de un curso específico
    public function chat(array $params): void
    {
        $cursoId = (int) $params['id'];
        $curso = $this->cursos->buscarPorId($cursoId);
        
        if (!$curso) { $this->notFound(); }

        $mensajeModel = new MensajeModel();
        
        // Si el estudiante envía un mensaje
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $texto = trim($_POST['texto'] ?? '');
            if ($texto !== '') {
                $mensajeModel->enviar($cursoId, $_SESSION['user_id'], $curso['instructor_id'], $texto);
                $this->redirect('estudiante/chat/' . $cursoId);
            }
        }

        // Traemos la conversación con el instructor de ese curso
        $mensajes = $mensajeModel->obtenerConversacion($cursoId, $_SESSION['user_id'], $curso['instructor_id']);
        
        $this->view('student/chat', compact('curso', 'mensajes'));
    }
}