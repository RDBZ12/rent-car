let tblPenalidades;
let myModalPenalidad;
document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById('nuevoPenalidad')) {
        myModalPenalidad = new bootstrap.Modal(document.getElementById('nuevoPenalidad'));
    }
    // Inicializar DataTable
    tblPenalidades = $("#tblPenalidades").DataTable({
        ajax: {
            url: base_url + "Penalidades/listar",
            dataSrc: "",
        },
        columns: [
            { data: "reserva_id" },
            { data: "cliente" },
            { data: "placa" },
            { data: "tipo_badge" },
            { data: "descripcion" },
            { data: "f_registro" },
            { data: "monto_format" },
            { data: "estado_badge" }
        ],
        language: DT_LANG_ES,
        destroy: true,
        responsive: true,
        order: [[0, "desc"]]
    });
});

function frmPenalidad() {
    const titleP = document.getElementById("titlePenalidad");
    const btnAccP = document.getElementById("btnAccionPen");
    const frmP = document.getElementById("formularioPenalidad");
    const vehId = document.getElementById("vehiculo_id");

    if (titleP) titleP.textContent = "Añadir Nuevo Cargo Manual";
    if (btnAccP) btnAccP.textContent = "Guardar Penalidad";
    if (frmP) frmP.reset();
    if (vehId) vehId.innerHTML = '<option value="">Seleccione primero la reserva</option>';
    
    if (myModalPenalidad) myModalPenalidad.show();
}

function cargarVehiculos(reserva_id) {
    if (reserva_id == '') {
        document.getElementById("vehiculo_id").innerHTML = '<option value="">Seleccione primero la reserva</option>';
        return;
    }

    const url = base_url + "Penalidades/buscarVehiculos/" + reserva_id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            let html = '<option value="">Seleccione Vehículo Afectado</option>';
            res.forEach(row => {
                html += `<option value="${row.vehiculo_id}">Vehículo Placa: ${row.placa}</option>`;
            });
            document.getElementById("vehiculo_id").innerHTML = html;
        }
    };
}

function registrarPenalidad(e) {
    e.preventDefault();
    const reserva_id = document.getElementById("reserva_id").value;
    const vehiculo_id = document.getElementById("vehiculo_id").value;
    const tipo = document.getElementById("tipo").value;
    const monto = document.getElementById("monto").value;
    const desc = document.getElementById("descripcion").value;

    if (reserva_id == '' || vehiculo_id == '' || monto == '' || desc == '') {
        Swal.fire({
            position: 'top-end',
            icon: 'warning',
            title: 'Todos los campos son obligatorios',
            showConfirmButton: false,
            timer: 3000
        });
        return;
    }

    const url = base_url + "Penalidades/registrar";
    const frm = document.getElementById("formularioPenalidad");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res.icono == 'success') {
                if (myModalPenalidad) myModalPenalidad.hide();
                tblPenalidades.ajax.reload();
            }
            Swal.fire({
                position: 'top-end',
                icon: res.icono,
                title: res.msg,
                showConfirmButton: false,
                timer: 3000
            });
        }
    };
}
