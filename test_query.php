<?php
require_once "Config/Config.php";
require_once "Config/Helpers.php";

class DB {
    private $host = host;
    private $user = user;
    private $password = pass;
    private $db = db;
    private $charset = charset;
    private $conect;
    public function __construct() {
        $connectionString = "mysql:host=" . $this->host . ";dbname=" . $this->db . ";charset=" . $this->charset;
        try {
            $this->conect = new PDO($connectionString, $this->user, $this->password);
            $this->conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage();
        }
    }
    public function selectAll($sql) {
        $resul = $this->conect->prepare($sql);
        $resul->execute();
        return $resul->fetchAll(PDO::FETCH_ASSOC);
    }
}

$db = new DB();
$reserva_id = 10;
$sql = "SELECT rv.dias, rv.precio_unitario, rv.subtotal, v.placa, m.nombre AS marca, mo.nombre AS modelo, v.anio, v.color, v.imagen AS foto, g.nombre AS gama
        FROM reserva_vehiculos rv 
        INNER JOIN vehiculos v ON rv.vehiculo_id = v.vehiculo_id 
        INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id 
        INNER JOIN marcas m ON mo.marca_id = m.marca_id
        LEFT JOIN gamas g ON v.gama_id = g.gama_id
        WHERE rv.reserva_id = $reserva_id";
$data = $db->selectAll($sql);
print_r($data);
?>
