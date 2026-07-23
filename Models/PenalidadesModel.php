<?php
class PenalidadesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getPenalidades()
    {
        $sql = "SELECT p.penalidad_id AS id, p.tipo, p.descripcion, p.dias_retraso, p.monto, p.fecha_registro,
                       res.reserva_id, c.nombre, c.apellido, v.placa
                FROM penalidades p 
                INNER JOIN reservas res ON p.reserva_id = res.reserva_id 
                INNER JOIN clientes c ON res.cliente_id = c.cliente_id 
                LEFT JOIN vehiculos v ON p.vehiculo_id = v.vehiculo_id";
        $data = $this->selectAll($sql);
        return $data;
    }

    public function getReservas()
    {
        $sql = "SELECT res.reserva_id, c.nombre, c.apellido 
                FROM reservas res 
                INNER JOIN clientes c ON res.cliente_id = c.cliente_id";
        $data = $this->selectAll($sql);
        return $data;
    }

    public function getVehiculosPorReserva(int $reserva_id)
    {
        $sql = "SELECT v.vehiculo_id, v.placa 
                FROM reserva_vehiculos rv 
                INNER JOIN vehiculos v ON rv.vehiculo_id = v.vehiculo_id 
                WHERE rv.reserva_id = $reserva_id";
        $data = $this->selectAll($sql);
        return $data;
    }

    public function registrarPenalidadManual(int $reserva_id, int $vehiculo_id, string $tipo, string $descripcion, float $monto)
    {
        $sql = "INSERT INTO penalidades(reserva_id, vehiculo_id, tipo, descripcion, dias_retraso, monto, fecha_registro) 
                VALUES (?, ?, ?, ?, 0, ?, NOW())";
        $datos = array($reserva_id, $vehiculo_id, $tipo, $descripcion, $monto);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }
}
?>
