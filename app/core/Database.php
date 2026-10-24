<?php
// app/core/Database.php
// Única clase autorizada para abrir la conexión a MySQL (PDO).
// - Parámetros centralizados en config/config.php (leídos desde .env).
// - Manejo de errores: excepciones PDO; el detalle se registra en el log del
//   servidor y NUNCA se muestra al usuario.
// Los Modelos la reciben por constructor (ver BaseModel); ningún Controlador
// ni Vista la usa.

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    
                ]);
            } catch (PDOException $e) {
                error_log('DB connection error: ' . $e->getMessage());
                throw new RuntimeException('No fue posible conectar a la base de datos.');
            }
        }
        return self::$instance;
    }
}
