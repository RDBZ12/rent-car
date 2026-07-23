<?php include "Views/Templates/header.php"; ?>
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-car"></i> Vehículos más Rentados</h6>
        <button class="btn btn-outline-danger btn-sm" onclick="exportToPDF('vehiculos_rentados')" title="Exportar a PDF"><i class="fas fa-file-pdf"></i> PDF</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover display" id="tblVehiculosRentados" width="100%" cellspacing="0">
                <thead class="table-dark">
                    <tr>
                        <th>Foto</th>
                        <th>Placa</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Cantidad de Rentas</th>
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
