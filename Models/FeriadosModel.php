<?php
class FeriadosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getFeriados()
    {
        $sql = "SELECT * FROM feriados ORDER BY fecha DESC";
        return $this->selectAll($sql);
    }

    public function registrarFeriado(string $fecha, string $descripcion)
    {
        $verificar = "SELECT * FROM feriados WHERE fecha = '$fecha'";
        $existe = $this->select($verificar);
        if (empty($existe)) {
            $sql = "INSERT INTO feriados(fecha, descripcion) VALUES (?, ?)";
            $datos = array($fecha, $descripcion);
            $data = $this->save($sql, $datos);
            return ($data == 1) ? "ok" : "error";
        } else {
            return "existe";
        }
    }

    public function modificarFeriado(string $fecha, string $descripcion, int $id)
    {
        $sql = "UPDATE feriados SET fecha = ?, descripcion = ? WHERE feriado_id = ?";
        $datos = array($fecha, $descripcion, $id);
        $data = $this->save($sql, $datos);
        return ($data == 1) ? "modificado" : "error";
    }

    public function editarFeriado(int $id)
    {
        $sql = "SELECT * FROM feriados WHERE feriado_id = $id";
        return $this->select($sql);
    }

    public function eliminarFeriado(int $id)
    {
        $sql = "DELETE FROM feriados WHERE feriado_id = ?";
        $datos = array($id);
        return $this->save($sql, $datos);
    }
}
?>
