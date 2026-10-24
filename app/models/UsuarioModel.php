<?php
// app/models/UsuarioModel.php
// Capa de Modelo: única capa autorizada para tocar la base de datos.

class UsuarioModel extends BaseModel
{
    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** ¿Existe ya ese correo (la "llave" de acceso)? Opcionalmente ignora a un usuario. */
    public function emailExiste(string $email, ?int $exceptoId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM usuarios WHERE email = :email';
        $params = ['email' => $email];
        if ($exceptoId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptoId;
        }
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO usuarios (nombre_completo, genero, fecha_nacimiento, email,
                    password_hash, rol, avatar, fecha_registro)
                VALUES (:nombre_completo, :genero, :fecha_nacimiento, :email,
                    :password_hash, :rol, :avatar, NOW())';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'nombre_completo'  => $datos['nombre_completo'],
            'genero'           => $datos['genero'],
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'email'            => $datos['email'],
            'password_hash'    => password_hash($datos['password'], PASSWORD_DEFAULT),
            'rol'              => $datos['rol'], // 'instructor' | 'estudiante'
            'avatar'           => $datos['avatar'] ?? null,
        ]);
        return (int) $this->db()->lastInsertId();
    }

    /** Actualiza el perfil. El id NUNCA viene del formulario, sino de la sesión. */
    public function actualizarPerfil(int $id, array $datos): void
    {
        $campos = [
            'nombre_completo = :nombre_completo',
            'genero = :genero',
            'fecha_nacimiento = :fecha_nacimiento',
            'email = :email',
            'fecha_ultimo_cambio = NOW()',
        ];
        $params = [
            'id'               => $id,
            'nombre_completo'  => $datos['nombre_completo'],
            'genero'           => $datos['genero'],
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'email'            => $datos['email'],
        ];
        if (!empty($datos['avatar'])) {
            $campos[] = 'avatar = :avatar';
            $params['avatar'] = $datos['avatar'];
        }
        if (!empty($datos['password'])) {
            $campos[] = 'password_hash = :password_hash';
            $params['password_hash'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        }
        $stmt = $this->db()->prepare('UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id = :id');
        $stmt->execute($params);
    }

    /** Re-cifra la contraseña con el algoritmo actual (password_needs_rehash). */
    public function actualizarHash(int $userId, string $hash): void
    {
        $stmt = $this->db()->prepare('UPDATE usuarios SET password_hash = :h WHERE id = :id');
        $stmt->execute(['h' => $hash, 'id' => $userId]);
    }

    public function incrementarIntentosFallidos(int $userId): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE usuarios SET intentos_fallidos = intentos_fallidos + 1 WHERE id = :id'
        );
        $stmt->execute(['id' => $userId]);
    }

    public function resetearIntentosFallidos(int $userId): void
    {
        $stmt = $this->db()->prepare('UPDATE usuarios SET intentos_fallidos = 0 WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    public function deshabilitar(int $userId): void
    {
        $stmt = $this->db()->prepare('UPDATE usuarios SET activo = 0 WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }
}
