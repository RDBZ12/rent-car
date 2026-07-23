<?php
class PreciosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getPrecios()
    {
        $sql = "SELECT pv.precio_id AS id, v.placa, m.nombre AS marca, mo.nombre AS modelo, 
                       t.nombre AS tipo_dia, pv.precio, pv.estado
                FROM precios_vehiculo pv
                INNER JOIN vehiculos v ON pv.vehiculo_id = v.vehiculo_id
                INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id
                INNER JOIN marcas m ON mo.marca_id = m.marca_id
                INNER JOIN tipo_dia t ON pv.tipo_dia_id = t.tipo_dia_id";
        return $this->selectAll($sql);
    }

    public function getVehiculos()
    {
        $sql = "SELECT v.vehiculo_id AS id, v.placa, m.nombre AS marca, mo.nombre AS modelo 
                FROM vehiculos v
                INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id
                INNER JOIN marcas m ON mo.marca_id = m.marca_id
                WHERE v.estado = 'Activo'";
        return $this->selectAll($sql);
    }

    public function getTipos()
    {
        $sql = "SELECT * FROM tipo_dia";
        return $this->selectAll($sql);
    }

    public function registrarPrecio(int $vehiculo, int $tipo, float $precio)
    {
        // Desactivar cualquier otro precio activo para este vehículo
        $this->save("UPDATE precios_vehiculo SET estado = 'Inactivo' WHERE vehiculo_id = ?", array($vehiculo));

        $sql = "INSERT INTO precios_vehiculo(vehiculo_id, tipo_dia_id, precio, estado) VALUES (?, ?, ?, ?)";
        $datos = array($vehiculo, $tipo, $precio, 'Activo');
        $data = $this->save($sql, $datos);
        return ($data == 1) ? "ok" : "error";
    }

    public function modificarPrecio(int $vehiculo, int $tipo, float $precio, int $id)
    {
        $sql = "UPDATE precios_vehiculo SET vehiculo_id = ?, tipo_dia_id = ?, precio = ? WHERE precio_id = ?";
        $datos = array($vehiculo, $tipo, $precio, $id);
        $data = $this->save($sql, $datos);
        return ($data == 1) ? "modificado" : "error";
    }

    public function editarPrecio(int $id)
    {
        $sql = "SELECT * FROM precios_vehiculo WHERE precio_id = $id";
        return $this->select($sql);
    }

    public function accionPrecio(string $estado, int $id)
    {
        if ($estado == 'Activo') {
            // Obtener el vehiculo_id de este precio
            $res = $this->select("SELECT vehiculo_id FROM precios_vehiculo WHERE precio_id = $id");
            $vehiculo_id = $res['vehiculo_id'];
            // Desactivar todos los demás para este vehículo
            $this->save("UPDATE precios_vehiculo SET estado = 'Inactivo' WHERE vehiculo_id = ?", array($vehiculo_id));
        }
        $sql = "UPDATE precios_vehiculo SET estado = ? WHERE precio_id = ?";
        $datos = array($estado, $id);
        return $this->save($sql, $datos);
    }
}
?>
