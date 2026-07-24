<?php include "Views/Templates/header.php"; ?>

<div class="d-flex align-items-center justify-content-start mb-4 mt-2">
    <button class="btn btn-primary shadow-sm me-3" type="button" onclick="frmVehiculo();">
        <i class="fas fa-plus me-1"></i> Nuevo Vehículo
    </button>
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestión de Vehículos</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover display responsive nowrap" id="tblVehiculos" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Placa</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Gama</th>
                        <th>Año</th>
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
<div class="modal fade" id="myModal" aria-labelledby="Label" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="title">Nuevo Vehículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formulario" onsubmit="registrarVeh(event);" autocomplete="off">
                <div class="modal-body p-4 pt-2">
                    <div class="row align-items-center">
                        <!-- Left Column (7) -->
                        <div class="col-md-7">
                            <div class="mb-2">
                                <label for="placa" class="form-label small fw-bold mb-1"><i class="fas fa-barcode text-primary me-1"></i> Placa *</label>
                                <input type="hidden" id="id" name="id">
                                <input id="placa" class="form-control shadow-none border-2" type="text" name="placa" placeholder="Número de placa" required>
                                <div class="invalid-feedback" id="msg-placa" style="font-size: 0.8rem; font-weight: 500;"></div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label for="marca_text" class="form-label small fw-bold mb-1"><i class="fas fa-industry text-primary me-1"></i> Marca *</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="hidden" id="marca" name="marca" value="">
                                        <input type="text" id="marca_text" class="form-control shadow-none border-2 flex-grow-1" placeholder="Escriba para filtrar o ver todas" autocomplete="off">
                                        <button class="btn btn-outline-secondary border-2" type="button" title="Gestionar marcas" onclick="abrirOffcanvasMarca()" aria-controls="offcanvasMarca" style="min-width: 42px; width: 42px; height: 38px; padding: 0;">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="modelo_text" class="form-label small fw-bold mb-1"><i class="fas fa-car text-primary me-1"></i> Modelo *</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="hidden" id="modelo" name="modelo" value="">
                                        <input type="text" id="modelo_text" class="form-control shadow-none border-2 flex-grow-1" placeholder="Primero elija una marca" autocomplete="off" disabled>
                                        <button class="btn btn-outline-secondary border-2" type="button" title="Gestionar modelos" onclick="abrirOffcanvasModelo()" style="min-width: 42px; width: 42px; height: 38px; padding: 0;">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label for="tipo" class="form-label small fw-bold mb-1"><i class="fas fa-tags text-primary me-1"></i> Gama *</label>
                                    <select id="tipo" class="form-select shadow-none border-2" name="tipo" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($data['tipos'] as $row) { ?>
                                            <option value="<?php echo $row['gama_id']; ?>"><?php echo $row['nombre']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="anio" class="form-label small fw-bold mb-1"><i class="fas fa-calendar-alt text-primary me-1"></i> Año *</label>
                                    <select id="anio" class="form-select shadow-none border-2" name="anio" onchange="actualizarPreviewImagen()" required>
                                        <option value="">Seleccione...</option>
                                        <?php for ($y = date('Y') + 1; $y >= 2000; $y--) { ?>
                                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label for="color" class="form-label small fw-bold mb-1"><i class="fas fa-palette text-primary me-1"></i> Color *</label>
                                    <input id="color" class="form-control shadow-none border-2" type="text" name="color" placeholder="Color" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="kilometraje" class="form-label small fw-bold mb-1"><i class="fas fa-tachometer-alt text-primary me-1"></i> Km. Actual *</label>
                                    <input id="kilometraje" class="form-control shadow-none border-2" type="number" name="kilometraje" placeholder="0" required>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="combustible" class="form-label small fw-bold mb-1"><i class="fas fa-gas-pump text-primary me-1"></i> Combustible *</label>
                                <select id="combustible" class="form-select shadow-none border-2" name="combustible" required>
                                    <option value="">Seleccione nivel de combustible</option>
                                    <option value="Full: 1/1">Full: 1/1</option>
                                    <option value="Semi Full: 3/4">Semi Full: 3/4</option>
                                    <option value="Medio tanque: 1/2">Medio tanque: 1/2</option>
                                    <option value="Cuarto de tanque: 1/4">Cuarto de tanque: 1/4</option>
                                    <option value="Vacío">Vacío</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold mb-1"><i class="fas fa-dollar-sign text-primary me-1"></i> Precio Alquiler *</label>
                                <div class="d-flex align-items-center">
                                    <input type="hidden" id="precio" name="precio" required>
                                    <input type="hidden" id="tipo_dia_hidden" name="tipo_dia_hidden" value="">
                                    <input type="hidden" id="estado_precio_hidden" name="estado_precio_hidden" value="Activo">
                                    <button type="button" id="btnAbrirPrecioVehiculo" class="btn btn-outline-info w-100 border-2 btn-sm fw-bold shadow-sm" onclick="abrirPrecioVehiculo()">
                                        <i class="fas fa-tag me-1 text-primary"></i> <span id="btnTextPrecio">Configurar Precio</span>
                                    </button>
                                    <div id="precioBadge" class="ms-2 d-none">
                                        <span class="badge bg-success py-2 px-3 fw-bold shadow-sm">$<span id="valPrecio">0.00</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column (5) -->
                        <div class="col-md-5">
                            <label class="form-label small fw-bold d-block mb-1 text-center">Imagen</label>
                            
                            <label for="imagen" class="d-block card border-2 shadow-sm text-center bg-white overflow-hidden" style="border-radius: 20px; cursor: pointer; border-style: dashed !important; min-height: 280px;">
                                <div class="position-relative w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="min-height: 280px;">
                                    
                                    <!-- Spinner de Carga -->
                                    <div id="img-loading" class="position-absolute w-100 h-100 bg-white d-flex align-items-center justify-content-center" style="z-index: 10; display: none !important;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </div>
                                    
                                    <img class="w-100 h-100 position-absolute" id="img-preview" src="<?php echo base_url; ?>uploads/vehiculos/default.png" style="object-fit: cover; z-index: 1;">
                                    
                                    <div id="icon-cerrar" class="position-absolute top-0 end-0 m-2" style="z-index: 11;"></div>
                                    
                                    <!-- Placeholder Text (only visible if no default image covers it) -->
                                    <div id="no-image-placeholder" class="position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted" style="z-index: 2; display: none;">
                                        <i class="fas fa-camera fa-3x mb-2 text-primary opacity-50"></i>
                                        <span class="fw-bold small px-3">Haz clic para subir<br>la foto del vehículo</span>
                                    </div>
                                    
                                </div>
                            </label>
                            
                            <input id="imagen" class="d-none" type="file" name="imagen" onchange="preview(event)">
                            <input type="hidden" id="foto_actual" name="foto_actual">
                        </div>
                    </div>

                    <!-- Toggle Estado (At original position but compact) -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-check form-switch d-flex align-items-center justify-content-center p-0">
                                <input class="form-check-input ms-0" type="checkbox" id="estado" name="estado" checked style="width: 2.5em; height: 1.2em; cursor: pointer;">
                                <label class="form-check-label fw-bold small ms-2 mb-0" for="estado" style="cursor: pointer;">DISPONIBILIDAD DEL VEHÍCULO</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3">
                    <button class="btn btn-primary px-4 fw-bold" type="submit" id="btnAccion">
                        <i class="fas fa-save me-1"></i> Guardar Vehículo
                    </button>
                    <button class="btn btn-light px-4 fw-bold" type="button" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Overlay Oscuro Blur para Modal Secundario -->
<div id="backdropPrecioOverlay" onclick="cerrarPrecio()" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); z-index: 1065; display: none;"></div>

<!-- Modal Secundario para Precio -->
<div class="modal fade" id="modalPrecio" tabindex="-1" aria-labelledby="modalPrecioLabel" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg" style="border: 2px solid #6c5ce7; border-radius: 12px;">
            <div class="modal-header bg-white border-bottom-0 pb-0">
                <h6 class="modal-title fw-bold text-dark" id="modalPrecioLabel"><i
                        class="fas fa-dollar-sign text-primary me-2"></i>Tarifa Diaria</h6>
                <button type="button" class="btn-close" onclick="cerrarPrecio()" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="alert alert-info py-2 px-3 mb-3" role="alert" style="font-size: 0.8rem;">
                    <i class="fas fa-info-circle me-1"></i>
                    Esta tarifa se aplicará automáticamente en las reservas.
                </div>

                <div class="mb-3">
                    <label for="tipo_dia_modal" class="form-label fw-semibold mb-1 small">Tipo de Día</label>
                    <select id="tipo_dia_modal" class="form-select form-select-sm shadow-none border-2">
                        <?php foreach ($data['tipos_dia'] as $row) { ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="temp_precio" class="form-label fw-semibold mb-1 small">Precio / Día</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-2 border-end-0 text-success fw-bold">$</span>
                        <input id="temp_precio" class="form-control shadow-none border-2 border-start-0 ps-0 fw-bold"
                            type="number" step="0.01" placeholder="0.00">
                    </div>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="estado_precio_modal" checked>
                    <label class="form-check-label fw-bold small ms-1" for="estado_precio_modal">Precio Activo</label>
                    <div class="text-muted" style="font-size: 0.7rem;">Habilita este precio para reservas.</div>
                </div>
            </div>
            <div class="modal-footer border-top-0 p-3 pt-0">
                <button class="btn btn-primary btn-sm px-4 w-100 mb-2 shadow-sm fw-bold" type="button"
                    onclick="aplicarPrecio()" id="btnGuardarPrecio">
                    <i class="fas fa-check-circle me-1"></i> Confirmar Tarifa
                </button>
                <button class="btn btn-light btn-sm px-4 w-100 fw-bold" type="button" onclick="cerrarPrecio()">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Marca -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasMarca" aria-labelledby="offcanvasMarcaLabel" style="z-index: 1060;">
  <div class="offcanvas-header bg-white border-bottom shadow-sm">
    <h5 class="offcanvas-title fw-bold text-dark" id="offcanvasMarcaLabel"><i class="fas fa-industry text-primary me-2"></i>Nueva Marca</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-4">
    <form id="frmMarcaRapido" onsubmit="registrarMarcaRapido(event);">
        <div class="mb-3">
            <label for="nombre_marca_rapido" class="form-label small fw-bold mb-1">Nombre de la Marca *</label>
            <input id="nombre_marca_rapido" class="form-control shadow-none border-2" type="text" name="nombre" placeholder="Ej: Toyota" required>
        </div>
        <div class="mt-3 p-2 bg-light rounded border border-2 border-primary border-opacity-10">
            <div class="form-check form-switch d-flex align-items-center justify-content-center">
                <input class="form-check-input mb-0" type="checkbox" id="estado_marca_rapido" name="estado" checked style="width: 2.5em; height: 1.25em; cursor: pointer;">
                <label class="form-check-label fw-bold text-dark ms-3 mb-0 small" for="estado_marca_rapido" style="cursor: pointer;">ESTADO (ACTIVO)</label>
            </div>
        </div>
        <button class="btn btn-primary px-4 fw-bold w-100 mt-4" type="submit" id="btnGuadarMarcaRapido">
            <i class="fas fa-save me-1"></i> Guardar Marca
        </button>
    </form>
  </div>
</div>

<!-- Offcanvas Modelo -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasModelo" aria-labelledby="offcanvasModeloLabel" style="z-index: 1060;">
  <div class="offcanvas-header bg-white border-bottom shadow-sm">
    <h5 class="offcanvas-title fw-bold text-dark" id="offcanvasModeloLabel"><i class="fas fa-car text-primary me-2"></i>Nuevo Modelo</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-4">
    <form id="frmModeloRapido" onsubmit="registrarModeloRapido(event);">
        <div class="mb-3">
            <label class="form-label small fw-bold mb-1">Marca Seleccionada</label>
            <input id="marca_nombre_rapido" class="form-control shadow-none border-2 bg-light" type="text" readonly>
            <input type="hidden" id="marca_id_rapido" name="marca_id">
        </div>
        <div class="mb-3">
            <label for="nombre_modelo_rapido" class="form-label small fw-bold mb-1">Nombre del Modelo *</label>
            <input id="nombre_modelo_rapido" class="form-control shadow-none border-2" type="text" name="nombre" placeholder="Ej: Corolla" required>
        </div>
        <div class="mt-3 p-2 bg-light rounded border border-2 border-primary border-opacity-10">
            <div class="form-check form-switch d-flex align-items-center justify-content-center">
                <input class="form-check-input mb-0" type="checkbox" id="estado_modelo_rapido" name="estado" checked style="width: 2.5em; height: 1.25em; cursor: pointer;">
                <label class="form-check-label fw-bold text-dark ms-3 mb-0 small" for="estado_modelo_rapido" style="cursor: pointer;">ESTADO (ACTIVO)</label>
            </div>
        </div>
        <button class="btn btn-primary px-4 fw-bold w-100 mt-4" type="submit" id="btnGuadarModeloRapido">
            <i class="fas fa-save me-1"></i> Guardar Modelo
        </button>
    </form>
  </div>
</div>

<script>
window.marcasVehiculo = <?php echo json_encode($data['marcas'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>;

function abrirOffcanvasMarca() {
    const el = document.getElementById('offcanvasMarca');
    let myOffcanvas = bootstrap.Offcanvas.getInstance(el);
    if (!myOffcanvas) {
        myOffcanvas = new bootstrap.Offcanvas(el);
    }
    myOffcanvas.show();
}

function abrirOffcanvasModelo() {
    const marcaId = document.getElementById('marca').value;
    const marcaText = document.getElementById('marca_text').value;
    if (!marcaId) {
        alertas('Primero debe seleccionar una marca', 'warning');
        return;
    }
    document.getElementById('marca_id_rapido').value = marcaId;
    document.getElementById('marca_nombre_rapido').value = marcaText;
    document.getElementById('nombre_modelo_rapido').value = '';
    const el = document.getElementById('offcanvasModelo');
    let myOffcanvas = bootstrap.Offcanvas.getInstance(el);
    if (!myOffcanvas) {
        myOffcanvas = new bootstrap.Offcanvas(el);
    }
    myOffcanvas.show();
}

function registrarMarcaRapido(e) {
    e.preventDefault();
    const nombre = document.getElementById("nombre_marca_rapido").value;
    if (nombre == '') {
        alertas('El nombre es requerido', 'warning');
        return;
    }
    const url = base_url + 'Marcas/registrar';
    const frm = document.getElementById("frmMarcaRapido");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    document.getElementById("btnGuadarMarcaRapido").disabled = true;
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("btnGuadarMarcaRapido").disabled = false;
            const res = JSON.parse(this.responseText);
            alertas(res.msg, res.icono);
            if (res.icono === 'success') {
                frm.reset();
                bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasMarca')).hide();
                actualizarListaMarcasRapido(nombre);
            }
        }
    }
}

function actualizarListaMarcasRapido(nombreNuevo) {
    const url = base_url + 'Marcas/listar';
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const data = JSON.parse(this.responseText);
            // Filtrar solo activos para autocompletado
            window.marcasVehiculo = data.filter(m => m.estado === '<span class="badge bg-success">Activo</span>' || m.estado === 'Activo').map(m => {
                return { id: m.id, nombre: m.marca || m.nombre || (m.marca === undefined ? Object.values(m)[1] : m.marca) }; // Manejar diferentes nombres de columna
            });
            // Update source
            if (window.jQuery && $('#marca_text').data('ui-autocomplete')) {
                $('#marca_text').autocomplete('option', 'source', getMarcasAutocompleteSource());
            }
            // Auto seleccionar
            const newMarca = window.marcasVehiculo.find(m => String(m.nombre).toLowerCase() === String(nombreNuevo).toLowerCase());
            if (newMarca) {
                document.getElementById('marca_text').value = newMarca.nombre;
                document.getElementById('marca').value = newMarca.id;
                cargarModelos(newMarca.id);
            }
        }
    }
}

