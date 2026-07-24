<?php
class ConsultasModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getVehiculosDisponibles()
    {
        $sql = "SELECT v.vehiculo_id, v.placa, v.anio, v.color,
                       COALESCE(NULLIF(TRIM(v.imagen), ''), 'default.png') AS imagen,
                       COALESCE(m.nombre, 'Sin Marca') AS marca, 
                       COALESCE(mo.nombre, 'Sin Modelo') AS modelo, 
                       v.estado
                FROM vehiculos v
                LEFT JOIN modelos mo ON v.modelo_id = mo.modelo_id
                LEFT JOIN marcas m ON mo.marca_id = m.marca_id
                WHERE v.estado = 'Activo'
                  AND v.vehiculo_id NOT IN (
                      SELECT rv.vehiculo_id
                      FROM reserva_vehiculos rv
                      INNER JOIN reservas r ON rv.reserva_id = r.reserva_id
                      WHERE r.estado IN ('Activa', 'Pendiente')
                        AND rv.vehiculo_id NOT IN (
                            SELECT rd.vehiculo_id
                            FROM recepcion_detalle rd
                            INNER JOIN recepciones rec ON rd.recepcion_id = rec.recepcion_id
                            WHERE rec.reserva_id = r.reserva_id
                        )
                  )
                ORDER BY m.nombre, mo.nombre, v.placa";
        return $this->selectAll($sql);
    }

    public function getAlquileresActivos()
    {
        $sql = "SELECT r.reserva_id, r.cliente_id, r.fecha_inicio, r.fecha_fin, r.total, r.estado,
                       c.nombre, c.apellido,
                       (SELECT GROUP_CONCAT(CONCAT(m.nombre, ' ', mo.nombre, ' (', v.placa, ')') SEPARATOR ' | ')
                        FROM reserva_vehiculos rv
                        INNER JOIN vehiculos v ON rv.vehiculo_id = v.vehiculo_id
                        LEFT JOIN modelos mo ON v.modelo_id = mo.modelo_id
                        LEFT JOIN marcas m ON mo.marca_id = m.marca_id
                        WHERE rv.reserva_id = r.reserva_id
                          AND rv.vehiculo_id NOT IN (
                              SELECT rd.vehiculo_id
                              FROM recepcion_detalle rd
                              INNER JOIN recepciones rec ON rd.recepcion_id = rec.recepcion_id
                              WHERE rec.reserva_id = r.reserva_id
                          )
                       ) AS vehiculo
                FROM reservas r
                INNER JOIN clientes c ON r.cliente_id = c.cliente_id
                WHERE r.estado IN ('Activa', 'Pendiente')
                HAVING vehiculo IS NOT NULL AND vehiculo != ''
                ORDER BY r.reserva_id DESC";
        return $this->selectAll($sql);
    }

    public function getHistorialCliente(int $cliente_id = 0)
    {
        $sql = "SELECT r.reserva_id, r.cliente_id, r.fecha_inicio, r.fecha_fin, r.estado,
                       COALESCE(NULLIF(TRIM(v.imagen), ''), 'default.png') AS imagen,
                       CONCAT(COALESCE(m.nombre, ''), ' ', COALESCE(mo.nombre, ''), ' (', COALESCE(v.placa, 'S/P'), ')') AS vehiculo
                FROM reservas r
                INNER JOIN reserva_vehiculos rv ON r.reserva_id = rv.reserva_id
                INNER JOIN vehiculos v ON rv.vehiculo_id = v.vehiculo_id
                LEFT JOIN modelos mo ON v.modelo_id = mo.modelo_id
                LEFT JOIN marcas m ON mo.marca_id = m.marca_id";
        if ($cliente_id > 0) {
            $sql .= " WHERE r.cliente_id = $cliente_id";
        }
        $sql .= " ORDER BY r.reserva_id DESC";
        return $this->selectAll($sql);
    }

    public function getEstadoCuenta()
    {
        $sql = "SELECT r.reserva_id, r.total,
                       COALESCE((SELECT SUM(pd.monto_aplicado) FROM pago_detalle pd WHERE pd.reserva_id = r.reserva_id), 0) AS pagado,
                       (r.total - COALESCE((SELECT SUM(pd.monto_aplicado) FROM pago_detalle pd WHERE pd.reserva_id = r.reserva_id), 0)) AS saldo
                FROM reservas r
                ORDER BY r.reserva_id DESC";
        return $this->selectAll($sql);
    }

    public function getVehiculosFeriados()
    {
        $sql = "SELECT r.reserva_id, v.placa, v.color,
                       COALESCE(NULLIF(TRIM(v.imagen), ''), 'default.png') AS imagen,
                       COALESCE(m.nombre, '') AS marca, COALESCE(mo.nombre, '') AS modelo,
                       f.fecha AS fecha_feriado, f.nombre AS nombre_feriado
                FROM reservas r
                INNER JOIN reserva_vehiculos rv ON r.reserva_id = rv.reserva_id
                INNER JOIN vehiculos v ON rv.vehiculo_id = v.vehiculo_id
                LEFT JOIN modelos mo ON v.modelo_id = mo.modelo_id
                LEFT JOIN marcas m ON mo.marca_id = m.marca_id
                INNER JOIN feriados f ON f.fecha BETWEEN r.fecha_inicio AND r.fecha_fin
                ORDER BY f.fecha DESC";
        return $this->selectAll($sql);
    }

    // Para llenar el combo de clientes en el filtro
    public function getClientes(string $q = '')
    {
        $sql = "SELECT cliente_id AS id, nombre, apellido, cedula FROM clientes WHERE estado = 'Activo'";
        if ($q != '') {
            $sql .= " AND (nombre LIKE '%$q%' OR apellido LIKE '%$q%' OR cedula LIKE '%$q%')";
        }
        $sql .= " LIMIT 10";
        return $this->selectAll($sql);
    }
}
?>
