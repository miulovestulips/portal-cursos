<?php
require_once __DIR__ . '/../config/config.php';
// require_once __DIR__ . '/../vendor/autoload.php'; // <-- Elimina o comenta esta línea

$db = Database::getConnection();

// Datos del nuevo admin (puedes cambiarlos si quieres)
$nombre = "Nuevo Admin";
$email = "nuevo.admin@portal.com";
$password_plana = "AdminSeguro123!"; 
$rol = "administrador";

// Encriptamos la contraseña con el estándar de PHP
$password_hash = password_hash($password_plana, PASSWORD_DEFAULT);

try {
    // Primero verificamos si el correo ya existe para no duplicarlo
    $check = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
    $check->execute(['email' => $email]);
    
    if ($check->fetch()) {
        // Si existe, actualizamos la contraseña y el rol
        $sql = "UPDATE usuarios SET password_hash = :hash, rol = :rol, activo = 1 WHERE email = :email";
        $stmt = $db->prepare($sql);
        $stmt->execute(['hash' => $password_hash, 'rol' => $rol, 'email' => $email]);
        echo "El usuario ya existía. ¡Contraseña y rol actualizados con éxito!\n";
    } else {
        // Si no existe, lo creamos
        $sql = "INSERT INTO usuarios (nombre_completo, genero, fecha_nacimiento, email, password_hash, rol, activo) 
                VALUES (:nombre, 'O', '1990-01-01', :email, :hash, :rol, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'email'  => $email,
            'hash'   => $password_hash,
            'rol'    => $rol
        ]);
        echo "¡Administrador creado con éxito!\n";
    }
    
    echo "---------------------------------\n";
    echo "Email: " . $email . "\n";
    echo "Contraseña: " . $password_plana . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}