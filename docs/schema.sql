-- schema.sql
-- Portal de Cursos — script de creación de base de datos
-- No se evalúa normalización ni diccionario de datos en esta UA.

-- ATENCIÓN: este script recrea la base desde cero (borra la existente).
DROP DATABASE IF EXISTS portal_cursos;
CREATE DATABASE portal_cursos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portal_cursos;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(150) NOT NULL,
    genero CHAR(1) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('instructor','estudiante','administrador') NOT NULL,
    avatar VARCHAR(255) NULL,
    intentos_fallidos TINYINT NOT NULL DEFAULT 0,
    activo TINYINT NOT NULL DEFAULT 1,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_ultimo_cambio DATETIME NULL
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    creado_por INT NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_categorias_usuario FOREIGN KEY (creado_por) REFERENCES usuarios(id)
);

CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instructor_id INT NOT NULL,
    categoria_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    imagen VARCHAR(255) NULL,
    total_niveles INT NOT NULL DEFAULT 1,
    costo DECIMAL(10,2) NOT NULL DEFAULT 0,
    gratis TINYINT NOT NULL DEFAULT 0,
    activo TINYINT NOT NULL DEFAULT 1,
    fecha_publicacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cursos_instructor FOREIGN KEY (instructor_id) REFERENCES usuarios(id),
    CONSTRAINT fk_cursos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE curso_niveles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    numero_nivel INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    costo_nivel DECIMAL(10,2) NULL,
    gratis TINYINT NOT NULL DEFAULT 0,
    CONSTRAINT fk_niveles_curso FOREIGN KEY (curso_id) REFERENCES cursos(id)
);

CREATE TABLE inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    estudiante_id INT NOT NULL,
    fecha_inscripcion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultima_actividad DATETIME NULL,
    fecha_terminacion DATETIME NULL,
    nivel_actual INT NOT NULL DEFAULT 1,
    precio_pagado DECIMAL(10,2) NOT NULL DEFAULT 0,
    forma_pago VARCHAR(50) NULL,
    completo TINYINT NOT NULL DEFAULT 0,
    CONSTRAINT fk_inscripciones_curso FOREIGN KEY (curso_id) REFERENCES cursos(id),
    CONSTRAINT fk_inscripciones_estudiante FOREIGN KEY (estudiante_id) REFERENCES usuarios(id)
);

CREATE TABLE certificados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscripcion_id INT NOT NULL,
    fecha_terminacion DATE NOT NULL,
    certificado_por VARCHAR(150) NOT NULL,
    CONSTRAINT fk_certificados_inscripcion FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id)
);

CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    usuario_id INT NOT NULL,
    texto TEXT NOT NULL,
    calificacion TINYINT NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    eliminado TINYINT NOT NULL DEFAULT 0,
    fecha_eliminacion DATETIME NULL,
    causa_eliminacion VARCHAR(255) NULL,
    CONSTRAINT fk_comentarios_curso FOREIGN KEY (curso_id) REFERENCES cursos(id),
    CONSTRAINT fk_comentarios_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    emisor_id INT NOT NULL,
    receptor_id INT NOT NULL,
    texto TEXT NOT NULL,
    fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mensajes_curso FOREIGN KEY (curso_id) REFERENCES cursos(id),
    CONSTRAINT fk_mensajes_emisor FOREIGN KEY (emisor_id) REFERENCES usuarios(id),
    CONSTRAINT fk_mensajes_receptor FOREIGN KEY (receptor_id) REFERENCES usuarios(id)
);

-- =====================================================================
-- DATOS INICIALES (2da entrega)
-- Contraseñas de prueba (se re-cifran con bcrypt en el primer login):
--   admin@portal.com        -> Admin#2026   (administrador)
--   ana.torres@portal.com   -> Demo#2026    (instructor)
--   maria.lopez@portal.com  -> Demo#2026    (estudiante)
-- Cámbialas antes de cualquier uso fuera de pruebas.
-- =====================================================================
INSERT INTO usuarios (id, nombre_completo, genero, fecha_nacimiento, email, password_hash, rol, avatar) VALUES
(1, 'Administrador del Portal', 'O', '1990-01-01', 'admin@portal.com',       '$6$aGk3Zr9QpLm2Xw7T$wL4ilDqrzys4fMHRIh56jHivV1azc0hIdAs62lV.g15ibxYq3etL853XQ1pZVG49ByDR9.PdHRdR9UvS9RaG0/', 'administrador', NULL),
(2, 'Ana Torres',               'F', '1988-05-14', 'ana.torres@portal.com',  '$6$bQ7nVt4KsYe8Hc1D$i2mjgGvXNcxIucAul6xsU/SoyqEVnM.1mxnv1SLrZB14Sx4iJmIlqtmGNPSsrPNZlLmLIiBsPaAmlIvIC4ULZ1', 'instructor',    NULL),
(3, 'María López',              'F', '2001-09-30', 'maria.lopez@portal.com', '$6$bQ7nVt4KsYe8Hc1D$i2mjgGvXNcxIucAul6xsU/SoyqEVnM.1mxnv1SLrZB14Sx4iJmIlqtmGNPSsrPNZlLmLIiBsPaAmlIvIC4ULZ1', 'estudiante',    NULL);

INSERT INTO categorias (id, nombre, descripcion, creado_por) VALUES
(1, 'IT & Software', 'Programación, bases de datos, DevOps.', 1),
(2, 'Marketing',     'Marketing digital, redes sociales, SEO.', 1),
(3, 'Design',        'Diseño gráfico, UX/UI, ilustración.', 1),
(4, 'Negocios',      'Emprendimiento, finanzas, liderazgo.', 1);

INSERT INTO cursos (id, instructor_id, categoria_id, titulo, descripcion, imagen, total_niveles, costo, gratis, activo, fecha_publicacion) VALUES
(1, 2, 1, 'Introducción a PHP',      'Aprende los fundamentos de PHP desde cero: variables, estructuras de control, funciones y programación orientada a objetos.', NULL, 6, 299.00, 0, 1, '2026-08-10 10:00:00'),
(2, 2, 1, 'MySQL desde Cero',        'Diseña y consulta bases de datos relacionales con MySQL: tablas, llaves, JOINs y buenas prácticas.', NULL, 4, 199.00, 0, 1, '2026-08-20 12:00:00'),
(3, 2, 3, 'Fundamentos de UX/UI',    'Principios de experiencia e interfaz de usuario con ejercicios prácticos en cada nivel.', NULL, 5, 0.00, 1, 1, '2026-09-02 09:30:00'),
(4, 2, 1, 'APIs REST con PHP',       'Construye una API REST en PHP puro con JSON, códigos de estado y middleware.', NULL, 5, 249.00, 0, 0, '2026-09-10 16:00:00');

INSERT INTO inscripciones (curso_id, estudiante_id, fecha_inscripcion, ultima_actividad, fecha_terminacion, nivel_actual, precio_pagado, forma_pago, completo) VALUES
(1, 3, '2026-08-12 18:00:00', '2026-09-15 20:00:00', NULL, 5, 299.00, 'Tarjeta', 0),
(3, 3, '2026-09-03 08:00:00', '2026-09-20 21:00:00', '2026-09-20 21:00:00', 5, 0.00, NULL, 1);

INSERT INTO comentarios (curso_id, usuario_id, texto, calificacion, fecha_creacion) VALUES
(3, 3, 'Muy buen curso para empezar, los ejercicios ayudan mucho.', 5, '2026-09-21 10:00:00');
