# Propuesta de Estructura MVC y Arquitectura en 3 Capas

## Estructura de carpetas

```
portal-cursos/
├── public/              # Único punto accesible desde el navegador
│   ├── index.php        # Front Controller
│   ├── css/
│   ├── js/
│   └── uploads/
├── app/
│   ├── core/             # Router, Controller base, Database, Middlewares
│   ├── controllers/       # Un controlador por módulo/rol
│   ├── models/             # Acceso a datos (único lugar con SQL)
│   └── views/
│       ├── layout/         # header/footer compartidos
│       ├── public/
│       ├── auth/
│       ├── student/
│       ├── instructor/
│       └── admin/
├── config/
│   └── config.php         # Configuración + autoload
└── docs/                    # Documentación de la entrega
```

## Arquitectura en tres capas

| Capa | Tecnología | Responsabilidad | Dónde vive |
|------|------------|------------------|------------|
| **Presentación** | HTML, CSS, JavaScript (fetch) | Renderiza vistas, captura entradas, valida en cliente, consume la API propia | `app/views/`, `public/css`, `public/js` |
| **Lógica de negocio / Capa intermedia** | PHP puro, POO, patrón MVC | Enruta peticiones, aplica middleware, orquesta modelos, expone la API propia (JSON/REST) | `app/core/`, `app/controllers/`, `public/index.php` |
| **Datos** | MySQL (vía PDO) | Persistencia, integridad referencial | `app/models/`, `Database.php` |

**Punto donde se ubica la API propia:** dentro de la capa intermedia. Los
endpoints `/api/*` se enrutan por el mismo Front Controller (`public/index.php`)
y son atendidos por controladores dedicados (p. ej. `ApiCursosController`) que
solo llaman a los modelos y devuelven JSON — nunca acceden a la BD directamente.

## Recorrido de una petición (ejemplo: `GET /instructor/cursos`)

1. El navegador solicita `/instructor/cursos`.
2. `public/index.php` (Front Controller) recibe la petición y se la pasa al `Router`.
3. El `Router` encuentra la ruta registrada y ejecuta sus middlewares en orden:
   `AuthMiddleware` → valida que exista sesión activa.
4. Si pasa el middleware, se instancia `InstructorController` y se llama a `misCursos()`.
5. El controlador pide los datos a `CursoModel` (capa de modelo), que ejecuta la
   consulta contra MySQL a través de `Database::getConnection()`.
6. El controlador recibe el arreglo de datos y llama a `$this->view('instructor/mis_cursos', [...])`.
7. La vista (capa de presentación) recibe los datos ya listos y solo los muestra;
   no ejecuta ninguna consulta SQL.

## Recorrido de una petición a la API (ejemplo: `GET /api/cursos/1`)

1. El cliente (JavaScript, vía `fetch`) o Postman hace `GET /api/cursos/1`.
2. Pasa por `CorsMiddleware` (valida origen, agrega encabezados CORS).
3. `ApiCursosController::obtener()` valida el `id`, consulta el modelo y responde
   con `json_encode` + código de estado HTTP correspondiente (200/400/404/500).
