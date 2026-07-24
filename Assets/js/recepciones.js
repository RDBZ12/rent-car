let myModalRecepcion, modalInspeccion, modalPenalidad, myModalDetalleRecepcion;
let vehiculosTemp = [], reservasCache = [], reservasFiltradas = [];
let _clientesBusquedaCacheRec = [];
let seleccionGlobal = {
    reservas: new Set(),
    vehiculos: {} // { reserva_id: Set([vehiculo_id, ...]) }
};

document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById('nuevoRecepcion')) {
        myModalRecepcion = new bootstrap.Modal(document.getElementById('nuevoRecepcion'));
    }
    if (document.getElementById('modalInspeccion')) {
        modalInspeccion = new bootstrap.Modal(document.getElementById('modalInspeccion'));
    }
    if (document.getElementById('modalPenalidad')) {
        modalPenalidad = new bootstrap.Modal(document.getElementById('modalPenalidad'));
    }
    const modalDet = document.getElementById('modalDetalleRecepcion');
    if (modalDet) myModalDetalleRecepcion = new bootstrap.Modal(modalDet);
    
    // Inicializar DataTable (tblRecepciones ya declarada en funciones.js)
    // Se utiliza 'dom' y 'buttons' definidos globalmente en funciones.js para habilitar el reporte superior
    tblRecepciones = $("#tblRecepciones").DataTable({
        ajax: {
            url: base_url + "Recepciones/listar",
            dataSrc: "",
        },
        columns: [
            { 
               className: 'dt-control', 
               orderable: false, 
               data: null, 
               defaultContent: '',
               width: '30px'
            },
            { data: "reserva_id" },
            { data: "cliente" },
            { data: "f_recepcion" },
            { data: "cargos_f" },
            { data: "penalidad_badge" },
            { data: "estado_badge" },
            { data: "acciones" }
        ],
        language: DT_LANG_ES,
        dom: dom, 
        buttons: buttons,
        destroy: true,
        responsive: true,
        order: [[1, "desc"]]
    });

    // Control de expansión
    tblRecepciones.on('responsive-display', function (e, datatable, row, showHide, update) {
        if (showHide) {
             datatable.rows().every(function () {
                 if (this.index() !== row.index() && this.child.isShown()) {
                     this.child.hide();
                     $(this.node()).removeClass('parent');
                 }
             });
        }
    });

    const f_recep = document.getElementById("fecha_recepcion");
    if (f_recep) f_recep.addEventListener("change", calcularRetraso);
    
    const m_pen = document.getElementById("monto_penalidad");
    if (m_pen) {
        m_pen.readOnly = true;
        m_pen.addEventListener("input", recalcularSaldoFinal);
    }
    
    const gen_pen = document.getElementById("generar_penalidad");
    if (gen_pen) {
        gen_pen.addEventListener("change", function() {
            const monto = document.getElementById("monto_penalidad");
            if(this.checked) {
                if (monto) monto.readOnly = false;
                calcularMontoSugerido();
            } else {
                if (monto) {
                    monto.readOnly = true;
                    monto.value = 0;
                }
            }
            recalcularSaldoFinal();
        });
    }

    const inputAbo = document.getElementById("abono_recepcion");
    if (inputAbo) inputAbo.addEventListener("input", recalcularSaldoFinal);

    // Autocompletado de clientes
    let debounceTimeoutRec;
    const inpCliRec = document.getElementById("cliente_buscar_rec");
    if (inpCliRec) {
        inpCliRec.addEventListener("input", function () {
            if (this.value.trim() === "") {
                limpiarSeccionReservas();
                const box = document.getElementById("resultados_clientes_rec");
                if (box) { box.classList.add("d-none"); box.innerHTML = ""; }
            } else {
                clearTimeout(debounceTimeoutRec);
                debounceTimeoutRec = setTimeout(() => {
                    buscarClientesRecepcion();
                }, 500);
            }
        });
        inpCliRec.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                clearTimeout(debounceTimeoutRec);
                buscarClientesRecepcion();
            }
        });
    }
});

