<?php include "Views/Templates/header.php"; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-building text-primary me-2"></i> Configuración de la Empresa</h5>
            </div>
            <div class="card-body p-4 pt-2">
                <form id="formulario" onsubmit="modificarEmpresa(event)" autocomplete="off">
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-info-circle me-2 fs-5"></i>
                        <div>
                            Los datos configurados aquí aparecerán en los comprobantes y reportes del sistema.
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="ruc" class="form-label fw-semibold text-muted small">RUC / Identificación</label>
                            <input id="id" type="hidden" name="id" value="<?php echo $data['empresa']['id'] ?>">
                            <input id="ruc" class="form-control shadow-none" type="number" name="ruc" value="<?php echo $data['empresa']['ruc'] ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label fw-semibold text-muted small">Nombre de la Empresa</label>
                            <input id="nombre" class="form-control shadow-none" type="text" name="nombre" value="<?php echo $data['empresa']['nombre'] ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="telefono" class="form-label fw-semibold text-muted small">Teléfono de contacto</label>
                            <input id="telefono" class="form-control shadow-none" type="text" name="telefono" value="<?php echo $data['empresa']['telefono'] ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="correo" class="form-label fw-semibold text-muted small">Correo Electrónico</label>
                            <input id="correo" class="form-control shadow-none" type="text" name="correo" value="<?php echo $data['empresa']['correo'] ?>" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="direccion" class="form-label fw-semibold text-muted small">Dirección Física</label>
                            <input id="direccion" class="form-control shadow-none" name="direccion" value="<?php echo $data['empresa']['direccion'] ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="moneda" class="form-label fw-semibold text-muted small">Moneda Principal</label>
                            <select id="moneda" class="form-select shadow-none" name="moneda">
                                <?php foreach ($data['monedas'] as $row) { ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $data['empresa']['moneda']) ? 'selected' : ''; ?>>
                                        <?php echo $row['simbolo'] . ' - ' . $row['nombre'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mensaje" class="form-label fw-semibold text-muted small">Mensaje en Comprobantes (Pie de página)</label>
                            <textarea id="mensaje" class="form-control shadow-none" name="mensaje" rows="4"><?php echo $data['empresa']['mensaje'] ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted small">Logotipo Empresarial</label>
                            <div class="card bg-light border-0 p-3 text-center" style="border: 2px dashed var(--border-color) !important; border-radius: 12px;">
                                <div class="mb-2">
                                    <img class="img-fluid rounded shadow-sm bg-white p-2" id="img-preview" src="<?php echo base_url; ?>Assets/img/<?php echo $data['empresa']['logo']; ?>" style="max-height: 100px;">
                                </div>
                                <div>
                                    <label for="imagen" class="btn btn-primary btn-sm px-4">
                                        <i class="fas fa-upload me-1"></i> Cambiar Logo
                                        <input id="imagen" class="d-none" type="file" name="imagen" onchange="previewLogo(event)">
                                    </label>
                                    <input type="hidden" id="foto_actual">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-3 border-top pt-4">
                        <button class="btn btn-primary px-5 shadow-sm" type="submit" id="btnAccion">
                            <i class="fas fa-save me-1"></i> Actualizar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "Views/Templates/footer.php"; ?>