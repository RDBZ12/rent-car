<?php
class Penalidades extends Controller
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
        header("location: " . base_url . "Administracion");
    }

    public function listar()
    {
        $data = $this->model->getPenalidades();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['f_registro'] = '<span class="badge bg-primary">' . $data[$i]['fecha_registro'] .'</span>';
            $data[$i]['cliente'] = $data[$i]['nombre'] . ' ' . $data[$i]['apellido'];
            
            if ($data[$i]['tipo'] == 'Retraso') {
                $data[$i]['tipo_badge'] = '<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Retraso (' . $data[$i]['dias_retraso'] . ' días)</span>';
            } else if ($data[$i]['tipo'] == 'Daño' || $data[$i]['tipo'] == 'Daño/Extra') {
                $data[$i]['tipo_badge'] = '<span class="badge bg-danger"><i class="fas fa-car-crash"></i> Daños</span>';
            } else {
                $data[$i]['tipo_badge'] = '<span class="badge bg-secondary"><i class="fas fa-exclamation"></i> ' . $data[$i]['tipo'] . '</span>';
            }
            
            $data[$i]['monto_format'] = '$' . number_format($data[$i]['monto'], 2);
            $data[$i]['estado_badge'] = '<span class="badge bg-warning text-dark">Pendiente</span>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function buscarVehiculos(int $reserva_id)
    {
        $data = $this->model->getVehiculosPorReserva($reserva_id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function registrar()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $reserva_id = intval($_POST['reserva_id']);
            $vehiculo_id = intval($_POST['vehiculo_id']);
            $tipo = strClean($_POST['tipo']);
            $descripcion = strClean($_POST['descripcion']);
            $monto = floatval($_POST['monto']);

            if (empty($reserva_id) || empty($vehiculo_id) || empty($monto) || empty($tipo)) {
                $msg = array('msg' => 'Faltan datos obligatorios', 'icono' => 'warning');
            } else {
                $data = $this->model->registrarPenalidadManual($reserva_id, $vehiculo_id, $tipo, $descripcion, $monto);
                if ($data == "ok") {
                    $msg = array('msg' => 'Penalidad registrada con éxito', 'icono' => 'success');
                } else {
                    $msg = array('msg' => 'Error al registrar', 'icono' => 'error');
                }
            }
            echo json_encode($msg, JSON_UNESCAPED_UNICODE);
            die();
        }
    }
}
?>