function buscarClientesRecepcion() {
    const q = document.getElementById("cliente_buscar_rec").value.trim();
    const box = document.getElementById("resultados_clientes_rec");
    if (q.length < 1) return;
    const fd = new FormData();
    fd.append("q", q);
    fetch(base_url + "Recepciones/buscarClientesConReservas", { method: "POST", body: fd })
        .then((r) => r.json())
        .then((rows) => {
            console.log("Clientes encontrados:", rows);
            if (!rows || rows.length === 0) {
                box.innerHTML = '<div class="p-2 small text-muted">Sin resultados.</div>';
                box.classList.remove("d-none");
                return;
            }
            _clientesBusquedaCacheRec = rows;
            let html = '<div class="list-group list-group-flush">';
            rows.forEach((row, idx) => {
                const nom = (row.nombre + " " + row.apellido).trim();
                const ced = row.cedula || "";
                html += `<button type="button" class="list-group-item list-group-item-action cli-row-rec" data-idx="${idx}">
                    <strong>${nom}</strong> <br><small class="text-muted">Cédula: ${ced}</small>
                </button>`;
            });
            html += "</div>";
            box.innerHTML = html;
            box.classList.remove("d-none");
            box.querySelectorAll(".cli-row-rec").forEach((btn) => {
                btn.addEventListener("click", function () {
                    const idx = parseInt(this.getAttribute("data-idx"), 10);
                    if (!isNaN(idx) && _clientesBusquedaCacheRec[idx]) {
                        const c = _clientesBusquedaCacheRec[idx];
                        document.getElementById("cliente_id_rec").value = c.cliente_id;
                        document.getElementById("cliente_buscar_rec").value = (c.nombre + " " + c.apellido).trim();
                        document.getElementById("cliente_reserva").value = (c.nombre + " " + c.apellido).trim();
                        box.classList.add("d-none");
                        const inputFiltro = document.getElementById("filtro_reservas");
                        if (inputFiltro) inputFiltro.value = "";
                        cargarReservasClienteRec(c.cliente_id);
                    }
                });
            });
        })
        .catch(() => {});
}

function filtrarReservas(val) {
    if (!reservasCache || reservasCache.length === 0) return;
    const busqueda = val.toLowerCase().trim();
    if (busqueda === "") {
        reservasFiltradas = [...reservasCache];
    } else {
        reservasFiltradas = reservasCache.filter(r => 
            r.reserva_id.toString().includes(busqueda) ||
            (r.vehiculos && r.vehiculos.some(v => 
                v.placa.toLowerCase().includes(busqueda) || 
                v.marca.toLowerCase().includes(busqueda) || 
                v.modelo.toLowerCase().includes(busqueda)
            ))
        );
    }
    renderizarTablaReservas();
}

function cargarReservasClienteRec(cliente_id) {
    const tbody = document.getElementById("tbl_reservas_cliente");
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';
    
    const fd = new FormData();
    fd.append("cliente_id", cliente_id);
    fetch(base_url + "Recepciones/buscarReservasPorCliente", { method: "POST", body: fd })
        .then(r => r.json())
        .then(reservas => {
            console.log("Reservas del cliente:", reservas);
            reservasCache = reservas;
            reservasFiltradas = [...reservas];
            renderizarTablaReservas();
        })
        .catch(() => {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error al cargar reservas.</td></tr>';
        });
}

