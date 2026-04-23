<?php
/**
 * ZONA TURBO - Modelo Vehículo
 * La placa es el identificador principal
 */

class Vehiculo {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /** Busca vehículo por placa (identificador principal) */
    public function findByPlaca(string $placa): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT v.*, c.whatsapp, u.nombre AS cliente_nombre, u.email AS cliente_email
             FROM vehiculos v
             JOIN clientes c ON c.id = v.cliente_id
             JOIN usuarios u ON u.id = c.usuario_id
             WHERE v.placa = ?
             LIMIT 1'
        );
        $stmt->execute([strtoupper(trim($placa))]);
        return $stmt->fetch() ?: null;
    }

    /** Historial completo de OTs por placa */
    public function getHistorialByPlaca(string $placa): array {
        $stmt = $this->pdo->prepare(
            'SELECT ot.*, v_cot.total_repuestos, v_cot.total_mano_obra, v_cot.costo_total,
                    u.nombre AS mecanico_nombre
             FROM ordenes_trabajo ot
             JOIN vehiculos v      ON v.id  = ot.vehiculo_id
             JOIN v_costo_ot v_cot ON v_cot.id = ot.id
             LEFT JOIN mecanicos m ON m.id  = ot.mecanico_id
             LEFT JOIN usuarios u  ON u.id  = m.usuario_id
             WHERE v.placa = ?
             ORDER BY ot.fecha_ingreso DESC'
        );
        $stmt->execute([strtoupper(trim($placa))]);
        return $stmt->fetchAll();
    }

    /** Lista todos los vehículos con datos del cliente */
    public function getAll(): array {
        return $this->pdo->query(
            'SELECT v.*, u.nombre AS cliente_nombre, u.email AS cliente_email
             FROM vehiculos v
             JOIN clientes c ON c.id = v.cliente_id
             JOIN usuarios u ON u.id = c.usuario_id
             ORDER BY v.placa'
        )->fetchAll();
    }

    /** Registra un nuevo vehículo */
    public function create(array $data): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO vehiculos (cliente_id, placa, marca, modelo, anio, color, tipo, kilometraje, notas)
             VALUES (:cliente_id, :placa, :marca, :modelo, :anio, :color, :tipo, :km, :notas)'
        );
        $stmt->execute([
            ':cliente_id' => (int) $data['cliente_id'],
            ':placa'      => strtoupper(sanitize($data['placa'])),
            ':marca'      => sanitize($data['marca']),
            ':modelo'     => sanitize($data['modelo']),
            ':anio'       => (int) $data['anio'],
            ':color'      => sanitize($data['color'] ?? ''),
            ':tipo'       => $data['tipo'] ?? 'sedan',
            ':km'         => (int) ($data['kilometraje'] ?? 0),
            ':notas'      => sanitize($data['notas'] ?? ''),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** Verifica si una placa ya existe */
    public function placaExists(string $placa): bool {
        $stmt = $this->pdo->prepare('SELECT id FROM vehiculos WHERE placa = ? LIMIT 1');
        $stmt->execute([strtoupper(trim($placa))]);
        return (bool) $stmt->fetch();
    }
}
