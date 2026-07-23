<?php
class ReportesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getIngresosFecha($fecha_inicio = '', $fecha_fin = '')
    {
        $sql = "SELECT fecha_pago, SUM(total) AS ingresos FROM pagos WHERE estado = 'Activo'";
        if ($fecha_inicio != '' && $fecha_fin != '') {
            $sql .= " AND fecha_pago BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        }
        $sql .= " GROUP BY fecha_pago ORDER BY fecha_pago DESC";
        return $this->selectAll($sql);
    }

    public function getVehiculosRentados()
    {
        $sql = "SELECT v.placa, m.nombre AS marca, mo.nombre AS modelo, COUNT(*) AS cantidad, v.imagen
                FROM reserva_vehiculos rv
                INNER JOIN vehiculos v ON v.vehiculo_id = rv.vehiculo_id
                LEFT JOIN modelos mo ON v.modelo_id = mo.modelo_id
                LEFT JOIN marcas m ON mo.marca_id = m.marca_id
                GROUP BY v.vehiculo_id
                ORDER BY cantidad DESC";
        return $this->selectAll($sql);
    }

    public function getClientesFrecuentes()
    {
        $sql = "SELECT c.nombre, c.apellido, c.cedula, COUNT(*) AS total
                FROM reservas r
                INNER JOIN clientes c ON c.cliente_id = r.cliente_id
                GROUP BY c.cliente_id
                ORDER BY total DESC";
        return $this->selectAll($sql);
    }

    public function getPenalidadesGeneradas()
    {
        $sql = "SELECT tipo, SUM(monto) AS total
                FROM penalidades
                GROUP BY tipo
                ORDER BY total DESC";
        return $this->selectAll($sql);
    }
}
?>
