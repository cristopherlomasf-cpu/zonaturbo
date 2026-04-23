<?php
/**
 * ZONA TURBO - Configuración de Base de Datos
 * Patrón Singleton para una sola conexión PDO por request
 */

class Database {
    private static ?Database $instance = null;
    private PDO $pdo;

    private string $host;
    private string $dbname;
    private string $user;
    private string $pass;
    private string $charset = 'utf8mb4';

    private function __construct() {
        $this->host   = $_ENV['DB_HOST']   ?? 'localhost';
        $this->dbname = $_ENV['DB_NAME']   ?? 'zonaturbo';
        $this->user   = $_ENV['DB_USER']   ?? 'root';
        $this->pass   = $_ENV['DB_PASS']   ?? '';

        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // No exponer detalles en producción
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'message' => 'Error de conexión a la base de datos.'
            ]));
        }
    }

    /** Obtiene la instancia única (Singleton) */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Retorna el objeto PDO */
    public function getConnection(): PDO {
        return $this->pdo;
    }

    /** Evita clonación */
    private function __clone() {}
}
