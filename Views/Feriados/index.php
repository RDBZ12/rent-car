<?php include "Views/Templates/header.php"; ?>

<div class="d-flex align-items-center justify-content-start mb-4 mt-2">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmFeriado();">
        <i class="fas fa-calendar-plus me-1"></i> Nuevo Feriado
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestión de Feriados</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap w-100" id="tblFeriados">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
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
                <h5 class="modal-title fw-bold text-dark" id="title">Nuevo Feriado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formulario" onsubmit="registrarFeriado(event);" autocomplete="off">
                <div class="modal-body p-4 pt-1">
                    <div class="mb-3">
                        <label for="fecha" class="form-label small fw-bold mb-1"><i class="fas fa-calendar-day text-primary me-1"></i> Fecha *</label>
                        <input type="hidden" id="id" name="id">
                        <input id="fecha" class="form-control shadow-none border-2" type="date" name="fecha" required>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label small fw-bold mb-1"><i class="fas fa-edit text-primary me-1"></i> Descripción *</label>
                        <input id="descripcion" class="form-control shadow-none border-2" type="text" name="descripcion" placeholder="Ej: Año Nuevo" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 pt-0">
                    <button class="btn btn-primary px-4 w-100 mb-2 fw-bold shadow-sm" type="submit" id="btnAccion">
                        <i class="fas fa-save me-1"></i> Guardar Feriado
                    </button>
                    <button class="btn btn-light px-4 w-100 fw-bold" type="button" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "Views/Templates/footer.php"; ?>
