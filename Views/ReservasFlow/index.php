<?php include "Views/Templates/header.php"; ?>

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --accent-color: #4cc9f0;
        --bg-glass: rgba(255, 255, 255, 0.9);
        --border-radius: 16px;
    }

    body {
        font-family: 'Outfit', sans-serif;
        background-color: #f8f9fa;
    }

    .card-premium {
        background: var(--bg-glass);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--border-radius);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        transition: transform 0.3s ease;
        margin-bottom: 2rem;
    }

    .section-title {
        color: var(--primary-color);
        font-weight: 700;
        border-bottom: 2px solid var(--accent-color);
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
        display: inline-block;
    }

    .vehicle-item {
        transition: all 0.3s ease;
        border-radius: 12px;
        border: 1px solid #eee;
    }

    .vehicle-item:hover {
        background: #f0f4ff;
        border-color: var(--primary-color);
        cursor: pointer;
    }

    .btn-premium {
        border-radius: 10px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-premium-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
    }

    .btn-premium-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        color: white;
    }

    #tableDetalle thead {
        background-color: #f1f3f9;
    }

    #tableDetalle th {
        font-weight: 600;
        color: #444;
    }

    .total-badge {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .saldo-badge {
        font-size: 1.2rem;
        font-weight: 600;
        color: #e63946;
    }

    .search-results {
        position: absolute;
        width: 100%;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        background: white;
        border: 1px solid #ddd;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        display: none;
    }

    .search-results div {
        padding: 10px 15px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .search-results div:hover {
        background: #f0f0f0;
    }

    .sticky-summary {
        position: sticky;
        top: 20px;
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4 fw-bold"><i class="fas fa-car-side me-2"></i> Flujo Profesional de Reservas</h2>
        </div>
    </div>

    <div class="row">
        <!-- Izquierda: Formulario y Selección -->
        <div class="col-lg-8">
            <!-- Paso 1: Cliente y Fechas -->
            <div class="card card-premium p-4 animate__animated animate__fadeInUp">
                <h4 class="section-title"><i class="fas fa-user-clock me-2"></i> 1. Cliente y Período</h4>
                <div class="row g-3">
                    <div class="col-md-12 position-relative">
                        <label class="form-label fw-bold">Buscar Cliente (Nombre, Apellido o Cédula)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="buscarCliente" class="form-control border-start-0" placeholder="Escriba para buscar..." autocomplete="off">
                        </div>
                        <div id="resultadosCliente" class="search-results"></div>
                        <input type="hidden" id="id_cliente">
                        <div id="clienteSeleccionado" class="mt-2 d-none">
                            <div class="alert alert-info d-flex justify-content-between align-items-center py-2 px-3">
                                <span><i class="fas fa-user-check me-2"></i> <strong id="nombreCliente"></strong></span>
                                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="deseleccionarCliente()"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha Inicio</label>
                        <input type="date" id="f_inicio" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha Fin</label>
                        <input type="date" id="f_fin" class="form-control" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea id="observacion" class="form-control" rows="2" placeholder="Notas adicionales..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Paso 2: Selección de Vehículos -->
            <div class="card card-premium p-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="section-title mb-0"><i class="fas fa-car me-2"></i> 2. Selección de Vehículos</h4>
                    <button class="btn btn-premium btn-premium-primary btn-sm" onclick="buscarVehiculosDisponibles()">
                        <i class="fas fa-sync-alt me-1"></i> Actualizar Disponibilidad
                    </button>
                </div>
                
                <div id="listaVehiculos" class="row g-3" style="max-height: 400px; overflow-y: auto;">
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p>Seleccione fechas para ver vehículos disponibles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Derecha: Resumen y Detalle -->
        <div class="col-lg-4">
            <div class="sticky-summary">
                <div class="card card-premium p-4 animate__animated animate__fadeInRight">
                    <h4 class="section-title"><i class="fas fa-receipt me-2"></i> Resumen de Reserva</h4>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-hover align-middle" id="tableDetalle">
                            <thead>
                                <tr>
                                    <th>Vehículo</th>
                                    <th class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetalle">
                                <tr>
                                    <td colspan="3" class="text-center text-muted small py-3">No hay vehículos agregados</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total General:</span>
                        <span class="total-badge" id="totalGeneral">$0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Abono Inicial</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="abono" class="form-control form-control-lg fw-bold text-success" step="0.01" value="0.00" oninput="calcularSaldo()">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-3">
                        <span class="fw-bold">Saldo Pendiente:</span>
                        <span class="saldo-badge" id="saldoPendiente">$0.00</span>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-premium btn-premium-primary" onclick="guardarReserva()">
                            <i class="fas fa-save me-2"></i> GUARDAR RESERVA
                        </button>
                        <button class="btn btn-outline-secondary btn-premium" onclick="window.location.reload()">
                            <i class="fas fa-times me-2"></i> CANCELAR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url; ?>Assets/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const base_url = "<?php echo base_url; ?>";
    let detalleReserva = [];
    let timerBusqueda;

    $(document).ready(function() {
        // Buscador de clientes
        $('#buscarCliente').on('keyup', function() {
            let term = $(this).val();
            clearTimeout(timerBusqueda);
            if (term.length >= 2) {
                timerBusqueda = setTimeout(() => {
                    $.get(base_url + 'ReservasFlow/buscarCliente?term=' + term, function(res) {
                        let data = JSON.parse(res);
                        let html = '';
                        data.forEach(c => {
                            html += `<div onclick="seleccionarCliente(${c.id}, '${c.nombre} ${c.apellido}', '${c.dni}')">
                                        <i class="fas fa-user me-2"></i> ${c.nombre} ${c.apellido} - <small>${c.dni}</small>
                                    </div>`;
                        });
                        if(data.length == 0) html = '<div class="text-muted">No se encontraron clientes</div>';
                        $('#resultadosCliente').html(html).show();
                    });
                }, 300);
            } else {
                $('#resultadosCliente').hide();
            }
        });

        // Cerrar resultados al hacer click fuera
        $(document).click(function(e) {
            if (!$(e.target).closest('#buscarCliente').length) {
                $('#resultadosCliente').hide();
            }
        });

        // Buscar vehículos inicialmente si hay fechas
        buscarVehiculosDisponibles();

        // Recalcular si cambian las fechas
        $('#f_inicio, #f_fin').on('change', function() {
            // Validar fechas
            let f1 = new Date($('#f_inicio').val());
            let f2 = new Date($('#f_fin').val());
            if (f2 < f1) {
                $('#f_fin').val($('#f_inicio').val());
            }
            
            // Si hay vehículos en detalle, avisar que se borrarán o recalcular
            if(detalleReserva.length > 0) {
                Swal.fire({
                    title: 'Cambio de fechas',
                    text: 'Al cambiar las fechas se recalcularán los subtotales de los vehículos ya agregados.',
                    icon: 'info',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                recalcularDetalle();
            }
            buscarVehiculosDisponibles();
        });
    });

    function seleccionarCliente(id, nombre, dni) {
        $('#id_cliente').val(id);
        $('#nombreCliente').text(nombre + ' (' + dni + ')');
        $('#clienteSeleccionado').removeClass('d-none');
        $('#buscarCliente').val('').parent().hide();
        $('#resultadosCliente').hide();
    }

    function deseleccionarCliente() {
        $('#id_cliente').val('');
        $('#clienteSeleccionado').addClass('d-none');
        $('#buscarCliente').val('').parent().show();
    }

    function buscarVehiculosDisponibles() {
        const f_ini = $('#f_inicio').val();
        const f_fin = $('#f_fin').val();
        const exclude = detalleReserva.map(d => d.vehiculo_id).join(',');

        $('#listaVehiculos').html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Buscando vehículos disponibles...</p></div>');

        $.post(base_url + 'ReservasFlow/buscarVehiculos', { f_ini, f_fin, exclude }, function(res) {
            let data = JSON.parse(res);
            let html = '';
            if (data.length > 0) {
                data.forEach(v => {
                    const img = v.imagen ? (v.imagen.startsWith('http') ? v.imagen : (v.imagen.startsWith('uploads') ? base_url + v.imagen : base_url + 'uploads/vehiculos/' + v.imagen)) : base_url + 'uploads/vehiculos/default.png';
                    html += `
                        <div class="col-md-6 col-xl-4">
                            <div class="vehicle-item p-3 d-flex flex-column h-100" onclick="agregarVehiculo(${JSON.stringify(v).replace(/"/g, '&quot;')})">
                                <img src="${img}" class="rounded mb-2" style="height: 120px; object-fit: cover;" onerror="this.src='${base_url}uploads/vehiculos/default.png'">
                                <div class="fw-bold text-dark">${v.marca} ${v.modelo}</div>
                                <div class="small text-muted mb-2"><i class="fas fa-hashtag me-1"></i> ${v.placa} | ${v.tipo}</div>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <span class="badge bg-light text-primary border border-primary">$${v.precio_dia}/día</span>
                                    <button class="btn btn-sm btn-outline-primary rounded-circle"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="col-12 text-center py-5 text-muted"><p>No hay vehículos disponibles para este rango de fechas.</p></div>';
            }
            $('#listaVehiculos').html(html);
        });
    }

    function agregarVehiculo(v) {
        const f_ini = $('#f_inicio').val();
        const f_fin = $('#f_fin').val();
        
        // Calcular días
        const d1 = new Date(f_ini);
        const d2 = new Date(f_fin);
        const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) || 1;
        const dias = diff >= 0 ? diff : 1;
        const subtotal = v.precio_dia * dias;

        detalleReserva.push({
            vehiculo_id: v.vehiculo_id,
            nombre: v.marca + ' ' + v.modelo,
            placa: v.placa,
            precio_dia: v.precio_dia,
            dias: dias,
            subtotal: subtotal
        });

        renderDetalle();
        buscarVehiculosDisponibles(); // Actualizar lista para quitar el que ya agregamos
    }

    function renderDetalle() {
        let html = '';
        let total = 0;

        if (detalleReserva.length == 0) {
            html = '<tr><td colspan="3" class="text-center text-muted small py-3">No hay vehículos agregados</td></tr>';
        } else {
            detalleReserva.forEach((d, index) => {
                total += d.subtotal;
                html += `
                    <tr>
                        <td>
                            <div class="fw-bold">${d.nombre}</div>
                            <div class="small text-muted">${d.placa} (${d.dias} días)</div>
                        </td>
                        <td class="text-end fw-bold">$${d.subtotal.toFixed(2)}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-link text-danger" onclick="eliminarDetalle(${index})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        }

        $('#tbodyDetalle').html(html);
        $('#totalGeneral').text('$' + total.toLocaleString('en-US', { minimumFractionDigits: 2 }));
        calcularSaldo();
    }

    function eliminarDetalle(index) {
        detalleReserva.splice(index, 1);
        renderDetalle();
        buscarVehiculosDisponibles();
    }

    function recalcularDetalle() {
        const f_ini = $('#f_inicio').val();
        const f_fin = $('#f_fin').val();
        const d1 = new Date(f_ini);
        const d2 = new Date(f_fin);
        const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) || 1;
        const dias = diff >= 0 ? diff : 1;

        detalleReserva.forEach(d => {
            d.dias = dias;
            d.subtotal = d.precio_dia * dias;
        });
        renderDetalle();
    }

    function calcularSaldo() {
        let totalText = $('#totalGeneral').text().replace('$', '').replace(/,/g, '');
        let total = parseFloat(totalText) || 0;
        let abono = parseFloat($('#abono').val()) || 0;
        
        if (abono > total) {
            $('#abono').val(total.toFixed(2));
            abono = total;
        }

        let saldo = total - abono;
        $('#saldoPendiente').text('$' + saldo.toLocaleString('en-US', { minimumFractionDigits: 2 }));
    }

    function guardarReserva() {
        const cliente_id = $('#id_cliente').val();
        const f_ini = $('#f_inicio').val();
        const f_fin = $('#f_fin').val();
        const observacion = $('#observacion').val();
        const totalText = $('#totalGeneral').text().replace('$', '').replace(/,/g, '');
        const total = parseFloat(totalText);
        const abono = parseFloat($('#abono').val());

        if (!cliente_id) {
            Swal.fire('Atención', 'Debe seleccionar un cliente', 'warning');
            return;
        }
        if (detalleReserva.length == 0) {
            Swal.fire('Atención', 'Debe agregar al menos un vehículo', 'warning');
            return;
        }

        Swal.fire({
            title: '¿Confirmar Reserva?',
            text: `Se registrará la reserva por un total de $${total.toFixed(2)}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4361ee',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(base_url + 'ReservasFlow/registrar', {
                    cliente_id,
                    f_ini,
                    f_fin,
                    total,
                    abono,
                    observacion,
                    vehiculos: JSON.stringify(detalleReserva)
                }, function(res) {
                    let data = JSON.parse(res);
                    Swal.fire({
                        title: data.icono === 'success' ? '¡Éxito!' : 'Error',
                        text: data.msg,
                        icon: data.icono
                    }).then(() => {
                        if (data.icono === 'success') {
                            window.location.href = base_url + 'Reservas';
                        }
                    });
                });
            }
        });
    }
</script>

<?php include "Views/Templates/footer.php"; ?>
