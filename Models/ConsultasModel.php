<?php
class ConsultasModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getVehiculosDisponibles()
    {
        $sql = "SELECT * FROM vw_vehiculos_disponibles";
        return $this->selectAll($sql);
    }

    public function getAlquileresActivos()
    {
        $sql = "SELECT * FROM vw_alquileres_activos";
        return $this->selectAll($sql);
    }

    public function getHistorialCliente(int $cliente_id = 0)
    {
        $sql = "SELECT * FROM vw_historial_cliente";
        if ($cliente_id > 0) {
            $sql .= " WHERE cliente_id = $cliente_id";
        }
        return $this->selectAll($sql);
    }

    public function getEstadoCuenta()
    {
        $sql = "SELECT * FROM vw_estado_cuenta";
        return $this->selectAll($sql);
    }

    public function getVehiculosFeriados()
    {
        $sql = "SELECT * FROM vw_vehiculos_feriados";
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
