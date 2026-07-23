<?php
class Conexion{
    private $conect;
    public function __construct()
    {
        $port = defined('db_port') ? db_port : '3306';
        $pdo = "mysql:host=" . host . ";port=" . $port . ";dbname=" . db . ";charset=utf8";
        try {
            $this->conect = new PDO($pdo, user, pass);
            $this->conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error en la conexion".$e->getMessage();
        }
    }
    public function conect()
    {
        return $this->conect;
    }
}

?>