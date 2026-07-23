<?php include "Views/Templates/header.php"; ?>

<div class="d-flex align-items-center justify-content-start mb-4 mt-2">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmMoneda();">
        <i class="fas fa-plus me-1"></i> Nueva Moneda
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Configuración de Monedas</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap w-100" id="t_moneda">
                <thead>
                    <tr>
                        <th>Símbolo</th>
                        <th>Nombre de Moneda</th>
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
                <h5 class="modal-title fw-bold text-dark" id="title">Nueva Moneda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formulario" onsubmit="registrarMoneda(event);" autocomplete="off">
                <div class="modal-body p-4">
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-info-circle me-2 fs-5"></i>
                        <div>
                            Configure las monedas disponibles para los cobros y reportes.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="simbolo" class="form-label fw-semibold"><i class="fas fa-dollar-sign text-primary me-1"></i> Símbolo <span class="text-danger">*</span></label>
                            <input id="simbolo" class="form-control shadow-none" type="text" name="simbolo" placeholder="Ej: $" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="nombre" class="form-label fw-semibold"><i class="fas fa-wallet text-primary me-1"></i> Nombre <span class="text-danger">*</span></label>
                            <input type="hidden" id="id" name="id">
                            <input id="nombre" class="form-control shadow-none" type="text" name="nombre" placeholder="Ej: Dólares Americanos" required>
                        </div>
                    </div>

                    <div class="row mt-4 d-none" id="estado_container">
                         <!-- El toggle de estado si fuera necesario, aunque usualmente estas tablas se manejan igual que el resto -->
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button class="btn btn-primary px-4 w-100 mb-2" type="submit" id="btnAccion">
                        <i class="fas fa-save me-1"></i> Guardar Moneda
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