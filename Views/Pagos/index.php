<?php include "Views/Templates/header.php"; ?>

<style>
    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>

<div class="d-flex align-items-center justify-content-start mb-4 mt-2">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmPago();">
        <i class="fas fa-plus me-1"></i> Nuevo Pago
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Registro de Pagos</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap w-100" id="tblPagos">
                <thead>
                    <tr>
                        <th>N° Reserva</th>
                        <th>Cliente</th>
                        <th>DNI</th>
                        <th>Fecha de Pago</th>
                        <th>Método</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo Pago -->
<div class="modal fade" id="modalPago" tabindex="-1" aria-labelledby="modalPagoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="titlePago">Registrar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formularioPago" onsubmit="registrarPago(event);" autocomplete="off">
                <div class="modal-body p-4">
                    
                    <!-- Sección 1: Búsqueda y Selección -->
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">1. Búsqueda de Cliente o Reserva</h6>
                    <div class="mb-4 position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" id="buscar_pago" class="form-control border-start-0 shadow-none py-2" placeholder="Escriba el nombre del cliente aquí..." onkeyup="buscarPagoNuevo(this.value)">
                        </div>
                        <div id="resultados_busqueda_pago" class="position-absolute shadow-lg border rounded bg-white w-100 d-none" style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                            <!-- Resultados -->
                        </div>
                    </div>

                    <input type="hidden" id="pago_cliente_id">

                    <!-- Sección 2: Detalle de Reservas del Cliente -->
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">2. Selección de Reservas a Pagar</h6>
                    <div id="contenedor_reservas_pago" class="mb-4">
                        <div class="table-responsive border rounded bg-white shadow-sm" style="max-height: 300px;">
                            <table class="table table-sm table-hover align-middle mb-0" id="tbl_multi_pago">
                                <thead class="table-light sticky-top">
                                    <tr style="font-size: 0.75rem;">
                                        <th class="text-center" style="width: 40px;">N°</th>
                                        <th>Reserva / Fechas</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end text-success">Pagado</th>
                                        <th class="text-end text-danger">Saldo</th>
                                        <th class="text-center" style="width: 50px;">
                                            <input class="form-check-input" type="checkbox" id="chk_pago_all" onclick="toggleTodasReservasPago(this.checked)">
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbl_reservas_pago" style="font-size: 0.85rem;">
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-user-clock fa-2x mb-2 d-block opacity-25"></i>
                                            Seleccione un cliente para cargar sus reservas
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sección 3: Resumen y Pago -->
                    <div id="seccion_resumen_pago" class="d-none">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">3. Resumen y Monto</h6>
                        
                        <div id="info_reserva_detalles" class="d-none bg-light p-2 rounded mb-3 border">
                            <div class="row small mx-0">
                                <div class="col-6 px-1"><strong>Cliente:</strong> <span id="pago_nombre_cli"></span></div>
                                <div class="col-6 px-1 text-end"><strong>DNI:</strong> <span id="pago_dni_cli"></span></div>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 border-0 shadow-sm mb-4" id="saldo_info" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                            <div class="row text-center text-dark g-0">
                                <div class="col-4 border-end border-info">
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.6rem;">BRUTO</small>
                                    <strong id="pago_total_sel" class="small">$0.00</strong>
                                </div>
                                <div class="col-4 border-end border-info">
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.6rem;">PAGADO</small>
                                    <strong class="small text-success" id="pago_pagado_sel">$0.00</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-info d-block fw-bold" style="font-size: 0.6rem;">SALDO</small>
                                    <strong class="h6 mb-0 text-danger" id="pago_saldo_sel">$0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success d-none py-2 mb-4" id="saldo_cero_info">
                        <i class="fas fa-check-circle me-1"></i> No hay saldo pendiente para lo seleccionado.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pago_monto" class="form-label fw-semibold"><i class="fas fa-money-bill-wave text-primary me-1"></i> Monto a Pagar ($) <span class="text-danger">*</span></label>
                            <input id="pago_monto" class="form-control shadow-none border-primary" type="number" step="0.01" name="monto" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pago_metodo" class="form-label fw-semibold"><i class="fas fa-credit-card text-primary me-1"></i> Método de Pago <span class="text-danger">*</span></label>
                            <select id="pago_metodo" class="form-select shadow-none" name="metodo" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="pago_fecha" class="form-label fw-semibold"><i class="fas fa-calendar-alt text-primary me-1"></i> Fecha de Pago <span class="text-danger">*</span></label>
                        <input id="pago_fecha" class="form-control shadow-none" type="date" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button class="btn btn-primary px-4 w-100 mb-2" type="submit" id="btnAccionPago">
                        <i class="fas fa-save me-1"></i> Guardar Pago
                    </button>
                    <button class="btn btn-light px-4 w-100" type="button" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Detalle Pago -->
<div class="modal fade" id="modalDetallePago" tabindex="-1" aria-labelledby="modalDetallePagoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalDetallePagoLabel"><i class="fas fa-info-circle me-1"></i> Detalle del Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4 text-center">
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Cliente</h6>
                    <h5 id="det_pago_cliente" class="fw-bold mb-0">---</h5>
                    <small id="det_pago_dni" class="text-secondary">---</small>
                </div>
                
                <div class="row mb-4 text-center border-top border-bottom py-3 mx-0">
                    <div class="col-6 border-end">
                        <small class="text-muted d-block small fw-bold">FECHA</small>
                        <span id="det_pago_fecha" class="fw-bold">---</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block small fw-bold">MÉTODO</small>
                        <span id="det_pago_metodo" class="fw-bold badge bg-info">---</span>
                    </div>
                </div>

                <h6 class="fw-bold mb-3"><i class="fas fa-list text-primary me-1"></i> Reservas Aplicadas</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr style="font-size: 0.75rem;">
                                <th>N° Reserva</th>
                                <th class="text-end">Monto Aplicado</th>
                            </tr>
                        </thead>
                        <tbody id="det_pago_lista" style="font-size: 0.85rem;">
                            <!-- Items -->
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td>TOTAL</td>
                                <td class="text-end text-primary" id="det_pago_total">$0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0 p-4 pt-0">
                <button class="btn btn-secondary w-100" type="button" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php include "Views/Templates/footer.php"; ?>
