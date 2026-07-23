<?php include "Views/Templates/header.php"; ?>

<style>
    .reserva-modal-header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
    .reserva-panel-title { font-size: 0.7rem; letter-spacing: 0.06em; color: #64748b; text-transform: uppercase; font-weight: 700; }
    .reserva-cliente-card { border-radius: 0.75rem; border: 1px solid #e2e8f0; background: #f8fafc; min-height: 140px; }
    .resumen-reserva-box { border-radius: 0.75rem; border: 1px solid #e2e8f0; background: #fff; }
    .vehiculo-card-pick { border-radius: 0.75rem; overflow: hidden; border: 1px solid #e2e8f0; transition: box-shadow 0.2s, transform 0.15s; height: 100%; }
    .vehiculo-card-pick:hover { box-shadow: 0 8px 24px rgba(37, 99, 235, 0.12); transform: translateY(-2px); }
    .vehiculo-card-pick .veh-img { height: 140px; object-fit: cover; width: 100%; background: #f1f5f9; }
    .vehiculo-card-pick .precio-veh { color: #2563eb; font-weight: 800; font-size: 1.05rem; }
    #resultados_clientes_buscar { max-height: 220px; overflow-y: auto; }
    .modal-shift-left { transform: translateX(-100px) !important; transition: transform 0.35s cubic-bezier(0.2, 0, 0, 1) !important; }
    @media (max-width: 1400px) { .modal-shift-left { transform: translateX(-50px) !important; } }
    @media (max-width: 992px) { .modal-shift-left { transform: none !important; } }

    /*
     * Scroll interno: Bootstrap modal-dialog-scrollable no aplica bien cuando el <form>
     * envuelve modal-body + footer. Forzamos columna flex y solo el body hace scroll.
     */
    #modalReserva.modal .modal-dialog.modal-xl,
    #modalVehiculos.modal .modal-dialog.modal-xl {
        max-height: calc(100vh - 1rem);
        margin: 0.5rem auto;
        display: flex;
        align-items: stretch;
    }
    #modalReserva .modal-content,
    #modalVehiculos .modal-content {
        max-height: calc(100vh - 1rem);
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    #modalReserva .modal-header,
    #modalVehiculos .modal-header {
        flex-shrink: 0;
    }
    #modalReserva #formularioReserva {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }
    #modalReserva .modal-body {
        overflow-y: auto !important;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
        flex: 1 1 auto;
        min-height: 0;
    }
    #modalReserva .modal-footer {
        flex-shrink: 0;
        border-top: 1px solid #dee2e6;
    }
    #modalVehiculos .modal-body {
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        flex: 1 1 auto;
        min-height: 0;
    }
    #modalVehiculos .modal-footer {
        flex-shrink: 0;
    }
</style>

<div class="d-flex align-items-center justify-content-start mb-4 mt-2">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmReserva();">
        <i class="fas fa-plus me-1"></i> Nueva Reserva
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestión de Reservas</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap w-100" id="tblReservas">
                <thead>
                    <tr class="bg-dark text-white">
                        <th width="30" class="all text-center">#</th>
                        <th width="100" class="all">N° Reserva</th>
                        <th class="all">Cliente</th>
                        <th>Vehículo(s)</th>
                        <th>F. Inicio</th>
                        <th>F. Fin</th>
                        <th>Saldo</th>
                        <th class="all">Estado</th>
                        <th class="none">Total</th>
                        <th class="none">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nueva Reserva -->