function renderizarTablaReservas() {
    const tbody = document.getElementById("tbl_reservas_cliente");
    if (!reservasFiltradas || reservasFiltradas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No se encontraron reservas con ese criterio.</td></tr>';
        return;
    }
    
    let html = '';
    reservasFiltradas.forEach(r => {
        const tot = parseFloat(r.total_alquiler) || 0;
        const sal = parseFloat(r.saldo_pendiente) || 0;
        const isSelected = seleccionGlobal.reservas.has(r.reserva_id.toString());
        
        html += `<tr class="${isSelected ? 'table-info' : 'table-primary'} border-top border-2 border-primary">
            <td class="text-center">
                <input class="form-check-input check-reserva-master" type="checkbox" value="${r.reserva_id}" 
                    id="chk_res_${r.reserva_id}" ${isSelected ? 'checked' : ''} 
                    onchange="toggleSeleccionReserva(${r.reserva_id}, this.checked)">
            </td>
            <td><strong class="text-primary"><i class="fas fa-file-contract me-1"></i> Reserva N° ${r.reserva_id}</strong></td>
            <td class="small fw-bold">${r.fecha_inicio}</td>
            <td class="small fw-bold">${r.fecha_fin}</td>
            <td><span class="badge bg-success">${r.estado}</span></td>
            <td class="text-end fw-bold">$${tot.toFixed(2)}</td>
            <td class="text-end fw-bold text-danger">$${sal.toFixed(2)}</td>
        </tr>`;
        
        html += `<tr id="row_vehiculos_${r.reserva_id}">
            <td colspan="7" class="p-0 border-0">
            <div class="bg-white p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-muted text-uppercase">Vehículos de esta reserva:</span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" onclick="seleccionarTodosVehiculos(${r.reserva_id}, true)">Todos</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="seleccionarTodosVehiculos(${r.reserva_id}, false)">Ninguno</button>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2" id="cont_vehiculos_${r.reserva_id}">`;
        
        if (r.vehiculos && r.vehiculos.length > 0) {
            r.vehiculos.forEach(v => {
                const img = v.imagen ? (v.imagen.startsWith('http') ? v.imagen : (base_url + (v.imagen.startsWith('uploads') ? v.imagen : 'uploads/vehiculos/' + v.imagen))) : (base_url + 'uploads/vehiculos/default.png');
                const vehSelected = seleccionGlobal.vehiculos[r.reserva_id] && seleccionGlobal.vehiculos[r.reserva_id].has(v.vehiculo_id.toString());
                
                html += `<div class="d-flex align-items-center border rounded p-2 bg-light shadow-sm position-relative" style="min-width: 250px;">
                    <div class="form-check me-2">
                        <input class="form-check-input check-vehiculo-${r.reserva_id} check-item-vehiculo" 
                            type="checkbox" value="${v.vehiculo_id}" 
                            data-reserva="${r.reserva_id}"
                            id="chk_v_${r.reserva_id}_${v.vehiculo_id}" 
                            ${vehSelected ? 'checked' : ''}
                            onchange="actualizarSeleccionVehiculo(${r.reserva_id}, ${v.vehiculo_id}, this.checked)">
                    </div>
                    <label class="d-flex align-items-center mb-0" style="cursor: pointer;" for="chk_v_${r.reserva_id}_${v.vehiculo_id}">
                        <img src="${img}" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='${base_url}uploads/vehiculos/default.png'">
                        <div>
                            <h6 class="mb-0 fw-bold small">${v.marca} ${v.modelo}</h6>
                            <span class="badge bg-secondary">Placa: ${v.placa}</span>
                        </div>
                    </label>
                </div>`;
            });
        } else {
            html += `<span class="text-muted small">Sin vehículos disponibles en esta reserva.</span>`;
        }
        
        html += `</div>
            </div>
        </td></tr>`;
    });
    tbody.innerHTML = html;
}

function seleccionarTodasLasReservas(state) {
    reservasFiltradas.forEach(r => {
        toggleSeleccionReserva(r.reserva_id, state, false); // false para no re-renderizar en cada iteración
    });
    renderizarTablaReservas();
    actualizarVistaPreviaSeleccion();
}

function toggleSeleccionReserva(reserva_id, state, rerender = true) {
    reserva_id = reserva_id.toString();
    if (state) {
        seleccionGlobal.reservas.add(reserva_id);
        // Al seleccionar la reserva, seleccionamos todos sus vehículos por defecto
        seleccionarTodosVehiculos(reserva_id, true, false);
    } else {
        seleccionGlobal.reservas.delete(reserva_id);
        delete seleccionGlobal.vehiculos[reserva_id];
    }
    if (rerender) {
        renderizarTablaReservas();
        actualizarVistaPreviaSeleccion();
    }
}

function seleccionarTodosVehiculos(reserva_id, state, rerender = true) {
    reserva_id = reserva_id.toString();
    if (!seleccionGlobal.vehiculos[reserva_id]) {
        seleccionGlobal.vehiculos[reserva_id] = new Set();
    }
    
    const res = reservasCache.find(r => r.reserva_id == reserva_id);
    if (res && res.vehiculos) {
        res.vehiculos.forEach(v => {
            if (state) {
                seleccionGlobal.vehiculos[reserva_id].add(v.vehiculo_id.toString());
            } else {
                seleccionGlobal.vehiculos[reserva_id].delete(v.vehiculo_id.toString());
            }
        });
    }

    // Sincronizar con la reserva master
    if (state) {
        seleccionGlobal.reservas.add(reserva_id);
    } else {
        seleccionGlobal.reservas.delete(reserva_id);
    }

    if (rerender) {
        renderizarTablaReservas();
        actualizarVistaPreviaSeleccion();
    }
}

