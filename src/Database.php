<?php

// Include the configuration file
require_once(dirname(__DIR__) . '/config/config.php');

class Database {
    // Specify the database credentials using the constants from config.php
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;

    // Hold the class instance.
    private static $instance = null;
    public $conn;

    // The constructor is private to prevent initiation with 'new'.
    private function __construct() {
        $this->conn = null;

        try {
            // Create a new PDO instance
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);

            // Set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Set character set to utf8
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            // For a real application, you would log this error, not echo it.
            // For now, we'll just re-throw the exception.
            throw new Exception("Connection error: " . $exception->getMessage());
        }
    }

    // The clone and wakeup methods are private to prevent cloning of the instance.
    private function __clone() {}
    public function __wakeup() {}

    // The static method that controls the access to the singleton instance.
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // A method to get the database connection.
    public function getConnection() {
        return $this->conn;
    }
}
?>
