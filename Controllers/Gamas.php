<?php
class Gamas extends Controller{
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (empty($_SESSION['activo'])) {
            header("location: ".base_url);
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
        $data = $this->model->getAllGamas();
        for ($i = 0; $i < count($data); $i++) {
            $idRow = (int) $data[$i]['id'];
            $btnEdit = '<button class="btn btn-outline-primary btn-sm" type="button" onclick="btnEditarGama(' . $idRow . ');" title="Editar"><i class="fas fa-edit"></i></button>';
            $btnDes = '<button class="btn btn-outline-danger btn-sm" type="button" onclick="btnEliminarGama(' . $idRow . ');" title="Desactivar"><i class="fas fa-trash-alt"></i></button>';
            $btnAct = '<button class="btn btn-outline-success btn-sm" type="button" onclick="btnReingresarGama(' . $idRow . ');" title="Reactivar"><i class="fas fa-undo"></i></button>';
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
        $nombre = strClean($_POST['nombre']);
        $descripcion = strClean($_POST['descripcion']);
        $estado = isset($_POST['estado']) ? 'Activo' : 'Inactivo';
        $id = strClean($_POST['id']);
        if (empty($nombre)) {
            $msg = array('msg' => 'El nombre es requerido', 'icono' => 'warning');
        } elseif (!validarTextoCatalogo($nombre, 2, 120)) {
            $msg = array('msg' => 'El nombre de la gama no es válido.', 'icono' => 'warning');
        } elseif (!validarTextoCatalogo($descripcion, 2, 500)) {
            $msg = array('msg' => 'La descripción debe tener entre 2 y 500 caracteres válidos.', 'icono' => 'warning');
        } else {
            if ($id == "") {
                    $data = $this->model->registrarGama($nombre, $descripcion, $estado);
                    if ($data == "ok") {
                        $msg = array('msg' => 'Gama registrada con éxito', 'icono' => 'success');
                    }else if($data == "existe"){
                        $msg = array('msg' => 'La gama ya existe', 'icono' => 'warning');
                    }else{
                        $msg = array('msg' => 'Error al registrar', 'icono' => 'error');
                    }
            }else{
                $data = $this->model->modificarGama($nombre, $descripcion, $estado, $id);
                if ($data == "modificado") {
                    $msg = array('msg' => 'Gama modificada', 'icono' => 'success');
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
        $data = $this->model->editarGama($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminar(int $id)
    {
        $data = $this->model->accionGama('Inactivo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Gama dada de baja', 'icono' => 'success');
        }else{
            $msg = array('msg' => 'Error al eliminar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresar(int $id)
    {
        $data = $this->model->accionGama('Activo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Gama reingresada', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al reingresar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function inactivos()
    {
        $data['gamas'] = $this->model->getGamas('Inactivo');
        $this->views->getView($this, "inactivos", $data);
    }
}
