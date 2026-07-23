<?php include "Views/Templates/header.php"; ?>
<div class="d-flex align-items-center justify-content-start mb-4 mt-2">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmTipoDia();">
        <i class="fas fa-plus me-1"></i> Nuevo Tipo de Día
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestión de Tipos de Día</h1>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap" id="tblTiposDia" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
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
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="Label" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="title">Nuevo Tipo de Día</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formulario" onsubmit="registrarTipoDia(event);" autocomplete="off">
                <div class="modal-body p-4">
                    <input type="hidden" id="id" name="id">
                    <div class="mb-3">
                        <label for="nombre" class="form-label small fw-bold mb-1"><i class="fas fa-tag text-primary me-1"></i> Nombre del Tipo de Día *</label>
                        <input id="nombre" class="form-control shadow-none border-2" type="text" name="nombre" placeholder="Ej: Feriado, Fin de Semana" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 pt-0">
                    <button class="btn btn-primary px-4 fw-bold" type="submit" id="btnAccion">
                        <i class="fas fa-save me-1"></i> Registrar
                    </button>
                    <button class="btn btn-light px-4 fw-bold" type="button" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include "Views/Templates/footer.php"; ?>
