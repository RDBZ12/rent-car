<?php
class TiposDia extends Controller
{
    public function __construct()
    {
        session_start();
        if (empty($_SESSION['activo'])) {
            header("location: " . base_url);
        }
        parent::__construct();
    }
    public function index()
    {
        if ($_SESSION['id_usuario'] != 1) {
            header("location: " . base_url);
        }
        $this->views->getView($this, "index");
    }
    public function listar()
    {
        if ($_SESSION['id_usuario'] != 1) {
            header("location: " . base_url);
        }
        $data = $this->model->getTiposDia();
        for ($i = 0; $i < count($data); $i++) {
            $idRow = (int) $data[$i]['id'];
            $btnEdit = '<button class="btn btn-outline-primary btn-sm" type="button" onclick="btnEditarTipoDia(' . $idRow . ');" title="Editar"><i class="fas fa-edit"></i></button>';
            $btnDes = '<button class="btn btn-outline-danger btn-sm" type="button" onclick="btnEliminarTipoDia(' . $idRow . ');" title="Desactivar"><i class="fas fa-trash-alt"></i></button>';
            $btnAct = '<button class="btn btn-outline-success btn-sm" type="button" onclick="btnReingresarTipoDia(' . $idRow . ');" title="Reactivar"><i class="fas fa-undo"></i></button>';
            $wrap = '<div class="d-flex flex-wrap gap-1 justify-content-center">';
            if ($data[$i]['estado'] == 'Activo') {
                $data[$i]['estado'] = '<span class="badge bg-success">Activo</span>';
                $data[$i]['acciones'] = $wrap . $btnEdit . $btnDes . '</div>';
            } else {
                $data[$i]['estado'] = '<span class="badge bg-secondary">Inactivo</span>';
                $data[$i]['acciones'] = $wrap . $btnEdit . $btnAct . '</div>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = strClean($_POST['nombre']);
            $id = strClean($_POST['id']);
            if (empty($nombre)) {
                $msg = array('msg' => 'El nombre es obligatorio', 'icono' => 'warning');
            } elseif (!validarTextoCatalogo($nombre, 2, 120)) {
                $msg = array('msg' => 'El nombre del tipo de día no es válido.', 'icono' => 'warning');
            } else {
                if ($id == "") {
                    $data = $this->model->registrarTipoDia($nombre);
                    if ($data == "ok") {
                        $msg = array('msg' => 'Tipo de Día registrado con éxito', 'icono' => 'success');
                    } else if ($data == "existe") {
                        $msg = array('msg' => 'El Tipo de Día ya existe', 'icono' => 'warning');
                    } else {
                        $msg = array('msg' => 'Error al registrar', 'icono' => 'error');
                    }
                } else {
                    $data = $this->model->modificarTipoDia($nombre, $id);
                    if ($data == "modificado") {
                        $msg = array('msg' => 'Tipo de Día modificado con éxito', 'icono' => 'success');
                    } else if ($data == "existe") {
                        $msg = array('msg' => 'El Tipo de Día ya existe', 'icono' => 'warning');
                    } else {
                        $msg = array('msg' => 'Error al modificar', 'icono' => 'error');
                    }
                }
            }
            echo json_encode($msg, JSON_UNESCAPED_UNICODE);
            die();
        }
    }
    public function editar(int $id)
    {
        $data = $this->model->editarTipoDia($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminar(int $id)
    {
        $data = $this->model->accionTipoDia('Inactivo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Tipo de Día Inactivo', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al inactivar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresar(int $id)
    {
        $data = $this->model->accionTipoDia('Activo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Tipo de Día Restaurado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al restaurar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
}
?>
