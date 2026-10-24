<?php
// app/core/Controller.php
// Controlador base: solo orquesta (recibe petición, llama al modelo, elige vista).
// No debe contener SQL ni lógica de acceso a datos.

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new RuntimeException("Vista no encontrada: $view");
        }
        require __DIR__ . '/../views/layout/header.php';
        require $viewPath;
        require __DIR__ . '/../views/layout/footer.php';
    }

    protected function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/public/404.php';
        exit;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }

    protected function json($data, int $status = 200): void
    {
        Http::json($data, $status);
    }

    protected function jsonError(int $status, string $mensaje, array $detalles = []): void
    {
        Http::error($status, $mensaje, $detalles);
    }

    /** Cuerpo de la petición (JSON o formulario). */
    protected function body(): array
    {
        return Http::body();
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}
