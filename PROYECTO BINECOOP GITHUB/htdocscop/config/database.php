<?php
// Cargar variables de entorno
require_once __DIR__ . '/../env_loader.php';

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
        
        // Log de depuración (solo en desarrollo)
        error_log("Intentando conectar a BD - Host: {$this->host}, DB: {$this->name}, User: {$this->user}, Port: {$this->port}");
        
        try {
            // Verificar que la extensión PDO MySQL esté disponible
            if (!extension_loaded('pdo_mysql')) {
                throw new PDOException("La extensión PDO MySQL no está disponible");
            }
            
            // Para hosts de Docker (como 'db'), no verificar DNS ya que es un nombre interno
            // Solo verificar DNS para hosts externos
            if ($this->host !== 'db' && $this->host !== 'localhost' && $this->host !== '127.0.0.1') {
                $ip = @gethostbyname($this->host);
                if ($ip === $this->host && filter_var($this->host, FILTER_VALIDATE_IP) === false) {
                    // Si el hostname no se resolvió y no es una IP, lanzar error más descriptivo
                    error_log("Advertencia: No se pudo resolver el hostname: {$this->host}");
                    error_log("Intentando conectar de todas formas...");
                    // Continuar de todas formas, puede ser un problema temporal de DNS
                } else {
                    error_log("Hostname resuelto: {$this->host} -> {$ip}");
                }
            }
            
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->name};charset={$this->charset}";
            
            // Preparar opciones de PDO
            // Para hosts externos, aumentar timeout ya que pueden tardar más en Docker
            $timeout = ($this->host !== 'db' && $this->host !== 'localhost' && $this->host !== '127.0.0.1') ? 30 : 10;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => $timeout // Timeout más largo para hosts externos
            ];
            
            error_log("Timeout configurado: {$timeout} segundos para host: {$this->host}");
            
            // Agregar MYSQL_ATTR_INIT_COMMAND usando el valor numérico (1002)
            // Esto evita el error si la constante PDO::MYSQL_ATTR_INIT_COMMAND no está disponible
            // El valor 1002 es el valor numérico de PDO::MYSQL_ATTR_INIT_COMMAND
            $options[1002] = "SET NAMES {$this->charset} COLLATE {$this->charset}_unicode_ci";
            
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
            error_log("Conexión a BD exitosa - Host: {$this->host}, DB: {$this->name}");
        } catch (PDOException $e) {
            $errorMsg = "Error de conexión a BD - Host: {$this->host}, DB: {$this->name}, User: {$this->user}, Error: " . $e->getMessage();
            error_log($errorMsg);
            die("Error de conexión: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}
