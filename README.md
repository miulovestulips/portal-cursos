# Portal de Cursos — Segunda Entrega (PHP puro + MVC + API REST)

## Requisitos
- PHP 8.1+ con extensiones `pdo_mysql`, `curl` y `fileinfo`
- MySQL 8 / MariaDB 10.4+

## Cómo correrlo
1. Crear la BD con datos iniciales (**recrea la base `portal_cursos`**):
   ```bash
   mysql -u root -p < docs/schema.sql
   ```
2. Configuración: copia `.env.example` a `.env` y ajusta `DB_*` (y, si ya tienes cuenta,
   las credenciales de Cloudinary). `.env` está en `.gitignore`: nunca se sube al repo.
3. Servidor embebido de PHP desde la raíz del proyecto:
   ```bash
   php -S localhost:8000 -t public
   ```
4. Abrir `http://localhost:8000`.

### Usuarios de prueba
| Rol | Correo | Contraseña |
|-----|--------|------------|
| Administrador | admin@portal.com | Admin#2026 |
| Instructor | ana.torres@portal.com | Demo#2026 |
| Estudiante | maria.lopez@portal.com | Demo#2026 |

## Qué incluye esta entrega
- **API propia REST** (`/api/cursos`, `/api/categorias`, `/api/sesiones`) con JSON, códigos
  de estado correctos, validación en servidor y manejo de errores. Contrato en `docs/contrato_api.md`.
- **Colección de Postman** en `docs/postman/` (casos de éxito y de error, con pruebas automáticas).
- **MVC con POO** en PHP puro: modelos (`UsuarioModel`, `CursoModel`, `CategoriaModel`) con todo el SQL;
  controladores sin consultas; conexión en `Database` y modelos con la conexión inyectable (`BaseModel`).
- **Registro, login, perfil** por rol: contraseña cifrada (bcrypt), bloqueo al 3er intento fallido,
  avatar, validación en servidor.
- **Middlewares**: CORS explícito → control de acceso (sesión + usuario activo) → rol. Ver `docs/middleware.md`.
- **API de terceros: Cloudinary** (imágenes de avatar y cursos), consumida desde el controlador;
  credenciales en `.env`.
- **Cliente con fetch/promesas**: home (pestañas), buscador con filtros y baja lógica de cursos,
  todo sin recargar y con mensajes de error visibles (`public/js/api.js`, `public/js/app.js`).

## Pendiente para la 3ra entrega
Niveles/videos/contenido, inscripción y pago, certificado, comentarios, mensajería, Kardex y ventas
con datos reales, módulo de administrador (categorías, bloqueo/desbloqueo, comentarios),
inyección de dependencias por contenedor, documentación final y hosting (opcional).
