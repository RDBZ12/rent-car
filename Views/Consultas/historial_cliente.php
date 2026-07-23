<?php include "Views/Templates/header.php"; ?>
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history"></i> Historial de Alquileres por Cliente</h6>
        <button class="btn btn-outline-danger btn-sm" onclick="exportToPDF('historial_cliente')" title="Exportar a PDF"><i class="fas fa-file-pdf"></i> PDF</button>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-5">
                <label class="fw-bold mb-1"><i class="fas fa-search me-1"></i> Buscar Cliente:</label>
                <div class="position-relative">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" id="buscar_cliente_historial" class="form-control border-start-0 shadow-none" placeholder="Escriba nombre o cédula..." onkeyup="buscarClienteHistorial(this.value)">
                        <button class="btn btn-outline-secondary" type="button" onclick="resetHistorial()"><i class="fas fa-times"></i></button>
                    </div>
                    <div id="resultados_historial" class="position-absolute shadow-lg border rounded bg-white w-100 d-none" style="z-index: 1050; max-height: 250px; overflow-y: auto;">
                        <!-- Resultados -->
                    </div>
                </div>
                <input type="hidden" id="cliente_id_historial" value="0">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover display" id="tblHistorialCliente" width="100%" cellspacing="0">
                <thead class="table-dark">
                    <tr>
                        <th>Reserva</th>
                        <th>Foto</th>
                        <th>Vehículo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include "Views/Templates/footer.php"; ?>
<script src="<?php echo base_url; ?>Assets/js/consultas.js?v=<?php echo date('YmdHis'); ?>"></script>
