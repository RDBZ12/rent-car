<?php include "Views/Templates/header.php"; ?>

<div class="d-flex align-items-center justify-content-start mb-4 mt-2">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmRecepcion();">
        <i class="fas fa-plus me-1"></i> Nueva Recepción
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Recepciones de Vehículos</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap w-100" id="tblRecepciones">
                <thead>
                    <tr>
                        <th width="30">#</th>
                        <th>N° Reserva</th>
                        <th>Cliente</th>
                        <th>Fecha de Recepción</th>
                        <th>Cargos Extra</th>
                        <th>Penalidad</th>
                        <th>Estado</th>
                        <th class="none text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Principal -->
<div class="modal fade" id="nuevoRecepcion" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="titleRecepcion">Registrar Recepción de Vehículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formularioRecepcion" onsubmit="registrarRecepcion(event);" autocomplete="off">
                <div class="modal-body p-4">
                    
                    <!-- Búsqueda de Cliente -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fas fa-user text-primary me-1"></i> Seleccionar Cliente <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-primary"></i></span>
                                <input type="text" class="form-control" id="cliente_buscar_rec" placeholder="Buscar por nombre, apellido o cédula…" autocomplete="off">
                            </div>
                            <div id="resultados_clientes_rec" class="border rounded mt-1 bg-white d-none position-absolute w-100 shadow-sm" style="z-index: 1050; max-height: 200px; overflow-y: auto;"></div>
                            <input type="hidden" id="cliente_id_rec" name="cliente_id_rec">
                        </div>
                        <div class="col-md-6" id="resumen_seleccion_container">
                            <div class="card border-0 bg-light shadow-none">
                                <div class="card-body p-2 px-3 d-flex align-items-center justify-content-around">
                                    <div class="text-center">
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Reservas</small>
                                        <span id="resumen_cant_res" class="h6 mb-0 fw-bold">0</span>
                                    </div>
                                    <div class="border-start mx-3" style="height: 30px;"></div>
                                    <div class="text-center">
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Vehículos</small>
                                        <span id="resumen_cant_veh" class="h6 mb-0 fw-bold">0</span>
                                    </div>
                                    <div class="border-start mx-3" style="height: 30px;"></div>
                                    <div class="text-center">
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Total Saldo</small>
                                        <span id="resumen_saldo_acum" class="h6 mb-0 fw-bold text-danger">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="fecha_inicio_res" value="">
                        <input type="hidden" id="fecha_fin_res" value="">
                    </div>

                    <!-- Grid de Reservas -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold text-secondary mb-0"><i class="fas fa-list me-1"></i> Reservas Activas del Cliente</h6>
                                <div class="input-group input-group-sm" style="max-width: 280px;">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" id="filtro_reservas" class="form-control border-start-0 shadow-none" placeholder="Buscar reserva por número..." autocomplete="off" oninput="filtrarReservas(this.value)">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="filtrarReservas(''); document.getElementById('filtro_reservas').value='';" title="Limpiar filtro"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <div class="table-responsive border rounded bg-white shadow-sm" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0 align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr class="small text-uppercase text-muted">
                                            <th width="40" class="text-center">
                                                <input class="form-check-input" type="checkbox" id="check_all_reservas" onclick="seleccionarTodasLasReservas(this.checked)">
                                            </th>
                                            <th>N° Reserva / Vehículos</th>
                                            <th>F. Inicio</th>
                                            <th>F. Fin</th>
                                            <th>Estado</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbl_reservas_cliente">
                                        <tr><td colspan="7" class="text-center text-muted py-4">Busque un cliente para ver sus reservas.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="reserva_id" name="reserva_id">
                    <input type="hidden" id="cliente_reserva" name="cliente_reserva">

                    <div class="row mb-4 align-items-end">
                         <div class="col-md-8">
                            <label for="observaciones" class="form-label fw-semibold"><i class="fas fa-comment-dots text-primary me-1"></i> Observaciones Generales</label>
                            <input id="observaciones" class="form-control shadow-none" type="text" name="observaciones" placeholder="Detalles adicionales sobre la entrega...">
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_recepcion" class="form-label fw-semibold"><i class="fas fa-calendar-check text-primary me-1"></i> Fecha de Recepción <span class="text-danger">*</span></label>
                            <input type="date" id="fecha_recepcion" name="fecha_recepcion" class="form-control shadow-none" required>
                        </div>
                    </div>

                    <!-- Acciones Adicionales (Más pequeñas) -->
                    <div class="row mb-4 g-3">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-primary w-100 p-3 border-dashed rounded-3 shadow-none d-flex align-items-center justify-content-start" onclick="abrirModalInspeccion();" id="btn_inspeccion" disabled>
                                <i class="fas fa-search-plus fa-2x me-3 text-primary"></i>
                                <div class="text-start">
                                    <span class="fw-bold d-block">Inspección de Vehículos</span>
                                    <small class="text-secondary">Combustible, KM y Daños</small>
                                </div>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-danger w-100 p-3 border-dashed rounded-3 shadow-none d-flex align-items-center justify-content-start" onclick="abrirModalPenalidad();" id="btn_penalidad" disabled>
                                <i class="fas fa-exclamation-triangle fa-2x me-3 text-danger"></i>
                                <div class="text-start">
                                    <span class="fw-bold d-block">Cargar Penalidades</span>
                                    <small class="text-secondary">Días de retraso y multas</small>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Resumen de Pagos Glassy -->
                    <div class="alert alert-info py-4 border-0 shadow-sm mb-2" style="background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);">
                        <div class="row align-items-center text-center">
                            <div class="col-md-3">
                                <small class="text-secondary d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Subtotal Alquiler</small>
                                <span class="h5 mb-0 fw-bold text-dark" id="total_alquiler_res">$0.00</span>
                            </div>
                            <div class="col-md-3 border-start">
                                <small class="text-secondary d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Pagado hasta hoy</small>
                                <span class="h5 mb-0 fw-bold text-success" id="total_pagado_res">$0.00</span>
                            </div>
                            <div class="col-md-3 border-start">
                                <small class="text-secondary d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Total + Cargos</small>
                                <span class="h5 mb-0 fw-bold text-primary" id="total_general_res">$0.00</span>
                            </div>
                            <div class="col-md-3 border-start">
                                <small class="text-info d-block fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">SALDO PENDIENTE</small>
                                <span class="h4 mb-0 fw-bold text-danger" id="saldo_final_res">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pago / Abono -->
                    <div class="row mt-3 justify-content-end">
                        <div class="col-md-3">
                            <div class="card border-primary shadow-sm h-100">
                                <div class="card-body p-3">
                                    <label for="metodo_pago_rec" class="form-label fw-bold text-primary mb-1 small"><i class="fas fa-wallet me-1"></i> Método</label>
                                    <select id="metodo_pago_rec" class="form-select border-0 bg-light" name="metodo">
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="Transferencia">Transferencia</option>
                                        <option value="Tarjeta">Tarjeta</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-primary shadow-sm h-100">
                                <div class="card-body p-3 text-center">
                                    <label for="abono_recepcion" class="form-label fw-bold text-primary mb-1"><i class="fas fa-money-bill-wave me-1"></i> Abono del Cliente ($)</label>
                                    <input id="abono_recepcion" class="form-control form-control-lg fw-bold text-center border-0 bg-light" type="number" step="0.01" name="abono" placeholder="0.00" oninput="recalcularSaldoFinal()">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button class="btn btn-light px-4" type="button" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button class="btn btn-primary btn-lg px-5 shadow" type="submit" id="btnAccionRec">
                        <i class="fas fa-save me-1"></i> Guardar Devolución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Inspección -->
