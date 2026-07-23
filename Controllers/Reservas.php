<?php
class Reservas extends Controller
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
        $data['clientes'] = $this->model->getClientes();
        $this->views->getView($this, "index", $data);
    }

    public function listar()
    {
        $data = $this->model->getReservas();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['vehiculos'] = $this->htmlVehiculosTabla($data[$i]['vehiculos_raw'] ?? '');
            unset($data[$i]['vehiculos_raw']);

            $data[$i]['f_prestamo'] = '<span class="badge bg-primary">' . $data[$i]['fecha_prestamo'] .'</span>';
            $data[$i]['f_devolucion'] = '<span class="badge bg-info">' . $data[$i]['fecha_devolucion'] . '</span>';
            $data[$i]['cliente'] = $data[$i]['nombre'] . ' ' . $data[$i]['apellido'];
            
            // Calcular saldo: Total + Penalidades - Pagos
            $saldoData = $this->model->getSaldoReserva($data[$i]['id']);
            $totalBase = floatval($data[$i]['total']);
            $penalidades = ($saldoData) ? floatval($saldoData['total_penalidades']) : 0;
            $pagos = ($saldoData) ? floatval($saldoData['total_pagos']) : 0;
            $saldo = $totalBase + $penalidades - $pagos;

            $data[$i]['total_format'] = '$' . number_format($totalBase, 2);
            if ($saldo > 0) {
                $data[$i]['saldo_format'] = '<span class="text-danger fw-bold">$' . number_format($saldo, 2) . '</span>';
            } else {
                $data[$i]['saldo_format'] = '<span class="text-success fw-bold">$0.00</span>';
            }
            
            $btnDetalle = '<button class="btn btn-outline-info btn-sm me-1" onclick="verDetalleReserva(' . $data[$i]['id'] . ')" title="Ver Detalle"><i class="fas fa-eye"></i></button>';
            $linkPdf = '<a class="btn btn-outline-danger btn-sm me-1" href="' . base_url . 'Reservas/pdfPrestamo/' . $data[$i]['id'] . '" target="_blank" title="Imprimir PDF"><i class="fas fa-file-pdf"></i></a>';
            $btnCancelar = '<button type="button" class="btn btn-outline-warning btn-sm me-1" onclick="cancelarReserva(' . (int) $data[$i]['id'] . ')" title="Cancelar reserva (solo Pendiente)"><i class="fas fa-ban"></i></button>';

            // Estados de reserva
            if ($data[$i]['estado'] == 'Pendiente' || $data[$i]['estado'] == 'Activa') {
                $data[$i]['estado_badge'] = $data[$i]['estado'] == 'Pendiente'
                    ? '<span class="badge bg-warning text-dark">Pendiente</span>'
                    : '<span class="badge bg-primary">Activa</span>';
                
                $btnEntregar = ($data[$i]['estado'] == 'Pendiente') ? '<button type="button" class="btn btn-outline-primary btn-sm me-1" onclick="entregarReserva(' . (int) $data[$i]['id'] . ')" title="Entregar Vehículo / Iniciar Alquiler"><i class="fas fa-key"></i></button>' : '';
                $cancelSeg = ($data[$i]['estado'] == 'Pendiente') ? $btnCancelar : '';
                
                $data[$i]['acciones'] = $btnDetalle . $btnEntregar . $cancelSeg . $linkPdf . '<a class="btn btn-outline-success btn-sm" href="' . base_url . 'Pagos?reserva=' . $data[$i]['id'] . '&monto=' . $saldo . '" title="Pagar"><i class="fas fa-money-bill-wave"></i> Pagar</a>';
            } else if ($data[$i]['estado'] == 'Devuelta' || $data[$i]['estado'] == 'Finalizada' || $data[$i]['estado'] == 'Devuelto con deuda') {
                $data[$i]['estado_badge'] = '<span class="badge bg-success">Devuelta</span>';
                if ($saldo > 0) {
                    $data[$i]['acciones'] = $btnDetalle . '<a class="btn btn-outline-success btn-sm" href="' . base_url . 'Pagos?reserva=' . $data[$i]['id'] . '&monto=' . $saldo . '" title="Liquidar"><i class="fas fa-money-bill-wave"></i> Liquidar</a>';
                } else {
                    $data[$i]['acciones'] = $btnDetalle . '<span class="badge bg-light text-success border border-success px-2 py-1"><i class="fas fa-check-double me-1"></i>Saldado</span>';
                }
            } else if ($data[$i]['estado'] == 'Cancelada') {
                $data[$i]['estado_badge'] = '<span class="badge bg-danger">Cancelada</span>';
                $data[$i]['acciones'] = $btnDetalle . $linkPdf;
            } else {
                $data[$i]['estado_badge'] = '<span class="badge bg-dark">' . $data[$i]['estado'] . '</span>';
                $data[$i]['acciones'] = $btnDetalle;
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    /**
     * Listado JSON de marcas activas (filtro modal de vehículos).
     */
    public function marcasVehiculos()
    {
        header('Content-Type: application/json; charset=utf-8');
        require_once 'Models/VehiculosModel.php';
        $vm = new VehiculosModel();
        $data = $vm->getMarcasActivas();
        echo json_encode($data ?: [], JSON_UNESCAPED_UNICODE);
        die();
    }

    /**
     * Modelos por marca_id (filtro modal de vehículos).
     */
    public function modelosVehiculos($marca_id = '')
    {
        header('Content-Type: application/json; charset=utf-8');
        $mid = intval($marca_id);
        require_once 'Models/VehiculosModel.php';
        $vm = new VehiculosModel();
        $data = $mid > 0 ? $vm->getModelosByMarca($mid) : [];
        echo json_encode($data ?: [], JSON_UNESCAPED_UNICODE);
        die();
    }

    public function vehiculosDisponibles()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $f_ini = isset($_POST['f_ini']) ? trim((string) $_POST['f_ini']) : '';
            $f_fin = isset($_POST['f_fin']) ? trim((string) $_POST['f_fin']) : '';
            $marca = isset($_POST['marca']) ? trim((string) $_POST['marca']) : '';
            $modelo = isset($_POST['modelo']) ? trim((string) $_POST['modelo']) : '';
            $term = isset($_POST['term']) ? trim((string) $_POST['term']) : '';
            $exclude = isset($_POST['exclude']) ? trim((string) $_POST['exclude']) : '';

            if (empty($f_ini) || empty($f_fin)) {
                echo json_encode(['error' => 'Fechas requeridas'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $data = $this->model->getVehiculosDisponibles($f_ini, $f_fin, $marca, $modelo, $term, $exclude);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            die();
        }
    }

    public function calcularPrecioVehiculo()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $vehiculo_id = intval($_POST['vehiculo_id'] ?? 0);
            $f_ini = isset($_POST['f_ini']) ? trim((string) $_POST['f_ini']) : '';
            $f_fin = isset($_POST['f_fin']) ? trim((string) $_POST['f_fin']) : '';
            $num_dias = isset($_POST['num_dias']) ? intval($_POST['num_dias']) : 0;

            if ($f_ini !== '' && $f_fin !== '') {
                $d1 = new DateTime($f_ini);
                $d2 = new DateTime($f_fin);
                if ($d2 < $d1) {
                    echo json_encode(['error' => 'La fecha fin debe ser igual o posterior a la fecha inicio'], JSON_UNESCAPED_UNICODE);
                    die();
                }
                $num_dias = max(1, (int) $d1->diff($d2)->days);
            } elseif ($num_dias < 1) {
                echo json_encode(['error' => 'Indique fechas o número de días válido'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $precioData = $this->model->getPrecioEspecifico($vehiculo_id);
            if (empty($precioData)) {
                echo json_encode(['error' => 'Este vehículo no tiene una tarifa activa configurada'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $precioUnitario = floatval($precioData['precio']);
            $subtotal = $precioUnitario * $num_dias;

            echo json_encode([
                'status' => 'ok',
                'precio_unitario' => $precioUnitario,
                'subtotal' => round($subtotal, 2),
                'dias_totales' => $num_dias,
            ], JSON_UNESCAPED_UNICODE);
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
            $estado = isset($_POST['estado']) ? trim((string) $_POST['estado']) : 'Pendiente';
            $vehiculos = json_decode($_POST['vehiculos'] ?? '[]', true);

            if (empty($cliente_id) || empty($f_ini) || empty($f_fin) || empty($vehiculos) || !is_array($vehiculos) || $total <= 0) {
                $msg = array('msg' => 'Falta información para la reserva (cliente, fechas, vehículos y total)', 'icono' => 'warning');
            } else {
                $d1 = new DateTime($f_ini);
                $d2 = new DateTime($f_fin);
                if ($d2 < $d1) {
                    $msg = array('msg' => 'La fecha fin debe ser igual o posterior a la fecha inicio', 'icono' => 'warning');
                } else {
                    $idsVeh = array();
                    foreach ($vehiculos as $v) {
                        $idsVeh[] = intval($v['vehiculo_id'] ?? 0);
                    }
                    $idsVeh = array_filter($idsVeh);
                    if (count($idsVeh) !== count(array_unique($idsVeh))) {
                        $msg = array('msg' => 'No se permiten vehículos duplicados en la misma reserva', 'icono' => 'warning');
                    } elseif ($abono < 0) {
                        $msg = array('msg' => 'El monto abonado o pagado no puede ser negativo', 'icono' => 'warning');
                    } elseif ($abono > $total + 0.009) {
                        $msg = array('msg' => 'El abono o pago no puede exceder el total de la reserva (RD$ ' . number_format($total, 2) . ')', 'icono' => 'warning');
                    } else {
                        $reserva_id = $this->model->registrarReserva($cliente_id, $f_ini, $f_fin, $total, $estado);

                        if ($reserva_id > 0) {
                            foreach ($vehiculos as $v) {
                                $vehiculo_id = intval($v['vehiculo_id']);
                                $this->model->registrarDetalleReserva(
                                    $reserva_id,
                                    $vehiculo_id,
                                    floatval($v['promedio_diario']),
                                    intval($v['dias_totales']),
                                    floatval($v['subtotal'])
                                );
                            }

                            if ($abono > 0) {
                                $this->model->registrarPago($reserva_id, $abono);
                            }

                            $msg = array('msg' => 'Reserva guardada correctamente', 'icono' => 'success', 'reserva_id' => $reserva_id);
                        } else {
                            $msg = array('msg' => 'Error al registrar la reserva', 'icono' => 'error');
                        }
                    }
                }
            }
            echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Cancelar reserva en Pendiente: pasa a Cancelada (el registro no se borra).
     */
    public function cancelar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array('msg' => 'Método no permitido', 'icono' => 'error'), JSON_UNESCAPED_UNICODE);
            die();
        }
        $id = intval($_POST['reserva_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(array('msg' => 'Reserva no válida', 'icono' => 'warning'), JSON_UNESCAPED_UNICODE);
            die();
        }
        if ($this->model->cancelarReservaPendiente($id)) {
            echo json_encode(array('msg' => 'Reserva cancelada. El registro permanece en el listado.', 'icono' => 'success'), JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(array('msg' => 'Solo se pueden cancelar reservas en estado Pendiente.', 'icono' => 'warning'), JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    /**
     * Iniciar alquiler: pasa reserva Pendiente a Activa.
     */
    public function entregar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array('msg' => 'Método no permitido', 'icono' => 'error'), JSON_UNESCAPED_UNICODE);
            die();
        }
        $id = intval($_POST['reserva_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(array('msg' => 'Reserva no válida', 'icono' => 'warning'), JSON_UNESCAPED_UNICODE);
            die();
        }
        if ($this->model->entregarReserva($id)) {
            echo json_encode(array('msg' => 'Vehículo(s) entregado(s). Reserva en curso (Activa).', 'icono' => 'success'), JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(array('msg' => 'Solo se pueden iniciar alquileres de reservas en estado Pendiente.', 'icono' => 'warning'), JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    /**
     * Miniaturas + texto para columna Vehículo(s) en DataTables.
     */
    private function htmlVehiculosTabla(?string $raw): string
    {
        $raw = (string) ($raw ?? '');
        if ($raw === '') {
            return '<span class="text-muted small">—</span>';
        }
        $def = base_url . 'uploads/vehiculos/default.png';
        $filas = explode('|||', $raw);
        $html = '<div class="d-flex flex-column gap-1 align-items-start">';
        foreach ($filas as $fila) {
            $parts = explode('###', $fila, 2);
            $img = isset($parts[0]) ? trim($parts[0]) : '';
            $txt = isset($parts[1]) ? $parts[1] : '';
            if ($img === '__DEFAULT__' || $img === '') {
                $src = $def;
            } elseif (strpos($img, 'uploads/') === 0) {
                $src = base_url . str_replace('\\', '/', $img);
            } else {
                $src = base_url . 'uploads/vehiculos/' . $img;
            }
            $escSrc = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');
            $escDef = htmlspecialchars($def, ENT_QUOTES, 'UTF-8');
            $html .= '<div class="d-flex align-items-center gap-2">';
            $html .= '<img src="' . $escSrc . '" class="rounded border" width="48" height="40" style="object-fit:cover;min-width:48px" alt="" loading="lazy" onerror="this.src=\'' . $escDef . '\'">';
            $html .= '<span class="small text-dark mb-0">' . htmlspecialchars($txt, ENT_QUOTES, 'UTF-8') . '</span>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    public function getDetalle($id)
    {
        $id = intval($id);
        $reserva = $this->model->verReserva($id);
        if (empty($reserva)) {
            echo json_encode(['error' => 'Reserva no encontrada'], JSON_UNESCAPED_UNICODE);
            die();
        }

        $vehiculos = $this->model->getDetalleReserva($id);
        $pagos = $this->model->getPagosByReserva($id);
        $saldoData = $this->model->getSaldoReserva($id);

        echo json_encode([
            'reserva' => $reserva,
            'vehiculos' => $vehiculos,
            'pagos' => $pagos,
            'financiero' => [
                'total' => floatval($saldoData['total']),
                'total_pagos' => floatval($saldoData['total_pagos']),
                'total_penalidades' => floatval($saldoData['total_penalidades']),
                'saldo' => floatval($saldoData['total']) + floatval($saldoData['total_penalidades']) - floatval($saldoData['total_pagos'])
            ]
        ], JSON_UNESCAPED_UNICODE);
        die();
    }
}
?>