function actualizarSeleccionVehiculo(reserva_id, vehiculo_id, state) {
    reserva_id = reserva_id.toString();
    vehiculo_id = vehiculo_id.toString();
    
    if (!seleccionGlobal.vehiculos[reserva_id]) {
        seleccionGlobal.vehiculos[reserva_id] = new Set();
    }
    
    if (state) {
        seleccionGlobal.vehiculos[reserva_id].add(vehiculo_id);
        // Si selecciono un vehículo, la reserva debe estar marcada como seleccionada
        seleccionGlobal.reservas.add(reserva_id);
    } else {
        seleccionGlobal.vehiculos[reserva_id].delete(vehiculo_id);
        // Si ya no quedan vehículos seleccionados en esta reserva, la desmarcamos del master (opcional)
        if (seleccionGlobal.vehiculos[reserva_id].size === 0) {
            seleccionGlobal.reservas.delete(reserva_id);
        }
    }
    renderizarTablaReservas();
    actualizarVistaPreviaSeleccion();
}

function actualizarVistaPreviaSeleccion() {
    let cantRes = seleccionGlobal.reservas.size;
    let cantVeh = 0;
    let saldoAcum = 0;
    let subtotalAcum = 0;
    let pagadoAcum = 0;

    vehiculosTemp = []; // Reiniciamos para la inspección

    seleccionGlobal.reservas.forEach(resId => {
        const res = reservasCache.find(r => r.reserva_id == resId);
        if (res) {
            subtotalAcum += parseFloat(res.total_alquiler) || 0;
            pagadoAcum += parseFloat(res.total_pagos) || 0;
            saldoAcum += parseFloat(res.saldo_pendiente) || 0;
            
            const vIds = seleccionGlobal.vehiculos[resId] || new Set();
            if (vIds.size > 0 && res.vehiculos) {
                // Agregar a vehiculosTemp para inspección
                res.vehiculos.forEach(v => {
                    if (vIds.has(v.vehiculo_id.toString())) {
                        cantVeh++;
                        // Clonamos para evitar problemas de referencia y guardamos el ID de reserva origen
                        vehiculosTemp.push({...v, reserva_id_origen: resId});
                    }
                });
            }
        }
    });

    // Actualizar badges superiores
    document.getElementById("resumen_cant_res").textContent = cantRes;
    document.getElementById("resumen_cant_veh").textContent = cantVeh;
    document.getElementById("resumen_saldo_acum").textContent = '$' + saldoAcum.toFixed(2);

    // Actualizar resumen inferior
    document.getElementById("total_alquiler_res").textContent = '$' + subtotalAcum.toFixed(2);
    document.getElementById("total_pagado_res").textContent = '$' + pagadoAcum.toFixed(2);
    document.getElementById("total_alquiler_res").dataset.raw = subtotalAcum;
    document.getElementById("total_pagado_res").dataset.raw = pagadoAcum;

    // Habilitar botones de inspección y penalidad si hay selección
    const btnI = document.getElementById("btn_inspeccion");
    const btnP = document.getElementById("btn_penalidad");
    if (btnI) btnI.disabled = cantVeh === 0;
    if (btnP) btnP.disabled = cantRes === 0;

    // Actualizar tabla de inspección
    prepararTablaInspeccion();
    recalcularSaldoFinal();
}

function prepararTablaInspeccion() {
    let html = '';
    vehiculosTemp.forEach((v, i) => {
        html += `<tr>
            <td class="small fw-bold">
                <span class="badge bg-dark mb-1">R#${v.reserva_id_origen}</span><br>
                ${v.placa} <br><small class="text-muted fw-normal">${v.marca} ${v.modelo}</small>
            </td>
            <td><input type="number" class="form-control form-control-sm" id="fuel_${i}" value="0"></td>
            <td><input type="number" class="form-control form-control-sm" id="km_${i}" value="0"></td>
            <td><input type="text" class="form-control form-control-sm" id="danos_${i}"></td>
            <td><input type="number" class="form-control form-control-sm x-cargo" id="extra_${i}" value="0" step="0.01" oninput="recalcularSaldoFinal()"></td>
        </tr>`;
    });
    document.getElementById("tblVehiculosRecepcion").innerHTML = html;
}

