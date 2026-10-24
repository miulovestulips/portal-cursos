# Propuesta de API de Terceros

> Requiere visto bueno del profesor antes de integrarse (según lo indicado en
> la Forma de Evaluación).

## Servicio propuesto: Cloudinary (almacenamiento y transmisión de imágenes/video)

- **Necesidad del portal que resuelve:** almacenamiento de las imágenes de
  avatar de usuario, imágenes de portada de cada curso, y los videos de
  contenido de cada nivel, sin depender del almacenamiento local del servidor.
- **Documentación oficial:** https://cloudinary.com/documentation
- **Datos que se consumirán/enviarán:**
  - Envío: archivo de imagen/video (avatar, portada de curso, video de nivel).
  - Recepción: URL segura (`secure_url`) del recurso subido, para guardarla en
    la base de datos y mostrarla en las vistas.
- **Dónde se consume (decisión 2da entrega): desde el CONTROLADOR (servidor).**
  Justificación: la subida firmada necesita el API Secret, que no puede viajar al
  navegador; además el servidor valida tipo real (finfo) y tamaño (≤5 MB) antes de
  enviar el archivo y solo guarda en la BD la `secure_url`. Implementación:
  `app/services/CloudinaryService.php` (cURL + firma SHA-1) y
  `app/services/ImagenService.php`; se usa en el registro/perfil (avatar) y en el
  alta de cursos (imagen de portada). Si no hay credenciales en `.env`, en
  desarrollo se guarda en `public/uploads`.
- **Manejo de credenciales:** API Key/Secret se guardarán en variables de
  entorno (`.env`, fuera del repositorio) y se leerán mediante configuración,
  nunca hardcodeadas en el código versionado.

### Alternativa de respaldo (por si no se aprueba Cloudinary)
- **Servicio:** SendGrid (envío de correo) — para notificar al estudiante
  cuando se genera su certificado/diploma, o al instructor cuando recibe un
  mensaje nuevo.
- **Documentación oficial:** https://docs.sendgrid.com/

*(El equipo integrará el servicio que reciba el visto bueno del profesor;
ambas opciones cubren una necesidad real y distinta del portal.)*
