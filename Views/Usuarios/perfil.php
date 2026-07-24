<?php include "Views/Templates/header.php"; ?>

<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-lg-5 col-md-12">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <div class="position-relative" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); height: 140px;">
                <!-- Decorative element -->
                <div class="position-absolute top-0 end-0 p-3 opacity-25">
                    <i class="fas fa-car fa-5x text-white"></i>
                </div>
            </div>
            <div class="text-center" style="margin-top: -70px; position: relative; z-index: 1;">
                <div class="d-inline-block position-relative">
                    <img src="<?php echo base_url . 'Assets/img/users/' . (empty($data['perfil']) ? 'avatar.svg' : $data['perfil']) ?>" 
                         id="img-preview" alt="profile-image" class="rounded-circle border border-5 border-white shadow-sm" 
                         style="width: 140px; height: 140px; object-fit: cover; background: white;" />
                    <label for="imagen" class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 mb-1 me-1 shadow" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>
            </div>
            
            <div class="card-body p-4 pt-4 text-center">
                <h3 class="fw-bold text-dark mb-1"><?php echo (string)($data['nombre'] ?? '') . ' ' . (string)($data['apellido'] ?? ''); ?></h3>
                <p class="text-muted mb-4">@<?php echo (string)($data['usuario'] ?? ''); ?> • Administrador</p>
                
                <div class="list-group list-group-flush text-start border-top pt-3">
                    <div class="list-group-item bg-transparent border-0 px-0 py-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-light-primary rounded-circle p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-envelope fs-6"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block fw-medium text-uppercase" style="font-size: 0.65rem;">Correo Electrónico</small>
                                <span class="text-dark fw-semibold"><?php echo !empty($data['correo']) ? (string)$data['correo'] : 'Sin registrar'; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item bg-transparent border-0 px-0 py-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-light-success rounded-circle p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-phone fs-6"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block fw-medium text-uppercase" style="font-size: 0.65rem;">Teléfono de Contacto</small>
                                <span class="text-dark fw-semibold"><?php echo !empty($data['telefono']) ? (string)$data['telefono'] : 'Sin registrar'; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item bg-transparent border-0 px-0 py-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-light-warning rounded-circle p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-calendar-alt fs-6"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block fw-medium text-uppercase" style="font-size: 0.65rem;">Fecha de Registro</small>
                                <span class="text-dark fw-semibold"><?php echo !empty($data['fecha']) ? date('d M, Y', strtotime((string)$data['fecha'])) : date('d M, Y'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile & Password -->
    <div class="col-lg-7 col-md-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-user-edit text-primary me-2"></i> Actualizar Perfil</h5>
            </div>
            <div class="card-body p-4 pt-2">
                <form id="frmDatos" onsubmit="actualizarDatos(event)" autocomplete="off">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="usuario_form" class="form-label fw-semibold text-muted small">Nombre de Usuario</label>
                            <input id="usuario_form" class="form-control shadow-none border-light bg-light" type="text" name="usuario" value="<?php echo (string)($data['usuario'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nombre_form" class="form-label fw-semibold text-muted small">Nombre(s)</label>
                            <input id="nombre_form" class="form-control shadow-none" type="text" name="nombre" value="<?php echo (string)($data['nombre'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido_form" class="form-label fw-semibold text-muted small">Apellidos</label>
                            <input id="apellido_form" class="form-control shadow-none" type="text" name="apellido" value="<?php echo (string)($data['apellido'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="correo_form" class="form-label fw-semibold text-muted small">Correo Electrónico</label>
                            <input id="correo_form" class="form-control shadow-none" type="email" name="correo" value="<?php echo (string)($data['correo'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono_form" class="form-label fw-semibold text-muted small">Teléfono</label>
                            <input id="telefono_form" class="form-control shadow-none" type="text" name="telefono" value="<?php echo (string)($data['telefono'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="direccion_form" class="form-label fw-semibold text-muted small">Dirección</label>
                            <input id="direccion_form" class="form-control shadow-none" type="text" name="direccion" value="<?php echo (string)($data['direccion'] ?? '') ?>">
                        </div>
                        <div class="col-md-12 d-none">
                            <input id="imagen" type="file" onchange="preview(event)" name="imagen">
                            <input type="hidden" name="foto_actual" value="<?php echo (string)($data['perfil'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mt-2 text-end">
                        <button class="btn btn-primary px-5 shadow-sm" type="submit" id="btnGuardar">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-lock text-danger me-2"></i> Seguridad y Acceso</h5>
            </div>
            <div class="card-body p-4 pt-2">
                <form id="frmCambiarPass" onsubmit="frmCambiarPass(event);">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="clave_actual" class="form-label fw-semibold text-muted small">Contraseña Actual</label>
                            <input id="clave_actual" class="form-control shadow-none" type="password" name="clave_actual" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="clave_nueva" class="form-label fw-semibold text-muted small">Nueva Contraseña</label>
                            <input id="clave_nueva" class="form-control shadow-none" type="password" name="clave_nueva" placeholder="Nueva clave" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirmar_clave" class="form-label fw-semibold text-muted small">Confirmar</label>
                            <input id="confirmar_clave" class="form-control shadow-none" type="password" name="confirmar_clave" placeholder="Repetir clave" required>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button class="btn btn-danger px-4 shadow-sm" type="submit">
                            <i class="fas fa-shield-alt me-1"></i> Actualizar Seguridad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "Views/Templates/footer.php"; ?>