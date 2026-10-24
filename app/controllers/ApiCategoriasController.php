<?php
// app/controllers/ApiCategoriasController.php
// GET /api/categorias -> lista de categorías (público, 200)

class ApiCategoriasController extends Controller
{
    private CategoriaModel $categorias;

    public function __construct(?CategoriaModel $categorias = null)
    {
        $this->categorias = $categorias ?? new CategoriaModel();
    }

    public function listar(array $params = []): void
    {
        try {
            $this->json(['data' => $this->categorias->listar()], 200);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->jsonError(500, 'Error interno del servidor.');
        }
    }
}
