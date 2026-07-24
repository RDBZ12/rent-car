<?php
class Clientes extends Controller{
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (empty($_SESSION['activo'])) {
            header("location: ".base_url);
        }
        parent::__construct();
    }
    public function index()
    {
        $this->views->getView($this, "index");
    }
    public function listar()
    {
        $data = $this->model->getAllClientes();
        for ($i = 0; $i < count($data); $i++) {
            $idRow = (int) $data[$i]['id'];
            $btnEdit = '<button class="btn btn-outline-primary btn-sm" type="button" onclick="btnEditarCli(' . $idRow . ');" title="Editar"><i class="fas fa-edit"></i></button>';
            $btnDes = '<button class="btn btn-outline-danger btn-sm" type="button" onclick="btnEliminarCli(' . $idRow . ');" title="Desactivar"><i class="fas fa-trash-alt"></i></button>';
            $btnAct = '<button class="btn btn-outline-success btn-sm" type="button" onclick="btnReingresarCli(' . $idRow . ');" title="Reactivar"><i class="fas fa-undo"></i></button>';
            $wrap = '<div class="d-flex flex-wrap gap-1 justify-content-center">';
            if ($data[$i]['estado'] == 'Activo') {
                $data[$i]['estado'] = '<span class="badge bg-success">Activo</span>';
                $data[$i]['editar'] = $wrap . $btnEdit . $btnDes . '</div>';
            } else {
                $data[$i]['estado'] = '<span class="badge bg-secondary">Inactivo</span>';
                $data[$i]['editar'] = $wrap . $btnEdit . $btnAct . '</div>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        $dni = strClean($_POST['dni']);
        $nombre = strClean($_POST['nombre']);
        $apellido = strClean($_POST['apellido']);
        $telefono = strClean($_POST['telefono']);
        $email = strClean($_POST['email']);
        $direccion = strClean($_POST['direccion']);
        $estado = isset($_POST['estado']) ? 'Activo' : 'Inactivo';
        $id = strClean($_POST['id']);
        if (empty($dni)) {
            $msg = array('msg' => 'Ingrese la cédula.', 'icono' => 'warning');
        } elseif (!validarCedulaDominicana($dni)) {
            $msg = array('msg' => 'La cédula no es válida (formato dominicano, 11 dígitos).', 'icono' => 'warning');
        } elseif (empty($telefono)) {
            $msg = array('msg' => 'Ingrese el teléfono.', 'icono' => 'warning');
        } elseif (!validarTelefonoRepublicaDominicana($telefono)) {
            $msg = array('msg' => 'El teléfono debe tener 10 dígitos (puede incluir +1).', 'icono' => 'warning');
        } elseif (empty($nombre)) {
            $msg = array('msg' => 'Ingrese el nombre.', 'icono' => 'warning');
        } elseif (!validarNombrePersona($nombre)) {
            $msg = array('msg' => 'El nombre solo debe contener letras y espacios.', 'icono' => 'warning');
        } elseif (empty($apellido)) {
            $msg = array('msg' => 'Ingrese el apellido.', 'icono' => 'warning');
        } elseif (!validarNombrePersona($apellido)) {
            $msg = array('msg' => 'El apellido solo debe contener letras y espacios.', 'icono' => 'warning');
        } elseif (empty($email)) {
            $msg = array('msg' => 'Ingrese el correo electrónico.', 'icono' => 'warning');
        } elseif (!validarEmailBasico($email)) {
            $msg = array('msg' => 'El correo electrónico no es válido.', 'icono' => 'warning');
        } elseif (empty($direccion)) {
            $msg = array('msg' => 'Ingrese la dirección.', 'icono' => 'warning');
        } elseif (!validarDireccionCliente($direccion)) {
            $msg = array('msg' => 'La dirección debe tener entre 5 y 500 caracteres.', 'icono' => 'warning');
        } else {
            if ($id == "") {
                $data = $this->model->registrarCliente($dni, $nombre, $apellido, $telefono, $email, $direccion, $estado);
                if ($data == "ok") {
                    $msg = array('msg' => 'Cliente registrado con éxito', 'icono' => 'success');
                } else if ($data == "existe") {
                    $msg = array('msg' => 'El cliente ya existe', 'icono' => 'warning');
                } else {
                    $msg = array('msg' => 'Error al registrar el cliente', 'icono' => 'error');
                }
            }else{
                $data = $this->model->modificarCliente($dni, $nombre, $apellido, $telefono, $email, $direccion, $estado, $id);
                if ($data == "modificado") {
                    $msg = array('msg' => 'Cliente modificado', 'icono' => 'success');
                } else {
                    $msg = array('msg' => 'Error al modificar el cliente', 'icono' => 'error');
                }
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function editar(int $id)
    {
        $data = $this->model->editarCli($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminar(int $id)
    {
        $data = $this->model->accionCli('Inactivo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Cliente dado de baja', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al eliminar el cliente', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresar(int $id)
    {
        $data = $this->model->accionCli('Activo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Cliente reingresado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error la reingresar el cliente', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function buscarCliente()
    {
        if (isset($_GET['cli'])) {
            $data = $this->model->buscarCliente($_GET['cli']);
            $datos = array();
            foreach ($data as $row) {
                $tmp['id'] = $row['id'];
                $tmp['label'] = $row['nombre'] . ' ' . $row['apellido'] . ' - ' . $row['direccion'];
                $tmp['value'] = $row['nombre'] . ' ' . $row['apellido'];
                array_push($datos, $tmp);
            }
            echo json_encode($datos, JSON_UNESCAPED_UNICODE);
            die();
        }
    }

    /** POST q — JSON lista de clientes para reserva (AJAX, sin recargar) */
    public function buscarClientesReserva()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([]);
            die();
        }
        $q = isset($_POST['q']) ? trim((string) $_POST['q']) : '';
        if (strlen($q) < 1) {
            echo json_encode([]);
            die();
        }
        $data = $this->model->buscarClientesParaReserva($q);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function inactivos()
    {
        $data['clientes'] = $this->model->getClientes('Inactivo');
        $this->views->getView($this, "inactivos", $data);
    }
}
