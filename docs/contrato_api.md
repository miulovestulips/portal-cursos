# Contrato de la API Propia — Portal de Cursos

API REST en PHP puro (sin frameworks). Recursos: **cursos**, **categorias**, **sesiones**.

- **Base URL:** `http://localhost:8000/api`
- **Formato:** todas las respuestas son JSON con `Content-Type: application/json; charset=utf-8`.
- **Cuerpo de peticiones (POST/PUT):** JSON con `Content-Type: application/json`.
- **Autenticación:** sesión por cookie. Se obtiene con `POST /api/sesiones` y se
  conserva en la cookie `portal_cursos_sid` (Postman la guarda automáticamente).
- **Convención de errores:** `{"error": "mensaje legible", "detalles": {"campo": "motivo"}}`
  (`detalles` solo aparece en errores de validación). Nunca se exponen consultas,
  rutas internas ni trazas.
- **Rutas:** identifican recursos (sustantivos); la operación la define el método HTTP.
- **Códigos usados:** 200, 201, 400, 401, 403, 404, 405, 500 (y 204 en el preflight CORS).

## Resumen

| Método | Ruta | Acceso | Éxito | Errores |
|--------|------|--------|-------|---------|
| GET | `/api/categorias` | público | 200 | 500 |
| GET | `/api/cursos` | público | 200 | 400, 500 |
| GET | `/api/cursos/{id}` | público | 200 | 400, 404, 500 |
| POST | `/api/cursos` | instructor | 201 | 400, 401, 403, 500 |
| PUT | `/api/cursos/{id}` | instructor dueño o administrador | 200 | 400, 401, 403, 404, 500 |
| DELETE | `/api/cursos/{id}` | instructor dueño o administrador | 200 | 400, 401, 403, 404, 500 |
| POST | `/api/sesiones` | público | 201 | 400, 401, 403, 500 |
| DELETE | `/api/sesiones` | público | 200 | — |

Cualquier ruta `/api/...` inexistente responde `404`; un método no permitido para una ruta existente responde `405` con la cabecera `Allow`.

---

## 1. GET /api/cursos
Lista los cursos **activos** (los dados de baja nunca aparecen).

**Parámetros de consulta (todos opcionales)**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| q | texto (≤100) | Búsqueda **parcial** por título |
| categoria_id | entero | Filtra por categoría |
| instructor | texto (≤100) | Búsqueda parcial por nombre del instructor |
| desde / hasta | AAAA-MM-DD | Rango de fecha de publicación |
| orden | `recientes` \| `mejor_calificados` \| `mas_vendidos` | Orden (default `recientes`) |
| limite | entero 1–50 | Tamaño de página (default 20) |
| pagina | entero ≥1 | Número de página (default 1) |

**Ejemplo:** `GET /api/cursos?q=php&orden=mejor_calificados&limite=10`

**200 OK**
```json
{
  "data": [
    {
      "id": 1,
      "titulo": "Introducción a PHP",
      "descripcion": "Aprende los fundamentos de PHP desde cero...",
      "imagen": null,
      "costo": 299,
      "gratis": false,
      "total_niveles": 6,
      "categoria_id": 1,
      "categoria": "IT & Software",
      "instructor_id": 2,
      "instructor": "Ana Torres",
      "calificacion_promedio": null,
      "alumnos": 1,
      "fecha_publicacion": "2026-08-10T10:00:00-06:00"
    }
  ]
}
```
**400 Bad Request** (parámetro inválido)
```json
{ "error": "Parámetros de consulta inválidos.", "detalles": { "desde": "desde debe tener formato AAAA-MM-DD." } }
```

## 2. GET /api/cursos/{id}
Detalle de un curso activo.

| Parámetro | Ubicación | Tipo | Obligatorio |
|-----------|-----------|------|-------------|
| id | ruta | entero ≥ 1 | Sí |

