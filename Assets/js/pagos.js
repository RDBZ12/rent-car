// tblPagos is already declared in funciones.js
let myModalPago;
let _reservasCachePago = [];
let _clientesBusquedaCachePago = [];
let seleccionGlobalPago = {
    cliente_id: null,
    reservas: new Set(), // Set de IDs de reserva
    montoPorReserva: {} // reserva_id -> monto_a_pagar
};

document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById('modalPago')) {
        myModalPago = new bootstrap.Modal(document.getElementById('modalPago'));
    }

    // Inicializar DataTable Principal
    if ($("#tblPagos").length) {
        tblPagos = $("#tblPagos").DataTable({
            ajax: {
                url: base_url + "Pagos/listar",
                dataSrc: "",
            },
            columns: [
                { data: "reserva_id" },
                { data: "cliente" },
                { data: "dni" },
                { data: "f_pago" },
                { data: "metodo_pago" },
                { data: "monto_format" },
                { data: "estatus" },
                {
                    data: "id",
                    render: function(data) {
                        return `<div class="d-flex justify-content-center">
                            <button class="btn btn-outline-primary btn-sm me-1" onclick="verDetallePago(${data})" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="verComprobantePago(${data})" title="Ver Comprobante">
                                <i class="fas fa-file-invoice"></i>
                            </button>
                        </div>`;
                    }
                }
            ],
            language: DT_LANG_ES,
            destroy: true,
            responsive: true,
            order: [[0, "desc"]]
        });
    }

    // Resetear estado al cerrar modal
    if (document.getElementById('modalPago')) {
        document.getElementById('modalPago').addEventListener('hidden.bs.modal', function () {
            resetearSeleccionPago();
        });
    }
});

function frmPago() {
    const titlePago = document.getElementById("titlePago");
    if (titlePago) titlePago.textContent = "Registrar Pago";
    
    const form = document.getElementById("formularioPago");
    if (form) form.reset();
    resetearSeleccionPago();

    if (myModalPago) myModalPago.show();
}

function resetearSeleccionPago() {
    seleccionGlobalPago = {
        cliente_id: null,
        reservas: new Set(),
        montoPorReserva: {}
    };
    
    const hCliente = document.getElementById("pago_cliente_id");
    if (hCliente) hCliente.value = "";
    
    const resBox = document.getElementById("resultados_busqueda_pago");
    if (resBox) resBox.classList.add("d-none");
    
    const resSec = document.getElementById("seccion_resumen_pago");
    if (resSec) resSec.classList.add("d-none");
    
    const detBox = document.getElementById("info_reserva_detalles");
    if (detBox) detBox.classList.add("d-none");
    
    const zeroBox = document.getElementById("saldo_cero_info");
    if (zeroBox) zeroBox.classList.add("d-none");
    
    const tbody = document.getElementById("tbl_reservas_pago");
    if (tbody) {
        tbody.innerHTML = `<tr>
            <td colspan="6" class="text-center py-4 text-muted">
                <i class="fas fa-user-clock fa-2x mb-2 d-block opacity-25"></i>
                Seleccione un cliente para cargar sus reservas
            </td>
        </tr>`;
    }

    recalcularTotalesPago();
}

function buscarPagoNuevo(val) {
    const box = document.getElementById("resultados_busqueda_pago");
    if (!box) return;

    const q = val.trim();
    if (q.length < 1) {
        box.classList.add("d-none");
        return;
    }

    const fd = new FormData();
    fd.append("q", q);
    
    fetch(base_url + "Pagos/buscarClientes", { method: "POST", body: fd })
        .then(r => r.json())
        .then(rows => {
            _clientesBusquedaCachePago = rows;
            let html = '<div class="list-group list-group-flush">';
            
            if (/^\d+$/.test(q)) {
                html += `<button type="button" class="list-group-item list-group-item-action bg-light fw-bold text-primary" onclick="cargarReservaIndividualPago(${q})">
                    <i class="fas fa-hashtag me-2"></i> Ir a Reserva N° ${q}
                </button>`;
            }

            if (rows.length > 0) {
                rows.forEach((row, idx) => {
                    const nom = (row.nombre + " " + row.apellido).trim();
                    html += `<button type="button" class="list-group-item list-group-item-action cli-row-pago" data-idx="${idx}">
                        <div class="d-flex justify-content-between">
                            <strong>${nom}</strong>
                            <small class="text-muted">ID: ${row.cliente_id}</small>
                        </div>
                    </button>`;
                });
            } else if (!/^\d+$/.test(q)) {
                html += '<div class="p-3 text-center text-muted small">Sin coincidencias</div>';
            }

            html += "</div>";
            box.innerHTML = html;
            box.classList.remove("d-none");

            box.querySelectorAll(".cli-row-pago").forEach(btn => {
                btn.addEventListener("click", function() {
                    const idx = this.getAttribute("data-idx");
                    const c = _clientesBusquedaCachePago[idx];
                    seleccionarClientePago(c);
                });
            });
        });
}

