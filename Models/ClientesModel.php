<?php
class ClientesModel extends Query{
    public function __construct()
    {
        parent::__construct();
    }
    public function getClientes(string $estado)
    {
        $sql = "SELECT cliente_id AS id, cedula AS dni, nombre, apellido, telefono, email, direccion, estado FROM clientes WHERE estado = '$estado'";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function getAllClientes()
    {
        $sql = "SELECT cliente_id AS id, cedula AS dni, nombre, apellido, telefono, email, direccion, estado FROM clientes";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function buscarCliente(string $valor)
    {
        $sql = "SELECT cliente_id AS id, nombre, apellido, direccion FROM clientes WHERE (nombre LIKE '%" . $valor . "%' OR apellido LIKE '%" . $valor . "%' OR cedula LIKE '%" . $valor . "%') AND estado = 'Activo'";
        $data = $this->selectAll($sql);
        return $data;
    }

    /**
     * Búsqueda para reservas: nombre, apellido o cédula (LIKE), solo activos.
     */
    public function buscarClientesParaReserva(string $q)
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }
        $sql = "SELECT cliente_id, nombre, apellido, cedula, telefono
                FROM clientes
                WHERE estado = 'Activo'
                AND (nombre LIKE ? OR apellido LIKE ? OR cedula LIKE ?)";
        $p = ['%' . $q . '%', '%' . $q . '%', '%' . $q . '%'];
        return $this->selectAllParams($sql, $p);
    }
    public function registrarCliente(string $dni, string $nombre, string $apellido, string $telefono, string $email, string $direccion, string $estado)
    {
        $verficar = "SELECT * FROM clientes WHERE nombre = '$nombre' OR cedula = '$dni'";
        $existe = $this->select($verficar);
        if (empty($existe)) {
            # code...
            $sql = "INSERT INTO clientes(cedula, nombre, apellido, telefono, email, direccion, estado) VALUES (?,?,?,?,?,?,?)";
            $datos = array($dni, $nombre, $apellido, $telefono, $email, $direccion, $estado);
            $data = $this->save($sql, $datos);
            if ($data == 1) {
                $res = "ok";
            }else{
                $res = "error";
            }
        }else{
            $res = "existe";
        }
        return $res;
    }
    public function modificarCliente(string $dni, string $nombre, string $apellido, string $telefono, string $email, string $direccion, string $estado, int $id)
    {
        $sql = "UPDATE clientes SET cedula = ?, nombre = ?, apellido = ?, telefono = ?, email = ?, direccion = ?, estado = ? WHERE cliente_id = ?";
        $datos = array($dni, $nombre, $apellido, $telefono, $email, $direccion, $estado, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function editarCli(int $id)
    {
        $sql = "SELECT cliente_id AS id, cedula AS dni, nombre, apellido, telefono, email, direccion, estado FROM clientes WHERE cliente_id = $id";
        $data = $this->select($sql);
        return $data;
    }
    public function accionCli(string $estado, int $id)
    {
        $sql = "UPDATE clientes SET estado = ? WHERE cliente_id = ?";
        $datos = array($estado, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }
}
