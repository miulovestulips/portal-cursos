<?php
// app/controllers/PublicController.php
// Pantallas visibles sin sesión: home, buscador, detalle de curso (solo lectura).
// Las listas de cursos del home y del buscador las carga el CLIENTE con fetch
// contra la API propia (/api/cursos), sin recargar la página.

class PublicController extends Controller
{
    private CursoModel $cursos;
    private CategoriaModel $categoriasModel;

    public function __construct(?CursoModel $cursos = null, ?CategoriaModel $categorias = null)
    {
        $this->cursos = $cursos ?? new CursoModel();
        $this->categoriasModel = $categorias ?? new CategoriaModel();
    }

    public function home(array $params = []): void
    {
        $categorias = $this->categoriasModel->listar();
        $this->view('public/home', compact('categorias'));
    }

    public function buscador(array $params = []): void
    {
        $categorias = $this->categoriasModel->listar();
        $filtros = [
            'q'            => (string) $this->input('q', ''),
            'categoria_id' => (string) $this->input('categoria_id', ''),
            'instructor'   => (string) $this->input('instructor', ''),
            'desde'        => (string) $this->input('desde', ''),
            'hasta'        => (string) $this->input('hasta', ''),
        ];
        $this->view('public/buscador', compact('categorias', 'filtros'));
    }

    public function detalleCurso(array $params): void
    {
        $id = (string) ($params['id'] ?? '');
        $curso = ctype_digit($id) ? $this->cursos->buscarPorId((int) $id) : null;
        if (!$curso) {
            $this->notFound(); // no existe o está dado de baja
        }
        $this->view('public/detalle_curso', compact('curso'));
    }
}
