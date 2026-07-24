<?php
class Precios extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (empty($_SESSION['activo'])) {
            header("location: " . base_url);
        }
        parent::__construct();
        if ($_SESSION['id_usuario'] != 1) {
            header("location: " . base_url);
        }
    }

    public function index()
    {
        $data['vehiculos'] = $this->model->getVehiculos();
        $data['tipos'] = $this->model->getTipos();
        $this->views->getView($this, "index", $data);
    }

    public function listar()
    {
        $data = $this->model->getPrecios();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['vehiculo'] = $data[$i]['marca'] . ' ' . $data[$i]['modelo'] . ' (' . $data[$i]['placa'] . ')';
            $idRow = (int) $data[$i]['id'];
            $btnEdit = '<button class="btn btn-outline-primary btn-sm" type="button" onclick="btnEditarPrecio(' . $idRow . ');" title="Editar"><i class="fas fa-edit"></i></button>';
            $btnDes = '<button class="btn btn-outline-danger btn-sm" type="button" onclick="btnEliminarPrecio(' . $idRow . ');" title="Desactivar"><i class="fas fa-trash-alt"></i></button>';
            $btnAct = '<button class="btn btn-outline-success btn-sm" type="button" onclick="btnReingresarPrecio(' . $idRow . ');" title="Reactivar"><i class="fas fa-undo"></i></button>';
            $wrap = '<div class="d-flex flex-wrap gap-1 justify-content-center">';
            if ($data[$i]['estado'] == 'Activo') {
                $data[$i]['estado_badge'] = '<span class="badge bg-success">Activo</span>';
                $data[$i]['acciones'] = $wrap . $btnEdit . $btnDes . '</div>';
            } else {
                $data[$i]['estado_badge'] = '<span class="badge bg-secondary">Inactivo</span>';
                $data[$i]['acciones'] = $wrap . $btnEdit . $btnAct . '</div>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function registrar()
    {
        $vehiculo = intval($_POST['vehiculo']);
        $tipo = intval($_POST['tipo_dia']);
        $precio = floatval($_POST['precio']);
        $id = $_POST['id'];

        if (empty($vehiculo) || empty($tipo) || $precio <= 0) {
            $msg = array('msg' => 'Todos los campos son obligatorios', 'icono' => 'warning');
        } elseif ($precio > 999999.99) {
            $msg = array('msg' => 'El precio excede el máximo permitido.', 'icono' => 'warning');
        } else {
            if ($id == "") {
                $data = $this->model->registrarPrecio($vehiculo, $tipo, $precio);
                if ($data == "ok") {
                    $msg = array('msg' => 'Precio registrado con éxito', 'icono' => 'success');
                } else {
                    $msg = array('msg' => 'Error al registrar', 'icono' => 'error');
                }
            } else {
                $data = $this->model->modificarPrecio($vehiculo, $tipo, $precio, intval($id));
                if ($data == "modificado") {
                    $msg = array('msg' => 'Precio modificado con éxito', 'icono' => 'success');
                } else {
                    $msg = array('msg' => 'Error al modificar', 'icono' => 'error');
                }
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar(int $id)
    {
        $data = $this->model->editarPrecio($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar(int $id)
    {
        $data = $this->model->accionPrecio('Inactivo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Precio desactivado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al desactivar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function reingresar(int $id)
    {
        $data = $this->model->accionPrecio('Activo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Precio reactivado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al reactivar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
}
?>
