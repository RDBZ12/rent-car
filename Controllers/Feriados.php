<?php
class Feriados extends Controller
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
        $this->views->getView($this, "index");
    }

    public function listar()
    {
        $data = $this->model->getFeriados();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<div class="d-flex">
                <button class="btn btn-outline-primary btn-sm me-1" type="button" onclick="btnEditarFeriado(' . $data[$i]['feriado_id'] . ');"><i class="fas fa-edit"></i></button>
                <button class="btn btn-outline-danger btn-sm" type="button" onclick="btnEliminarFeriado(' . $data[$i]['feriado_id'] . ');"><i class="fas fa-trash"></i></button>
                </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function registrar()
    {
        $fecha = strClean($_POST['fecha']);
        $descripcion = strClean($_POST['descripcion']);
        $id = $_POST['id'];

        if (empty($fecha) || empty($descripcion)) {
            $msg = array('msg' => 'Todos los campos son obligatorios', 'icono' => 'warning');
        } else {
            if ($id == "") {
                $data = $this->model->registrarFeriado($fecha, $descripcion);
                if ($data == "ok") {
                    $msg = array('msg' => 'Feriado registrado con éxito', 'icono' => 'success');
                } else if ($data == "existe") {
                    $msg = array('msg' => 'Esa fecha ya está registrada como feriado', 'icono' => 'warning');
                } else {
                    $msg = array('msg' => 'Error al registrar', 'icono' => 'error');
                }
            } else {
                $data = $this->model->modificarFeriado($fecha, $descripcion, intval($id));
                if ($data == "modificado") {
                    $msg = array('msg' => 'Feriado modificado con éxito', 'icono' => 'success');
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
        $data = $this->model->editarFeriado($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar(int $id)
    {
        $data = $this->model->eliminarFeriado($id);
        if ($data == 1) {
            $msg = array('msg' => 'Feriado eliminado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al eliminar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
}
?>
