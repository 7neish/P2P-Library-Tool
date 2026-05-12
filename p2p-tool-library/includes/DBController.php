<?php
class DBController {
    private static $instance = null;
    private $conn = null;

    private function __construct() {
        $host = 'localhost';
        $user = 'root';
        $password = '';
        $database = 'os_project';

        $this->conn = mysqli_connect($host, $user, $password, $database);

        if (!$this->conn) {
            die('Database connection failed: ' . mysqli_connect_error());
        }

        mysqli_set_charset($this->conn, 'utf8mb4');
    }


    private function __clone() {}


    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DBController();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function escape($value) {
        return mysqli_real_escape_string($this->getConnection(), $value);
    }
}