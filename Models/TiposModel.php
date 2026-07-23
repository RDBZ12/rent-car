<?php
class TiposModel extends Query{
    public function __construct()
    {
        parent::__construct();
    }
    public function getTipos(int $estado)
    {
        $sql = "SELECT tipo_dia_id AS id, nombre AS tipo, estado FROM tipo_dia WHERE estado = $estado";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function getAllTipos()
    {
        $sql = "SELECT tipo_dia_id AS id, nombre AS tipo, estado FROM tipo_dia";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function registrarTipo(string $tipo, int $estado)
    {
        $verficar = "SELECT * FROM tipo_dia WHERE nombre = '$tipo'";
        $existe = $this->select($verficar);
        if (empty($existe)) {
            $sql = "INSERT INTO tipo_dia(nombre, estado) VALUES (?, ?)";
            $datos = array($tipo, $estado);
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
    public function modificarTipo(string $tipo, int $estado, int $id)
    {
        $sql = "UPDATE tipo_dia SET nombre = ?, estado = ? WHERE tipo_dia_id = ?";
        $datos = array($tipo, $estado, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function editarTipo(int $id)
    {
        $sql = "SELECT tipo_dia_id AS id, nombre AS tipo, estado FROM tipo_dia WHERE tipo_dia_id = $id";
        $data = $this->select($sql);
        return $data;
    }
    public function accionTipo(int $estado, int $id)
    {
        $sql = "UPDATE tipo_dia SET estado = ? WHERE tipo_dia_id = ?";
        $datos = array($estado, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }
}
