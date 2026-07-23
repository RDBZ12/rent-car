<?php
class GamasModel extends Query{
    public function __construct()
    {
        parent::__construct();
    }
    public function getGamas(string $estado)
    {
        $sql = "SELECT gama_id AS id, nombre AS tipo, descripcion, estado FROM gamas WHERE estado = '$estado'";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function getAllGamas()
    {
        $sql = "SELECT gama_id AS id, nombre AS tipo, descripcion, estado FROM gamas";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function registrarGama(string $tipo, string $descripcion, string $estado)
    {
        $verficar = "SELECT * FROM gamas WHERE nombre = '$tipo'";
        $existe = $this->select($verficar);
        if (empty($existe)) {
            $sql = "INSERT INTO gamas(nombre, descripcion, estado) VALUES (?,?,?)";
            $datos = array($tipo, $descripcion, $estado);
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
    public function modificarGama(string $tipo, string $descripcion, string $estado, int $id)
    {
        $sql = "UPDATE gamas SET nombre = ?, descripcion = ?, estado = ? WHERE gama_id = ?";
        $datos = array($tipo, $descripcion, $estado, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function editarGama(int $id)
    {
        $sql = "SELECT gama_id AS id, nombre AS tipo, descripcion, estado FROM gamas WHERE gama_id = $id";
        $data = $this->select($sql);
        return $data;
    }
    public function accionGama(string $estado, int $id)
    {
        $sql = "UPDATE gamas SET estado = ? WHERE gama_id = ?";
        $datos = array($estado, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }
}
