let myModalReserva, myModalDetalle, myModalVehiculos;
let lineasVehiculos = [];
let clienteSeleccionado = null;
/** Total de la reserva tras descuento (tope máximo para abonos/pagos). */
let totalReservaActual = 0;

function fmtRD(n) {
    const v = Number(n) || 0;
    return "RD$ " + v.toLocaleString("es-DO", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function urlImagenVeh(imagen) {
    const def = base_url + "uploads/vehiculos/default.png";
    if (!imagen || String(imagen).trim() === "") return def;
    const img = String(imagen).trim();
    if (img.indexOf("http://") === 0 || img.indexOf("https://") === 0) return img;
    if (img.indexOf("uploads/") === 0) return base_url + img.replace(/^\//, "");
    return base_url + "uploads/vehiculos/" + img;
}

function getDiasEntreFechas() {
    const fi = document.getElementById("fecha_inicio").value;
    const ff = document.getElementById("fecha_fin").value;
    if (!fi || !ff) return 1;
    const d1 = new Date(fi + "T12:00:00");
    const d2 = new Date(ff + "T12:00:00");
    if (d2 < d1) return 0;
    const diff = Math.round((d2 - d1) / (86400000));
    return Math.max(1, diff);
}

function getExcludeIds() {
    return lineasVehiculos
        .map((v) => parseInt(v.vehiculo_id, 10))
        .filter((id) => !isNaN(id))
        .join(",");
}

function aplicarReglasTipoCobro() {
    const tipo = document.getElementById("tipo_cobro_reserva")?.value || "ninguno";
    const inp = document.getElementById("abono");
    const leyenda = document.getElementById("abono_max_leyenda");
    const err = document.getElementById("abono_error_msg");
    const max = Math.max(0, Math.round(totalReservaActual * 100) / 100);

    if (leyenda) leyenda.textContent = "Máximo permitido: " + fmtRD(max);
    if (err) err.classList.add("d-none");
    if (!inp) return;

    if (tipo === "ninguno") {
        inp.value = "0";
        inp.disabled = true;
        return;
    }
    if (tipo === "total") {
        inp.value = max.toFixed(2);
        inp.disabled = true;
        return;
    }
    inp.disabled = max <= 0;
    let v = parseFloat(inp.value);
    if (isNaN(v) || v < 0) v = 0;
    if (v > max) {
        inp.value = max.toFixed(2);
        if (err) err.classList.remove("d-none");
    }
}

function recalcularResumen() {
    const dias = getDiasEntreFechas();
    const elDias = document.getElementById("dias_totales");
    if (elDias) elDias.value = dias < 1 ? 0 : dias;

    lineasVehiculos.forEach((linea) => {
        linea.dias = dias;
        linea.subtotal = Math.round(linea.precio_dia * dias * 100) / 100;
    });

    let subtotalGen = 0;
    lineasVehiculos.forEach((l) => {
        subtotalGen += l.subtotal;
    });
    subtotalGen = Math.round(subtotalGen * 100) / 100;

    const desc = parseFloat(document.getElementById("descuento").value) || 0;
    totalReservaActual = Math.max(0, Math.round((subtotalGen - desc) * 100) / 100);

    const trd = document.getElementById("total_reserva_display");
    if (trd) trd.textContent = fmtRD(totalReservaActual);

    aplicarReglasTipoCobro();

    const abono = parseFloat(document.getElementById("abono").value) || 0;
    const saldo = Math.round((totalReservaActual - abono) * 100) / 100;

    let prom = 0;
    if (lineasVehiculos.length > 0) {
        const sumPrecios = lineasVehiculos.reduce((a, l) => a + l.precio_dia, 0);
        prom = sumPrecios / lineasVehiculos.length;
    }

    const elProm = document.getElementById("res_tarifa_promedio");
    const elSub = document.getElementById("res_subtotal_general");
    const elDesc = document.getElementById("res_descuento_line");
    const elTot = document.getElementById("res_total_final");
    const elSaldo = document.getElementById("saldo_estimado_display");
    if (elProm) elProm.textContent = fmtRD(prom);
    if (elSub) elSub.textContent = fmtRD(subtotalGen);
    if (elDesc) elDesc.textContent = fmtRD(desc);
    if (elTot) elTot.textContent = fmtRD(totalReservaActual);
    if (elSaldo) elSaldo.textContent = fmtRD(saldo);

    renderTablaDetalle();
    actualizarPanelesLateralVehiculo();
}

function actualizarPanelesLateralVehiculo() {
    const ph = document.getElementById("panel_vehiculo_placeholder");
    const pl = document.getElementById("panel_vehiculo_lista");
    const th = document.getElementById("panel_tarifa_placeholder");
    const td = document.getElementById("panel_tarifa_desglose");
    if (!ph || !pl || !th || !td) return;

    if (lineasVehiculos.length === 0) {
        ph.classList.remove("d-none");
        pl.classList.add("d-none");
        pl.innerHTML = "";
        th.classList.remove("d-none");
        td.classList.add("d-none");
        td.innerHTML = "";
        return;
    }

    ph.classList.add("d-none");
    pl.classList.remove("d-none");
    let vh = "";
    lineasVehiculos.forEach((l) => {
        vh += `<div class="mb-1"><i class="fas fa-check text-success me-1"></i><strong>${l.label}</strong> <span class="text-muted">(${l.placa})</span></div>`;
    });
    pl.innerHTML = vh;

    th.classList.add("d-none");
    td.classList.remove("d-none");
    let des = '<ul class="list-unstyled mb-0">';
    lineasVehiculos.forEach((l) => {
        des += `<li class="mb-2 border-bottom pb-2"><div class="fw-semibold">${l.label}</div>
            <div class="text-muted">RD$ ${Number(l.precio_dia).toFixed(2)} × ${l.dias} días = <strong class="text-dark">${fmtRD(l.subtotal)}</strong></div></li>`;
    });
    des += "</ul>";
    td.innerHTML = des;
}

function renderTablaDetalle() {
    const tbody = document.getElementById("tbl_detalle_vehiculos");
    const vacio = document.getElementById("tbl_detalle_vacio");
    if (!tbody) return;
    if (lineasVehiculos.length === 0) {
        tbody.innerHTML =
            '<tr id="tbl_detalle_vacio"><td colspan="6" class="text-center text-muted py-4">Agregue vehículos con el botón superior.</td></tr>';
        return;
    }
    if (vacio) vacio.remove();
    let html = "";
    lineasVehiculos.forEach((l) => {
        html += `<tr>
            <td><strong>${l.label}</strong><br><small class="text-muted">${l.tipo || ""}</small></td>
            <td>${l.placa}</td>
            <td class="text-end">${fmtRD(l.precio_dia)}</td>
            <td class="text-center">${l.dias}</td>
            <td class="text-end fw-semibold">${fmtRD(l.subtotal)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarVehiculoLinea(${l.vehiculo_id})" title="Quitar"><i class="fas fa-times"></i></button>
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function quitarVehiculoLinea(vehiculo_id) {
    const id = parseInt(vehiculo_id, 10);
    lineasVehiculos = lineasVehiculos.filter((v) => parseInt(v.vehiculo_id, 10) !== id);
    recalcularResumen();
    const mv = document.getElementById("modalVehiculos");
    if (mv && mv.classList.contains("show")) {
        cargarVehiculosModal();
    }
}

function seleccionarCliente(c) {
    clienteSeleccionado = c;
    document.getElementById("cliente_id").value = c.cliente_id;
    document.getElementById("panel_cliente_vacio").classList.add("d-none");
    document.getElementById("panel_cliente_datos").classList.remove("d-none");
    document.getElementById("pc_nombre").textContent = (c.nombre + " " + c.apellido).trim();
    document.getElementById("pc_cedula").textContent = c.cedula || "—";
    document.getElementById("pc_telefono").textContent = c.telefono || "—";
    document.getElementById("resultados_clientes_buscar").classList.add("d-none");
    document.getElementById("cliente_buscar").value = (c.nombre + " " + c.apellido).trim();
}

function limpiarClienteSeleccion() {
    clienteSeleccionado = null;
    document.getElementById("cliente_id").value = "";
    document.getElementById("panel_cliente_vacio").classList.remove("d-none");
    document.getElementById("panel_cliente_datos").classList.add("d-none");
}

window._clientesBusquedaCache = [];

function buscarClientesAjax() {
    const q = document.getElementById("cliente_buscar").value.trim();
    const box = document.getElementById("resultados_clientes_buscar");
    if (q.length < 1) {
        Swal.fire("Atención", "Escriba al menos un carácter para buscar", "info");
        return;
    }
    const fd = new FormData();
    fd.append("q", q);
    fetch(base_url + "Clientes/buscarClientesReserva", { method: "POST", body: fd })
        .then((r) => r.json())
        .then((rows) => {
            if (!rows || rows.length === 0) {
                box.innerHTML = '<div class="p-2 small text-muted">Sin resultados.</div>';
                box.classList.remove("d-none");
                return;
            }
            window._clientesBusquedaCache = rows;
            let html =
                '<table class="table table-sm table-hover mb-0"><thead><tr><th>Nombre</th><th>Cédula</th><th></th></tr></thead><tbody>';
            rows.forEach((row, idx) => {
                const nom = (row.nombre + " " + row.apellido).trim();
                const ced = row.cedula || "";
                html += `<tr style="cursor:pointer" class="cli-row" data-idx="${idx}">
                    <td>${nom}</td><td>${ced}</td>
                    <td><button type="button" class="btn btn-sm btn-primary">Elegir</button></td>
                </tr>`;
            });
            html += "</tbody></table>";
            box.innerHTML = html;
            box.classList.remove("d-none");
            box.querySelectorAll(".cli-row").forEach((tr) => {
                tr.addEventListener("click", function () {
                    const idx = parseInt(this.getAttribute("data-idx"), 10);
                    if (!isNaN(idx) && window._clientesBusquedaCache[idx]) {
                        seleccionarCliente(window._clientesBusquedaCache[idx]);
                    }
                });
            });
        })
        .catch(() => Swal.fire("Error", "No se pudo buscar clientes", "error"));
}

let _marcasFiltroCargadas = false;

function cargarMarcasFiltroModal(callback) {
    fetch(base_url + "Reservas/marcasVehiculos")
        .then((r) => r.json())
        .then((marcas) => {
            const sel = document.getElementById("filtro_marca");
            if (!sel) return;
            const primera = sel.options[0] ? sel.options[0].cloneNode(true) : null;
            sel.innerHTML = "";
            if (primera) sel.appendChild(primera);
            else {
                const o0 = document.createElement("option");
                o0.value = "";
                o0.textContent = "Todas las marcas";
                sel.appendChild(o0);
            }
            (marcas || []).forEach((m) => {
                const o = document.createElement("option");
                o.value = String(m.id);
                o.textContent = m.nombre;
                sel.appendChild(o);
            });
            _marcasFiltroCargadas = true;
            if (typeof callback === "function") callback();
        })
        .catch(() => {
            Swal.fire("Error", "No se pudieron cargar las marcas", "error");
        });
}

function cargarModelosFiltroModal(marcaId) {
    const sel = document.getElementById("filtro_modelo");
    if (!sel) return;
    sel.innerHTML = '<option value="">Todos los modelos</option>';
    const mid = parseInt(marcaId, 10);
    if (!mid) return;
    fetch(base_url + "Reservas/modelosVehiculos/" + mid)
        .then((r) => r.json())
        .then((modelos) => {
            (modelos || []).forEach((m) => {
                const o = document.createElement("option");
                o.value = String(m.id);
                o.textContent = m.nombre;
                sel.appendChild(o);
            });
        })
        .catch(() => {});
}

function leerTextoFiltrosMarcaModelo() {
    const ms = document.getElementById("filtro_marca");
    const md = document.getElementById("filtro_modelo");
    let marca = "";
    let modelo = "";
    if (ms && ms.value !== "") {
        marca = ms.options[ms.selectedIndex].text.trim();
    }
    if (md && md.value !== "") {
        modelo = md.options[md.selectedIndex].text.trim();
    }
    return { marca, modelo };
}

function abrirModalVehiculos() {
    const fi = document.getElementById("fecha_inicio").value;
    const ff = document.getElementById("fecha_fin").value;
    if (!fi || !ff) {
        Swal.fire("Atención", "Indique fecha inicio y fecha fin", "warning");
        return;
    }
    if (getDiasEntreFechas() < 1) {
        Swal.fire("Atención", "La fecha fin debe ser igual o posterior a la fecha inicio", "warning");
        return;
    }
    const abrir = () => {
        if (myModalVehiculos) myModalVehiculos.show();
        cargarVehiculosModal();
    };
    if (!_marcasFiltroCargadas) {
        cargarMarcasFiltroModal(abrir);
    } else {
        abrir();
    }
}

window._vehiculosModalCache = {};

function cargarVehiculosModal() {
    const fi = document.getElementById("fecha_inicio").value;
    const ff = document.getElementById("fecha_fin").value;
    const mm = leerTextoFiltrosMarcaModelo();
    const marca = mm.marca;
    const modelo = mm.modelo;
    const term = document.getElementById("filtro_general").value.trim();
    const exclude = getExcludeIds();

    const load = document.getElementById("vehiculos_modal_loading");
    const grid = document.getElementById("vehiculos_modal_grid");
    const empty = document.getElementById("vehiculos_modal_empty");
    load.classList.remove("d-none");
    grid.innerHTML = "";
    empty.classList.add("d-none");

    const fd = new FormData();
    fd.append("f_ini", fi);
    fd.append("f_fin", ff);
    fd.append("marca", marca);
    fd.append("modelo", modelo);
    fd.append("term", term);
    fd.append("exclude", exclude);

    fetch(base_url + "Reservas/vehiculosDisponibles", { method: "POST", body: fd })
        .then((r) => r.json())
        .then((data) => {
            load.classList.add("d-none");
            if (data.error) {
                Swal.fire("Atención", data.error, "warning");
                return;
            }
            const excluir = new Set(
                lineasVehiculos.map((l) => parseInt(l.vehiculo_id, 10)).filter((id) => !isNaN(id))
            );
            data = (data || []).filter((v) => !excluir.has(parseInt(v.vehiculo_id, 10)));
            if (!data.length) {
                empty.classList.remove("d-none");
                return;
            }
            window._vehiculosModalCache = {};
            let html = "";
            data.forEach((v) => {
                const vid = v.vehiculo_id;
                window._vehiculosModalCache[vid] = v;
                const titulo = `${v.marca} ${v.modelo} ${v.anio || ""}`.trim();
                const precio = parseFloat(v.precio_dia) || 0;
                const dias = getDiasEntreFechas();
                html += `<div class="col-sm-6 col-lg-4 col-xl-3">
                    <div class="vehiculo-card-pick bg-white">
                        <img class="veh-img" src="${urlImagenVeh(v.imagen)}" alt="" loading="lazy" onerror="this.src='${base_url}uploads/vehiculos/default.png'">
                        <div class="p-3">
                            <div class="fw-bold mb-1">${titulo}</div>
                            <div class="small text-muted mb-2">
                                <div>Placa: <strong>${v.placa}</strong></div>
                                <div>Gama: <strong>${v.tipo || "—"}</strong></div>
                                <div>Días del alquiler: ${dias}</div>
                            </div>
                            <div class="precio-veh mb-2">${fmtRD(precio)} <small class="text-muted fw-normal">/ día</small></div>
                            <button type="button" class="btn btn-primary w-100 fw-bold text-uppercase btn-agregar-veh" data-vid="${vid}">Agregar</button>
                        </div>
                    </div>
                </div>`;
            });
            grid.innerHTML = html;
            grid.querySelectorAll(".btn-agregar-veh").forEach((btn) => {
                btn.addEventListener("click", function (ev) {
                    ev.stopPropagation();
                    const vid = parseInt(this.getAttribute("data-vid"), 10);
                    const v = window._vehiculosModalCache[vid];
                    if (v) agregarVehiculoDesdeModal(v);
                });
            });
        })
        .catch(() => {
            load.classList.add("d-none");
            Swal.fire("Error", "No se pudieron cargar los vehículos", "error");
        });
}

function agregarVehiculoDesdeModal(v) {
    const vid = parseInt(v.vehiculo_id, 10);
    if (lineasVehiculos.some((l) => parseInt(l.vehiculo_id, 10) === vid)) {
        Swal.fire("Atención", "Este vehículo ya está en la reserva", "info");
        return;
    }
    const dias = getDiasEntreFechas();
    const precio = parseFloat(v.precio_dia) || 0;
    const subtotal = Math.round(precio * dias * 100) / 100;
    const label = `${v.marca} ${v.modelo}${v.anio ? " " + v.anio : ""}`;
    lineasVehiculos.push({
        vehiculo_id: vid,
        placa: v.placa,
        marca: v.marca,
        modelo: v.modelo,
        anio: v.anio,
        tipo: v.tipo || "",
        precio_dia: precio,
        dias: dias,
        subtotal: subtotal,
        label: label,
    });
    recalcularResumen();
    cargarVehiculosModal();
    if (lineasVehiculos.length === 1) {
        const sec = document.getElementById("seccion_montos_pago");
        if (sec) sec.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
    Swal.fire({ position: "center", icon: "success", title: "Vehículo agregado", showConfirmButton: false, timer: 1500 });
}

document.addEventListener("DOMContentLoaded", function () {
    const modalRes = document.getElementById("modalReserva");
    if (modalRes) myModalReserva = new bootstrap.Modal(modalRes);

    const modalDet = document.getElementById("modalDetalleReserva");
    if (modalDet) myModalDetalle = new bootstrap.Modal(modalDet);

    const modalVeh = document.getElementById("modalVehiculos");
    if (modalVeh) myModalVehiculos = new bootstrap.Modal(modalVeh);

    tblReservas = $("#tblReservas").DataTable({
        ajax: {
            url: base_url + "Reservas/listar",
            dataSrc: "",
        },
        columns: [
            {
                className: "dt-control",
                orderable: false,
                data: null,
                defaultContent: "",
            },
            { data: "id", className: "text-center fw-bold" },
            { data: "cliente" },
            { data: "vehiculos" },
            { data: "f_prestamo" },
            { data: "f_devolucion" },
            { data: "saldo_format" },
            { data: "estado_badge" },
            { data: "total_format", className: "none" },
            { data: "acciones", className: "none" }
        ],
        language: DT_LANG_ES,
        destroy: true,
        responsive: true,
        order: [[1, "desc"]],
        columnDefs: [
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: 1 },
            { responsivePriority: 3, targets: 2 },
            { responsivePriority: 4, targets: 7 },
        ],
    });

    tblReservas.on("responsive-display", function (e, datatable, row, showHide) {
        if (showHide) {
            datatable.rows().every(function () {
                if (this.index() !== row.index() && this.child.isShown()) {
                    this.child.hide();
                    $(this.node()).removeClass("parent");
                }
            });
        }
    });

    const fi = document.getElementById("fecha_inicio");
    const ff = document.getElementById("fecha_fin");
    const abono = document.getElementById("abono");
    const desc = document.getElementById("descuento");
    if (fi)
        fi.addEventListener("change", function () {
            recalcularResumen();
        });
    if (ff)
        ff.addEventListener("change", function () {
            recalcularResumen();
        });
    if (abono) abono.addEventListener("input", recalcularResumen);
    if (desc) desc.addEventListener("input", recalcularResumen);

    const tipoCobro = document.getElementById("tipo_cobro_reserva");
    if (tipoCobro) {
        tipoCobro.addEventListener("change", function () {
            aplicarReglasTipoCobro();
            recalcularResumen();
        });
    }

    let debounceTimeout;
    const inpCli = document.getElementById("cliente_buscar");
    if (inpCli) {
        inpCli.addEventListener("input", function () {
            if (this.value.trim() === "") {
                limpiarClienteSeleccion();
                const box = document.getElementById("resultados_clientes_buscar");
                if (box) {
                    box.classList.add("d-none");
                    box.innerHTML = "";
                }
            } else {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    buscarClientesAjax();
                }, 500);
            }
        });
        inpCli.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                clearTimeout(debounceTimeout);
                buscarClientesAjax();
            }
        });
    }

    const selMarca = document.getElementById("filtro_marca");
    if (selMarca) {
        selMarca.addEventListener("change", function () {
            cargarModelosFiltroModal(this.value);
        });
    }
});

function verDetalleReserva(id) {
    if (!id) return;
    Swal.fire({ title: "Cargando...", didOpen: () => Swal.showLoading(), allowOutsideClick: false });
    const url = base_url + "Reservas/getDetalle/" + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            try {
                const res = JSON.parse(this.responseText);
                Swal.close();
                document.getElementById("det_cliente").textContent = res.reserva.nombre + " " + res.reserva.apellido;
                const ced = res.reserva.cedula || res.reserva.num_identidad || "—";
                document.getElementById("det_dni").textContent = "Cédula: " + ced + " | Tel: " + (res.reserva.telefono || "—");
                document.getElementById("det_fechas").textContent = res.reserva.fecha_inicio + " hasta " + res.reserva.fecha_fin;

                const badge = document.getElementById("det_estado");
                badge.textContent = res.reserva.estado;
                badge.className =
                    "badge rounded-pill " +
                    (res.reserva.estado == "Activa" || res.reserva.estado == "Pendiente"
                        ? "bg-primary"
                        : res.reserva.estado == "Finalizada" || res.reserva.estado == "Devuelta"
                          ? "bg-success"
                          : "bg-warning text-dark");

                let htmlVeh = "";
                res.vehiculos.forEach((v) => {
                    htmlVeh += `<tr><td class="px-3"><strong>${v.marca} ${v.modelo}</strong><br><small class="text-muted">${v.placa}</small></td><td class="text-center">${v.dias}</td><td class="text-end">$${v.precio_unitario}</td><td class="text-end px-3 fw-bold">$${v.subtotal}</td></tr>`;
                });
                document.getElementById("det_table_vehiculos").innerHTML = htmlVeh;

                let htmlPagos = "";
                if (res.pagos && res.pagos.length > 0) {
                    document.getElementById("cont_pagos").classList.remove("d-none");
                    res.pagos.forEach((p) => {
                        htmlPagos += `<tr><td class="px-3">${p.fecha}</td><td class="text-end px-3 fw-bold text-success">$${p.total}</td></tr>`;
                    });
                } else {
                    htmlPagos = '<tr><td colspan="2" class="text-center text-muted py-3 small">No se registran abonos</td></tr>';
                }
                document.getElementById("det_table_pagos").innerHTML = htmlPagos;

                document.getElementById("det_subtotal").textContent = "$" + res.financiero.total;
                document.getElementById("det_pagos").textContent = "$" + res.financiero.total_pagos;
                document.getElementById("det_saldo").textContent = "$" + res.financiero.saldo;

                if (myModalDetalle) myModalDetalle.show();
            } catch (e) {
                console.error(e);
                Swal.fire("Error", "No se pudo procesar la respuesta del servidor", "error");
            }
        }
    };
}

function frmReserva() {
    lineasVehiculos = [];
    clienteSeleccionado = null;
    _marcasFiltroCargadas = false;
    const form = document.getElementById("formularioReserva");
    if (form) form.reset();
    document.getElementById("fecha_reserva").value = new Date().toISOString().slice(0, 10);
    document.getElementById("fecha_inicio").value = new Date().toISOString().slice(0, 10);
    const ma = new Date();
    ma.setDate(ma.getDate() + 1);
    document.getElementById("fecha_fin").value = ma.toISOString().slice(0, 10);
    document.getElementById("cliente_id").value = "";
    document.getElementById("estado_reserva").value = "Pendiente";
    document.getElementById("abono").value = "0";
    document.getElementById("descuento").value = "0";
    const tc = document.getElementById("tipo_cobro_reserva");
    if (tc) tc.value = "ninguno";
    limpiarClienteSeleccion();
    document.getElementById("resultados_clientes_buscar").classList.add("d-none");
    document.getElementById("resultados_clientes_buscar").innerHTML = "";
    const fm = document.getElementById("filtro_marca");
    const fmo = document.getElementById("filtro_modelo");
    if (fmo) fmo.innerHTML = '<option value="">Todos los modelos</option>';
    if (document.getElementById("filtro_general")) document.getElementById("filtro_general").value = "";
    recalcularResumen();
    actualizarPanelesLateralVehiculo();
    if (myModalReserva) myModalReserva.show();
}

function registrarReserva(e) {
    if (e) e.preventDefault();
    const cliente_id = document.getElementById("cliente_id").value;
    const f_ini = document.getElementById("fecha_inicio").value;
    const f_fin = document.getElementById("fecha_fin").value;
    const estado = document.getElementById("estado_reserva").value;
    let abono = parseFloat(document.getElementById("abono").value) || 0;
    const desc = parseFloat(document.getElementById("descuento").value) || 0;
    const tipoCobro = document.getElementById("tipo_cobro_reserva")?.value || "ninguno";
    if (tipoCobro === "ninguno") {
        abono = 0;
    }

    if (!cliente_id) {
        Swal.fire("Atención", "Debe buscar y seleccionar un cliente activo", "warning");
        return;
    }
    if (!f_ini || !f_fin) {
        Swal.fire("Atención", "Complete las fechas de la reserva", "warning");
        return;
    }
    if (getDiasEntreFechas() < 1) {
        Swal.fire("Atención", "Revise el rango de fechas", "warning");
        return;
    }
    if (lineasVehiculos.length === 0) {
        Swal.fire("Atención", "Agregue al menos un vehículo a la reserva", "warning");
        return;
    }

    const dias = getDiasEntreFechas();
    let subtotalGen = 0;
    lineasVehiculos.forEach((l) => {
        subtotalGen += l.subtotal;
    });
    subtotalGen = Math.round(subtotalGen * 100) / 100;
    const totalFinal = Math.max(0, Math.round((subtotalGen - desc) * 100) / 100);

    if (totalFinal <= 0) {
        Swal.fire("Atención", "El total debe ser mayor que cero (revise descuentos)", "warning");
        return;
    }

    abono = Math.round(abono * 100) / 100;
    if (abono < 0) {
        Swal.fire("Atención", "El monto no puede ser negativo", "warning");
        return;
    }
    if (abono > totalFinal + 0.001) {
        Swal.fire(
            "Monto no permitido",
            "El abono o pago no puede exceder el total de la reserva (" + fmtRD(totalFinal) + ").",
            "warning"
        );
        return;
    }

    const vehiculos = lineasVehiculos.map((l) => ({
        vehiculo_id: l.vehiculo_id,
        promedio_diario: l.precio_dia,
        dias_totales: dias,
        subtotal: l.subtotal,
    }));

    const frm = new FormData();
    frm.append("cliente_id", cliente_id);
    frm.append("f_ini", f_ini);
    frm.append("f_fin", f_fin);
    frm.append("total", totalFinal);
    frm.append("abono", abono);
    frm.append("estado", estado);
    frm.append("vehiculos", JSON.stringify(vehiculos));

    const http = new XMLHttpRequest();
    http.open("POST", base_url + "Reservas/registrar", true);
    http.send(frm);
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res.icono == "success") {
                if (myModalReserva) myModalReserva.hide();
                tblReservas.ajax.reload();
            }
            Swal.fire("Mensaje", res.msg, res.icono);
        }
    };
}

function cancelarReserva(id) {
    if (!id) return;
    Swal.fire({
        title: "¿Cancelar esta reserva?",
        html: "El registro <strong>no se elimina</strong>: pasará a estado <strong>Cancelada</strong> y seguirá en la tabla.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, cancelar",
        cancelButtonText: "No",
    }).then((r) => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append("reserva_id", id);
        fetch(base_url + "Reservas/cancelar", { method: "POST", body: fd })
            .then((x) => x.json())
            .then((res) => {
                if (res.icono === "success" && typeof tblReservas !== "undefined") {
                    tblReservas.ajax.reload(null, false);
                }
                Swal.fire("Mensaje", res.msg || "", res.icono || "info");
            })
            .catch(() => Swal.fire("Error", "No se pudo cancelar la reserva", "error"));
    });
}

function entregarReserva(id) {
    if (!id) return;
    Swal.fire({
        title: "¿Entregar vehículo(s)?",
        html: "El alquiler iniciará y la reserva pasará a estado <strong>Activa</strong>.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#0d6efd",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, iniciar alquiler",
        cancelButtonText: "No, cancelar",
    }).then((r) => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append("reserva_id", id);
        fetch(base_url + "Reservas/entregar", { method: "POST", body: fd })
            .then((x) => x.json())
            .then((res) => {
                if (res.icono === "success" && typeof tblReservas !== "undefined") {
                    tblReservas.ajax.reload(null, false);
                }
                Swal.fire("Mensaje", res.msg || "", res.icono || "info");
            })
            .catch(() => Swal.fire("Error", "No se pudo iniciar el alquiler", "error"));
    });
}

function registrarCliRapido(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsCliente);
    }
    const dni = document.getElementById("dni").value;
    const nombre = document.getElementById("nombre").value;
    const apellido = document.getElementById("apellido").value;
    const telefono = document.getElementById("telefono").value;
    const email = document.getElementById("email").value;
    const direccion = document.getElementById("direccion").value;
    
    const errCli = typeof validarFormularioClienteOrden === 'function'
        ? validarFormularioClienteOrden(dni, telefono, nombre, apellido, email, direccion)
        : null;
        
    if (errCli) {
        Swal.fire('Atención', errCli, 'warning');
        return false;
    }
    
    const url = base_url + 'Clientes/registrar';
    const frm = document.getElementById("formularioCliente");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    
    // As in Clientes, we simulate a hidden ID empty since it's always a new creation
    const fd = new FormData(frm);
    fd.append('id', '');
    
    http.send(fd);
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            try {
                const res = JSON.parse(this.responseText);
                if (res.icono === 'success') {
                    Swal.fire({ position: "center", icon: "success", title: "Cliente registrado", showConfirmButton: false, timer: 1500 });
                    frm.reset();
                    const offcanvasEl = document.getElementById('offcanvasCliente');
                    const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (offcanvas) offcanvas.hide();
                    
                    // Auto-search and select
                    const inpCli = document.getElementById("cliente_buscar");
                    if (inpCli) {
                        inpCli.value = dni;
                        buscarClientesAjax();
                    }
                } else {
                    Swal.fire('Aviso', res.msg, res.icono);
                }
            } catch (err) {
                Swal.fire('Error', 'No se pudo procesar la respuesta', 'error');
            }
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const offcanvasCliente = document.getElementById('offcanvasCliente');
    const modalReservaDialog = document.querySelector('#modalReserva .modal-dialog');
    
    if (offcanvasCliente && modalReservaDialog) {
        offcanvasCliente.addEventListener('show.bs.offcanvas', function () {
            modalReservaDialog.classList.add('modal-shift-left');
            if (typeof SecuencialMantenimiento !== 'undefined') {
                SecuencialMantenimiento.bindCliente(false);
            }
        });
        
        offcanvasCliente.addEventListener('hide.bs.offcanvas', function () {
            modalReservaDialog.classList.remove('modal-shift-left');
        });
    }
});