function calcularRetraso() {
    const f_recep = document.getElementById("fecha_recepcion").value;
    const f_fin = document.getElementById("fecha_fin_res").value;
    const dias_in = document.getElementById("dias_retraso");
    if (f_recep && f_fin && f_fin !== '') {
        const d1 = new Date(f_fin);
        const d2 = new Date(f_recep);
        d1.setHours(24,0,0,0);
        d2.setHours(24,0,0,0);
        const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
        if (dias_in) dias_in.value = diff > 0 ? diff : 0;
        if (document.getElementById("generar_penalidad").checked) calcularMontoSugerido();
    }
}

function calcularMontoSugerido() {
    const d = parseInt(document.getElementById("dias_retraso").value) || 0;
    const m = document.getElementById("monto_penalidad");
    if (d > 0 && vehiculosTemp.length > 0) {
        let p = 0;
        vehiculosTemp.forEach(v => p += parseFloat(v.precio_unitario) || 0);
        if (m) m.value = (d * p).toFixed(2);
        document.getElementById("motivo_penalidad").value = "Días adicionales: " + d;
    } else if (m) {
        m.value = (0).toFixed(2);
    }
    recalcularSaldoFinal();
}

function frmRecepcion() {
    document.getElementById("formularioRecepcion").reset();
    limpiarSeccionReservas();
    document.getElementById("cliente_buscar_rec").value = '';

    if(document.getElementById("fecha_recepcion")) {
        const tzoffset = (new Date()).getTimezoneOffset() * 60000;
        document.getElementById("fecha_recepcion").value = (new Date(Date.now() - tzoffset)).toISOString().split("T")[0];
    }
    
    if (myModalRecepcion) myModalRecepcion.show();
}

function limpiarSeccionReservas() {
    document.getElementById("tblVehiculosRecepcion").innerHTML = '';
    document.getElementById("reserva_id").value = '';
    document.getElementById("cliente_id_rec").value = '';
    document.getElementById("cliente_reserva").value = '';
    document.getElementById("fecha_inicio_res").value = '';
    document.getElementById("fecha_fin_res").value = '';
    document.getElementById("tbl_reservas_cliente").innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Busque un cliente para ver sus reservas.</td></tr>';
    
    // Limpiar selección global
    seleccionGlobal = { reservas: new Set(), vehiculos: {} };
    document.getElementById("check_all_reservas").checked = false;
    actualizarVistaPreviaSeleccion();

    document.getElementById("btn_inspeccion").disabled = true;
    document.getElementById("btn_penalidad").disabled = true;
    // ... rest of the original logic ...
    
    document.getElementById("total_alquiler_res").textContent = '$0.00';
    document.getElementById("total_pagado_res").textContent = '$0.00';
    document.getElementById("total_general_res").textContent = '$0.00';
    document.getElementById("saldo_final_res").textContent = '$0.00';
    document.getElementById("total_alquiler_res").dataset.raw = 0;
    document.getElementById("total_pagado_res").dataset.raw = 0;
    
    const abono = document.getElementById("abono_recepcion");
    if (abono) abono.value = '';
    
    const inputFiltro = document.getElementById("filtro_reservas");
    if (inputFiltro) inputFiltro.value = "";
    
    const dias = document.getElementById("dias_retraso");
    if (dias) dias.value = '0';
    
    const gen_pen = document.getElementById("generar_penalidad");
    if (gen_pen) gen_pen.checked = false;
    
    const monto_pen = document.getElementById("monto_penalidad");
    if (monto_pen) {
        monto_pen.value = '0';
        monto_pen.readOnly = true;
    }
    
    vehiculosTemp = [];
}

function abrirModalInspeccion() { modalInspeccion.show(); }


