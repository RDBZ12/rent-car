<?php include "Views/Templates/header.php"; ?>

<div class="d-flex align-items-center justify-content-start mb-4">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmGama();">
        <i class="fas fa-plus me-1"></i> Nueva Gama
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Gamas de Vehículos</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap" id="tblGamas" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
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
                <h5 class="modal-title fw-bold text-dark" id="title">Nueva Gama</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formulario" onsubmit="registrarGama(event);" autocomplete="off">
                <div class="modal-body p-4 pt-1">
                    <div class="mb-2">
                        <label for="nombre" class="form-label small fw-bold mb-1"><i class="fas fa-tags text-primary me-1"></i> Nombre de la Gama *</label>
                        <input type="hidden" id="id" name="id">
                        <input id="nombre" class="form-control shadow-none border-2" type="text" name="nombre" placeholder="Ej: Alta Gama / Económica" required>
                    </div>

                    <div class="mb-2">
                        <label for="descripcion" class="form-label small fw-bold mb-1"><i class="fas fa-file-alt text-primary me-1"></i> Descripción *</label>
                        <textarea id="descripcion" class="form-control shadow-none border-2" name="descripcion" rows="2" placeholder="Descripción de la categoría" required></textarea>
                    </div>

                    <div class="mt-3 p-2 bg-light rounded border border-2 border-primary border-opacity-10">
                        <div class="form-check form-switch d-flex align-items-center justify-content-center">
                            <input class="form-check-input mb-0" type="checkbox" id="estado" name="estado" checked style="width: 2.5em; height: 1.25em; cursor: pointer;">
                            <label class="form-check-label fw-bold text-dark ms-3 mb-0 small" for="estado" style="cursor: pointer; letter-spacing: 0.5px;">ESTADO DE LA GAMA (ACTIVO)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3">
                    <button class="btn btn-primary px-4 fw-bold" type="submit" id="btnAccion">
                        <i class="fas fa-save me-1"></i> Guardar Gama
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