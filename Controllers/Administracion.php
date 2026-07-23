<?php

class Administracion extends Controller
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
        $data['empresa'] = $this->model->getEmpresa();
        $data['monedas'] = $this->model->getMonedas(1);
        $this->views->getView($this, "index", $data);
    }
    public function home()
    {
        $data['usuarios'] = $this->model->getDatos('usuarios');
        $data['clientes'] = $this->model->getDatos('clientes');
        $data['vehiculos'] = $this->model->getDatos('vehiculos');
        $data['gamas'] = $this->model->getDatos('gamas');
        $data['marcas'] = $this->model->getDatos('marcas');
        $this->views->getView($this, "home", $data);
    }
    function is_valid_email($str)
    {
        return (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
    }
    public function modificar()
    {
        if ($this->is_valid_email($_POST['correo'])) {
            $ruc = intval(strClean($_POST['ruc']));
            $nombre = strClean($_POST['nombre']);
            $tel = strClean($_POST['telefono']);
            $dir = strClean($_POST['direccion']);
            $correo = strClean($_POST['correo']);
            $mensaje = strClean($_POST['mensaje']);
            $moneda = strClean($_POST['moneda']);
            $id = intval(strClean($_POST['id']));
            $img = $_FILES['imagen'];
            $tmpName = $img['tmp_name'];
            if (empty($id) || empty($nombre) || empty($tel) || empty($correo) || empty($dir) || empty($moneda)) {
                $msg = array('msg' => 'Todo los campos son requeridos', 'icono' => 'warning');
            } else {
                $name = "logo.png";
                $destino = 'Assets/img/logo.png';
                $data = $this->model->modificar($ruc, $nombre, $tel, $correo, $dir, $mensaje, $name, $moneda,$id);
                if ($data == 'ok') {
                    if (!empty($img['name'])) {
                        $extension = pathinfo($img['name'], PATHINFO_EXTENSION);
                        $formatos_permitidos =  array('png');
                        $extension = pathinfo($img['name'], PATHINFO_EXTENSION);
                        if (!in_array($extension, $formatos_permitidos)) {
                            $msg = array('msg' => 'Imagen no permitido', 'icono' => 'warning');
                        } else {
                            move_uploaded_file($tmpName, $destino);
                            $msg = array('msg' => 'Datos modificado con éxito', 'icono' => 'success');
                        }
                    } else {
                        $msg = array('msg' => 'Datos modificado con éxito', 'icono' => 'success');
                    }
                } else {
                    $msg = array('msg' => 'Error al modificar', 'icono' => 'error');
                }
            }
        }else{
            $msg = array('msg' => 'Ingrese un correo valido', 'icono' => 'warning');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    //Monedas
    public function moneda()
    {
        if ($_SESSION['id_usuario'] != 1) {
            header("location: " . base_url);
        }
        $this->views->getView($this, 'moneda');
    }
    public function listarMonedas()
    {
        $data = $this->model->getAllMonedas();
        for ($i = 0; $i < count($data); $i++) {
            $idRow = (int) $data[$i]['id'];
            $btnEdit = '<button class="btn btn-outline-primary btn-sm" type="button" onclick="btnEditarMoneda(' . $idRow . ');" title="Editar"><i class="fas fa-edit"></i></button>';
            $btnDes = '<button class="btn btn-outline-danger btn-sm" type="button" onclick="btnEliminarMoneda(' . $idRow . ');" title="Desactivar"><i class="fas fa-trash-alt"></i></button>';
            $btnAct = '<button class="btn btn-outline-success btn-sm" type="button" onclick="btnReingresarMoneda(' . $idRow . ');" title="Reactivar"><i class="fas fa-undo"></i></button>';
            $wrap = '<div class="d-flex flex-wrap gap-1 justify-content-center">';
            if ($data[$i]['estado'] == 1) {
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
    public function registrarMoneda()
    {
        $simbolo = strClean($_POST['simbolo']);
        $nombre = strClean($_POST['nombre']);
        $id = strClean($_POST['id']);
        if (strlen(trim($simbolo)) < 1 || strlen(trim($simbolo)) > 12) {
            $msg = array('msg' => 'El símbolo de la moneda debe tener entre 1 y 12 caracteres.', 'icono' => 'warning');
        } elseif (!validarTextoCatalogo($nombre, 2, 120)) {
            $msg = array('msg' => 'El nombre de la moneda no es válido.', 'icono' => 'warning');
        } elseif ($id == '') {
            $data = $this->model->registrarMoneda($simbolo, $nombre);
            if ($data == 'ok') {
                $msg = array('msg' => 'Moneda registrado', 'icono' => 'success');
            } else {
                $msg = array('msg' => 'Error al registrar', 'icono' => 'error');
            }
        } else {
            $data = $this->model->modificarMoneda($simbolo, $nombre, $id);
            if ($data == "modificado") {
                $msg = array('msg' => 'Moneda modificado con éxito', 'icono' => 'success');
            } else {
                $msg = array('msg' => 'Error al modificar la moneda', 'icono' => 'error');
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function editarMoneda(int $id)
    {
        $data = $this->model->editarMoneda($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminarMoneda(int $id)
    {
        $data = $this->model->accionMoneda(0, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Moneda dado de baja', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al eliminar el cliente', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresarMoneda(int $id)
    {
        $data = $this->model->accionMoneda(1, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Moneda reingresado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al reingresar la moneda', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function inactivos()
    {
        if ($_SESSION['id_usuario'] != 1) {
            header("location: " . base_url);
        }
        $data['monedas'] = $this->model->getMonedas(0);
        $this->views->getView($this, "inactivos", $data);
    }
}
