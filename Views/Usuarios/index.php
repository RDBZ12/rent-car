<?php include "Views/Templates/header.php"; ?>

<div class="d-flex align-items-center justify-content-start mb-4 mt-2">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmUsuario();">
        <i class="fas fa-plus me-1"></i> Nuevo Usuario
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestión de Usuarios</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap" id="tblUsuarios" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Rol</th>
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
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="title">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formulario" onsubmit="registrarUser(event);" autocomplete="off">
                <div class="modal-body p-4 pt-1">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="usuario" class="form-label small fw-bold mb-1"><i class="fas fa-id-card text-primary me-1"></i> Usuario *</label>
                            <input type="hidden" id="id" name="id">
                            <input id="usuario" class="form-control shadow-none border-2" type="text" name="usuario" maxlength="50" placeholder="Ej: admin123" title="3-50 caracteres: letras, números, . _ -" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="rol" class="form-label small fw-bold mb-1"><i class="fas fa-user-tag text-primary me-1"></i> Rol *</label>
                            <select id="rol" class="form-select shadow-none border-2" name="rol" required>
                                <option value="">Seleccione un rol…</option>
                                <option value="Administrador">Administrador</option>
                                <option value="Vendedor">Vendedor</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="nombre" class="form-label small fw-bold mb-1"><i class="fas fa-user text-primary me-1"></i> Nombre *</label>
                            <input id="nombre" class="form-control shadow-none border-2" type="text" name="nombre" maxlength="80" placeholder="Solo letras" title="Solo letras y espacios" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="apellido" class="form-label small fw-bold mb-1"><i class="fas fa-user-friends text-primary me-1"></i> Apellido *</label>
                            <input id="apellido" class="form-control shadow-none border-2" type="text" name="apellido" maxlength="80" placeholder="Solo letras" title="Solo letras y espacios" required>
                        </div>
                    </div>

                    <div class="row" id="claves">
                        <div class="col-md-6 mb-2">
                            <label for="clave" class="form-label small fw-bold mb-1"><i class="fas fa-key text-primary me-1"></i> Contraseña *</label>
                            <input id="clave" class="form-control shadow-none border-2" type="password" name="clave" placeholder="Seguridad alta">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="confirmar" class="form-label small fw-bold mb-1"><i class="fas fa-lock text-primary me-1"></i> Confirmar *</label>
                            <input id="confirmar" class="form-control shadow-none border-2" type="password" name="confirmar" placeholder="Repetir contraseña">
                        </div>
                    </div>

                    <div class="mt-3 p-2 bg-light rounded border border-2 border-primary border-opacity-10">
                        <div class="form-check form-switch d-flex align-items-center justify-content-center">
                            <input class="form-check-input mb-0" type="checkbox" id="estado" name="estado" checked style="width: 2.5em; height: 1.25em; cursor: pointer;">
                            <label class="form-check-label fw-bold text-dark ms-3 mb-0 small" for="estado" style="cursor: pointer; letter-spacing: 0.5px;">ESTADO DEL USUARIO (ACTIVO)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3">
                    <button class="btn btn-primary px-4 fw-bold" type="submit" id="btnAccion">
                        <i class="fas fa-save me-1"></i> Guardar Usuario
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