function seleccionarClientePago(c) {
    const hId = document.getElementById("pago_cliente_id");
    const inpBus = document.getElementById("buscar_pago");
    const nomS = document.getElementById("pago_nombre_cli");
    const dniS = document.getElementById("pago_dni_cli");
    const detB = document.getElementById("info_reserva_detalles");
    const resS = document.getElementById("seccion_resumen_pago");

    if (hId) hId.value = c.cliente_id;
    if (inpBus) inpBus.value = (c.nombre + " " + c.apellido).trim();
    
    const box = document.getElementById("resultados_busqueda_pago");
    if (box) box.classList.add("d-none");
    
    if (nomS) nomS.textContent = (c.nombre + " " + c.apellido).trim();
    if (dniS) dniS.textContent = c.cedula || "N/A";
    if (detB) detB.classList.remove("d-none");
    if (resS) resS.classList.remove("d-none");
    
    cargarReservasClientePago(c.cliente_id);
}

function cargarReservasClientePago(cliente_id) {
    const tbody = document.getElementById("tbl_reservas_pago");
    if (!tbody) return;

    const fd = new FormData();
    fd.append("cliente_id", cliente_id);
    
    fetch(base_url + "Pagos/buscarPorCliente", { method: "POST", body: fd })
        .then(r => r.json())
        .then(data => {
            _reservasCachePago = data;
            let html = "";
            
            if (data.length === 0) {
                html = '<tr><td colspan="6" class="text-center py-3 text-muted">No tiene reservas pendientes.</td></tr>';
            } else {
                data.forEach(res => {
                    const total = (parseFloat(res.total) + parseFloat(res.total_penalidades)).toFixed(2);
                    const pagado = parseFloat(res.total_pagos).toFixed(2);
                    const saldo = parseFloat(res.saldo).toFixed(2);
                    
                    html += `<tr>
                        <td class="text-center fw-bold">${res.reserva_id}</td>
                        <td>
                            <div class="small fw-bold">${res.fecha_inicio} al ${res.fecha_fin}</div>
                        </td>
                        <td class="text-end">$${total}</td>
                        <td class="text-end text-success">$${pagado}</td>
                        <td class="text-end fw-bold text-danger">$${saldo}</td>
                        <td class="text-center">
                            <input class="form-check-input chk-reserva-pago" type="checkbox" value="${res.reserva_id}" 
                                onchange="toggleReservaSeleccionPago(${res.reserva_id}, this.checked)">
                        </td>
                    </tr>`;
                });
            }
            
            tbody.innerHTML = html;
            recalcularTotalesPago();
        });
}

function cargarReservaIndividualPago(reserva_id) {
    const box = document.getElementById("resultados_busqueda_pago");
    if (box) box.classList.add("d-none");

    const inpBus = document.getElementById("buscar_pago");
    if (inpBus) inpBus.value = "Reserva N° " + reserva_id;
    
    fetch(base_url + "Pagos/saldo/" + reserva_id)
        .then(r => r.json())
        .then(res => {
            if (!res || !res.total) {
                Swal.fire("Error", "Reserva no encontrada.", "error");
                return;
            }
            
            const nomS = document.getElementById("pago_nombre_cli");
            const dniS = document.getElementById("pago_dni_cli");
            const detB = document.getElementById("info_reserva_detalles");
            const resS = document.getElementById("seccion_resumen_pago");

            if (nomS) nomS.textContent = (res.cliente_nombre + " " + res.cliente_apellido).trim();
            if (dniS) dniS.textContent = res.cedula || "N/A";
            if (detB) detB.classList.remove("d-none");
            if (resS) resS.classList.remove("d-none");
            
            const total = (parseFloat(res.total) + parseFloat(res.total_penalidades)).toFixed(2);
            const pagado = parseFloat(res.total_pagos).toFixed(2);
            const saldo = parseFloat(res.saldo).toFixed(2);
            
            const tbody = document.getElementById("tbl_reservas_pago");
            if (tbody) {
                tbody.innerHTML = `<tr>
                    <td class="text-center fw-bold">${res.reserva_id}</td>
                    <td><div class="small fw-bold">${res.fecha_inicio} al ${res.fecha_fin}</div></td>
                    <td class="text-end">$${total}</td>
                    <td class="text-end text-success">$${pagado}</td>
                    <td class="text-end fw-bold text-danger">$${saldo}</td>
                    <td class="text-center">
                        <input class="form-check-input chk-reserva-pago" type="checkbox" value="${res.reserva_id}" checked
                            onchange="toggleReservaSeleccionPago(${res.reserva_id}, this.checked)">
                    </td>
                </tr>`;
            }
            
            seleccionGlobalPago.reservas.clear();
            seleccionGlobalPago.reservas.add(reserva_id.toString());
            _reservasCachePago = [res];
            
            recalcularTotalesPago();
        });
}

function toggleReservaSeleccionPago(id, checked) {
    id = id.toString();
    if (checked) {
        seleccionGlobalPago.reservas.add(id);
    } else {
        seleccionGlobalPago.reservas.delete(id);
    }
    recalcularTotalesPago();
}

