<?php include "Views/Templates/header.php"; ?>
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-exclamation-circle"></i> Penalidades Generadas</h6>
        <button class="btn btn-outline-danger btn-sm" onclick="exportToPDF('penalidades_generadas')" title="Exportar a PDF"><i class="fas fa-file-pdf"></i> PDF</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover display" id="tblPenalidadesGeneradas" width="100%" cellspacing="0">
                <thead class="table-dark">
                    <tr>
                        <th>Tipo de Penalidad</th>
                        <th>Total Recaudado</th>
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
