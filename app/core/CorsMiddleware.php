<?php
// app/core/CorsMiddleware.php
// Middleware OBLIGATORIO: CORS explícito para el consumo de la API propia.
//
// Reglas:
//  - Sin cabecera Origin (Postman, curl, mismo servidor): pasa sin tocar nada.
//  - Origin autorizado (lista CORS_ALLOWED_ORIGINS del .env, o el mismo host
//    que sirve la API): se devuelven las cabeceras CORS.
//  - Origin NO autorizado: 403 JSON, tanto en preflight como en la petición real.
//  - Preflight (OPTIONS) autorizado: 204 y se corta aquí; no llega al controlador.
// Corre ANTES del match de rutas (Router::before), por eso el preflight funciona
// aunque ninguna ruta esté registrada para OPTIONS.

class CorsMiddleware implements Middleware
{
    private array $origenesPermitidos;

    public function __construct(?array $origenes = null)
    {
        $this->origenesPermitidos = $origenes ?? array_values(array_filter(array_map(
            'trim',
            explode(',', CORS_ALLOWED_ORIGINS)
        )));
    }

    public function handle(): void
    {
        header('Vary: Origin');
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ($origin === '') {
            return;
        }

        if (!$this->estaPermitido($origin)) {
            Http::error(403, 'Origen no autorizado.');
        }

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Max-Age: 600');
            http_response_code(204);
            exit;
        }
    }

    private function estaPermitido(string $origin): bool
    {
        if (in_array($origin, $this->origenesPermitidos, true)) {
            return true;
        }
        // El propio portal (mismo host:puerto) siempre puede llamar a su API.
        $host = parse_url($origin, PHP_URL_HOST);
        $port = parse_url($origin, PHP_URL_PORT);
        $hostOrigen = $host . ($port ? ':' . $port : '');
        return $hostOrigen !== '' && isset($_SERVER['HTTP_HOST']) && strcasecmp($hostOrigen, $_SERVER['HTTP_HOST']) === 0;
    }
}
