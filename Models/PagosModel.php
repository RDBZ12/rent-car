<?php
class PagosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getPagos()
    {
        $sql = "SELECT p.pago_id AS id, pd.reserva_id, pd.monto_aplicado AS monto, p.fecha_pago, p.metodo AS metodo_pago, 
                       c.nombre, c.apellido, c.cedula AS dni 
                FROM pagos p 
                INNER JOIN pago_detalle pd ON p.pago_id = pd.pago_id
                INNER JOIN reservas res ON pd.reserva_id = res.reserva_id 
                INNER JOIN clientes c ON res.cliente_id = c.cliente_id
                ORDER BY p.pago_id DESC";
        $data = $this->selectAll($sql);
        return $data;
    }

    public function getReservasConSaldo()
    {
        $sql = "SELECT r.reserva_id, c.nombre, c.apellido, r.total,
                       COALESCE((SELECT SUM(pd.monto_aplicado) FROM pago_detalle pd WHERE pd.reserva_id = r.reserva_id), 0) AS total_pagos,
                       COALESCE((SELECT SUM(pen.monto) FROM penalidades pen WHERE pen.reserva_id = r.reserva_id), 0) AS total_penalidades
                FROM reservas r
                INNER JOIN clientes c ON r.cliente_id = c.cliente_id
                WHERE r.estado != 'Cancelada'
                ORDER BY r.reserva_id DESC";
        $data = $this->selectAll($sql);
        return $data;
    }

    public function getSaldoReserva(int $reserva_id)
    {
        $sql = "SELECT r.total, r.fecha_reserva, r.fecha_inicio, r.fecha_fin, r.estado,
                       c.nombre AS cliente_nombre, c.apellido AS cliente_apellido, c.cedula, c.telefono,
                       COALESCE((SELECT SUM(pd.monto_aplicado) FROM pago_detalle pd WHERE pd.reserva_id = r.reserva_id), 0) AS total_pagos,
                       COALESCE((SELECT SUM(pen.monto) FROM penalidades pen WHERE pen.reserva_id = r.reserva_id), 0) AS total_penalidades
                FROM reservas r
                INNER JOIN clientes c ON r.cliente_id = c.cliente_id
                WHERE r.reserva_id = $reserva_id";
        $data = $this->select($sql);
        return $data;
    }

    public function getVehiculosReserva(int $reserva_id)
    {
        $sql = "SELECT rv.dias, rv.precio_unitario, rv.subtotal, v.placa, m.nombre AS marca, mo.nombre AS modelo, v.anio, v.color, v.imagen AS foto, g.nombre AS gama
                FROM reserva_vehiculos rv 
                INNER JOIN vehiculos v ON rv.vehiculo_id = v.vehiculo_id 
                INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id 
                INNER JOIN marcas m ON mo.marca_id = m.marca_id
                LEFT JOIN gamas g ON v.gama_id = g.gama_id
                WHERE rv.reserva_id = $reserva_id";
        return $this->selectAll($sql);
    }

    public function registrarPago(string $fecha, string $metodo, float $monto)
    {
        $sql = "INSERT INTO pagos(fecha_pago, metodo, total, estado) VALUES (?, ?, ?, 'Activo')";
        $datos = array($fecha, $metodo, $monto);
        return $this->insertar($sql, $datos);
    }

    public function registrarPagoDetalle(int $pago_id, int $reserva_id, float $monto_aplicado)
    {
        $sql = "INSERT INTO pago_detalle(pago_id, reserva_id, monto_aplicado) VALUES (?, ?, ?)";
        $datos = array($pago_id, $reserva_id, $monto_aplicado);
        return $this->insertar($sql, $datos);
    }

    public function getEstadoReserva(int $reserva_id)
    {
        $sql = "SELECT estado FROM reservas WHERE reserva_id = $reserva_id";
        return $this->select($sql);
    }

    public function updateEstadoReserva(int $reserva_id, string $estado)
    {
        $sql = "UPDATE reservas SET estado = ? WHERE reserva_id = ?";
        $datos = array($estado, $reserva_id);
        return $this->save($sql, $datos);
    }

    public function buscarClientesConSaldo(string $q)
    {
        $sql = "SELECT DISTINCT c.cliente_id, c.nombre, c.apellido, c.cedula, c.telefono
                FROM clientes c
                INNER JOIN reservas r ON c.cliente_id = r.cliente_id
                WHERE (c.nombre LIKE '%$q%' OR c.apellido LIKE '%$q%' OR c.cedula LIKE '%$q%')
                AND r.estado != 'Cancelada'
                AND (
                    (r.total + COALESCE((SELECT SUM(monto) FROM penalidades WHERE reserva_id = r.reserva_id), 0)) 
                    > 
                    COALESCE((SELECT SUM(monto_aplicado) FROM pago_detalle WHERE reserva_id = r.reserva_id), 0)
                )
                LIMIT 10";
        return $this->selectAll($sql);
    }

    public function getReservasConSaldoPorCliente(int $cliente_id)
    {
        $sql = "SELECT r.reserva_id, r.total, r.fecha_reserva, r.fecha_inicio, r.fecha_fin, r.estado,
                       COALESCE((SELECT SUM(pd.monto_aplicado) FROM pago_detalle pd WHERE pd.reserva_id = r.reserva_id), 0) AS total_pagos,
                       COALESCE((SELECT SUM(pen.monto) FROM penalidades pen WHERE pen.reserva_id = r.reserva_id), 0) AS total_penalidades
                FROM reservas r
                WHERE r.cliente_id = $cliente_id AND r.estado != 'Cancelada'
                ORDER BY r.reserva_id DESC";
        return $this->selectAll($sql);
    }
    public function getPagoById(int $id)
    {
        $sql = "SELECT p.*, p.metodo as metodo_pago, c.nombre, c.apellido, c.cedula as dni 
                FROM pagos p 
                INNER JOIN pago_detalle pd ON p.pago_id = pd.pago_id
                INNER JOIN reservas r ON pd.reserva_id = r.reserva_id
                INNER JOIN clientes c ON r.cliente_id = c.cliente_id
                WHERE p.pago_id = $id LIMIT 1";
        return $this->select($sql);
    }

    public function getDetallesPago(int $id)
    {
        $sql = "SELECT pd.* FROM pago_detalle pd WHERE pd.pago_id = $id";
        return $this->selectAll($sql);
    }
}
