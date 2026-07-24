<?php
class Reportes extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (empty($_SESSION['activo'])) {
            header("location: " . base_url);
        }
        parent::__construct();
    }

    // --- VISTAS REPORTES ---

    public function ingresos_fecha()
    {
        $this->views->getView($this, "ingresos_fecha");
    }

    public function vehiculos_rentados()
    {
        $this->views->getView($this, "vehiculos_rentados");
    }

    public function clientes_frecuentes()
    {
        $this->views->getView($this, "clientes_frecuentes");
    }

    public function penalidades_generadas()
    {
        $this->views->getView($this, "penalidades_generadas");
    }

    // --- API LISTAR PARA CONSULTAS Y REPORTES ---
    
    public function listar_clientes()
    {
        $q = isset($_GET['q']) ? strClean($_GET['q']) : '';
        require_once 'Models/ConsultasModel.php';
        $cm = new ConsultasModel();
        $data = $cm->getClientes($q);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function listar_ingresos_fecha()
    {
        $fecha_inicio = isset($_POST['fecha_inicio']) ? strClean($_POST['fecha_inicio']) : '';
        $fecha_fin = isset($_POST['fecha_fin']) ? strClean($_POST['fecha_fin']) : '';
        
        $data = $this->model->getIngresosFecha($fecha_inicio, $fecha_fin);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function listar_vehiculos_rentados()
    {
        $data = $this->model->getVehiculosRentados();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['imagen_html'] = $this->generarHtmlImagen($data[$i]['imagen']);
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function listar_clientes_frecuentes()
    {
        $data = $this->model->getClientesFrecuentes();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function listar_penalidades_generadas()
    {
        $data = $this->model->getPenalidadesGeneradas();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    private function generarHtmlImagen($foto)
    {
        $foto = (empty($foto)) ? 'default.png' : $foto;
        if (strpos($foto, 'http://') === 0 || strpos($foto, 'https://') === 0) {
            $url_img = $foto;
        } else if (strpos($foto, 'uploads/') === 0) {
            $url_img = base_url . $foto;
        } else {
            $url_img = base_url . "uploads/vehiculos/" . $foto;
        }
        return '<div class="d-flex justify-content-center">
                    <img class="rounded-circle shadow-sm border border-2 border-white" src="' . $url_img . '" width="40" height="40" style="object-fit: cover;" onerror="this.onerror=null;this.src=\'' . base_url . 'uploads/vehiculos/default.png\';">
                </div>';
    }

    // --- EXPORTACIÓN A PDF ---
    public function pdf_vehiculos_disponibles()
    {
        require_once 'Libraries/fpdf/fpdf.php';
        require_once 'Models/ConsultasModel.php';
        $cm = new ConsultasModel();
        $data = $cm->getVehiculosDisponibles();

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(190, 10, utf8_decode('Reporte de Vehículos Disponibles'), 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, 8, 'Placa', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Marca', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Modelo', 1, 0, 'C');
        $pdf->Cell(20, 8, utf8_decode('Año'), 1, 0, 'C');
        $pdf->Cell(40, 8, 'Color', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            $pdf->Cell(30, 8, $row['placa'], 1, 0, 'C');
            $pdf->Cell(50, 8, utf8_decode($row['marca']), 1, 0, 'C');
            $pdf->Cell(50, 8, utf8_decode($row['modelo']), 1, 0, 'C');
            $pdf->Cell(20, 8, $row['anio'], 1, 0, 'C');
            $pdf->Cell(40, 8, utf8_decode($row['color']), 1, 1, 'C');
        }
        $pdf->Output();
    }

    public function pdf_alquileres_activos()
    {
        require_once 'Libraries/fpdf/fpdf.php';
        require_once 'Models/ConsultasModel.php';
        $cm = new ConsultasModel();
        $data = $cm->getAlquileresActivos();

        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(277, 10, 'Reporte de Alquileres Activos', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 8, 'Reserva', 1, 0, 'C');
        $pdf->Cell(80, 8, 'Cliente', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Vehiculo', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Inicio', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Fin', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Total', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            $pdf->Cell(20, 8, $row['reserva_id'], 1, 0, 'C');
            $pdf->Cell(80, 8, utf8_decode($row['nombre'] . ' ' . $row['apellido']), 1, 0, 'C');
            $pdf->Cell(30, 8, $row['vehiculo_id'], 1, 0, 'C');
            $pdf->Cell(40, 8, $row['fecha_inicio'], 1, 0, 'C');
            $pdf->Cell(40, 8, $row['fecha_fin'], 1, 0, 'C');
            $pdf->Cell(30, 8, number_format($row['total'], 2), 1, 1, 'R');
        }
        $pdf->Output();
    }

    public function pdf_historial_cliente()
    {
        $cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
        require_once 'Libraries/fpdf/fpdf.php';
        require_once 'Models/ConsultasModel.php';
        $cm = new ConsultasModel();
        $data = $cm->getHistorialCliente($cliente_id);

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(190, 10, 'Historial de Alquileres por Cliente', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 8, 'Reserva', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Marca', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Modelo', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Inicio', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Fin', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            $pdf->Cell(20, 8, $row['reserva_id'], 1, 0, 'C');
            $pdf->Cell(40, 8, utf8_decode($row['marca']), 1, 0, 'C');
            $pdf->Cell(50, 8, utf8_decode($row['modelo']), 1, 0, 'C');
            $pdf->Cell(40, 8, $row['fecha_inicio'], 1, 0, 'C');
            $pdf->Cell(40, 8, $row['fecha_fin'], 1, 1, 'C');
        }
        $pdf->Output();
    }

    public function pdf_estado_cuenta()
    {
        require_once 'Libraries/fpdf/fpdf.php';
        require_once 'Models/ConsultasModel.php';
        $cm = new ConsultasModel();
        $data = $cm->getEstadoCuenta();

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(190, 10, 'Estado de Cuenta por Reserva', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 8, 'Reserva', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Total', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Pagado', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Saldo', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            $pdf->Cell(40, 8, $row['reserva_id'], 1, 0, 'C');
            $pdf->Cell(50, 8, number_format($row['total'], 2), 1, 0, 'R');
            $pdf->Cell(50, 8, number_format($row['pagado'], 2), 1, 0, 'R');
            $pdf->Cell(50, 8, number_format($row['saldo'], 2), 1, 1, 'R');
        }
        $pdf->Output();
    }

    public function pdf_vehiculos_feriados()
    {
        require_once 'Libraries/fpdf/fpdf.php';
        require_once 'Models/ConsultasModel.php';
        $cm = new ConsultasModel();
        $data = $cm->getVehiculosFeriados();

        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(277, 10, utf8_decode('Vehículos Alquilados en Días Feriados'), 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, 8, 'Placa', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Marca', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Modelo', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Color', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Fecha Feriado', 1, 0, 'C');
        $pdf->Cell(97, 8, 'Feriado', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            $pdf->Cell(30, 8, $row['placa'], 1, 0, 'C');
            $pdf->Cell(40, 8, utf8_decode($row['marca']), 1, 0, 'C');
            $pdf->Cell(40, 8, utf8_decode($row['modelo']), 1, 0, 'C');
            $pdf->Cell(30, 8, utf8_decode($row['color']), 1, 0, 'C');
            $pdf->Cell(40, 8, $row['fecha_feriado'], 1, 0, 'C');
            $pdf->Cell(97, 8, utf8_decode($row['nombre_feriado']), 1, 1, 'L');
        }
        $pdf->Output();
    }

    public function pdf_ingresos_fecha()
    {
        $fecha_inicio = isset($_GET['fecha_inicio']) ? strClean($_GET['fecha_inicio']) : '';
        $fecha_fin = isset($_GET['fecha_fin']) ? strClean($_GET['fecha_fin']) : '';
        require_once 'Libraries/fpdf/fpdf.php';
        $data = $this->model->getIngresosFecha($fecha_inicio, $fecha_fin);

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(190, 10, 'Ingresos por Fecha', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(95, 8, 'Fecha Pago', 1, 0, 'C');
        $pdf->Cell(95, 8, 'Ingresos', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        $total = 0;
        foreach ($data as $row) {
            $pdf->Cell(95, 8, $row['fecha_pago'], 1, 0, 'C');
            $pdf->Cell(95, 8, number_format($row['ingresos'], 2), 1, 1, 'R');
            $total += $row['ingresos'];
        }
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(95, 8, 'TOTAL', 1, 0, 'C');
        $pdf->Cell(95, 8, number_format($total, 2), 1, 1, 'R');
        
        $pdf->Output();
    }

    public function pdf_vehiculos_rentados()
    {
        require_once 'Libraries/fpdf/fpdf.php';
        $data = $this->model->getVehiculosRentados();

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(190, 10, utf8_decode('Vehículos más Rentados'), 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(50, 8, 'Placa', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Marca', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Modelo', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Cantidad', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            $pdf->Cell(50, 8, $row['placa'], 1, 0, 'C');
            $pdf->Cell(50, 8, utf8_decode($row['marca']), 1, 0, 'C');
            $pdf->Cell(50, 8, utf8_decode($row['modelo']), 1, 0, 'C');
            $pdf->Cell(40, 8, $row['cantidad'], 1, 1, 'C');
        }
        $pdf->Output();
    }

    public function pdf_clientes_frecuentes()
    {
        require_once 'Libraries/fpdf/fpdf.php';
        $data = $this->model->getClientesFrecuentes();

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(190, 10, 'Clientes Frecuentes', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 8, 'Cliente', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Cedula', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Total Alquileres', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            $pdf->Cell(100, 8, utf8_decode($row['nombre'] . ' ' . $row['apellido']), 1, 0, 'C');
            $pdf->Cell(50, 8, $row['cedula'], 1, 0, 'C');
            $pdf->Cell(40, 8, $row['total'], 1, 1, 'C');
        }
        $pdf->Output();
    }

    public function pdf_penalidades_generadas()
    {
        require_once 'Libraries/fpdf/fpdf.php';
        $data = $this->model->getPenalidadesGeneradas();

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(190, 10, 'Penalidades Generadas', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 8, 'Tipo de Penalidad', 1, 0, 'C');
        $pdf->Cell(90, 8, 'Total Recaudado', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        $total = 0;
        foreach ($data as $row) {
            $pdf->Cell(100, 8, utf8_decode($row['tipo']), 1, 0, 'C');
            $pdf->Cell(90, 8, number_format($row['total'], 2), 1, 1, 'R');
            $total += $row['total'];
        }
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 8, 'TOTAL GLOBAL', 1, 0, 'C');
        $pdf->Cell(90, 8, number_format($total, 2), 1, 1, 'R');

        $pdf->Output();
    }
}
?>
