<?php
class MarcasModel extends Query{
    public function __construct()
    {
        parent::__construct();
    }
    public function getMarcas(string $estado)
    {
        $sql = "SELECT marca_id AS id, nombre AS marca, estado FROM marcas WHERE estado = '$estado'";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function getAllMarcas()
    {
        $sql = "SELECT marca_id AS id, nombre AS marca, estado FROM marcas";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function registrarMarca(string $marca, string $estado)
    {
        $verficar = "SELECT * FROM marcas WHERE nombre = '$marca'";
        $existe = $this->select($verficar);
        if (empty($existe)) {
            $sql = "INSERT INTO marcas(nombre, estado) VALUES (?, ?)";
            $datos = array($marca, $estado);
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
    public function modificarMarca(string $marca, string $estado, int $id)
    {
        $sql = "UPDATE marcas SET nombre = ?, estado = ? WHERE marca_id = ?";
        $datos = array($marca, $estado, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function editarMarca(int $id)
    {
        $sql = "SELECT marca_id AS id, nombre AS marca, estado FROM marcas WHERE marca_id = $id";
        $data = $this->select($sql);
        return $data;
    }
    public function accionMarca(string $estado, int $id)
    {
        $sql = "UPDATE marcas SET estado = ? WHERE marca_id = ?";
        $datos = array($estado, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }
}
