<?php
/**
 * ZONA TURBO - Modelo Usuario
 */

class Usuario {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /** Busca un usuario por ID */
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT id, nombre, email, rol, telefono, activo, creado_en
             FROM usuarios WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Busca un usuario por email */
    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM usuarios WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /** Lista todos los usuarios (solo admin) */
    public function getAll(string $rol = ''): array {
        if ($rol) {
            $stmt = $this->pdo->prepare(
                'SELECT id, nombre, email, rol, telefono, activo, creado_en
                 FROM usuarios WHERE rol = ? ORDER BY nombre'
            );
            $stmt->execute([$rol]);
        } else {
            $stmt = $this->pdo->query(
                'SELECT id, nombre, email, rol, telefono, activo, creado_en
                 FROM usuarios ORDER BY rol, nombre'
            );
        }
        return $stmt->fetchAll();
    }

    /** Crea un nuevo usuario */
    public function create(array $data): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nombre, email, password, rol, telefono)
             VALUES (:nombre, :email, :password, :rol, :telefono)'
        );
        $stmt->execute([
            ':nombre'   => sanitize($data['nombre']),
            ':email'    => sanitize($data['email']),
            ':password' => Auth::hashPassword($data['password']),
            ':rol'      => $data['rol'] ?? 'cliente',
            ':telefono' => sanitize($data['telefono'] ?? ''),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** Actualiza datos de un usuario */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        if (!empty($data['nombre'])) {
            $fields[] = 'nombre = :nombre';
            $params[':nombre'] = sanitize($data['nombre']);
        }
        if (!empty($data['telefono'])) {
            $fields[] = 'telefono = :telefono';
            $params[':telefono'] = sanitize($data['telefono']);
        }
        if (!empty($data['password'])) {
            $fields[] = 'password = :password';
            $params[':password'] = Auth::hashPassword($data['password']);
        }
        if (isset($data['activo'])) {
            $fields[] = 'activo = :activo';
            $params[':activo'] = (int) $data['activo'];
        }

        if (empty($fields)) return false;

        $sql  = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /** Desactiva un usuario (no eliminar) */
    public function deactivate(int $id): bool {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET activo = 0 WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
