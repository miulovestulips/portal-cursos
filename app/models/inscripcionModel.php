<?php
class InscripcionModel extends BaseModel
{
    // Verifica si el estudiante ya está inscrito
    public function yaInscrito(int $cursoId, int $estudianteId): bool
    {
        $stmt = $this->db()->prepare("SELECT id FROM inscripciones WHERE curso_id = :c AND estudiante_id = :e");
        $stmt->execute(['c' => $cursoId, 'e' => $estudianteId]);
        return (bool) $stmt->fetch();
    }

    // Registra la inscripción (y la compra)
    public function crear(array $datos): int
    {
        $sql = "INSERT INTO inscripciones (curso_id, estudiante_id, precio_pagado, forma_pago, nivel_actual, completo) 
                VALUES (:curso, :estudiante, :precio, :pago, 1, 0)";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'curso'      => $datos['curso_id'],
            'estudiante' => $datos['estudiante_id'],
            'precio'     => $datos['precio'],
            'pago'       => $datos['forma_pago']
        ]);
        return (int) $this->db()->lastInsertId();
    }
}