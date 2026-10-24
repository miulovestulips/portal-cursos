<?php
// app/models/CursoModel.php
// Todo el SQL de cursos vive aquí (nunca en controladores ni vistas).

class CursoModel extends BaseModel
{
    private const SELECT_BASE = "
        SELECT c.id, c.titulo, c.descripcion, c.imagen, c.costo, c.gratis, c.total_niveles,
               c.activo, c.fecha_publicacion,
               c.categoria_id, cat.nombre AS categoria,
               c.instructor_id, u.nombre_completo AS instructor,
               (SELECT ROUND(AVG(cm.calificacion), 1) FROM comentarios cm
                 WHERE cm.curso_id = c.id AND cm.eliminado = 0) AS calificacion_promedio,
               (SELECT COUNT(*) FROM inscripciones i WHERE i.curso_id = c.id) AS alumnos
          FROM cursos c
          JOIN categorias cat ON cat.id = c.categoria_id
          JOIN usuarios u ON u.id = c.instructor_id";

    private const ORDENES = [
        'recientes'         => 'c.fecha_publicacion DESC, c.id DESC',
        'mejor_calificados' => 'calificacion_promedio IS NULL, calificacion_promedio DESC, c.id DESC',
        'mas_vendidos'      => 'alumnos DESC, c.id DESC',
    ];

    /**
     * Búsqueda de cursos ACTIVOS (los dados de baja nunca aparecen).
     * Filtros: q (título parcial), categoria_id, instructor (nombre parcial),
     * desde / hasta (fecha de publicación), orden, limite, pagina.
     */
    public function listarActivos(array $f = []): array
    {
        $where = ['c.activo = 1'];
        $params = [];

        if (!empty($f['q'])) {
            $where[] = 'c.titulo LIKE :q';
            $params['q'] = '%' . addcslashes($f['q'], '%_\\') . '%';
        }
        if (!empty($f['categoria_id'])) {
            $where[] = 'c.categoria_id = :categoria_id';
            $params['categoria_id'] = (int) $f['categoria_id'];
        }
        if (!empty($f['instructor'])) {
            $where[] = 'u.nombre_completo LIKE :instructor';
            $params['instructor'] = '%' . addcslashes($f['instructor'], '%_\\') . '%';
        }
        if (!empty($f['desde'])) {
            $where[] = 'c.fecha_publicacion >= :desde';
            $params['desde'] = $f['desde'] . ' 00:00:00';
        }
        if (!empty($f['hasta'])) {
            $where[] = 'c.fecha_publicacion <= :hasta';
            $params['hasta'] = $f['hasta'] . ' 23:59:59';
        }

        $orden = self::ORDENES[$f['orden'] ?? 'recientes'] ?? self::ORDENES['recientes'];
        $limite = max(1, min(50, (int) ($f['limite'] ?? 20)));
        $offset = (max(1, (int) ($f['pagina'] ?? 1)) - 1) * $limite;

        $sql = self::SELECT_BASE . ' WHERE ' . implode(' AND ', $where)
             . ' ORDER BY ' . $orden . ' LIMIT :lim OFFSET :off';
        $stmt = $this->db()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id, bool $soloActivos = true): ?array
    {
        $sql = self::SELECT_BASE . ' WHERE c.id = :id' . ($soloActivos ? ' AND c.activo = 1' : '');
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Todos los cursos del instructor, incluidos los dados de baja. */
    public function listarPorInstructor(int $instructorId): array
    {
        $sql = self::SELECT_BASE . ' WHERE c.instructor_id = :iid ORDER BY c.fecha_publicacion DESC, c.id DESC';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['iid' => $instructorId]);
        return $stmt->fetchAll();
    }

    public function crear(array $d): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO cursos (instructor_id, categoria_id, titulo, descripcion, imagen, costo, gratis, total_niveles)
             VALUES (:instructor_id, :categoria_id, :titulo, :descripcion, :imagen, :costo, :gratis, :total_niveles)'
        );
        $stmt->execute([
            'instructor_id' => $d['instructor_id'],
            'categoria_id'  => $d['categoria_id'],
            'titulo'        => $d['titulo'],
            'descripcion'   => $d['descripcion'],
            'imagen'        => $d['imagen'] ?? null,
            'costo'         => $d['costo'],
            'gratis'        => $d['gratis'] ? 1 : 0,
            'total_niveles' => $d['total_niveles'],
        ]);
        return (int) $this->db()->lastInsertId();
    }

    public function actualizar(int $id, array $d): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE cursos SET categoria_id = :categoria_id, titulo = :titulo, descripcion = :descripcion,
                    imagen = COALESCE(:imagen, imagen), costo = :costo, gratis = :gratis,
                    total_niveles = :total_niveles
              WHERE id = :id'
        );
        $stmt->execute([
            'id'            => $id,
            'categoria_id'  => $d['categoria_id'],
            'titulo'        => $d['titulo'],
            'descripcion'   => $d['descripcion'],
            'imagen'        => $d['imagen'] ?? null,
            'costo'         => $d['costo'],
            'gratis'        => $d['gratis'] ? 1 : 0,
            'total_niveles' => $d['total_niveles'],
        ]);
    }

    /** Baja LÓGICA: el curso se conserva para reportes de ventas y Kardex. */
    public function darDeBaja(int $id): void
    {
        $stmt = $this->db()->prepare('UPDATE cursos SET activo = 0 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
