<?php
class Consultas extends Controller
{
    public function __construct()
    {
        session_start();
        if (empty($_SESSION['activo'])) {
            header("location: " . base_url);
        }
        parent::__construct();
    }

    // --- VISTAS ---

    public function vehiculos_disponibles()
    {
        $this->views->getView($this, "vehiculos_disponibles");
    }

    public function alquileres_activos()
    {
        $this->views->getView($this, "alquileres_activos");
    }

    public function historial_cliente()
    {
        $this->views->getView($this, "historial_cliente");
    }

    public function estado_cuenta()
    {
        $this->views->getView($this, "estado_cuenta");
    }

    public function vehiculos_feriados()
    {
        $this->views->getView($this, "vehiculos_feriados");
    }

    // --- API LISTAR JSON ---

    public function listar_vehiculos_disponibles()
    {
        $data = $this->model->getVehiculosDisponibles();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['imagen_html'] = $this->generarHtmlImagen($data[$i]['imagen']);
            $data[$i]['estado_html'] = '<span class="badge bg-success">Disponible</span>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function listar_alquileres_activos()
    {
        $data = $this->model->getAlquileresActivos();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['estado_html'] = '<span class="badge bg-primary">Activo</span>';
            $data[$i]['cliente'] = $data[$i]['nombre'] . ' ' . $data[$i]['apellido'];
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function listar_historial_cliente()
    {
        $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
        $data = $this->model->getHistorialCliente($cliente_id);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['imagen_html'] = $this->generarHtmlImagen($data[$i]['imagen']);
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function listar_estado_cuenta()
    {
        $data = $this->model->getEstadoCuenta();
        for ($i = 0; $i < count($data); $i++) {
            $saldo = floatval($data[$i]['saldo']);
            if ($saldo > 0) {
                $data[$i]['estado_html'] = '<span class="badge bg-warning text-dark">Pendiente</span>';
            } else if ($saldo < 0) {
                $data[$i]['estado_html'] = '<span class="badge bg-info">A favor</span>';
            } else {
                $data[$i]['estado_html'] = '<span class="badge bg-success">Saldado</span>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function listar_vehiculos_feriados()
    {
        $data = $this->model->getVehiculosFeriados();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['imagen_html'] = $this->generarHtmlImagen($data[$i]['imagen']);
            $data[$i]['estado_html'] = '<span class="badge bg-dark">Alquilado</span>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    private function generarHtmlImagen($foto)
    {
        $foto = ($foto == '') ? 'default.png' : $foto;
        if (strpos($foto, 'uploads/') === 0) {
            $url_img = base_url . $foto;
        } else {
            $url_img = base_url . "uploads/vehiculos/" . $foto;
        }
        return '<div class="d-flex justify-content-center">
                    <img class="rounded-circle shadow-sm border border-2 border-white" src="' . $url_img . '" width="40" height="40" style="object-fit: cover;">
                </div>';
    }
}
?>
