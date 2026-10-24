# Middleware — orden de ejecución y función de cada uno

El `Router` ejecuta, para cada petición, este orden fijo:

| # | Middleware | Alcance | Qué hace | Si falla |
|---|------------|---------|----------|----------|
| 1 | `CorsMiddleware` | Todo `/api/*` (se registra con `Router::before`, **antes** de buscar la ruta) | Valida el `Origin` contra la lista autorizada (`CORS_ALLOWED_ORIGINS` + el propio host). Agrega las cabeceras CORS. Responde el preflight `OPTIONS` con 204. | Origen no autorizado → `403` JSON |
| 2 | `AuthMiddleware` | Rutas protegidas | Valida que haya sesión y que el usuario **siga activo en la BD** (un bloqueo del administrador surte efecto de inmediato). Refresca el rol desde la BD. | API → `401` JSON · Portal → redirige a `/login` |
| 3 | `RoleMiddleware::para(...)` | Rutas protegidas | Valida que el rol de la sesión esté entre los permitidos de **esa** ruta. | API → `403` JSON · Portal → página 403 |
| 4 | Controlador | — | Orquesta: valida, llama al modelo, responde. | — |

**¿Por qué CORS va primero y fuera del match de rutas?** El navegador envía el preflight
`OPTIONS` *antes* de la petición real; como ninguna ruta se registra para `OPTIONS`, si CORS
corriera después del match el preflight terminaría en 404. Con `Router::before` se resuelve
siempre, y un origen no autorizado se rechaza antes de tocar autenticación o base de datos.

**¿Por qué Auth antes que Role?** Role lee el rol de la sesión; Auth garantiza que esa sesión
exista, que el usuario esté activo y que el rol esté actualizado.

Ejemplo (`public/index.php`):
```php
$router->before('/api', CorsMiddleware::class);
$router->delete('/api/cursos/{id}', $handler,
    [AuthMiddleware::class, RoleMiddleware::para('instructor', 'administrador')]);
```
Los middlewares implementan la interfaz `Middleware` (`handle(): void`) y, si la petición no
debe continuar, responden y terminan con `exit`.