function registrarModeloRapido(e) {
    e.preventDefault();
    const nombre = document.getElementById("nombre_modelo_rapido").value;
    const marca_id = document.getElementById("marca_id_rapido").value;
    if (nombre == '' || marca_id == '') {
        alertas('El nombre y la marca son requeridos', 'warning');
        return;
    }
    const url = base_url + 'Modelos/registrar';
    const frm = document.getElementById("frmModeloRapido");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    document.getElementById("btnGuadarModeloRapido").disabled = true;
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("btnGuadarModeloRapido").disabled = false;
            const res = JSON.parse(this.responseText);
            alertas(res.msg, res.icono);
            if (res.icono === 'success') {
                frm.reset();
                bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasModelo')).hide();
                // Refresh modelos de esta marca
                cargarModelos(marca_id, function() {
                    // Auto select the new model
                    setTimeout(() => {
                        const newMod = window.modelosVehiculoCache.find(m => m.label.toLowerCase() === nombre.toLowerCase());
                        if (newMod) {
                            document.getElementById('modelo_text').value = newMod.label;
                            document.getElementById('modelo').value = newMod.value;
                            actualizarPreviewImagen();
                        }
                    }, 500);
                });
            }
        }
    }
}
</script>
<style>
/* Autocomplete encima del modal (appendTo body) */
body > .ui-autocomplete {
    z-index: 2000;
    max-height: 220px;
    overflow-y: auto;
    overflow-x: hidden;
}
</style>
<?php include "Views/Templates/footer.php"; ?>