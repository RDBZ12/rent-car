<?php
class RecepcionesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listarRecepciones()
    {
        $sql = "SELECT r.*, res.reserva_id, c.nombre, c.apellido, c.cedula, c.telefono,
                       COALESCE((SELECT SUM(rd.cargo_extra) FROM recepcion_detalle rd WHERE rd.recepcion_id = r.recepcion_id), 0) as total_cargos,
                       COALESCE((SELECT SUM(pen.monto) FROM penalidades pen WHERE pen.reserva_id = res.reserva_id), 0) as monto_penalidad
                FROM recepciones r 
                INNER JOIN reservas res ON r.reserva_id = res.reserva_id 
                INNER JOIN clientes c ON res.cliente_id = c.cliente_id 
                ORDER BY r.recepcion_id DESC";
        return $this->selectAll($sql);
    }

    public function getReservasActivasPorCliente(int $cliente_id)
    {
        $sql = "SELECT r.*, r.total as total_alquiler,
                       COALESCE((SELECT SUM(pd.monto_aplicado) FROM pago_detalle pd WHERE pd.reserva_id = r.reserva_id), 0) as total_pagos,
                       COALESCE((SELECT SUM(pen.monto) FROM penalidades pen WHERE pen.reserva_id = r.reserva_id), 0) as total_penalidades
                FROM reservas r 
                WHERE r.cliente_id = $cliente_id AND r.estado IN ('Activa', 'Pendiente')
                ORDER BY r.reserva_id DESC";
        $data = $this->selectAll($sql);
        $reservas = [];
        
        foreach ($data as $res) {
            $vehiculos = $this->getVehiculosReserva($res['reserva_id']);
            // Solo incluir si tiene vehículos pendientes por devolver
            if (count($vehiculos) > 0) {
                $res['vehiculos'] = $vehiculos;
                $res['saldo_pendiente'] = ($res['total'] + $res['total_penalidades']) - $res['total_pagos'];
                $reservas[] = $res;
            }
        }
        
        return $reservas;
    }

    public function getVehiculosReserva(int $reserva_id)
    {
        $sql = "SELECT rv.*, v.placa, mo.nombre AS modelo, m.nombre AS marca, v.imagen
                FROM reserva_vehiculos rv 
                INNER JOIN vehiculos v ON rv.vehiculo_id = v.vehiculo_id 
                INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id 
                INNER JOIN marcas m ON mo.marca_id = m.marca_id
                WHERE rv.reserva_id = $reserva_id
                AND rv.vehiculo_id NOT IN (
                    SELECT rd.vehiculo_id 
                    FROM recepcion_detalle rd 
                    INNER JOIN recepciones r ON rd.recepcion_id = r.recepcion_id 
                    WHERE r.reserva_id = $reserva_id
                )";
        return $this->selectAll($sql);
    }

    public function registrarRecepcion(int $reserva_id, string $fecha, string $observaciones)
    {
        $sql = "INSERT INTO recepciones (reserva_id, fecha_recepcion, observaciones) VALUES (?, ?, ?)";
        $datos = array($reserva_id, $fecha, $observaciones);
        return $this->insertar($sql, $datos);
    }

    public function registrarDetalleRecepcion(int $recepcion_id, int $vehiculo_id, float $combustible, int $km, string $danos, float $cargo)
    {
        $sql = "INSERT INTO recepcion_detalle (recepcion_id, vehiculo_id, combustible_devuelto, kilometraje_devuelto, danos, cargo_extra) VALUES (?, ?, ?, ?, ?, ?)";
        $datos = array($recepcion_id, $vehiculo_id, $combustible, $km, $danos, $cargo);
        return $this->save($sql, $datos);
    }

    public function esReservaCompletada(int $reserva_id)
    {
        $sqlTotal = "SELECT COUNT(*) as total FROM reserva_vehiculos WHERE reserva_id = $reserva_id";
        $resTotal = $this->select($sqlTotal);
        $totalVehiculos = intval($resTotal['total'] ?? 0);

        $sqlDevueltos = "SELECT COUNT(DISTINCT rd.vehiculo_id) as total 
                         FROM recepcion_detalle rd 
                         INNER JOIN recepciones r ON rd.recepcion_id = r.recepcion_id 
                         WHERE r.reserva_id = $reserva_id";
        $resDevueltos = $this->select($sqlDevueltos);
        $totalDevueltos = intval($resDevueltos['total'] ?? 0);

        return ($totalVehiculos > 0 && $totalDevueltos >= $totalVehiculos);
    }

    public function registrarPenalidad(int $reserva_id, int $vehiculo_id, string $tipo, string $descripcion, int $dias, float $monto)
    {
        $sql = "INSERT INTO penalidades (reserva_id, vehiculo_id, tipo, descripcion, dias_retraso, monto, fecha_registro) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $datos = array($reserva_id, $vehiculo_id, $tipo, $descripcion, $dias, $monto);
        return $this->save($sql, $datos);
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
        return $this->save($sql, $datos);
    }

    public function getRecepcionById(int $id)
    {
        $sql = "SELECT r.*, res.reserva_id, c.nombre, c.apellido, c.cedula, c.telefono,
                       COALESCE((SELECT SUM(rd.cargo_extra) FROM recepcion_detalle rd WHERE rd.recepcion_id = r.recepcion_id), 0) as total_cargos,
                       COALESCE((SELECT pen.monto FROM penalidades pen WHERE pen.reserva_id = res.reserva_id LIMIT 1), 0) as monto_penalidad,
                       COALESCE((SELECT pen.descripcion FROM penalidades pen WHERE pen.reserva_id = res.reserva_id LIMIT 1), 'N/A') as motivo_penalidad,
                       res.total as total_alquiler,
                       COALESCE((SELECT SUM(pd.monto_aplicado) FROM pago_detalle pd WHERE pd.reserva_id = res.reserva_id), 0) as total_pagos
                FROM recepciones r 
                INNER JOIN reservas res ON r.reserva_id = res.reserva_id 
                INNER JOIN clientes c ON res.cliente_id = c.cliente_id 
                WHERE r.recepcion_id = $id";
        return $this->select($sql);
    }

    public function getDetallesRecepcion(int $recepcion_id)
    {
        $sql = "SELECT rd.*, v.placa, mo.nombre AS modelo, m.nombre AS marca
                FROM recepcion_detalle rd 
                INNER JOIN vehiculos v ON rd.vehiculo_id = v.vehiculo_id 
                INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id 
                INNER JOIN marcas m ON mo.marca_id = m.marca_id
                WHERE rd.recepcion_id = $recepcion_id";
        return $this->selectAll($sql);
    }

    public function buscarClientesConReservasActivas(string $q)
    {
        $sql = "SELECT DISTINCT c.cliente_id, c.nombre, c.apellido, c.cedula, c.telefono
                FROM clientes c
                INNER JOIN reservas r ON c.cliente_id = r.cliente_id
                WHERE (r.estado = 'Activa' OR r.estado = 'Pendiente')
                AND (c.nombre LIKE '%$q%' OR c.apellido LIKE '%$q%' OR c.cedula LIKE '%$q%')
                LIMIT 10";
        return $this->selectAll($sql);
    }

    public function actualizarEstadoReserva(int $reserva_id, string $estado)
    {
        $sql = "UPDATE reservas SET estado = ? WHERE reserva_id = ?";
        return $this->save($sql, array($estado, $reserva_id));
    }
}
