<?php
// app/core/Http.php
// Utilidades HTTP compartidas por Router, Middlewares y Controladores.

class Http
{
    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Respuesta de error uniforme: {"error": "...", "detalles": {...}} */
    public static function error(int $status, string $mensaje, array $detalles = []): void
    {
        $body = ['error' => $mensaje];
        if ($detalles) {
            $body['detalles'] = $detalles;
        }
        self::json($body, $status);
    }

    public static function esApi(?string $path = null): bool
    {
        $path = $path ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        return $path === '/api' || str_starts_with($path, '/api/');
    }

    /**
     * Cuerpo de la petición como arreglo. JSON si el Content-Type lo indica;
     * de lo contrario, formulario. JSON mal formado => 400 (la API no se cae).
     */
    public static function body(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $tipo = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($tipo, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            if ($raw === false || trim($raw) === '') {
                return $cache = [];
            }
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                self::error(400, 'El cuerpo de la petición no es un JSON válido.');
            }
            return $cache = $data;
        }
        return $cache = $_POST;
    }
}
