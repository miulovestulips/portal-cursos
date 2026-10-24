<?php
// app/services/CloudinaryService.php
// API DE TERCEROS: Cloudinary (almacenamiento de imágenes).
//
// DECISIÓN: se consume DESDE EL CONTROLADOR (servidor), no desde la vista.
// Justificación: la subida firmada requiere el API Secret, que jamás debe
// llegar al navegador. Además el servidor valida tipo y tamaño del archivo
// antes de enviarlo, y solo guarda en la BD la URL segura devuelta.
// Las credenciales se leen de .env (fuera del repositorio).

class CloudinaryService
{
    public function estaConfigurado(): bool
    {
        return CLOUDINARY_CLOUD_NAME !== '' && CLOUDINARY_API_KEY !== '' && CLOUDINARY_API_SECRET !== '';
    }

    /** Sube la imagen y devuelve su secure_url. Lanza RuntimeException si falla. */
    public function subirImagen(string $rutaTmp, string $mime, string $nombre, string $carpeta): string
    {
        $timestamp = time();
        // Firma: parámetros ordenados alfabéticamente + API secret, SHA-1
        $firma = sha1('folder=' . $carpeta . '&timestamp=' . $timestamp . CLOUDINARY_API_SECRET);

        $campos = [
            'file'      => new CURLFile($rutaTmp, $mime, $nombre),
            'api_key'   => CLOUDINARY_API_KEY,
            'timestamp' => $timestamp,
            'folder'    => $carpeta,
            'signature' => $firma,
        ];

        $ch = curl_init('https://api.cloudinary.com/v1_1/' . rawurlencode(CLOUDINARY_CLOUD_NAME) . '/image/upload');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $campos,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $respuesta = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false) {
            throw new RuntimeException('Cloudinary no respondió: ' . $error);
        }
        $data = json_decode($respuesta, true);
        if ($status !== 200 || empty($data['secure_url'])) {
            $detalle = is_array($data) ? ($data['error']['message'] ?? 'sin detalle') : 'respuesta inválida';
            throw new RuntimeException('Cloudinary devolvió ' . $status . ': ' . $detalle);
        }
        return $data['secure_url'];
    }
}
