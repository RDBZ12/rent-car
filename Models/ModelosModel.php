<?php
class ModelosModel extends Query{
    public function __construct()
    {
        parent::__construct();
    }
    public function getModelos(string $estado)
    {
        $sql = "SELECT mo.modelo_id AS id, ma.nombre AS marca, mo.nombre, mo.estado FROM modelos mo INNER JOIN marcas ma ON mo.marca_id = ma.marca_id WHERE mo.estado = '$estado'";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function getAllModelos()
    {
        $sql = "SELECT mo.modelo_id AS id, ma.nombre AS marca, mo.nombre, mo.estado FROM modelos mo INNER JOIN marcas ma ON mo.marca_id = ma.marca_id";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function getMarcas()
    {
        $sql = "SELECT marca_id AS id, nombre AS marca FROM marcas WHERE estado = 'Activo'";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function registrarModelo(string $nombre, int $marca_id, string $estado)
    {
        $verficar = "SELECT * FROM modelos WHERE nombre = '$nombre' AND marca_id = $marca_id";
        $existe = $this->select($verficar);
        if (empty($existe)) {
            $sql = "INSERT INTO modelos(marca_id, nombre, estado) VALUES (?, ?, ?)";
            $datos = array($marca_id, $nombre, $estado);
            $data = $this->save($sql, $datos);
            if ($data == 1) {
                $res = "ok";
            }else{
                $res = "error";
            }
        }else{
            $res = "existe";
        }
        return $res;
    }
    public function modificarModelo(string $nombre, int $marca_id, string $estado, int $id)
    {
        $sql = "UPDATE modelos SET nombre = ?, marca_id = ?, estado = ? WHERE modelo_id = ?";
        $datos = array($nombre, $marca_id, $estado, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function editarModelo(int $id)
    {
        $sql = "SELECT modelo_id AS id, marca_id, nombre, estado FROM modelos WHERE modelo_id = $id";
        $data = $this->select($sql);
        return $data;
    }
    public function accionModelo(string $estado, int $id)
    {
        $sql = "UPDATE modelos SET estado = ? WHERE modelo_id = ?";
        $datos = array($estado, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }
}
?>
