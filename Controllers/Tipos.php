<?php
class Tipos extends Controller{
    public function __construct() {
        session_start();
        if (empty($_SESSION['activo'])) {
            header("location: ".base_url);
        }
        parent::__construct();
        if ($_SESSION['id_usuario'] != 1) {
            header("location: " . base_url);
            exit;
        }
    }
    public function index()
    {
        $this->views->getView($this, "index");
    }
    public function listar()
    {
        $data = $this->model->getAllTipos();
        if (is_array($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $idRow = (int) $data[$i]['id'];
                $btnEdit = '<button class="btn btn-outline-primary btn-sm" type="button" onclick="btnEditarTipo(' . $idRow . ');" title="Editar"><i class="fas fa-edit"></i></button>';
                $btnDes = '<button class="btn btn-outline-danger btn-sm" type="button" onclick="btnEliminarTipo(' . $idRow . ');" title="Desactivar"><i class="fas fa-trash-alt"></i></button>';
                $btnAct = '<button class="btn btn-outline-success btn-sm" type="button" onclick="btnReingresarTipo(' . $idRow . ');" title="Reactivar"><i class="fas fa-undo"></i></button>';
                $wrap = '<div class="d-flex flex-wrap gap-1 justify-content-center">';
                if ($data[$i]['estado'] == 1) {
                    $data[$i]['estado'] = '<span class="badge bg-success">Activo</span>';
                    $data[$i]['editar'] = $wrap . $btnEdit . $btnDes . '</div>';
                } else {
                    $data[$i]['estado'] = '<span class="badge bg-secondary">Inactivo</span>';
                    $data[$i]['editar'] = $wrap . $btnEdit . $btnAct . '</div>';
                }
            }
        } else {
            $data = [];
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        $nombre = strClean($_POST['nombre']);
        $estado = isset($_POST['estado']) ? 1 : 0;
        $id = strClean($_POST['id']);
        if (empty($nombre)) {
            $msg = array('msg' => 'El nombre es requerido', 'icono' => 'warning');
        } elseif (!validarTextoCatalogo($nombre, 2, 120)) {
            $msg = array('msg' => 'El nombre del tipo no es válido.', 'icono' => 'warning');
        } else {
            if ($id == "") {
                    $data = $this->model->registrarTipo($nombre, $estado);
                    if ($data == "ok") {
                        $msg = array('msg' => 'Tipo registrado con éxito', 'icono' => 'success');
                    }else if($data == "existe"){
                        $msg = array('msg' => 'El tipo ya existe', 'icono' => 'warning');
                    }else{
                        $msg = array('msg' => 'Error al registrar', 'icono' => 'error');
                    }
            }else{
                $data = $this->model->modificarTipo($nombre, $estado, $id);
                if ($data == "modificado") {
                    $msg = array('msg' => 'Tipo modificado', 'icono' => 'success');
                }else {
                    $msg = array('msg' => 'Error al modificar', 'icono' => 'error');
                }
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function editar(int $id)
    {
        $data = $this->model->editarTipo($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminar(int $id)
    {
        $data = $this->model->accionTipo(0, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Tipo dado de baja', 'icono' => 'success');
        }else{
            $msg = array('msg' => 'Error al eliminar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresar(int $id)
    {
        $data = $this->model->accionTipo(1, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Tipo reingresado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error la reingresar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function inactivos()
    {
        $data['tipos'] = $this->model->getTipos(0);
        $this->views->getView($this, "inactivos", $data);
    }
}
