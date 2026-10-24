<?php
// app/services/ImagenService.php
// Valida una imagen subida (tipo real y tamaño) y la guarda:
//  - Con credenciales de Cloudinary => se sube a Cloudinary y se devuelve su URL.
//  - Sin credenciales (desarrollo local) => se guarda en public/uploads.
// Devuelve la URL/ruta a almacenar en la BD.

class ImagenService
{
    private const MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    private CloudinaryService $cloudinary;

    public function __construct(?CloudinaryService $cloudinary = null)
    {
        $this->cloudinary = $cloudinary ?? new CloudinaryService();
    }

    /**
     * @throws InvalidArgumentException si el archivo no es válido (error del usuario)
     * @throws RuntimeException si falla el almacenamiento (error del servidor)
     */
    public function guardar(array $archivo, string $carpeta): string
    {
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('No se recibió la imagen o la subida falló.');
        }
        if (($archivo['size'] ?? 0) > UPLOAD_MAX_BYTES) {
            throw new InvalidArgumentException('La imagen no puede pesar más de 5 MB.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        if (!isset(self::MIMES[$mime])) {
            throw new InvalidArgumentException('Formato no permitido. Usa JPG, PNG, WEBP o GIF.');
        }

        if ($this->cloudinary->estaConfigurado()) {
            return $this->cloudinary->subirImagen($archivo['tmp_name'], $mime, basename($archivo['name']), $carpeta);
        }

        $dir = __DIR__ . '/../../public/uploads/' . $carpeta;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo crear la carpeta de subidas.');
        }
        $nombre = bin2hex(random_bytes(12)) . '.' . self::MIMES[$mime];
        if (!move_uploaded_file($archivo['tmp_name'], $dir . '/' . $nombre)) {
            throw new RuntimeException('No se pudo guardar la imagen.');
        }
        return '/uploads/' . $carpeta . '/' . $nombre;
    }
}
