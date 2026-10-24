# Estructura de Tablas de la Base de Datos — portal_cursos

> No se evalúa normalización, diccionario de datos, BLOBs, ni procedimientos
> almacenados/disparadores/vistas/funciones (fuera del alcance de esta UA).

## Tablas principales

### usuarios
| Campo | Tipo | Notas |
|---|---|---|
| id | INT PK AI | |
| nombre_completo | VARCHAR(150) | |
| genero | CHAR(1) | F/M/O |
| fecha_nacimiento | DATE | |
| email | VARCHAR(150) UNIQUE | |
| password_hash | VARCHAR(255) | `password_hash()` de PHP |
| rol | ENUM('instructor','estudiante','administrador') | |
| avatar | VARCHAR(255) | ruta/URL de la imagen |
| intentos_fallidos | TINYINT DEFAULT 0 | control de bloqueo (3 intentos) |
| activo | TINYINT DEFAULT 1 | 0 = deshabilitado |
| fecha_registro | DATETIME | |
| fecha_ultimo_cambio | DATETIME | |

### categorias
| Campo | Tipo | Notas |
|---|---|---|
| id | INT PK AI | |
| nombre | VARCHAR(100) | |
| descripcion | TEXT | |
| creado_por | INT FK → usuarios.id | |
| fecha_creacion | DATETIME | |

### cursos
| Campo | Tipo | Notas |
|---|---|---|
| id | INT PK AI | |
| instructor_id | INT FK → usuarios.id | |
| categoria_id | INT FK → categorias.id | |
| titulo | VARCHAR(150) | |
| descripcion | TEXT | |
| imagen | VARCHAR(255) | |
| costo | DECIMAL(10,2) | |
| gratis | TINYINT DEFAULT 0 | |
| activo | TINYINT DEFAULT 1 | baja lógica |
| fecha_publicacion | DATETIME | |

### curso_niveles
| Campo | Tipo | Notas |
|---|---|---|
| id | INT PK AI | |
| curso_id | INT FK → cursos.id | |
| numero_nivel | INT | |
| titulo | VARCHAR(150) | |
| video_url | VARCHAR(255) | obligatorio por nivel |
| costo_nivel | DECIMAL(10,2) | si se vende por nivel |
| gratis | TINYINT DEFAULT 0 | |

### inscripciones
| Campo | Tipo | Notas |
|---|---|---|
| id | INT PK AI | |
| curso_id | INT FK → cursos.id | |
| estudiante_id | INT FK → usuarios.id | |
| fecha_inscripcion | DATETIME | |
| ultima_actividad | DATETIME | |
| fecha_terminacion | DATETIME NULL | |
| nivel_actual | INT | |
| precio_pagado | DECIMAL(10,2) | |
| forma_pago | VARCHAR(50) | |
| completo | TINYINT DEFAULT 0 | |

### certificados
| Campo | Tipo | Notas |
|---|---|---|
| id | INT PK AI | |
| inscripcion_id | INT FK → inscripciones.id | |
| fecha_terminacion | DATE | |
| certificado_por | VARCHAR(150) | nombre del instructor |

### comentarios
| Campo | Tipo | Notas |
|---|---|---|
| id | INT PK AI | |
| curso_id | INT FK → cursos.id | |
| usuario_id | INT FK → usuarios.id | |
| texto | TEXT | |
| calificacion | TINYINT | |
| fecha_creacion | DATETIME | |
| eliminado | TINYINT DEFAULT 0 | |
| fecha_eliminacion | DATETIME NULL | |
| causa_eliminacion | VARCHAR(255) NULL | |

### mensajes
| Campo | Tipo | Notas |
|---|---|---|
| id | INT PK AI | |
| curso_id | INT FK → cursos.id | |
| emisor_id | INT FK → usuarios.id | |
| receptor_id | INT FK → usuarios.id | |
| texto | TEXT | |
| fecha_envio | DATETIME | |

Ver script ejecutable en `docs/schema.sql`.

## Cambios en la 2da entrega
- `cursos.total_niveles` (INT, default 1): cantidad de niveles del curso.
- El script `schema.sql` ahora recrea la base desde cero e incluye datos iniciales
  (1 administrador, 1 instructor, 1 estudiante, 4 categorías y 4 cursos de ejemplo,
  uno de ellos dado de baja).
