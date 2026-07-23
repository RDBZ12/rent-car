document.addEventListener("DOMContentLoaded", function () {
    // Inicializar datatables según qué tabla exista en la vista actual
    if (document.getElementById("tblVehiculosDisponibles")) {
        $('#tblVehiculosDisponibles').DataTable({
            ajax: {
                url: base_url + "Consultas/listar_vehiculos_disponibles",
                dataSrc: ""
            },
            columns: [
                { data: "imagen_html" },
                { data: "placa" },
                { data: "marca" },
                { data: "modelo" },
                { data: "anio" },
                { data: "color" },
                { data: "estado_html" }
            ],
            responsive: true,
            language: {
                url: base_url + "Assets/js/es-ES.json"
            }
        });
    }

    if (document.getElementById("tblAlquileresActivos")) {
        $('#tblAlquileresActivos').DataTable({
            ajax: {
                url: base_url + "Consultas/listar_alquileres_activos",
                dataSrc: ""
            },
            columns: [
                { data: "reserva_id" },
                { data: "cliente" },
                { data: "vehiculo" },
                { data: "fecha_inicio" },
                { data: "fecha_fin" },
                { data: "total" },
                { data: "estado_html" }
            ],
            responsive: true,
            language: {
                url: base_url + "Assets/js/es-ES.json"
            }
        });
    }

    if (document.getElementById("tblHistorialCliente")) {
        // Cargar historial general al inicio (todos)
        cargarHistorialCliente();
        
        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            const res = document.getElementById('resultados_historial');
            const bus = document.getElementById('buscar_cliente_historial');
            if (res && !res.contains(e.target) && e.target !== bus) {
                res.classList.add('d-none');
            }
        });
    }

    if (document.getElementById("tblEstadoCuenta")) {
        $('#tblEstadoCuenta').DataTable({
            ajax: {
                url: base_url + "Consultas/listar_estado_cuenta",
                dataSrc: ""
            },
            columns: [
                { data: "reserva_id" },
                { data: "total" },
                { data: "pagado" },
                { data: "saldo" },
                { data: "estado_html" }
            ],
            responsive: true,
            language: {
                url: base_url + "Assets/js/es-ES.json"
            }
        });
    }

    if (document.getElementById("tblVehiculosFeriados")) {
        $('#tblVehiculosFeriados').DataTable({
            ajax: {
                url: base_url + "Consultas/listar_vehiculos_feriados",
                dataSrc: ""
            },
            columns: [
                { data: "reserva_id" },
                { data: "imagen_html" },
                { data: "placa" },
                { data: "marca" },
                { data: "modelo" },
                { data: "color" },
                { data: "estado_html" },
                { data: "fecha_feriado" },
                { data: "nombre_feriado" }
            ],
            responsive: true,
            language: {
                url: base_url + "Assets/js/es-ES.json"
            }
        });
    }
});

function buscarClienteHistorial(valor) {
    const resultados = document.getElementById('resultados_historial');
    if (valor.length < 2) {
        resultados.classList.add('d-none');
        return;
    }
    
    resultados.innerHTML = '<div class="p-3 text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ms-2">Buscando...</span></div>';
    resultados.classList.remove('d-none');
    
    const url = base_url + "Reportes/listar_clientes?q=" + valor;
    fetch(url)
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length > 0) {
                data.forEach(row => {
                    html += `<div class="list-group-item list-group-item-action py-3 border-0 border-bottom cursor-pointer" onclick="seleccionarClienteHistorial(${row.id}, '${row.nombre} ${row.apellido}')" style="cursor: pointer;">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold text-dark">${row.nombre} ${row.apellido}</h6>
                                    <span class="badge bg-light text-muted border-0">${row.cedula}</span>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
                resultados.innerHTML = html;
            } else {
                resultados.innerHTML = '<div class="p-3 text-center text-muted"><i class="fas fa-info-circle me-1"></i> No se encontraron clientes</div>';
            }
        })
        .catch(err => {
            resultados.innerHTML = '<div class="p-3 text-center text-danger">Error al buscar</div>';
        });
}

function seleccionarClienteHistorial(id, nombre) {
    document.getElementById('cliente_id_historial').value = id;
    document.getElementById('buscar_cliente_historial').value = nombre;
    document.getElementById('resultados_historial').classList.add('d-none');
    cargarHistorialCliente();
}

function resetHistorial() {
    document.getElementById('cliente_id_historial').value = 0;
    document.getElementById('buscar_cliente_historial').value = '';
    document.getElementById('resultados_historial').classList.add('d-none');
    cargarHistorialCliente();
}

let tblHistorial;
function cargarHistorialCliente() {
    let cliente_id = document.getElementById("cliente_id_historial") ? document.getElementById("cliente_id_historial").value : 0;
    
    if (tblHistorial) {
        tblHistorial.destroy();
    }
    
    tblHistorial = $('#tblHistorialCliente').DataTable({
        ajax: {
            url: base_url + "Consultas/listar_historial_cliente",
            type: "POST",
            data: { cliente_id: cliente_id },
            dataSrc: ""
        },
        columns: [
            { data: "reserva_id" },
            { data: "imagen_html" },
            { data: "vehiculo" },
            { data: "fecha_inicio" },
            { data: "fecha_fin" },
            { 
                data: "estado",
                render: function(data) {
                    let color = (data == 'Devuelta' || data == 'Completado') ? 'success' : (data == 'Activa' ? 'primary' : 'warning');
                    return `<span class="badge bg-${color}">${data}</span>`;
                }
            }
        ],
        responsive: true,
        language: {
            url: base_url + "Assets/js/es-ES.json"
        },
        order: [[0, "desc"]]
    });
}

function exportToPDF(consulta) {
    let url = base_url + "Reportes/pdf_" + consulta;
    if (consulta === "historial_cliente") {
        let cliente_id = document.getElementById("cliente_id_historial").value;
        url += "?cliente_id=" + cliente_id;
    }
    window.open(url, "_blank");
}