**200 OK** → `{ "data": { ...mismo objeto que en la lista... } }`
**400** → `{ "error": "El identificador del curso no es válido." }`
**404** → `{ "error": "Curso no encontrado." }` (no existe o está dado de baja)

## 3. POST /api/cursos
Crea un curso del instructor autenticado. **Requiere sesión con rol instructor.**

**Cuerpo (JSON)**

| Campo | Tipo | Obligatorio | Regla |
|-------|------|-------------|-------|
| titulo | texto | Sí | 3–150 caracteres |
| descripcion | texto | Sí | 10–5000 caracteres |
| categoria_id | entero | Sí | Debe existir |
| costo | número | Sí | 0–100000 (0 = gratis) |
| total_niveles | entero | Sí | 1–100 |
| imagen | texto | No | URL `https://` |

**Petición**
```http
POST /api/cursos
Content-Type: application/json

{ "titulo": "Git desde cero", "descripcion": "Control de versiones paso a paso.", "categoria_id": 1, "costo": 149.5, "total_niveles": 4 }
```
**201 Created** (cabecera `Location: /api/cursos/5`) → `{ "data": { ...curso creado... } }`
**400** → `{ "error": "Datos inválidos.", "detalles": { "titulo": "El título es obligatorio (3 a 150 caracteres)." } }`
**401** → `{ "error": "No autenticado. Inicia sesión para continuar." }`
**403** → `{ "error": "No tienes permiso para realizar esta operación." }` (rol distinto de instructor)

## 4. PUT /api/cursos/{id}
Reemplaza los datos de un curso. Mismo cuerpo y reglas que el POST. Solo el instructor dueño o un administrador.

**200 OK** → `{ "data": { ...curso actualizado... } }`
**400** (id o cuerpo inválido) · **401** · **403** (`"Solo el instructor dueño del curso puede modificarlo."` o rol no permitido) · **404** (`"Curso no encontrado."`)

## 5. DELETE /api/cursos/{id}
**Baja lógica**: el curso deja de mostrarse y de aparecer en búsquedas, pero se conserva para reportes de ventas y Kardex. Solo el dueño o un administrador.

**200 OK**
```json
{ "mensaje": "El curso fue dado de baja.", "data": { "id": 5, "activo": false } }
```
**400** · **401** · **403** · **404** (no existe o ya estaba dado de baja)

## 6. GET /api/categorias
**200 OK** → `{ "data": [ { "id": 1, "nombre": "IT & Software", "descripcion": "..." } ] }`

## 7. POST /api/sesiones
Inicia sesión (establece la cookie de sesión).

**Petición**
```json
{ "email": "ana.torres@portal.com", "password": "Demo#2026" }
```
**201 Created** → `{ "data": { "id": 2, "nombre": "Ana Torres", "rol": "instructor" } }`
**400** → faltan campos (`detalles` por campo) o JSON mal formado
**401** → `{ "error": "Correo o contraseña incorrectos." }`
**403** → `{ "error": "La cuenta está deshabilitada. Contacta al administrador." }` (el tercer intento fallido consecutivo deshabilita la cuenta; solo un administrador la reactiva)

## 8. DELETE /api/sesiones
Cierra la sesión. **200** → `{ "mensaje": "Sesión cerrada." }`

---

## Seguridad y CORS
- Toda la información recibida se **valida en el servidor**, con independencia del cliente.
- Una petición mal formada (JSON inválido, tipos incorrectos) responde `400`; la API no se interrumpe.
- **CORS explícito** (`CorsMiddleware`): solo se permiten los orígenes de `CORS_ALLOWED_ORIGINS` (`.env`) y el propio host del portal. Un `Origin` no autorizado recibe `403 {"error":"Origen no autorizado."}`; el preflight `OPTIONS` autorizado recibe `204` con `Access-Control-Allow-Methods/Headers`. Las peticiones sin cabecera `Origin` (Postman, curl) pasan.
- Colección de Postman sincronizada con este contrato: `docs/postman/PortalCursos.postman_collection.json`.
