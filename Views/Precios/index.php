<?php include "Views/Templates/header.php"; ?>

<div class="d-flex align-items-center justify-content-start mb-4 mt-3">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmPrecio();">
        <i class="fas fa-plus me-1"></i> Nuevo Precio
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestión de Precios de Vehículos</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap w-100" id="tblPrecios">
                <thead>
                    <tr>
                        <th>Vehículo</th>
                        <th>Precio / Día</th>
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
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="title">Nuevo Precio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formulario" onsubmit="registrarPrecio(event);" autocomplete="off">
                <div class="modal-body p-4">
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-info-circle me-2 fs-5"></i>
                        <div>
                            Configure el precio diario general para cada vehículo. Este precio se aplicará a todos los días (Normales, Fines de Semana y Feriados).
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="vehiculo" class="form-label fw-semibold"><i class="fas fa-car text-primary me-1"></i> Seleccionar Vehículo <span class="text-danger">*</span></label>
                        <input type="hidden" id="id" name="id">
                        <select id="vehiculo" class="form-select shadow-none" name="vehiculo" required>
                            <option value="">Seleccione un Vehículo</option>
                            <?php foreach ($data['vehiculos'] as $row) { ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['marca'] . ' ' . $row['modelo'] . ' - ' . $row['placa']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="row">
                        <input type="hidden" id="tipo_dia" name="tipo_dia" value="1">
                        <div class="col-md-12 mb-3">
                            <label for="precio" class="form-label fw-semibold"><i class="fas fa-dollar-sign text-primary me-1"></i> Precio por Día <span class="text-danger">*</span></label>
                            <input id="precio" class="form-control shadow-none" type="number" step="0.01" name="precio" placeholder="0.00" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button class="btn btn-primary px-4 w-100 mb-2" type="submit" id="btnAccion">
                        <i class="fas fa-save me-1"></i> Guardar Precio
                    </button>
                    <button class="btn btn-light px-4 w-100" type="button" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "Views/Templates/footer.php"; ?>
