<?php
class TiposDiaModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getTiposDia()
    {
        $sql = "SELECT tipo_dia_id AS id, nombre, estado FROM tipo_dia";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function registrarTipoDia(string $nombre)
    {
        $verificar = "SELECT * FROM tipo_dia WHERE nombre = '$nombre'";
        $existe = $this->select($verificar);
        if (empty($existe)) {
            $sql = "INSERT INTO tipo_dia(nombre, estado) VALUES (?, 'Activo')";
            $datos = array($nombre);
            $data = $this->insertar($sql, $datos);
            if ($data > 0) {
                $res = "ok";
            } else {
                $res = "error";
            }
        } else {
            $res = "existe";
        }
        return $res;
    }
    public function editarTipoDia(int $id)
    {
        $sql = "SELECT tipo_dia_id AS id, nombre, estado FROM tipo_dia WHERE tipo_dia_id = $id";
        $data = $this->select($sql);
        return $data;
    }
    public function modificarTipoDia(string $nombre, int $id)
    {
        $verificar = "SELECT * FROM tipo_dia WHERE nombre = '$nombre' AND tipo_dia_id != $id";
        $existe = $this->select($verificar);
        if (empty($existe)) {
            $sql = "UPDATE tipo_dia SET nombre = ? WHERE tipo_dia_id = ?";
            $datos = array($nombre, $id);
            $data = $this->save($sql, $datos);
            if ($data == 1) {
                $res = "modificado";
            } else {
                $res = "error";
            }
        } else {
            $res = "existe";
        }
        return $res;
    }
    public function accionTipoDia(string $estado, int $id)
    {
        $sql = "UPDATE tipo_dia SET estado = ? WHERE tipo_dia_id = ?";
        $datos = array($estado, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }
}
?>
