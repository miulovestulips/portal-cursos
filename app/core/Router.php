<?php
// app/core/Router.php
// Router mínimo: mapea "ruta" + "método HTTP" a una acción de controlador.
//
// ORDEN DE EJECUCIÓN de una petición (ver docs/middleware.md):
//   1) Middlewares por prefijo (before): p. ej. CORS en /api. Corren ANTES de
//      buscar la ruta, por eso el preflight OPTIONS se resuelve aquí.
//   2) Match de ruta + método. Si la ruta existe con otro método => 405.
//   3) Middlewares de la ruta, en el orden declarado (Auth -> Role).
//   4) Controlador.
// Rutas /api/* inexistentes responden JSON (404/405), no HTML.

class Router
{
    private array $routes = [];
    private array $prefixMiddlewares = [];

    public function add(string $method, string $path, callable $handler, array $middlewares = []): void
    {
        $this->routes[] = [
            'method'      => strtoupper($method),
            'path'        => $this->toRegex($path),
            'handler'     => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function get(string $path, callable $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, callable $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, callable $handler, array $middlewares = []): void
    {
        $this->add('PUT', $path, $handler, $middlewares);
    }

    public function patch(string $path, callable $handler, array $middlewares = []): void
    {
        $this->add('PATCH', $path, $handler, $middlewares);
    }

    public function delete(string $path, callable $handler, array $middlewares = []): void
    {
        $this->add('DELETE', $path, $handler, $middlewares);
    }

    /** Middleware que corre antes del match para todo path que empiece con $prefix. */
    public function before(string $prefix, $middleware): void
    {
        $this->prefixMiddlewares[] = [rtrim($prefix, '/'), $middleware];
    }

    private function toRegex(string $path): string
    {
        $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /** Acepta nombre de clase o instancia ya configurada (p. ej. RoleMiddleware::para('administrador')). */
    private function runMiddleware($middleware): void
    {
        $mw = is_string($middleware) ? new $middleware() : $middleware;
        if (!$mw instanceof Middleware) {
            throw new LogicException('Middleware inválido: debe implementar Middleware.');
        }
        $mw->handle();
    }

    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }
        $method = strtoupper($method);

        // 1) Middlewares por prefijo (CORS)
        foreach ($this->prefixMiddlewares as [$prefix, $mw]) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                $this->runMiddleware($mw);
            }
        }

        // 2) Match de ruta y método
        $metodosPermitidos = [];
        foreach ($this->routes as $route) {
            if (!preg_match($route['path'], $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $method) {
                $metodosPermitidos[] = $route['method'];
                continue;
            }
            $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);

            // 3) Middlewares de la ruta, en orden
            foreach ($route['middlewares'] as $mw) {
                $this->runMiddleware($mw);
            }

            // 4) Controlador
            call_user_func($route['handler'], $params);
            return;
        }

        if ($metodosPermitidos) {
            header('Allow: ' . implode(', ', array_unique($metodosPermitidos)));
            if (Http::esApi($path)) {
                Http::error(405, 'Método no permitido para este recurso.');
            }
            http_response_code(405);
            echo 'Método no permitido.';
            return;
        }

        if (Http::esApi($path)) {
            Http::error(404, 'Recurso no encontrado.');
        }
        http_response_code(404);
        require __DIR__ . '/../views/public/404.php';
    }
}
