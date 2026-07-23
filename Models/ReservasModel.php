<?php
class ReservasModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getReservas()
    {
        $sql = "SELECT r.reserva_id AS id, r.fecha_inicio AS fecha_prestamo, r.fecha_fin AS fecha_devolucion, 
                       r.estado, c.nombre, c.apellido, r.total,
                       GROUP_CONCAT(
                           CONCAT(
                               COALESCE(NULLIF(TRIM(v.imagen), ''), '__DEFAULT__'),
                               '###',
                               TRIM(CONCAT(COALESCE(m.nombre, ''), ' ', COALESCE(mo.nombre, ''), ' (', COALESCE(v.placa, 'S/P'), ')'))
                           )
                           ORDER BY rv.vehiculo_id
                           SEPARATOR '|||'
                       ) AS vehiculos_raw
                FROM reservas r 
                INNER JOIN clientes c ON r.cliente_id = c.cliente_id 
                LEFT JOIN reserva_vehiculos rv ON r.reserva_id = rv.reserva_id
                LEFT JOIN vehiculos v ON rv.vehiculo_id = v.vehiculo_id
                LEFT JOIN modelos mo ON v.modelo_id = mo.modelo_id
                LEFT JOIN marcas m ON mo.marca_id = m.marca_id
                GROUP BY r.reserva_id
                ORDER BY r.reserva_id DESC";
        $data = $this->selectAll($sql);
        return $data;
    }

    /**
     * Solo reservas en Pendiente pasan a Cancelada (no elimina el registro).
     */
    public function cancelarReservaPendiente(int $reserva_id): bool
    {
        $cur = $this->selectParams('SELECT estado FROM reservas WHERE reserva_id = ?', array($reserva_id));
        if (empty($cur) || $cur['estado'] !== 'Pendiente') {
            return false;
        }
        $sql = "UPDATE reservas SET estado = 'Cancelada' WHERE reserva_id = ? AND estado = 'Pendiente'";
        $ok = $this->save($sql, array($reserva_id));
        return $ok === 1;
    }

    public function entregarReserva(int $reserva_id): bool
    {
        $cur = $this->selectParams('SELECT estado FROM reservas WHERE reserva_id = ?', array($reserva_id));
        if (empty($cur) || $cur['estado'] !== 'Pendiente') {
            return false;
        }
        $sql = "UPDATE reservas SET estado = 'Activa' WHERE reserva_id = ? AND estado = 'Pendiente'";
        $ok = $this->save($sql, array($reserva_id));
        return $ok === 1;
    }

    /**
     * Vehículos libres en el rango [f_ini, f_ini] frente a reservas Pendiente/Activa.
     * Filtros opcionales: marca, modelo, término general (placa/marca/modelo).
     * exclude_ids: lista de IDs separados por coma a excluir (ej. ya agregados al detalle).
     */
    public function getVehiculosDisponibles(string $f_ini, string $f_fin, string $marca = '', string $modelo = '', string $term = '', string $exclude_ids = '')
    {
        $params = [];
        $whereParts = [
            "v.estado = 'Activo'",
            "pv.estado = 'Activo'",
            "v.vehiculo_id NOT IN (
                SELECT rv.vehiculo_id
                FROM reserva_vehiculos rv
                INNER JOIN reservas r ON rv.reserva_id = r.reserva_id
                WHERE (r.estado = 'Activa' OR r.estado = 'Pendiente')
                AND r.fecha_inicio <= ?
                AND r.fecha_fin >= ?
            )",
        ];
        $params[] = $f_fin;
        $params[] = $f_ini;

        if ($marca !== '') {
            $whereParts[] = 'm.nombre LIKE ?';
            $params[] = '%' . $marca . '%';
        }
        if ($modelo !== '') {
            $whereParts[] = 'mo.nombre LIKE ?';
            $params[] = '%' . $modelo . '%';
        }
        if ($term !== '') {
            $whereParts[] = '(v.placa LIKE ? OR m.nombre LIKE ? OR mo.nombre LIKE ?)';
            $t = '%' . $term . '%';
            $params[] = $t;
            $params[] = $t;
            $params[] = $t;
        }

        if ($exclude_ids !== '') {
            $ids = array_filter(array_map('intval', explode(',', $exclude_ids)));
            if (count($ids) > 0) {
                $whereParts[] = 'v.vehiculo_id NOT IN (' . implode(',', $ids) . ')';
            }
        }

        $sql = "SELECT v.vehiculo_id, v.placa, v.anio,
                       COALESCE(NULLIF(TRIM(v.imagen), ''), '') AS imagen,
                       m.nombre AS marca, mo.nombre AS modelo, g.nombre AS tipo,
                       COALESCE(pv.precio, 0) AS precio_dia
                FROM vehiculos v
                INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id
                INNER JOIN marcas m ON mo.marca_id = m.marca_id
                INNER JOIN gamas g ON v.gama_id = g.gama_id
                INNER JOIN precios_vehiculo pv ON v.vehiculo_id = pv.vehiculo_id AND pv.estado = 'Activo'
                WHERE " . implode(' AND ', $whereParts) . '
                ORDER BY m.nombre, mo.nombre, v.placa';

        return $this->selectAllParams($sql, $params);
    }

    public function getPreciosVehiculo(int $vehiculo_id)
    {
        $sql = "SELECT pv.precio, t.nombre AS tipo 
                FROM precios_vehiculo pv 
                INNER JOIN tipo_dia t ON pv.tipo_dia_id = t.tipo_dia_id 
                WHERE pv.vehiculo_id = $vehiculo_id";
        return $this->selectAll($sql);
    }

    public function getFeriadosRango(string $f_ini, string $f_fin)
    {
        $sql = "SELECT fecha FROM feriados WHERE fecha BETWEEN ? AND ?";
        $datos = array($f_ini, $f_fin);
        return $this->selectAllParams($sql, $datos);
    }

    public function getFeriado(string $fecha)
    {
        $sql = "SELECT * FROM feriados WHERE fecha = ?";
        return $this->selectParams($sql, array($fecha));
    }

    public function getPrecioEspecifico(int $vehiculo_id)
    {
        $sql = "SELECT precio FROM precios_vehiculo 
                WHERE vehiculo_id = ? AND estado = 'Activo'";
        return $this->selectParams($sql, array($vehiculo_id));
    }

    public function getClientes()
    {
        $sql = "SELECT cliente_id as id, nombre, apellido, cedula as dni FROM clientes WHERE estado = 'Activo'";
        return $this->selectAll($sql);
    }

    public function buscarClientes(string $valor)
    {
        $sql = "SELECT cliente_id as id, nombre, apellido, cedula as dni, telefono, direccion 
                FROM clientes 
                WHERE estado = 'Activo' 
                AND (nombre LIKE ? OR apellido LIKE ? OR cedula LIKE ?) 
                LIMIT 10";
        $params = ["%$valor%", "%$valor%", "%$valor%"];
        return $this->selectAllParams($sql, $params);
    }

    public function registrarReserva(int $cliente_id, string $f_ini, string $f_fin, float $total, string $estado = 'Pendiente', string $observacion = '')
    {
        $estadosPermitidos = array('Pendiente', 'Activa', 'Cancelada', 'Devuelta', 'Finalizada');
        if (!in_array($estado, $estadosPermitidos, true)) {
            $estado = 'Pendiente';
        }
        $sql = "INSERT INTO reservas(cliente_id, fecha_reserva, fecha_inicio, fecha_fin, total, observacion, estado) 
                VALUES (?, NOW(), ?, ?, ?, ?, ?)";
        $datos = array($cliente_id, $f_ini, $f_fin, $total, $observacion, $estado);
        return $this->insertar($sql, $datos);
    }

    public function registrarDetalleReserva(int $reserva_id, int $vehiculo_id, float $precio, int $dias, float $subtotal, string $estado = 'Pendiente')
    {
        $sql = "INSERT INTO reserva_vehiculos(reserva_id, vehiculo_id, precio_unitario, dias, subtotal, estado) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $datos = array($reserva_id, $vehiculo_id, $precio, $dias, $subtotal, $estado);
        return $this->save($sql, $datos);
    }

    public function verReserva(int $id)
    {
        $sql = "SELECT r.*, c.nombre, c.apellido, c.cedula, c.telefono, c.direccion 
                FROM reservas r 
                INNER JOIN clientes c ON r.cliente_id = c.cliente_id 
                WHERE r.reserva_id = $id";
        return $this->select($sql);
    }

    public function getDetalleReserva(int $reserva_id)
    {
        $sql = "SELECT rv.*, v.placa, mo.nombre AS modelo, m.nombre AS marca
                FROM reserva_vehiculos rv 
                INNER JOIN vehiculos v ON rv.vehiculo_id = v.vehiculo_id 
                INNER JOIN modelos mo ON v.modelo_id = mo.modelo_id 
                INNER JOIN marcas m ON mo.marca_id = m.marca_id
                WHERE rv.reserva_id = $reserva_id";
        return $this->selectAll($sql);
    }

    public function getSaldoReserva(int $reserva_id)
    {
        $sql = "SELECT r.total,
                       COALESCE((SELECT SUM(pd.monto_aplicado) FROM pago_detalle pd WHERE pd.reserva_id = r.reserva_id), 0) AS total_pagos,
                       COALESCE((SELECT SUM(pen.monto) FROM penalidades pen WHERE pen.reserva_id = r.reserva_id), 0) AS total_penalidades
                FROM reservas r
                WHERE r.reserva_id = $reserva_id";
        return $this->select($sql);
    }

    public function registrarPago(int $reserva_id, float $monto)
    {
        $sqlCabina = "INSERT INTO pagos(fecha_pago, metodo, total, estado) VALUES (NOW(), 'Efectivo', ?, 'Activo')";
        $id_pago = $this->insertar($sqlCabina, array($monto));
        if ($id_pago > 0) {
            $sqlDetalle = "INSERT INTO pago_detalle(pago_id, reserva_id, monto_aplicado) VALUES (?, ?, ?)";
            return $this->save($sqlDetalle, array($id_pago, $reserva_id, $monto));
        }
        return 0;
    }

    public function getPagosByReserva(int $id_reserva)
    {
        $sql = "SELECT p.pago_id AS id, p.fecha_pago AS fecha, pd.monto_aplicado AS total 
                FROM pagos p 
                INNER JOIN pago_detalle pd ON p.pago_id = pd.pago_id 
                WHERE pd.reserva_id = $id_reserva ORDER BY p.pago_id DESC";
        return $this->selectAll($sql);
    }
}
?>
