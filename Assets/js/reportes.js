document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById("tblIngresosFecha")) {
        cargarIngresos();
    }

    if (document.getElementById("tblVehiculosRentados")) {
        $('#tblVehiculosRentados').DataTable({
            ajax: {
                url: base_url + "Reportes/listar_vehiculos_rentados",
                dataSrc: ""
            },
            columns: [
                { data: "imagen_html" },
                { data: "placa" },
                { data: "marca" },
                { data: "modelo" },
                { data: "cantidad" }
            ],
            responsive: true,
            language: {
                url: base_url + "Assets/js/es-ES.json"
            }
        });
    }

    if (document.getElementById("tblClientesFrecuentes")) {
        $('#tblClientesFrecuentes').DataTable({
            ajax: {
                url: base_url + "Reportes/listar_clientes_frecuentes",
                dataSrc: ""
            },
            columns: [
                { data: "nombre" },
                { data: "apellido" },
                { data: "cedula" },
                { data: "total" }
            ],
            responsive: true,
            language: {
                url: base_url + "Assets/js/es-ES.json"
            }
        });
    }

    if (document.getElementById("tblPenalidadesGeneradas")) {
        $('#tblPenalidadesGeneradas').DataTable({
            ajax: {
                url: base_url + "Reportes/listar_penalidades_generadas",
                dataSrc: ""
            },
            columns: [
                { data: "tipo" },
                { data: "total" }
            ],
            responsive: true,
            language: {
                url: base_url + "Assets/js/es-ES.json"
            }
        });
    }
});

let tblIngresos;
function cargarIngresos() {
    let f_inicio = document.getElementById("fecha_inicio") ? document.getElementById("fecha_inicio").value : "";
    let f_fin = document.getElementById("fecha_fin") ? document.getElementById("fecha_fin").value : "";

    if (tblIngresos) {
        tblIngresos.destroy();
    }

    tblIngresos = $('#tblIngresosFecha').DataTable({
        ajax: {
            url: base_url + "Reportes/listar_ingresos_fecha",
            type: "POST",
            data: { fecha_inicio: f_inicio, fecha_fin: f_fin },
            dataSrc: ""
        },
        columns: [
            { data: "fecha_pago" },
            { data: "ingresos" }
        ],
        responsive: true,
        language: {
            url: base_url + "Assets/js/es-ES.json"
        }
    });
}

function exportToPDF(reporte) {
    let url = base_url + "Reportes/pdf_" + reporte;
    
    if (reporte === "ingresos_fecha") {
        let f_inicio = document.getElementById("fecha_inicio").value;
        let f_fin = document.getElementById("fecha_fin").value;
        url += "?fecha_inicio=" + f_inicio + "&fecha_fin=" + f_fin;
    }
    
    window.open(url, "_blank");
}
