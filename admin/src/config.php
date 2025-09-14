<?php
define("API_KEY", "4f9b6a8e2f5e9c2a34f8c3d76d89a1e3c7d5f1a9e8f2b4d6c3a2f9b8c4d7e6f1");

if (!class_exists('Database')) {
    class Database {
        private $host;
        private $db_name;
        private $username;
        private $password;
        public $conn;

        public function __construct() {
            if ($_SERVER['HTTP_HOST'] === 'localhost') {
                $this->host = "localhost";
                $this->db_name = "mercurysoftech_whatsapp";
                $this->username = "root";
                $this->password = "";
            } else {
                $this->host = "localhost";
                $this->db_name = "MercurySoftech_whatsappbot";
                $this->username = "MercurySoftech_whatsappbot";
                $this->password = "Mercury@2025";
            }
        }

        public function getConnection() {
            $this->conn = null;
            try {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                    $this->username, 
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $exception) {
                echo json_encode(["message" => "Database Connection Failed: " . $exception->getMessage()]);
                exit();
            }
            return $this->conn;
        }
    }
}
?>
