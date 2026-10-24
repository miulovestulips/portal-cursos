<?php
class MensajeModel extends BaseModel
{
    // Obtener los mensajes de una conversación entre dos personas por un curso
    public function obtenerConversacion(int $cursoId, int $usuario1, int $usuario2): array
    {
        $sql = "SELECT m.*, u.nombre_completo as emisor_nombre, u.avatar as emisor_avatar 
                FROM mensajes m
                JOIN usuarios u ON m.emisor_id = u.id
                WHERE m.curso_id = :curso_id 
                AND (
                    (m.emisor_id = :u1 AND m.receptor_id = :u2) 
                    OR 
                    (m.emisor_id = :u2_alt AND m.receptor_id = :u1_alt)
                )
                ORDER BY m.fecha_envio ASC";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'curso_id' => $cursoId, 
            'u1'       => $usuario1, 
            'u2'       => $usuario2,
            'u2_alt'   => $usuario2, // Parámetro duplicado con nombre único
            'u1_alt'   => $usuario1  // Parámetro duplicado con nombre único
        ]);
        
        return $stmt->fetchAll();
    }

    // Guardar un mensaje nuevo
    public function enviar(int $cursoId, int $emisor, int $receptor, string $texto): void
    {
        $sql = "INSERT INTO mensajes (curso_id, emisor_id, receptor_id, texto) 
                VALUES (:curso, :emisor, :receptor, :texto)";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'curso' => $cursoId,
            'emisor' => $emisor,
            'receptor' => $receptor,
            'texto' => $texto
        ]);
    }
}