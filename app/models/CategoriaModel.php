<?php
// app/models/CategoriaModel.php

class CategoriaModel extends BaseModel
{
    public function listar(): array
    {
        $stmt = $this->db()->query('SELECT id, nombre, descripcion FROM categorias ORDER BY nombre');
        return $stmt->fetchAll();
    }

    public function existe(int $id): bool
    {
        $stmt = $this->db()->prepare('SELECT COUNT(*) FROM categorias WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