<div class="modal fade" id="modalReserva" aria-hidden="true" data-bs-backdrop="static" data-bs-focus="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header reserva-modal-header text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="titleModal"><i class="fas fa-calendar-check me-2"></i> Nueva Reserva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formularioReserva" onsubmit="registrarReserva(event);">
                <div class="modal-body p-3 p-md-4">
                    <input type="hidden" name="cliente_id" id="cliente_id" value="">

                    <!-- Prioridad visual: cliente + fechas (referencia de diseño) -->
                    <div class="card border-primary border-2 shadow-sm mb-3">
                        <div class="card-body py-3">
                            <h6 class="text-primary fw-bold mb-3"><i class="fas fa-user-check me-2"></i>Cliente y vigencia del alquiler</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-muted">Cliente <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-search text-primary"></i></span>
                                        <input type="text" class="form-control" id="cliente_buscar" placeholder="Buscar por nombre, apellido o cédula…" autocomplete="off">
                                        <button class="btn btn-outline-secondary bg-light" type="button" id="btn_crear_cliente" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCliente" title="Crear Cliente Rápido"><i class="fas fa-pencil-alt"></i></button>
                                    </div>
                                    <div id="resultados_clientes_buscar" class="border rounded mt-1 bg-white d-none"></div>
                                    <small class="text-muted">Coincidencia parcial. Solo clientes activos.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Fecha inicio <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Fecha fin <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Columna principal -->
                        <div class="col-lg-8">
                            <h6 class="fw-bold text-secondary mb-2"><i class="fas fa-sliders-h me-1"></i> Datos generales</h6>
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Fecha de reserva</label>
                                    <input type="date" class="form-control" id="fecha_reserva" name="fecha_reserva" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Estado</label>
                                    <select class="form-select" id="estado_reserva" name="estado_reserva">
                                        <option value="Pendiente" selected>Pendiente</option>
                                        <option value="Activa">Activa</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label small fw-semibold text-muted">Observaciones</label>
                                    <textarea class="form-control" id="observacion_reserva" name="observacion_reserva" rows="2" placeholder="Notas adicionales sobre la reserva..."></textarea>
                                </div>
                            </div>

                            <div class="alert alert-light border py-2 mb-3 small text-muted">
                                <strong class="text-dark">1.</strong> Elija los vehículos. <strong class="text-dark">2.</strong> Indique descuento (si aplica) y cómo desea pagar o abonar.
                            </div>

                            <h6 class="fw-bold text-primary mb-2"><span class="badge bg-primary me-1">1</span> Selección de vehículos</h6>
                            <button type="button" class="btn btn-primary w-100 py-3 fw-bold shadow-sm mb-3" id="btn_abrir_vehiculos" onclick="abrirModalVehiculos();">
                                <i class="fas fa-car me-2"></i> SELECCIONAR VEHÍCULOS
                            </button>

                            <h6 class="fw-bold text-secondary mb-2"><i class="fas fa-list me-1"></i> Vehículos en la reserva</h6>
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr class="small text-uppercase text-muted">
                                            <th>Vehículo</th>
                                            <th>Placa</th>
                                            <th class="text-end">Precio / día</th>
                                            <th class="text-center">Días</th>
                                            <th class="text-end">Subtotal</th>
                                            <th class="text-center" width="70">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbl_detalle_vehiculos">
                                        <tr id="tbl_detalle_vacio">
                                            <td colspan="6" class="text-center text-muted py-4">Agregue vehículos con el botón superior.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h6 class="fw-bold text-primary mb-2 mt-4" id="seccion_montos_pago"><span class="badge bg-primary me-1">2</span> Montos y pago</h6>
                            <p class="small text-muted mb-3">Con los vehículos ya agregados, revise el total y registre un abono o el pago completo si lo desea.</p>

                            <div class="row g-2 mb-2">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-muted">Días totales</label>
                                    <input type="number" class="form-control bg-light" id="dias_totales" readonly value="1" min="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-muted">Descuento</label>
                                    <div class="input-group">
                                        <span class="input-group-text">RD$</span>
                                        <input type="number" class="form-control" id="descuento" value="0" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="w-100 small text-muted border rounded p-2 bg-light" id="total_reserva_hint">
                                        <span class="fw-semibold text-dark">Total de la reserva:</span>
                                        <span class="float-end fw-bold text-primary" id="total_reserva_display">RD$ 0.00</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted"><i class="fas fa-money-bill-wave text-success me-1"></i> Pago o abono al guardar</label>
                                    <select class="form-select" id="tipo_cobro_reserva" name="tipo_cobro_reserva">
                                        <option value="ninguno" selected>No registrar pago ahora</option>
                                        <option value="abono">Abonar / anticipo (parcial)</option>
                                        <option value="total">Pagar el total de la reserva</option>
                                    </select>
                                    <small class="text-muted">El monto no puede ser mayor al total de la reserva.</small>
                                </div>
                                <div class="col-md-6" id="wrap_monto_pago">
                                    <label class="form-label small fw-semibold text-muted" for="abono">Monto a registrar (RD$)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">RD$</span>
                                        <input type="number" class="form-control" id="abono" name="abono" value="0" step="0.01" min="0" disabled>
                                    </div>
                                    <small class="text-danger d-none" id="abono_error_msg">El monto no puede exceder el total de la reserva.</small>
                                    <small class="text-muted" id="abono_max_leyenda">Máximo permitido: RD$ 0.00</small>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Modo de aplicación del abono</label>
                                    <select class="form-select" id="modo_abono">
                                        <option value="auto_vehiculo" selected>Automático por vehículo</option>
                                        <option value="manual">Manual (solo referencia)</option>
                                    </select>
                                    <small class="text-muted">El sistema repartirá el abono automáticamente entre los vehículos agregados.</small>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="w-100 p-3 rounded border bg-light">
                                        <div class="small text-muted">Saldo estimado</div>
                                        <div class="fs-4 fw-bold text-primary" id="saldo_estimado_display">RD$ 0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna lateral -->
                        <div class="col-lg-4">
                            <div class="reserva-cliente-card p-3 mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user-circle fa-2x text-primary me-2"></i>
                                    <div class="reserva-panel-title mb-0">Cliente seleccionado</div>
                                </div>
                                <div id="panel_cliente_vacio" class="text-muted small">Busque y seleccione un cliente.</div>
                                <div id="panel_cliente_datos" class="d-none small">
                                    <p class="mb-1 fw-bold fs-6" id="pc_nombre">—</p>
                                    <p class="mb-1"><span class="text-muted">Cédula:</span> <span id="pc_cedula">—</span></p>
                                    <p class="mb-1"><span class="text-muted">Teléfono:</span> <span id="pc_telefono">—</span></p>
                                    <p class="mb-0"><span class="text-muted">Estado:</span> <span class="badge bg-success">Activo</span></p>
                                </div>
                            </div>

                            <div class="reserva-cliente-card p-3 mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-car fa-lg text-primary me-2"></i>
                                    <div class="reserva-panel-title mb-0">Vehículo</div>
                                </div>
                                <p id="panel_vehiculo_placeholder" class="text-muted small mb-0 fst-italic">Seleccione un vehículo</p>
                                <div id="panel_vehiculo_lista" class="d-none small"></div>
                            </div>

                            <div class="reserva-cliente-card p-3 mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-tags fa-lg text-primary me-2"></i>
                                    <div class="reserva-panel-title mb-0">Tarifa desarrollada</div>
                                </div>
                                <p id="panel_tarifa_placeholder" class="text-muted small mb-0 fst-italic">Seleccione un vehículo para ver el desglose</p>
                                <div id="panel_tarifa_desglose" class="d-none small"></div>
                            </div>

                            <div class="resumen-reserva-box p-3">
                                <div class="reserva-panel-title mb-3"><i class="fas fa-receipt me-1"></i> Resumen</div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Tarifa promedio / día</span>
                                    <strong id="res_tarifa_promedio">RD$ 0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Subtotal vehículos</span>
                                    <strong id="res_subtotal_general">RD$ 0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Descuento</span>
                                    <strong class="text-danger" id="res_descuento_line">RD$ 0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2">
                                    <span class="fw-bold">Total</span>
                                    <span class="fw-bold text-primary" id="res_total_final">RD$ 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button class="btn btn-outline-secondary px-4" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary px-4 fw-bold" type="submit"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal selección de vehículos -->
