<?php
class Recepciones extends Controller
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
        $data = $this->model->listarRecepciones();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['f_recepcion'] = '<span class="badge bg-primary">' . $data[$i]['fecha_recepcion'] . '</span>';
            $data[$i]['cliente'] = $data[$i]['nombre'] . ' ' . $data[$i]['apellido'];
            $data[$i]['cargos_f'] = '$' . number_format($data[$i]['total_cargos'], 2);
            
            $penalidad = floatval($data[$i]['monto_penalidad']);
            if ($penalidad > 0) {
                $data[$i]['penalidad_badge'] = '<span class="badge bg-danger">$' . number_format($penalidad, 2) . '</span>';
            } else {
                $data[$i]['penalidad_badge'] = '<span class="badge bg-secondary">Sin multas</span>';
            }

            $data[$i]['estado_badge'] = '<span class="badge bg-success">Completado</span>';
            
            $data[$i]['acciones'] = '<div class="d-flex justify-content-center">
                <button class="btn btn-outline-info btn-sm me-1" onclick="verDetalleRecepcion(' . $data[$i]['recepcion_id'] . ')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function buscarClientesConReservas()
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
        $data = $this->model->buscarClientesConReservasActivas($q);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function buscarReservasPorCliente()
    {
        $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
        if ($cliente_id > 0) {
            $data = $this->model->getReservasActivasPorCliente($cliente_id);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([]);
        }
        die();
    }

    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $reserva_id = intval($_POST['reserva_id'] ?? 0);
            $fecha_recepcion = strClean($_POST['fecha_recepcion'] ?? '');
            $observaciones = strClean($_POST['observaciones'] ?? '');
            $detalles = json_decode($_POST['detalles'] ?? '[]', true);

            $monto_penalidad = floatval($_POST['monto_penalidad'] ?? 0);
            $motivo_penalidad = strClean($_POST['motivo_penalidad'] ?? '');

            if (empty($reserva_id) || empty($fecha_recepcion) || empty($detalles)) {
                $msg = array('msg' => 'Faltan datos para la recepción', 'icono' => 'warning');
            } else {
                require_once 'Models/ReservasModel.php';
                require_once 'Models/VehiculosModel.php';

                $res_db = new ReservasModel();
                $veh_mod = new VehiculosModel();

                $header = $res_db->verReserva($reserva_id);
                if (!$header || $header['estado'] == 'Devuelta') {
                    $msg = array('msg' => 'Esta reserva ya ha sido devuelta o no existe.', 'icono' => 'error');
                    echo json_encode($msg, JSON_UNESCAPED_UNICODE);
                    die();
                }

                $recepcion_id = $this->model->registrarRecepcion($reserva_id, $fecha_recepcion, $observaciones);

                if ($recepcion_id > 0) {
                    foreach ($detalles as $d) {
                        $vehiculo_id = intval($d['vehiculo_id']);
                        $this->model->registrarDetalleRecepcion(
                            $recepcion_id, 
                            $vehiculo_id, 
                            floatval($d['combustible']), 
                            intval($d['km']), 
                            strClean($d['danos']), 
                            floatval($d['cargo_extra'])
                        );
                        $veh_mod->accionVeh('Activo', $vehiculo_id);
                    }

                    if ($monto_penalidad > 0) {
                        $this->model->registrarPenalidad(
                            $reserva_id, 
                            $detalles[0]['vehiculo_id'], 
                            'Cargo por atraso', 
                            $motivo_penalidad, 
                            0,
                            $monto_penalidad
                        );
                    }

                    $abono = floatval($_POST['abono'] ?? 0);
                    if ($abono > 0) {
                        $metodo = strClean($_POST['metodo'] ?? 'Efectivo');
                        $p_id = $this->model->registrarPago($fecha_recepcion, $metodo, $abono);
                        if ($p_id > 0) {
                            $this->model->registrarPagoDetalle($p_id, $reserva_id, $abono);
                        }
                    }

                    if ($this->model->esReservaCompletada($reserva_id)) {
                        $this->model->actualizarEstadoReserva($reserva_id, 'Devuelta');
                        $msg_texto = 'Recepción registrada con éxito. La reserva ha sido completada.';
                    } else {
                        $msg_texto = 'Recepción parcial registrada con éxito. La reserva sigue activa para los vehículos restantes.';
                    }

                    $msg = array(
                        'msg' => $msg_texto,
                        'icono' => 'success',
                        'reserva_id' => $reserva_id
                    );
                } else {
                    $msg = array('msg' => 'Error al registrar cabecera de recepción', 'icono' => 'error');
                }
            }
            echo json_encode($msg, JSON_UNESCAPED_UNICODE);
            die();
        }
    }

    public function registrarBatch()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $batch_data = json_decode($_POST['batch_data'] ?? '[]', true);
                $cliente_id = intval($_POST['cliente_id'] ?? 0);
                $fecha_raw = strClean($_POST['fecha_recepcion'] ?? '');
                // Asegurar formato YYYY-MM-DD para MySQL
                $fecha_recepcion = $fecha_raw;
                if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha_raw, $matches)) {
                    $fecha_recepcion = "$matches[3]-$matches[2]-$matches[1]";
                }
                $observaciones = strClean($_POST['observaciones'] ?? '');
                $total_abono = floatval($_POST['abono'] ?? 0);
                $metodo_pago = strClean($_POST['metodo'] ?? 'Efectivo');

                if (empty($batch_data) || !is_array($batch_data) || empty($fecha_recepcion)) {
                    echo json_encode(array('msg' => 'Datos inválidos para procesar la recepción', 'icono' => 'warning'), JSON_UNESCAPED_UNICODE);
                    die();
                }

                require_once 'Models/ReservasModel.php';
                require_once 'Models/VehiculosModel.php';
                $res_mod = new ReservasModel();
                $veh_mod = new VehiculosModel();

                $pago_id = 0;
                if ($total_abono > 0) {
                    $pago_id = $this->model->registrarPago($fecha_recepcion, $metodo_pago, $total_abono);
                }

                $procesados = 0;
                $monto_restante_pago = $total_abono;

                foreach ($batch_data as $item) {
                    $reserva_id = intval($item['reserva_id']);
                    $detalles = $item['vehiculos'];
                    $pen = $item['penalidad'];

                    $recepcion_id = $this->model->registrarRecepcion($reserva_id, $fecha_recepcion, $observaciones);

                    if ($recepcion_id > 0) {
                        if (is_array($detalles)) {
                            foreach ($detalles as $d) {
                                $this->model->registrarDetalleRecepcion(
                                    $recepcion_id,
                                    intval($d['vehiculo_id']),
                                    floatval($d['combustible']),
                                    intval($d['km']),
                                    strClean($d['danos']),
                                    floatval($d['cargo_extra'])
                                );
                                $veh_mod->accionVeh('Activo', intval($d['vehiculo_id']));
                            }
                        }

                        if (is_array($pen) && floatval($pen['monto']) > 0) {
                            $vehiculo_id_pen = (!empty($detalles) && isset($detalles[0]['vehiculo_id'])) ? intval($detalles[0]['vehiculo_id']) : 0;
                            $this->model->registrarPenalidad($reserva_id, $vehiculo_id_pen, 'Cargo por atraso', strClean($pen['motivo']), intval($pen['dias']), floatval($pen['monto']));
                        }

                        if ($pago_id > 0 && $monto_restante_pago > 0) {
                            $saldo_info = $res_mod->getSaldoReserva($reserva_id);
                            if ($saldo_info) {
                                $total_res = floatval($saldo_info['total']);
                                $pagos_res = floatval($saldo_info['total_pagos']);
                                $penalidades_res = floatval($saldo_info['total_penalidades']);
                                $saldo_actual = ($total_res + $penalidades_res) - $pagos_res;

                                if ($saldo_actual > 0) {
                                    $pago_aplicar = min($monto_restante_pago, $saldo_actual);
                                    $this->model->registrarPagoDetalle($pago_id, $reserva_id, $pago_aplicar);
                                    $monto_restante_pago -= $pago_aplicar;
                                }
                            }
                        }

                        if ($this->model->esReservaCompletada($reserva_id)) {
                            $this->model->actualizarEstadoReserva($reserva_id, 'Devuelta');
                        }
                        $procesados++;
                    }
                }

                if ($procesados > 0) {
                    $res = array('msg' => "Se procesaron $procesados reserva(s) con éxito.", 'icono' => 'success');
                } else {
                    $res = array('msg' => 'No se pudo procesar ninguna recepción.', 'icono' => 'error');
                }
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            } catch (Exception $e) {
                echo json_encode(array('msg' => 'Error en el servidor: ' . $e->getMessage(), 'icono' => 'error'), JSON_UNESCAPED_UNICODE);
            }
            die();
        }
    }
    public function getDetalle($id)
    {
        $id = intval($id);
        if ($id > 0) {
            $header = $this->model->getRecepcionById($id);
            if ($header) {
                $detalles = $this->model->getDetallesRecepcion($id);
                $res = array(
                    'header' => $header,
                    'detalles' => $detalles
                );
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(array('error' => 'No se encontró la recepción'), JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(array('error' => 'ID inválido'), JSON_UNESCAPED_UNICODE);
        }
        die();
    }
}
