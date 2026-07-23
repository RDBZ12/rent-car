<?php
class VehiculosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getDatos(string $table)
    {
        $sql = "SELECT * FROM $table WHERE estado = 'Activo'";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function getModelos()
    {
        $sql = "SELECT mo.modelo_id AS id, CONCAT(ma.nombre, ' - ', mo.nombre) AS modelo FROM modelos mo INNER JOIN marcas ma ON mo.marca_id = ma.marca_id WHERE mo.estado = 'Activo'";
        return $this->selectAll($sql);
    }
    public function getMarcasActivas()
    {
        $sql = "SELECT marca_id AS id, nombre FROM marcas WHERE estado = 'Activo'";
        return $this->selectAll($sql);
    }
    public function getModelosByMarca(int $marca_id)
    {
        $sql = "SELECT modelo_id AS id, nombre FROM modelos WHERE marca_id = $marca_id AND estado = 'Activo'";
        return $this->selectAll($sql);
    }
    public function getVehiculos(string $estado)
    {
        $sql = "SELECT v.vehiculo_id AS id, v.placa, m.nombre AS marca, mo.nombre AS modelo, g.nombre AS tipo, v.estado, v.imagen AS foto FROM vehiculos v INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id INNER JOIN marcas m ON mo.marca_id = m.marca_id INNER JOIN gamas g ON v.gama_id = g.gama_id WHERE v.estado = '$estado'";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function vehiculos()
    {
        $sql = "SELECT v.vehiculo_id AS id, v.placa, m.nombre AS marca, mo.nombre AS modelo, g.nombre AS tipo, v.anio, v.estado, v.imagen AS foto,
                (SELECT COUNT(*) FROM reserva_vehiculos rv 
                 INNER JOIN reservas r ON rv.reserva_id = r.reserva_id 
                 WHERE rv.vehiculo_id = v.vehiculo_id 
                 AND r.estado IN ('Activa', 'Pendiente')
                 AND rv.vehiculo_id NOT IN (
                    SELECT rd.vehiculo_id FROM recepcion_detalle rd 
                    INNER JOIN recepciones rec ON rd.recepcion_id = rec.recepcion_id 
                    WHERE rec.reserva_id = r.reserva_id
                 )) AS en_reserva
                FROM vehiculos v 
                INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id 
                INNER JOIN marcas m ON mo.marca_id = m.marca_id 
                INNER JOIN gamas g ON v.gama_id = g.gama_id 
                WHERE v.estado IN ('Activo', 'Alquilado', 'Inactivo')";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function registrarVehiculo(string $placa, int $modelo_id, int $gama_id, string $anio, string $color, string $kilometraje, string $combustible, string $img, float $precio, int $tipo_dia_id, string $estado_precio)
    {
        $vericar = "SELECT * FROM vehiculos WHERE placa = '$placa'";
        $existe = $this->select($vericar);
        if (empty($existe)) {
            $sql = "INSERT INTO vehiculos(placa, modelo_id, gama_id, anio, color, kilometraje_actual, combustible_actual, imagen) VALUES (?,?,?,?,?,?,?,?)";
            $datos = array($placa, $modelo_id, $gama_id, $anio, $color, $kilometraje, $combustible, $img);
            $data = $this->insertar($sql, $datos);
            if ($data > 0) {
                // Registrar el precio inicial vinculado al vehículo
                $sqlP = "INSERT INTO precios_vehiculo (vehiculo_id, tipo_dia_id, precio, estado) VALUES (?,?,?,?)";
                $datosP = array($data, $tipo_dia_id, $precio, $estado_precio);
                $this->save($sqlP, $datosP);
                $res = "ok";
            } else {
                $res = "error";
            }
        } else {
            $res = "existe";
        }
        return $res;
    }
    public function modificarVehiculo(string $placa, int $modelo_id, int $gama_id, string $anio, string $color, string $kilometraje, string $combustible, string $img, int $id, float $precio, int $tipo_dia_id, string $estado_precio)
    {
        $sql = "UPDATE vehiculos SET placa=?, modelo_id=?, gama_id=?, anio=?, color=?, kilometraje_actual=?, combustible_actual=?, imagen=? WHERE vehiculo_id=?";
        $datos = array($placa, $modelo_id, $gama_id, $anio, $color, $kilometraje, $combustible, $img, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            // Actualizar o insertar el precio
            $sqlCheck = "SELECT * FROM precios_vehiculo WHERE vehiculo_id = $id";
            $existeP = $this->select($sqlCheck);
            if (empty($existeP)) {
                $sqlP = "INSERT INTO precios_vehiculo (vehiculo_id, tipo_dia_id, precio, estado) VALUES (?,?,?,?)";
            } else {
                $sqlP = "UPDATE precios_vehiculo SET tipo_dia_id=?, precio=?, estado=? WHERE vehiculo_id=?";
            }
            
            if (empty($existeP)) {
                $datosP = array($id, $tipo_dia_id, $precio, $estado_precio);
            } else {
                $datosP = array($tipo_dia_id, $precio, $estado_precio, $id);
            }
            $this->save($sqlP, $datosP);
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function editarVeh(int $id)
    {
        $sql = "SELECT v.vehiculo_id AS id, v.placa, v.modelo_id, mo.marca_id, v.gama_id AS tipo, v.anio, v.color, v.kilometraje_actual, v.combustible_actual, v.imagen AS foto,
                       p.precio, p.tipo_dia_id, p.estado AS estado_precio
                FROM vehiculos v 
                INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id 
                LEFT JOIN precios_vehiculo p ON v.vehiculo_id = p.vehiculo_id AND p.estado = 'Activo'
                WHERE v.vehiculo_id = $id";
        $data = $this->select($sql);
        return $data;
    }
    public function accionVeh(string $estado, int $id)
    {
        $sql = "UPDATE vehiculos SET estado = ? WHERE vehiculo_id = ?";
        $datos = array($estado, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }
    public function buscarVehiculo(string $valor)
    {
        $sql = "SELECT v.vehiculo_id AS id, v.placa, m.nombre AS marca, mo.nombre AS modelo, g.nombre AS tipo, v.anio, v.estado, v.imagen AS foto FROM vehiculos v INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id INNER JOIN marcas m ON mo.marca_id = m.marca_id INNER JOIN gamas g ON v.gama_id = g.gama_id WHERE (v.placa LIKE '%" . $valor . "%' OR mo.nombre LIKE '%" . $valor . "%' OR m.nombre LIKE '%" . $valor . "%' OR g.nombre LIKE '%" . $valor . "%' OR v.anio LIKE '%" . $valor . "%') AND v.estado = 'Activo'";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function getTiposDia()
    {
        return $this->selectAll("SELECT tipo_dia_id AS id, nombre FROM tipo_dia WHERE estado = 'Activo'");
    }
    public function getMarcaModeloById(int $modelo_id)
    {
        $sql = "SELECT m.nombre AS marca, mo.nombre AS modelo FROM modelos mo INNER JOIN marcas m ON mo.marca_id = m.marca_id WHERE mo.modelo_id = $modelo_id";
        return $this->select($sql);
        
    }
    public function verificarPlacaExistente(string $placa, int $id_excluir = 0)
    {
        $sql = "SELECT vehiculo_id FROM vehiculos WHERE placa = '$placa' AND vehiculo_id != $id_excluir";
        $data = $this->select($sql);
        return $data ? true : false;
    }
}
