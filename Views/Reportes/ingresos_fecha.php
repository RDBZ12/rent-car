<?php include "Views/Templates/header.php"; ?>
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-coins"></i> Ingresos por Fecha</h6>
        <button class="btn btn-outline-danger btn-sm" onclick="exportToPDF('ingresos_fecha')" title="Exportar a PDF"><i class="fas fa-file-pdf"></i> PDF</button>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Fecha Inicio:</label>
                <input type="date" class="form-control" id="fecha_inicio" onchange="cargarIngresos()">
            </div>
            <div class="col-md-4">
                <label>Fecha Fin:</label>
                <input type="date" class="form-control" id="fecha_fin" onchange="cargarIngresos()">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary" onclick="cargarIngresos()"><i class="fas fa-search"></i> Buscar</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover display" id="tblIngresosFecha" width="100%" cellspacing="0">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha de Pago</th>
                        <th>Total Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include "Views/Templates/footer.php"; ?>
<script src="<?php echo base_url; ?>Assets/js/reportes.js"></script>