<div class="modal fade" id="modalInspeccion" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-car me-2"></i> Inspección de Vehículos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered bg-white shadow-sm mb-0">
                        <thead class="bg-light text-dark text-center">
                            <tr>
                                <th>Vehículo (Placa)</th>
                                <th>Combustible (Gal)</th>
                                <th>KM Actual</th>
                                <th>Daños</th>
                                <th>Cargo ($)</th>
                            </tr>
                        </thead>
                        <tbody id="tblVehiculosRecepcion">
                            <!-- Filas dinámicas -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-check-circle me-1"></i> Confirmar Inspección
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Penalidad -->
<div class="modal fade" id="modalPenalidad" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-clock me-2"></i> Cargo por Atraso / Días Adicionales</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="lista_penalidades_multi">
                    <!-- Dinámico: se cargará una fila por cada reserva seleccionada -->
                    <div class="text-center text-muted">No hay reservas seleccionadas.</div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                    <i class="fas fa-save me-1"></i> Aplicar Penalidad
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle Recepción -->
<div class="modal fade" id="modalDetalleRecepcion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2"></i> Detalle de Recepción</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small text-uppercase fw-bold">Cliente</p>
                        <h6 id="det_cli_rec" class="fw-bold text-dark">---</h6>
                        <p id="det_info_rec" class="text-secondary small">---</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-1 text-muted small text-uppercase fw-bold">Fecha de Recepción</p>
                        <h6 id="det_fecha_rec" class="fw-bold text-primary">---</h6>
                    </div>
                </div>
                <hr class="text-light">
                <h6 class="fw-bold mb-3 text-secondary small text-uppercase"><i class="fas fa-car me-2"></i> Inspección de Vehículos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover border">
                        <thead class="bg-light">
                            <tr>
                                <th>Vehículo</th>
                                <th class="text-center">Combustible</th>
                                <th class="text-center">KM</th>
                                <th>Daños</th>
                                <th class="text-end">Cargo Extra</th>
                            </tr>
                        </thead>
                        <tbody id="det_lista_veh_rec">
                            <!-- Dinámico -->
                        </tbody>
                    </table>
                </div>
                <div class="row mt-4 align-items-center">
                    <div class="col-md-7">
                        <div class="bg-light p-3 rounded shadow-sm h-100">
                            <h6 class="fw-bold text-danger mb-2 small text-uppercase"><i class="fas fa-exclamation-circle me-1"></i> Penalidad / Observaciones</h6>
                            <p id="det_motivo_pen" class="text-dark small mb-0">---</p>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                Subtotal Alquiler: <span class="fw-bold text-dark" id="det_sub_rec">$0.00</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                Penalidad: <span class="fw-bold text-danger" id="det_pen_rec">$0.00</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                Cargos Extras: <span class="fw-bold text-warning" id="det_ext_rec">$0.00</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-top mt-2 bg-transparent">
                                <span class="h6 mb-0 fw-bold">TOTAL GENERAL:</span>
                                <span class="h6 mb-0 fw-bold text-primary" id="det_total_rec">$0.00</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle Reserva (Copiado de Reservas para consistencia) -->
