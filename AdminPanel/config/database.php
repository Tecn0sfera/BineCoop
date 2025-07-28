<?php
class Database {
    private $host = 'sql211.infinityfree.com';
    private $user = 'if0_39215471';
    private $pass = 'asdasd';
    private $name = 'if0_39215471_admin_panel';
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->name}", 
                $this->user, 
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}
