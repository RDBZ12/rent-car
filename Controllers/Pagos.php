<?php
class Pagos extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (empty($_SESSION['activo'])) {
            header("location: " . base_url);
        }
        parent::__construct();
    }

    public function index()
    {
        $this->views->getView($this, "index");
    }

    public function listar()
    {
        $data = $this->model->getPagos();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['f_pago'] = '<span class="badge bg-primary">' . $data[$i]['fecha_pago'] . '</span>';
            $data[$i]['cliente'] = $data[$i]['nombre'] . ' ' . $data[$i]['apellido'];
            $data[$i]['monto_format'] = '$' . number_format($data[$i]['monto'], 2);
            $data[$i]['estatus'] = '<span class="badge bg-success">Completado</span>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function buscarReservas()
    {
        $data = $this->model->getReservasConSaldo();
        $result = array();
        foreach ($data as $row) {
            $saldo = $row['total'] + $row['total_penalidades'] - $row['total_pagos'];
            $row['saldo'] = round($saldo, 2);
            if ($saldo > 0) {
                $result[] = $row;
            }
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function saldo(int $reserva_id)
    {
        $data = $this->model->getSaldoReserva($reserva_id);
        if (!empty($data)) {
            $saldo = $data['total'] + $data['total_penalidades'] - $data['total_pagos'];
            $data['saldo'] = round($saldo, 2);
            $data['vehiculos'] = $this->model->getVehiculosReserva($reserva_id);
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $reserva_id = intval($_POST['reserva_id']);
            $monto = floatval($_POST['monto']);
            $metodo = strClean($_POST['metodo']);
            $fecha = strClean($_POST['fecha_pago']);

            if (empty($reserva_id) || empty($monto) || empty($metodo) || empty($fecha)) {
                $msg = array('msg' => 'Todos los campos son obligatorios', 'icono' => 'warning');
            } else if ($monto <= 0) {
                $msg = array('msg' => 'El monto no puede ser cero o negativo', 'icono' => 'warning');
            } else {
                $reserva_info = $this->model->getEstadoReserva($reserva_id);
                if (empty($reserva_info) || strtolower($reserva_info['estado']) == 'cancelada' || strtolower($reserva_info['estado']) == 'cancelado') {
                    $msg = array('msg' => 'No se pueden registrar pagos a reservas canceladas', 'icono' => 'warning');
                } else {
                    $saldo_data = $this->model->getSaldoReserva($reserva_id);
                    $total_general = floatval($saldo_data['total']) + floatval($saldo_data['total_penalidades']);
                    $pagado = floatval($saldo_data['total_pagos']);
                    $saldo_final = round($total_general - $pagado, 2);

                    if ($monto > $saldo_final) {
                        $msg = array('msg' => 'El monto supera el saldo pendiente', 'icono' => 'warning');
                    } else {
                        $pago_id = $this->model->registrarPago($fecha, $metodo, $monto);
                        if ($pago_id > 0) {
                            $res_detalle = $this->model->registrarPagoDetalle($pago_id, $reserva_id, $monto);
                            if ($res_detalle > 0) {
                                $msg = array('msg' => 'Pago registrado con éxito', 'icono' => 'success');
                            } else {
                                $msg = array('msg' => 'Error al registrar el detalle del pago', 'icono' => 'error');
                            }
                        } else {
                            $msg = array('msg' => 'Error al registrar el pago', 'icono' => 'error');
                        }
                    }
                }
            }
            echo json_encode($msg, JSON_UNESCAPED_UNICODE);
            die();
        }
    }

    public function buscarClientes()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([]);
            die();
        }
        $q = isset($_POST['q']) ? trim((string) $_POST['q']) : '';
        if (strlen($q) < 1) {
            echo json_encode([]);
            die();
        }
        $data = $this->model->buscarClientesConSaldo($q);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function buscarPorCliente()
    {
        $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
        if ($cliente_id > 0) {
            $data = $this->model->getReservasConSaldoPorCliente($cliente_id);
            $result = array();
            foreach ($data as $row) {
                $saldo = floatval($row['total']) + floatval($row['total_penalidades']) - floatval($row['total_pagos']);
                $row['saldo'] = round($saldo, 2);
                if ($saldo > 0) {
                    $result[] = $row;
                }
            }
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([]);
        }
        die();
    }

    public function registrarBatch()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $batch_data = json_decode($_POST['batch_data'] ?? '[]', true);
            $total_pago = floatval($_POST['monto'] ?? 0);
            $metodo = strClean($_POST['metodo'] ?? 'Efectivo');
            $fecha = strClean($_POST['fecha_pago'] ?? date('Y-m-d'));

            if (empty($batch_data) || !is_array($batch_data) || $total_pago <= 0) {
                $msg = array('msg' => 'Datos inválidos o monto insuficiente', 'icono' => 'warning');
            } else {
                $pago_id = $this->model->registrarPago($fecha, $metodo, $total_pago);
                if ($pago_id > 0) {
                    $monto_restante = $total_pago;
                    $procesados = 0;

                    foreach ($batch_data as $item) {
                        if ($monto_restante <= 0) break;

                        $reserva_id = intval($item['reserva_id']);
                        $monto_a_pagar = floatval($item['monto_asignado']);

                        // Validar saldo real de nuevo por seguridad
                        $saldo_data = $this->model->getSaldoReserva($reserva_id);
                        $saldo_real = (floatval($saldo_data['total']) + floatval($saldo_data['total_penalidades'])) - floatval($saldo_data['total_pagos']);
                        
                        $pago_aplicar = min($monto_a_pagar, $monto_restante, $saldo_real);

                        if ($pago_aplicar > 0) {
                            $this->model->registrarPagoDetalle($pago_id, $reserva_id, $pago_aplicar);
                            $monto_restante -= $pago_aplicar;
                            $procesados++;

                            // Si se saldó completamente, podríamos actualizar estado si es necesario, 
                            // pero el estado 'Devuelta' se maneja en Recepciones.
                            // Aquí solo registramos el flujo de dinero.
                        }
                    }

                    if ($procesados > 0) {
                        $msg = array('msg' => "Pago de $$total_pago procesado con éxito en $procesados reserva(s).", 'icono' => 'success');
                    } else {
                        $msg = array('msg' => 'No se pudo aplicar el pago a ninguna reserva.', 'icono' => 'error');
                    }
                } else {
                    $msg = array('msg' => 'Error al registrar el pago cabecera', 'icono' => 'error');
                }
            }
            echo json_encode($msg, JSON_UNESCAPED_UNICODE);
            die();
        }
    }

    public function reporte(int $pago_id)
    {
        // Placeholder para generación de PDF de comprobante de pago
        echo "Generando Comprobante de Pago N° " . $pago_id;
        die();
    }
    public function getDetalle($id)
    {
        $id = intval($id);
        if ($id > 0) {
            $header = $this->model->getPagoById($id);
            if ($header) {
                $detalles = $this->model->getDetallesPago($id);
                $res = array(
                    'header' => $header,
                    'detalles' => $detalles
                );
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(array('error' => 'No se encontró el pago'), JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(array('error' => 'ID inválido'), JSON_UNESCAPED_UNICODE);
        }
        die();
    }
}
