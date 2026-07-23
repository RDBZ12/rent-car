<?php include "Views/Templates/header.php"; ?>

<div class="d-flex align-items-center justify-content-start mb-4 mt-2">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmCliente();">
        <i class="fas fa-plus me-1"></i> Nuevo Cliente
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestión de Clientes</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap" id="tblClientes" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="title">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formulario" onsubmit="registrarCli(event);" autocomplete="off">
                <div class="modal-body p-4 pt-1">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="dni" class="form-label small fw-bold mb-1"><i class="fas fa-id-card text-primary me-1"></i> Cédula *</label>
                            <input type="hidden" id="id" name="id">
                            <input id="dni" class="form-control shadow-none border-2" type="text" name="dni" inputmode="numeric" autocomplete="off" maxlength="13" placeholder="Ej: 001-0000000-1 (11 dígitos)" title="Cédula dominicana con dígito verificador" required oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="telefono" class="form-label small fw-bold mb-1"><i class="fas fa-phone text-primary me-1"></i> Teléfono *</label>
                            <input id="telefono" class="form-control shadow-none border-2" type="text" name="telefono" inputmode="numeric" maxlength="11" placeholder="10 dígitos (ej: 8293189136)" title="Solo números, 10 dígitos" required oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="nombre" class="form-label small fw-bold mb-1"><i class="fas fa-user text-primary me-1"></i> Nombre *</label>
                            <input id="nombre" class="form-control shadow-none border-2" type="text" name="nombre" maxlength="80" placeholder="Solo letras" title="Solo letras y espacios" required oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="apellido" class="form-label small fw-bold mb-1"><i class="fas fa-user-friends text-primary me-1"></i> Apellido *</label>
                            <input id="apellido" class="form-control shadow-none border-2" type="text" name="apellido" maxlength="80" placeholder="Solo letras" title="Solo letras y espacios" required oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="email" class="form-label small fw-bold mb-1"><i class="fas fa-envelope text-primary me-1"></i> Email *</label>
                        <input id="email" class="form-control shadow-none border-2" type="email" name="email" placeholder="correo@ejemplo.com" required>
                    </div>

                    <div class="mb-2">
                        <label for="direccion" class="form-label small fw-bold mb-1"><i class="fas fa-map-marker-alt text-primary me-1"></i> Dirección *</label>
                        <textarea id="direccion" class="form-control shadow-none border-2" name="direccion" rows="2" placeholder="Dirección completa" required></textarea>
                    </div>

                    <div class="mt-3 p-2 bg-light rounded border border-2 border-primary border-opacity-10">
                        <div class="form-check form-switch d-flex align-items-center justify-content-center">
                            <input class="form-check-input mb-0" type="checkbox" id="estado" name="estado" checked style="width: 2.5em; height: 1.25em; cursor: pointer;">
                            <label class="form-check-label fw-bold text-dark ms-3 mb-0 small" for="estado" style="cursor: pointer; letter-spacing: 0.5px;">ESTADO DEL CLIENTE (ACTIVO)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3">
                    <button class="btn btn-primary px-4 fw-bold" type="submit" id="btnAccion">
                        <i class="fas fa-save me-1"></i> Guardar Cliente
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