<div class="modal fade" id="modalDetalleReserva" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg animation-bounce-down">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2"></i> Detalle de Reserva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted small text-uppercase mb-2">CLIENTE</h6>
                        <p class="mb-0 fw-bold fs-5" id="det_cliente"></p>
                        <span class="text-muted small" id="det_dni"></span>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted small text-uppercase mb-2">VIGENCIA Y ESTADO</h6>
                        <p class="mb-1 fw-bold fs-5" id="det_fechas"></p>
                        <span class="badge" id="det_estado" style="font-size: 0.85rem;"></span>
                    </div>
                </div>

                <div class="table-responsive shadow-sm rounded-3 overflow-hidden mb-4 border">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-light">
                            <tr class="text-uppercase small fw-bold text-muted">
                                <th class="px-3">Vehículo</th>
                                <th class="text-center">Días</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end px-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="det_table_vehiculos" class="align-middle"></tbody>
                    </table>
                </div>

                <div id="cont_pagos">
                    <h6 class="fw-bold text-muted small text-uppercase mb-2 mt-4"><i class="fas fa-history me-1"></i> Historial de Pagos</h6>
                    <div class="table-responsive shadow-sm rounded-3 overflow-hidden mb-4 border">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light">
                                <tr class="text-uppercase small fw-bold text-muted">
                                    <th class="px-3">Fecha de Pago</th>
                                    <th class="text-end px-3">Monto</th>
                                </tr>
                            </thead>
                            <tbody id="det_table_pagos" class="align-middle"></tbody>
                        </table>
                    </div>
                </div>

                <div class="card bg-dark text-white border-0 shadow-sm mt-4 overflow-hidden">
                    <div class="card-body p-3 text-center">
                        <div class="row align-items-center">
                            <div class="col-4 border-end border-secondary">
                                <div class="small fw-bold text-muted mb-1">TOTAL</div>
                                <span class="fs-5 fw-bold" id="det_subtotal"></span>
                            </div>
                            <div class="col-4 border-end border-secondary">
                                <div class="small fw-bold text-success mb-1">PAGADO</div>
                                <span class="fs-5 fw-bold text-success" id="det_pagos"></span>
                            </div>
                            <div class="col-4">
                                <div class="small fw-bold text-warning mb-1">SALDO</div>
                                <span class="fs-5 fw-bold text-warning" id="det_saldo"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-3 border-0">
                <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php include "Views/Templates/footer.php"; ?>
