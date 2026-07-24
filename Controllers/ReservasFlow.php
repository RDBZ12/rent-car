<?php
class ReservasFlow extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (empty($_SESSION['activo'])) {
            header("location: " . base_url);
        }
        parent::__construct();
        // Load ReservasModel
        require_once "Models/ReservasModel.php";
        $this->model = new ReservasModel();
    }

    public function index()
    {
        $this->views->getView($this, "index");
    }

    public function buscarCliente()
    {
        if (isset($_GET['term'])) {
            $valor = trim($_GET['term']);
            $data = $this->model->buscarClientes($valor);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function buscarVehiculos()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $f_ini = isset($_POST['f_ini']) ? trim((string) $_POST['f_ini']) : '';
            $f_fin = isset($_POST['f_fin']) ? trim((string) $_POST['f_fin']) : '';
            $term = isset($_POST['term']) ? trim((string) $_POST['term']) : '';
            $exclude = isset($_POST['exclude']) ? trim((string) $_POST['exclude']) : '';

            if (empty($f_ini) || empty($f_fin)) {
                echo json_encode(['error' => 'Fechas requeridas'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $data = $this->model->getVehiculosDisponibles($f_ini, $f_fin, '', '', $term, $exclude);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            die();
        }
    }

    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cliente_id = intval($_POST['cliente_id'] ?? 0);
            $f_ini = isset($_POST['f_ini']) ? trim((string) $_POST['f_ini']) : '';
            $f_fin = isset($_POST['f_fin']) ? trim((string) $_POST['f_fin']) : '';
            $total = floatval($_POST['total'] ?? 0);
            $abono = floatval($_POST['abono'] ?? 0);
            $observacion = isset($_POST['observacion']) ? trim((string) $_POST['observacion']) : '';
            $vehiculos = json_decode($_POST['vehiculos'] ?? '[]', true);

            // Validations
            if (empty($cliente_id)) {
                echo json_encode(['msg' => 'Debe seleccionar un cliente', 'icono' => 'warning']); die();
            }
            if (empty($f_ini) || empty($f_fin)) {
                echo json_encode(['msg' => 'Debe seleccionar fechas de inicio y fin', 'icono' => 'warning']); die();
            }
            if (new DateTime($f_fin) < new DateTime($f_ini)) {
                echo json_encode(['msg' => 'La fecha fin no puede ser menor a la fecha inicio', 'icono' => 'warning']); die();
            }
            if (empty($vehiculos)) {
                echo json_encode(['msg' => 'Debe agregar al menos un vehículo', 'icono' => 'warning']); die();
            }
            if ($abono < 0) {
                echo json_encode(['msg' => 'El abono no puede ser negativo', 'icono' => 'warning']); die();
            }
            if ($abono > $total) {
                echo json_encode(['msg' => 'El abono no puede ser mayor al total', 'icono' => 'warning']); die();
            }

            // Check for duplicate vehicles in the array
            $idsVeh = array_column($vehiculos, 'vehiculo_id');
            if (count($idsVeh) !== count(array_unique($idsVeh))) {
                echo json_encode(['msg' => 'No se permiten vehículos duplicados en la misma reserva', 'icono' => 'warning']); die();
            }

            // Start Transaction
            $this->model->beginTransaction();
            try {
                // 1. Save Header
                $reserva_id = $this->model->registrarReserva($cliente_id, $f_ini, $f_fin, $total, 'Pendiente', $observacion);
                
                if ($reserva_id > 0) {
                    // 2. Save Detail
                    foreach ($vehiculos as $v) {
                        $v_id = intval($v['vehiculo_id']);
                        $precio = floatval($v['precio_dia']);
                        $dias = intval($v['dias']);
                        $subtotal = floatval($v['subtotal']);
                        
                        $okDetalle = $this->model->registrarDetalleReserva($reserva_id, $v_id, $precio, $dias, $subtotal);
                        if (!$okDetalle) throw new Exception("Error al guardar detalle del vehículo ID: $v_id");
                    }

                    // 3. Save Initial Payment if exists
                    if ($abono > 0) {
                        $okPago = $this->model->registrarPago($reserva_id, $abono);
                        if (!$okPago) throw new Exception("Error al registrar el abono inicial");
                    }

                    $this->model->commit();
                    echo json_encode(['msg' => 'Reserva registrada con éxito', 'icono' => 'success', 'id' => $reserva_id]);
                } else {
                    throw new Exception("Error al registrar la cabecera de la reserva");
                }
            } catch (Exception $e) {
                $this->model->rollBack();
                echo json_encode(['msg' => 'Error: ' . $e->getMessage(), 'icono' => 'error']);
            }
            die();
        }
    }
}
?>
