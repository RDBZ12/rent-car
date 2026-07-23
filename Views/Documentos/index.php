<?php include "Views/Templates/header.php"; ?>

<div class="d-flex align-items-center justify-content-start mb-4 mt-2">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmDoc();">
        <i class="fas fa-plus me-1"></i> Nuevo Documento
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestión de Documentos</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap w-100" id="tblDoc">
                <thead>
                    <tr>
                        <th>Documento</th>
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
                <h5 class="modal-title fw-bold text-dark" id="title">Nuevo Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formulario" onsubmit="registrarDoc(event);" autocomplete="off">
                <div class="modal-body p-4 pt-1">
                    <div class="mb-3">
                        <label for="documento" class="form-label small fw-bold mb-1"><i class="fas fa-id-card text-primary me-1"></i> Tipo de Documento *</label>
                        <input type="hidden" id="id" name="id">
                        <input id="documento" class="form-control shadow-none border-2" type="text" name="documento" placeholder="Ej: Cédula, Pasaporte" required>
                    </div>

                    <div class="mt-3 p-2 bg-light rounded border border-2 border-primary border-opacity-10">
                        <div class="form-check form-switch d-flex align-items-center justify-content-center">
                            <input class="form-check-input mb-0" type="checkbox" id="estado" name="estado" checked style="width: 2.5em; height: 1.25em; cursor: pointer;">
                            <label class="form-check-label fw-bold text-dark ms-3 mb-0 small" for="estado" style="cursor: pointer; letter-spacing: 0.5px;">ESTADO DEL DOCUMENTO (ACTIVO)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 pt-0">
                    <button class="btn btn-primary px-4 w-100 mb-2 fw-bold shadow-sm" type="submit" id="btnAccion">
                        <i class="fas fa-save me-1"></i> Guardar Documento
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