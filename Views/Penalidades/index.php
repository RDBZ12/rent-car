<?php include "Views/Templates/header.php"; ?>

<div class="d-flex align-items-center justify-content-start mb-4 mt-2">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmPenalidad();">
        <i class="fas fa-plus me-1"></i> Añadir Cargo Manual
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Penalidades y Cargos Extra</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap w-100" id="tblPenalidades">
                <thead>
                    <tr>
                        <th>N° Rsv</th>
                        <th>Cliente</th>
                        <th>Placa Veh.</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Fecha Reg.</th>
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

<!-- Modal -->
<div class="modal fade" id="nuevoPenalidad" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="titlePenalidad">Nuevo Cargo o Penalidad Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formularioPenalidad" onsubmit="registrarPenalidad(event);" autocomplete="off">
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert" style="background-color: #fef2f2; color: #991b1b;">
                        <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                        <div>
                            Registro de cargos adicionales fuera de la recepción estándar.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reserva_id" class="form-label fw-semibold"><i class="fas fa-calendar-check text-primary me-1"></i> Vincular a Reserva <span class="text-danger">*</span></label>
                        <select id="reserva_id" class="form-select shadow-none" name="reserva_id" onchange="cargarVehiculos(this.value);" required>
                            <option value="">Seleccione una Reserva...</option>
                            <?php foreach ($data['reservas'] as $r) { ?>
                                <option value="<?php echo $r['reserva_id']; ?>">Reserva N° <?php echo $r['reserva_id'] . ' - ' . $r['nombre'] . ' ' . $r['apellido']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="vehiculo_id" class="form-label fw-semibold"><i class="fas fa-car text-primary me-1"></i> Vehículo Afectado <span class="text-danger">*</span></label>
                        <select id="vehiculo_id" class="form-select shadow-none" name="vehiculo_id" required>
                            <option value="">Seleccione primero la reserva</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo" class="form-label fw-semibold"><i class="fas fa-list text-primary me-1"></i> Tipo de Cargo <span class="text-danger">*</span></label>
                            <select id="tipo" class="form-select shadow-none" name="tipo" required>
                                <option value="Daño">Daño Estructural</option>
                                <option value="Combustible">Falta de Combustible</option>
                                <option value="Limpieza">Cargos por Limpieza</option>
                                <option value="Otro">Otro Motivo</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="monto" class="form-label fw-semibold"><i class="fas fa-money-bill-wave text-primary me-1"></i> Monto ($) <span class="text-danger">*</span></label>
                            <input id="monto" class="form-control shadow-none" type="number" step="0.01" name="monto" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-semibold"><i class="fas fa-comment-alt text-primary me-1"></i> Descripción Detallada <span class="text-danger">*</span></label>
                        <textarea id="descripcion" class="form-control shadow-none" name="descripcion" rows="3" placeholder="Explique el motivo del cargo..." required></textarea>
                    </div>

                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button class="btn btn-danger px-4 shadow-sm" type="submit" id="btnAccionPen">
                        <i class="fas fa-save me-1"></i> Guardar Penalidad
                    </button>
                    <button class="btn btn-light px-4" type="button" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "Views/Templates/footer.php"; ?>