<div class="modal fade" id="modalVehiculos" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header reserva-modal-header text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-car-side me-2"></i> Seleccionar vehículo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Marca</label>
                        <select class="form-select form-select-sm" id="filtro_marca">
                            <option value="">Todas las marcas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Modelo</label>
                        <select class="form-select form-select-sm" id="filtro_modelo">
                            <option value="">Todos los modelos</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Búsqueda por</label>
                        <input type="text" class="form-control form-control-sm" id="filtro_general" placeholder="Placa, marca o modelo">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary w-100 fw-bold text-uppercase" onclick="cargarVehiculosModal();">Buscar</button>
                    </div>
                </div>
                <div id="vehiculos_modal_loading" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted small">Cargando vehículos disponibles…</p>
                </div>
                <div id="vehiculos_modal_grid" class="row g-3"></div>
                <div id="vehiculos_modal_empty" class="text-center text-muted py-5 d-none">No hay vehículos disponibles para las fechas indicadas.</div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle -->
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

<!-- Offcanvas para crear cliente -->
<div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="offcanvasCliente" aria-labelledby="offcanvasClienteLabel" style="width: 450px; z-index: 1060;" data-bs-scroll="true" data-bs-backdrop="false">
    <div class="offcanvas-header border-bottom bg-light">
        <h5 class="offcanvas-title fw-bold" id="offcanvasClienteLabel"><i class="fas fa-user-plus text-primary me-2"></i>Nuevo Cliente</h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form id="formularioCliente" onsubmit="registrarCliRapido(event);" autocomplete="off">
            <div class="mb-3">
                <label for="dni" class="form-label small fw-bold mb-1"><i class="fas fa-id-card text-primary me-1"></i> Cédula *</label>
                <input id="dni" class="form-control shadow-none border-2" type="text" name="dni" inputmode="numeric" autocomplete="off" maxlength="13" placeholder="Ej: 001-0000000-1 (11 dígitos)" required oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>
            
            <div class="mb-3">
                <label for="telefono" class="form-label small fw-bold mb-1"><i class="fas fa-phone text-primary me-1"></i> Teléfono *</label>
                <input id="telefono" class="form-control shadow-none border-2" type="text" name="telefono" inputmode="numeric" maxlength="11" placeholder="10 dígitos" required oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label small fw-bold mb-1"><i class="fas fa-user text-primary me-1"></i> Nombre *</label>
                <input id="nombre" class="form-control shadow-none border-2" type="text" name="nombre" maxlength="80" placeholder="Solo letras" required oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');">
            </div>
            
            <div class="mb-3">
                <label for="apellido" class="form-label small fw-bold mb-1"><i class="fas fa-user-friends text-primary me-1"></i> Apellido *</label>
                <input id="apellido" class="form-control shadow-none border-2" type="text" name="apellido" maxlength="80" placeholder="Solo letras" required oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label small fw-bold mb-1"><i class="fas fa-envelope text-primary me-1"></i> Email *</label>
                <input id="email" class="form-control shadow-none border-2" type="email" name="email" placeholder="correo@ejemplo.com" required>
            </div>

            <div class="mb-3">
                <label for="direccion" class="form-label small fw-bold mb-1"><i class="fas fa-map-marker-alt text-primary me-1"></i> Dirección *</label>
                <textarea id="direccion" class="form-control shadow-none border-2" name="direccion" rows="2" placeholder="Dirección completa" required></textarea>
            </div>

            <div class="mt-4 pt-3 border-top">
                <button class="btn btn-primary w-100 fw-bold py-2 mb-2" type="submit" id="btnAccionCliente">
                    <i class="fas fa-save me-1"></i> Guardar y Seleccionar
                </button>
                <button class="btn btn-light w-100 fw-bold py-2" type="button" data-bs-dismiss="offcanvas">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<?php include "Views/Templates/footer.php"; ?>
