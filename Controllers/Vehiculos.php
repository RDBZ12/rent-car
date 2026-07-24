<?php

class Vehiculos extends Controller
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
        $data['marcas'] = $this->model->getMarcasActivas();
        $data['tipos'] = $this->model->getDatos('gamas');
        $data['tipos_dia'] = $this->model->getTiposDia();
        $this->views->getView($this, "index", $data);
    }
    public function getModelosMarca(int $id)
    {
        $data = $this->model->getModelosByMarca($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function listar()
    {
        $id_user = $_SESSION['id_usuario'];
        $data = $this->model->vehiculos();
        $date = date('Y-m-d');
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['date'] = $date;
            $foto = ($data[$i]['foto'] == '') ? 'default.png' : $data[$i]['foto'];
            if (strpos($foto, 'uploads/') === 0) {
                $url_img = base_url . $foto;
            } else {
                $url_img = base_url . "uploads/vehiculos/" . $foto;
            }
            $data[$i]['imagen'] = '<div class="d-flex justify-content-center">
                                    <img class="rounded-circle shadow-sm border border-2 border-white" src="' . $url_img . '" width="40" height="40" style="object-fit: cover;">
                                  </div>';
            $idVeh = (int) $data[$i]['id'];
            $est = $data[$i]['estado'];
            $data[$i]['estado_raw'] = $est;
            $btnEdit = '<button class="btn btn-outline-primary btn-sm" type="button" onclick="btnEditarVeh(' . $idVeh . ');" title="Editar"><i class="fas fa-edit"></i></button>';
            $btnDes = '<button class="btn btn-outline-danger btn-sm" type="button" onclick="btnEliminarVeh(' . $idVeh . ');" title="Desactivar"><i class="fas fa-trash-alt"></i></button>';
            $btnAct = '<button class="btn btn-outline-success btn-sm" type="button" onclick="btnReingresarVeh(' . $idVeh . ');" title="Reactivar"><i class="fas fa-undo"></i></button>';
            $wrap = '<div class="d-flex flex-wrap gap-1 justify-content-center">';
            if ($est === 'Inactivo') {
                $data[$i]['estado'] = '<span class="badge bg-secondary">Inactivo</span>';
                $data[$i]['editar'] = $wrap . $btnEdit . $btnAct . '</div>';
            } elseif ($est === 'Alquilado' || $data[$i]['en_reserva'] > 0) {
                $data[$i]['estado'] = '<span class="badge bg-dark"><i class="fas fa-exclamation-triangle text-warning me-1"></i>Alquilado</span>';
                $data[$i]['editar'] = $wrap . '<button class="btn btn-light btn-sm border text-muted" disabled title="Bloqueado por reserva activa"><i class="fas fa-lock"></i></button></div>';
                $data[$i]['DT_RowAttr'] = [
                    'style' => 'background-color: #ffee58 !important;', 
                    'title' => 'Vehículo con reserva activa. No se puede editar.'
                ];
            } else {
                $data[$i]['estado'] = '<span class="badge bg-success">Activo</span>';
                $data[$i]['editar'] = $wrap . $btnEdit . $btnDes . '</div>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        $placa = strClean($_POST['placa']);
        $modelo_id = intval(strClean($_POST['modelo']));
        $tipo = intval(strClean($_POST['tipo']));
        $anio = strClean($_POST['anio']);
        $color = strClean($_POST['color']);
        $kilometraje = strClean($_POST['kilometraje']);
        $combustible = strClean($_POST['combustible']);
        $id = strClean($_POST['id']);
        $precio = floatval(strClean($_POST['precio']));
        $tipo_dia = intval(strClean($_POST['tipo_dia_hidden']));
        $estado_precio = strClean($_POST['estado_precio_hidden']);
        
        $img = $_FILES['imagen'];
        $name = $img['name'];
        $tmpname = $img['tmp_name'];

        $fecha = date("YmdHis");
        if (empty($placa) || empty($modelo_id) || empty($tipo) || empty($anio) || empty($color) || $kilometraje === '' || empty($combustible)) {
            $msg = array('msg' => 'Todo los campos son obligatorios', 'icono' => 'warning');
        } elseif (!validarPlacaVehiculo($placa)) {
            $msg = array('msg' => 'La placa no es válida (3-15 caracteres, letras y números).', 'icono' => 'warning');
        } elseif (!validarAnioVehiculo($anio)) {
            $msg = array('msg' => 'El año del vehículo no es válido.', 'icono' => 'warning');
        } elseif (!validarTextoCatalogo($color, 2, 40)) {
            $msg = array('msg' => 'El color no es válido.', 'icono' => 'warning');
        } elseif (!validarNumeroEnteroNoNegativo($kilometraje)) {
            $msg = array('msg' => 'El kilometraje debe ser un número entero mayor o igual a 0.', 'icono' => 'warning');
        } elseif (!in_array($combustible, ['Full: 1/1', 'Semi Full: 3/4', 'Medio tanque: 1/2', 'Cuarto de tanque: 1/4', 'Vacío'])) {
            $msg = array('msg' => 'Seleccione un nivel de combustible válido.', 'icono' => 'warning');
        } else if (empty($precio) || empty($tipo_dia)) {
            $msg = array('msg' => 'Debe hacer clic en "Configurar Precio" para asignar un monto y Tipo de Día válido al vehículo', 'icono' => 'warning');
        } else {
            if (!empty($name)) {
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $formatos_permitidos = array('png', 'jpeg', 'jpg');
                if (!in_array($extension, $formatos_permitidos)) {
                    $msg = array('msg' => 'Archivo no permitido', 'icono' => 'warning');
                } else {
                    $imgNombreOriginal = $fecha . ".jpg";
                    $imgNombre = "uploads/vehiculos/" . $imgNombreOriginal;
                    $destino = $imgNombre;
                }
            } else if (!empty($_POST['foto_actual']) && empty($name)) {
                $imgNombre = $_POST['foto_actual'];
            } else {
                $imgNombre = "default.png";
            }

            // Descarga automática si es default.png o está vacía (Aplica para Nuevo y Editar)
            if ($imgNombre == "default.png") {
                $marcaModelo = $this->model->getMarcaModeloById($modelo_id);
                if ($marcaModelo) {
                    $resultadoDescarga = descargarImagenVehiculo($marcaModelo['marca'], $marcaModelo['modelo'], $anio);
                    if ($resultadoDescarga != "default.png") {
                        $imgNombre = $resultadoDescarga;
                    }
                }
            }

            if ($id == "") {
                $data = $this->model->registrarVehiculo($placa, $modelo_id, $tipo, $anio, $color, $kilometraje, $combustible, $imgNombre, $precio, $tipo_dia, $estado_precio);
                if ($data == "ok") {
                    if (!empty($name)) {
                        move_uploaded_file($tmpname, $destino);
                    }
                    $msg = array('msg' => 'Vehículo registrado con éxito', 'icono' => 'success');
                } else if ($data == "existe") {
                    $msg = array('msg' => 'El Vehículo ya existe', 'icono' => 'warning');
                } else {
                    $msg = array('msg' => 'Error al registrar', 'icono' => 'error');
                }
            } else {
                $imgDelete = $this->model->editarVeh($id);
                if ($imgDelete['foto'] != 'default.png' && !empty($name)) {
                    $pathDelete = $imgDelete['foto'];
                    if (strpos($pathDelete, 'uploads/') !== 0) {
                        $pathDelete = "uploads/vehiculos/" . $pathDelete;
                    }
                    if (file_exists($pathDelete)) {
                        unlink($pathDelete);
                    }
                }
                $data = $this->model->modificarVehiculo($placa, $modelo_id, $tipo, $anio, $color, $kilometraje, $combustible, $imgNombre, $id, $precio, $tipo_dia, $estado_precio);
                if ($data == "modificado") {
                    if (!empty($name)) {
                        move_uploaded_file($tmpname, $destino);
                    }
                    $msg = array('msg' => 'Vehículo modificado', 'icono' => 'success');
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
        $data = $this->model->editarVeh($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminar(int $id)
    {
        $data = $this->model->accionVeh('Inactivo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Vehículo desactivado: ya no aparecerá en reservas', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al desactivar el vehículo', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresar(int $id)
    {
        $data = $this->model->accionVeh('Activo', $id);
        if ($data == 1) {
            $msg = array('msg' => 'Vehículo reactivado correctamente', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al reactivar el vehículo', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function inactivos()
    {
        $data['vehiculos'] = $this->model->getVehiculos('Inactivo');
        $this->views->getView($this, "inactivos", $data);
    }
    public function buscarVehiculo()
    {
        if (isset($_GET['veh'])) {
            $data = $this->model->buscarVehiculo($_GET['veh']);
            $datos = array();
            foreach ($data as $row) {
                $tmp['id'] = $row['id'];
                $tmp['label'] = $row['placa'] . ' - ' . $row['marca'] . ' ' . $row['modelo'] . ' (' . $row['tipo'] . ' - ' . $row['anio'] . ')';
                $tmp['value'] = $row['marca'] . ' - ' . $row['modelo'] . ' - ' . $row['placa'];
                array_push($datos, $tmp);
            }
            echo json_encode($datos, JSON_UNESCAPED_UNICODE);
            die();
        }
    }
    public function obtenerImagenApi()
    {
        if (ob_get_length()) ob_clean();
        $modelo_id = intval($_POST['modelo']);
        $anio = strClean($_POST['anio']);
        $img = "default.png";
        $url = base_url . "uploads/vehiculos/default.png";
        $source = "Default";
        $debug = [];

        if ($modelo_id > 0 && !empty($anio)) {
            $marcaModelo = $this->model->getMarcaModeloById($modelo_id);
            if ($marcaModelo) {
                $resultado = resolverImagenVehiculo($marcaModelo['marca'], $marcaModelo['modelo'], $anio);

                if (!empty($resultado['path']) && $resultado['path'] !== 'default.png') {
                    $img = $resultado['path'];
                    $url = (strpos($resultado['path'], 'http://') === 0 || strpos($resultado['path'], 'https://') === 0)
                        ? $resultado['path']
                        : base_url . $resultado['path'];
                    $source = ($resultado['source'] === 'local')
                        ? 'Archivo local (misma marca/modelo/año)'
                        : 'API (descargada)';
                    $debug['resultado'] = 'imagen_encontrada';
                    $debug['origen'] = $resultado['source'];
                } else {
                    $debug['resultado'] = 'imagen_no_disponible';
                }

                $debug['marca'] = $marcaModelo['marca'];
                $debug['modelo'] = $marcaModelo['modelo'];
            } else {
                $debug['error'] = 'Modelo no encontrado en BD';
                $debug['modelo_id'] = $modelo_id;
            }
        } else {
            $debug['error'] = 'Parámetros inválidos';
            $debug['modelo_id'] = $modelo_id;
            $debug['anio'] = $anio;
        }
        
        echo json_encode([
            'img' => $img,
            'url' => $url,
            'source' => $source,
            'debug' => $debug
        ], JSON_UNESCAPED_UNICODE);
        die();
    }

    public function obtenerImagenDirecta()
    {
        if (ob_get_length()) ob_clean();
        $marca = strClean($_POST['marca'] ?? '');
        $modelo = strClean($_POST['modelo'] ?? '');
        $anio = strClean($_POST['anio'] ?? '');
        $img = 'default.png';
        $source = 'default';

        if (!empty($marca) && !empty($modelo) && !empty($anio)) {
            $res = resolverImagenVehiculo($marca, $modelo, $anio);
            $img = $res['path'];
            $source = $res['source'];
        }

        $full_url = (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0)
            ? $img
            : (($img === 'default.png')
                ? base_url . 'uploads/vehiculos/default.png'
                : ((strpos($img, 'uploads/') === 0) ? base_url . $img : base_url . 'uploads/vehiculos/' . $img));
        echo json_encode(['img' => $img, 'url' => $full_url, 'source' => $source], JSON_UNESCAPED_UNICODE);
        die();
    }
    public function validarPlacaUnica()
    {
        $placa = isset($_POST['placa']) ? strClean($_POST['placa']) : '';
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!validarPlacaVehiculo($placa)) {
            echo json_encode(['valida' => false, 'disponible' => false, 'msg' => 'Formato no válido']);
            die();
        }
        
        $existe = $this->model->verificarPlacaExistente($placa, $id);
        if ($existe) {
            echo json_encode(['valida' => true, 'disponible' => false, 'msg' => 'Placa ya registrada']);
        } else {
            echo json_encode(['valida' => true, 'disponible' => true, 'msg' => 'Placa disponible']);
        }
        die();
    }
}