function abrirModalPenalidad() {
    const container = document.getElementById("lista_penalidades_multi");
    if (seleccionGlobal.reservas.size === 0) {
        container.innerHTML = '<div class="text-center text-muted">No hay reservas seleccionadas.</div>';
        modalPenalidad.show();
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-sm align-middle">';
    html += '<thead class="small text-muted"><tr><th>Reserva</th><th>F. Fin</th><th>Días Retraso</th><th>Aplicar?</th><th>Monto ($)</th><th>Motivo</th></tr></thead><tbody>';

    const fecha_recep = document.getElementById("fecha_recepcion").value;

    seleccionGlobal.reservas.forEach(resId => {
        const res = reservasCache.find(r => r.reserva_id == resId);
        if (res) {
            // Calcular retraso
            let dias = 0;
            if (fecha_recep && res.fecha_fin) {
                const d1 = new Date(res.fecha_fin);
                const d2 = new Date(fecha_recep);
                d1.setHours(24,0,0,0);
                d2.setHours(24,0,0,0);
                const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
                dias = diff > 0 ? diff : 0;
            }

            // Monto sugerido (suma de precios diarios de vehículos seleccionados * días)
            let sugerido = 0;
            if (dias > 0 && seleccionGlobal.vehiculos[resId]) {
                res.vehiculos.forEach(v => {
                    if (seleccionGlobal.vehiculos[resId].has(v.vehiculo_id.toString())) {
                        sugerido += (parseFloat(v.precio_unitario) || 0) * dias;
                    }
                });
            }

            html += `<tr>
                <td><span class="fw-bold">#${resId}</span></td>
                <td class="small">${res.fecha_fin}</td>
                <td><input type="number" class="form-control form-control-sm text-center fw-bold text-danger border-0 bg-light" id="dias_p_${resId}" value="${dias}" readonly style="width: 60px;"></td>
                <td class="text-center">
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input check-gen-pen" type="checkbox" id="gen_p_${resId}" data-reserva="${resId}" onchange="togglePenalidad(${resId}, this.checked)">
                    </div>
                </td>
                <td><input type="number" class="form-control form-control-sm x-monto-pen" id="monto_p_${resId}" value="${sugerido.toFixed(2)}" step="0.01" readonly oninput="recalcularSaldoFinal()" style="width: 90px;"></td>
                <td><input type="text" class="form-control form-control-sm" id="motivo_p_${resId}" value="${dias > 0 ? 'Días adicionales: ' + dias : ''}" placeholder="Motivo..."></td>
            </tr>`;
        }
    });

    html += '</tbody></table></div>';
    container.innerHTML = html;
    modalPenalidad.show();
}

function togglePenalidad(resId, state) {
    const input = document.getElementById("monto_p_" + resId);
    if (input) {
        input.readOnly = !state;
        if (!state) input.value = "0.00";
    }
    recalcularSaldoFinal();
}

function recalcularSaldoFinal() {
    const t_alq = parseFloat(document.getElementById("total_alquiler_res").dataset.raw) || 0;
    const t_pag = parseFloat(document.getElementById("total_pagado_res").dataset.raw) || 0;
    
    // Sumar todas las penalidades activas
    let m_pen = 0;
    document.querySelectorAll(".check-gen-pen").forEach(chk => {
        if (chk.checked) {
            const resId = chk.dataset.reserva;
            m_pen += parseFloat(document.getElementById("monto_p_" + resId).value) || 0;
        }
    });

    let t_ext = 0;
    document.querySelectorAll(".x-cargo").forEach(i => t_ext += parseFloat(i.value) || 0);
    
    const total = t_alq + m_pen + t_ext;
    const max = total - t_pag;
    let abo = parseFloat(document.getElementById("abono_recepcion").value) || 0;
    if (abo > max && max > 0) {
        abo = max;
        document.getElementById("abono_recepcion").value = max.toFixed(2);
    }
    
    document.getElementById("total_general_res").textContent = '$' + total.toFixed(2);
    document.getElementById("saldo_final_res").textContent = '$' + (total - t_pag - abo).toFixed(2);
}

function registrarRecepcion(e) {
    if (e) e.preventDefault();
    
    if (seleccionGlobal.reservas.size === 0) {
        Swal.fire("Error", "No hay ninguna reserva seleccionada para devolver", "error");
        return;
    }

    const btn = document.getElementById("btnAccionRec");
    btn.disabled = true;

    // Construir estructura masiva
    const payload = [];
    seleccionGlobal.reservas.forEach(resId => {
        const vIds = seleccionGlobal.vehiculos[resId];
        if (!vIds || vIds.size === 0) return;

        const detallesVehiculos = [];
        vehiculosTemp.forEach((vt, index) => {
            if (vt.reserva_id_origen == resId) {
                const elFuel = document.getElementById("fuel_" + index);
                const elKm = document.getElementById("km_" + index);
                const elDanos = document.getElementById("danos_" + index);
                const elExtra = document.getElementById("extra_" + index);

                detallesVehiculos.push({
                    vehiculo_id: vt.vehiculo_id,
                    combustible: elFuel ? elFuel.value : 0,
                    km: elKm ? elKm.value : (vt.kilometraje || 0),
                    danos: elDanos ? elDanos.value : "Sin daños reportados",
                    cargo_extra: elExtra ? elExtra.value : 0
                });
            }
        });

        // Penalidad para esta reserva
        const chkPen = document.getElementById("gen_p_" + resId);
        const penalidad = {
            monto: (chkPen && chkPen.checked) ? (document.getElementById("monto_p_" + resId).value || 0) : 0,
            motivo: (chkPen && chkPen.checked) ? (document.getElementById("motivo_p_" + resId).value || "") : "",
            dias: (chkPen && chkPen.checked) ? (document.getElementById("dias_p_" + resId).value || 0) : 0
        };

        payload.push({
            reserva_id: resId,
            vehiculos: detallesVehiculos,
            penalidad: penalidad
        });
    });

    const f = new FormData(document.getElementById("formularioRecepcion"));
    f.append("batch_data", JSON.stringify(payload));
    f.append("cliente_id", document.getElementById("cliente_id_rec").value);

    fetch(base_url + "Recepciones/registrarBatch", {
        method: "POST",
        body: f
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch (err) {
            console.error("Error parseando JSON:", text);
            throw new Error("Respuesta inválida del servidor");
        }
    })
    .then(res => {
        Swal.fire({ icon: res.icono, title: res.msg });
        if (res.icono == 'success') {
            myModalRecepcion.hide();
            tblRecepciones.ajax.reload();
        }
        btn.disabled = false;
    })
    .catch((error) => {
        console.error("Error en registrarRecepcion:", error);
        Swal.fire("Error", "Ocurrió un error en el servidor o la respuesta fue inválida", "error");
        btn.disabled = false;
    });
}

function verDetalleRecepcion(id) {
    if (!id) return;
    Swal.fire({ title: 'Cargando...', didOpen: () => { Swal.showLoading() }, allowOutsideClick: false });
    const url = base_url + "Recepciones/getDetalle/" + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            try {
                const res = JSON.parse(this.responseText);
                Swal.close();
                if (res.error) {
                    Swal.fire('Error', res.error, 'error');
                    return;
                }
                const h = res.header;
                document.getElementById('det_cli_rec').textContent = h.nombre + ' ' + h.apellido;
                document.getElementById('det_info_rec').textContent = 'Cédula: ' + (h.cedula || '---') + ' | Tel: ' + (h.telefono || '---');
                document.getElementById('det_fecha_rec').textContent = h.fecha_recepcion;
                
                let htmlVeh = '';
                let totalCargos = 0;
                res.detalles.forEach(v => { 
                    totalCargos += parseFloat(v.cargo_extra) || 0;
                    htmlVeh += `<tr>
                        <td class="small"><strong>${v.marca} ${v.modelo}</strong><br><small class="text-muted">${v.placa}</small></td>
                        <td class="text-center small">${v.combustible} Gal</td>
                        <td class="text-center small">${v.kilometraje} KM</td>
                        <td class="small">${v.danos || 'Sin daños'}</td>
                        <td class="text-end fw-bold small text-dark">$${parseFloat(v.cargo_extra).toFixed(2)}</td>
                    </tr>`; 
                });
                document.getElementById('det_lista_veh_rec').innerHTML = htmlVeh;
                
                document.getElementById('det_motivo_pen').innerHTML = `<div class="mb-1"><strong>Motivo Penalidad:</strong> ${h.motivo_penalidad}</div><div><strong>Observaciones:</strong> ${h.observaciones || 'Ninguna'}</div>`;
                
                const sub = parseFloat(h.total_alquiler) || 0;
                const pen = parseFloat(h.monto_penalidad) || 0;
                const total = sub + pen + totalCargos;

                document.getElementById('det_sub_rec').textContent = '$' + sub.toFixed(2);
                document.getElementById('det_pen_rec').textContent = '$' + pen.toFixed(2);
                document.getElementById('det_ext_rec').textContent = '$' + totalCargos.toFixed(2);
                document.getElementById('det_total_rec').textContent = '$' + total.toFixed(2);
                
                if (myModalDetalleRecepcion) myModalDetalleRecepcion.show();
            } catch (e) { 
                console.error(e); 
                Swal.fire('Error', 'No se pudo procesar la respuesta', 'error'); 
            }
        }
    };
}
