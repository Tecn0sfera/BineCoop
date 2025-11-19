<?php
// Cargar variables de entorno
require_once __DIR__ . '/../../env_loader.php';

class Database {
    private $host;
    private $user;
    private $pass;
    private $name;
    private $port;
    private $charset;
    private $conn;
    
    public function __construct() {
        // Cargar configuración desde variables de entorno
        $this->host = env('DB_HOST', 'localhost');
        $this->user = env('DB_USER', 'usuario_ejemplo');
        $this->pass = env('DB_PASS', 'contraseña_ejemplo');
        $this->name = env('DB_NAME', 'nombre_base_datos_ejemplo');
        $this->port = env('DB_PORT', '3306');
        $this->charset = env('DB_CHARSET', 'utf8mb4');
        
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->name};charset={$this->charset}";
            $this->conn = new PDO(
                $dsn, 
                $this->user, 
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE {$this->charset}_unicode_ci"
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión a BD: " . $e->getMessage());
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}
