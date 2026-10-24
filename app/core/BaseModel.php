<?php
// app/core/BaseModel.php
// Base de todos los Modelos. La conexión se recibe por constructor
// (inyección de dependencias) y, si no se pasa, se obtiene de la clase
// Database de forma perezosa (solo cuando realmente se consulta).

abstract class BaseModel
{
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    protected function db(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = Database::getConnection();
        }
        return $this->pdo;
    }
}
