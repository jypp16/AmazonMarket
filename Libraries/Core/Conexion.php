<?php
namespace Libraries\Core;

use PDO;
use PDOException;

class Conexion {

    private static $instance = null;
    protected $conect;

    private function __construct() {
        try {
            $this->conect = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
            $this->conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function conect() {
        return $this->conect;
    }
}