function toggleTodasReservasPago(estado) {
    const checks = document.querySelectorAll(".chk-reserva-pago");
    checks.forEach(chk => {
        chk.checked = estado;
        const id = chk.value;
        if (estado) seleccionGlobalPago.reservas.add(id);
        else seleccionGlobalPago.reservas.delete(id);
    });
    recalcularTotalesPago();
}

function recalcularTotalesPago() {
    let t_total = 0;
    let t_pagado = 0;
    let t_saldo = 0;

    seleccionGlobalPago.reservas.forEach(id => {
        const res = _reservasCachePago.find(r => r.reserva_id == id);
        if (res) {
            t_total += (parseFloat(res.total) + parseFloat(res.total_penalidades));
            t_pagado += parseFloat(res.total_pagos);
            t_saldo += parseFloat(res.saldo);
        }
    });

    const totS = document.getElementById("pago_total_sel");
    const pagS = document.getElementById("pago_pagado_sel");
    const salS = document.getElementById("pago_saldo_sel");

    if (totS) totS.textContent = "$" + t_total.toFixed(2);
    if (pagS) pagS.textContent = "$" + t_pagado.toFixed(2);
    if (salS) salS.textContent = "$" + t_saldo.toFixed(2);
    
    const inputMonto = document.getElementById("pago_monto");
    if (inputMonto) {
        if (t_saldo > 0) {
            inputMonto.value = t_saldo.toFixed(2);
            const zeroB = document.getElementById("saldo_cero_info");
            if (zeroB) zeroB.classList.add("d-none");
        } else {
            inputMonto.value = "0.00";
            if (seleccionGlobalPago.reservas.size > 0) {
                const zeroB = document.getElementById("saldo_cero_info");
                if (zeroB) zeroB.classList.remove("d-none");
            }
        }
    }
}

function registrarPago(e) {
    e.preventDefault();
    
    if (seleccionGlobalPago.reservas.size === 0) {
        Swal.fire("Atención", "Seleccione al menos una reserva.", "warning");
        return;
    }

    const mInp = document.getElementById("pago_monto");
    const montoTotal = mInp ? parseFloat(mInp.value) : 0;
    const metodo = document.getElementById("pago_metodo").value;
    const fecha = document.getElementById("pago_fecha").value;

    if (montoTotal <= 0) {
        Swal.fire("Atención", "Monto inválido.", "warning");
        return;
    }

    const payload = [];
    let restante = montoTotal;
    
    const seleccionadas = [];
    seleccionGlobalPago.reservas.forEach(id => {
        const res = _reservasCachePago.find(r => r.reserva_id == id);
        if (res) seleccionadas.push(res);
    });

    seleccionadas.forEach(res => {
        if (restante <= 0) return;
        const saldo = parseFloat(res.saldo);
        const pagoAplicar = Math.min(restante, saldo);
        
        payload.push({
            reserva_id: res.reserva_id,
            monto_asignado: pagoAplicar
        });
        restante -= pagoAplicar;
    });

    const fd = new FormData();
    fd.append("batch_data", JSON.stringify(payload));
    fd.append("monto", montoTotal);
    fd.append("metodo", metodo);
    fd.append("fecha_pago", fecha);

    const btn = document.getElementById("btnAccionPago");
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    fetch(base_url + "Pagos/registrarBatch", {
        method: "POST",
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Pago';
        }
        
        if (res.icono === 'success') {
            if (myModalPago) myModalPago.hide();
            if (tblPagos) tblPagos.ajax.reload();
            Swal.fire("Éxito", res.msg, "success");
        } else {
            Swal.fire("Error", res.msg, "error");
        }
    })
    .catch(err => {
        console.error(err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Pago';
        }
        Swal.fire("Error", "Error en el servidor", "error");
    });
}

function verComprobantePago(id) {
    window.open(base_url + "Pagos/reporte/" + id, "_blank");
}

function verDetallePago(id) {
    if (!id) return;
    const url = base_url + "Pagos/getDetalle/" + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res.error) {
                Swal.fire("Error", res.error, "error");
                return;
            }
            
            const h = res.header;
            document.getElementById("det_pago_cliente").textContent = h.nombre + ' ' + h.apellido;
            document.getElementById("det_pago_dni").textContent = h.dni;
            document.getElementById("det_pago_fecha").textContent = h.fecha_pago;
            document.getElementById("det_pago_metodo").textContent = h.metodo_pago;
            document.getElementById("det_pago_total").textContent = '$' + parseFloat(h.total).toFixed(2);
            
            let html = '';
            res.detalles.forEach(d => {
                html += `<tr>
                    <td>Reserva N° ${d.reserva_id}</td>
                    <td class="text-end fw-bold">$${parseFloat(d.monto_aplicado).toFixed(2)}</td>
                </tr>`;
            });
            document.getElementById("det_pago_lista").innerHTML = html;
            
            const modal = new bootstrap.Modal(document.getElementById("modalDetallePago"));
            modal.show();
        }
    };
}
