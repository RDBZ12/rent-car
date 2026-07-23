<?php
class Modelos extends Controller
{
    public function __construct()
    {
        session_start();
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
        $data['marcas'] = $this->model->getMarcas();
        $this->views->getView($this, "index", $data);
    }
    public function listar()
    {
        $data = $this->model->getAllModelos();
        for ($i = 0; $i < count($data); $i++) {
            $idRow = (int) $data[$i]['id'];
            $btnEdit = '<button class="btn btn-outline-primary btn-sm" type="button" onclick="btnEditarModelo(' . $idRow . ');" title="Editar"><i class="fas fa-edit"></i></button>';
            $btnDes = '<button class="btn btn-outline-danger btn-sm" type="button" onclick="btnEliminarModelo(' . $idRow . ');" title="Desactivar"><i class="fas fa-trash-alt"></i></button>';
            $btnAct = '<button class="btn btn-outline-success btn-sm" type="button" onclick="btnReingresarModelo(' . $idRow . ');" title="Reactivar"><i class="fas fa-undo"></i></button>';
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
        $marca_id = strClean($_POST['marca_id']);
        $estado = isset($_POST['estado']) ? 'Activo' : 'Inactivo';
        $id = strClean($_POST['id']);
        if (empty($nombre) || empty($marca_id)) {
            $msg = array('msg' => 'El nombre y la marca son requeridos', 'icono' => 'warning');
        } elseif (!validarTextoCatalogo($nombre, 2, 120)) {
            $msg = array('msg' => 'El nombre del modelo no es válido.', 'icono' => 'warning');
        } elseif (!ctype_digit((string) $marca_id) || (int) $marca_id < 1) {
            $msg = array('msg' => 'Seleccione una marca válida.', 'icono' => 'warning');
        } else {
            if ($id == "") {
                $data = $this->model->registrarModelo($nombre, $marca_id, $estado);
                if ($data == "ok") {
                    $msg = array('msg' => 'Modelo registrado con éxito', 'icono' => 'success');
                } else if ($data == "existe") {
                    $msg = array('msg' => 'El modelo ya existe en esta marca', 'icono' => 'warning');
                } else {
                    $msg = array('msg' => 'Error al registrar', 'icono' => 'error');
                }
            } else {
                $data = $this->model->modificarModelo($nombre, $marca_id, $estado, $id);
                if ($data == "modificado") {
                    $msg = array('msg' => 'Modelo modificado', 'icono' => 'success');
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
        $data = $this->model->editarModelo($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminar(int $id)
    {
        $data = $this->model->accionModelo('Inactivo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Modelo dado de baja', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al eliminar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresar(int $id)
    {
        $data = $this->model->accionModelo('Activo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Modelo reingresado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error la reingresar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function inactivos()
    {
        $data['modelos'] = $this->model->getModelos('Inactivo');
        $this->views->getView($this, "inactivos", $data);
    }
}
?>
