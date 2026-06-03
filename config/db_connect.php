<?php
/**
 * كلاس الداتا بيز لضمان اتصال واحد بقاعدة البيانات
 */
class Database {
    // متغير ثابت يحمل النسخة الوحيدة من الكلاس
    private static $instance = null;
    private $conn;

    private $host = "localhost";
    private $dbName = "tawasul db";
    private $username = "root";
    private $password = "";

    /**
     * منشئ خاص لمنع انشاء كائنات جديده من خارج الكلاس
     */
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
        }
    }
   // نقطة الوصول الوحيدة للحصول على الكائن
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

}
?>