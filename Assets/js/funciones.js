let tblUsuarios, tblClientes, tblMarcas, tblModelos, tblGamas, tblReservas,
    tblVehiculos, t_moneda, myModal, m_precio, tbl, tblDoc, m_entrega, tblPagos, tblRecepciones, tblTipos, tblPrecios, tblFeriados, tblTiposDia, buttons, dom;

/** Mantiene la columna Acciones en la fila principal (evita el desplegable responsive). */
const DT_PRIORIDAD_ACCIONES = [{ responsivePriority: 1, targets: -1 }];

/** Idioma español para DataTables (inline, sin petición HTTP) */
const DT_LANG_ES = {
    sProcessing: "Procesando...",
    sLengthMenu: "Mostrar _MENU_ registros",
    sZeroRecords: "No se encontraron resultados",
    sEmptyTable: "Ningún dato disponible en esta tabla",
    sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
    sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
    sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
    sInfoPostFix: "",
    sSearch: "Buscar:",
    sUrl: "",
    sInfoThousands: ",",
    sLoadingRecords: "Cargando...",
    oPaginate: { sFirst: "Primero", sLast: "Último", sNext: "Siguiente", sPrevious: "Anterior" },
    oAria: { sSortAscending: ": Activar para ordenar la columna de manera ascendente", sSortDescending: ": Activar para ordenar la columna de manera descendente" }
};

// Debounce para evitar múltiples llamadas simultáneas
let debounceTimer = null;
let isSearching = false;
document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById('entrega')) {
        m_entrega = new bootstrap.Modal(document.getElementById('entrega'));
    }
    //fin validaciones
    let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    let tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
    if (document.getElementById('myModal')) {
        myModal = new bootstrap.Modal(document.getElementById('myModal'));
    }
    if (document.getElementById('modalPrecio')) {
        m_precio = new bootstrap.Modal(document.getElementById('modalPrecio'));
    }
    // Toggle estado label dinámico
    const estadoCheck = document.getElementById('estado');
    if (estadoCheck) {
        estadoCheck.addEventListener('change', actualizarToggleLabel);
    }
    const selectAnioVeh = document.getElementById('anio');
    if (selectAnioVeh) {
        selectAnioVeh.addEventListener('change', actualizarPreviewImagen);
    }
    if (document.getElementById('marca_text')) {
        initVehiculoMarcaModeloCombobox();
    }
    // Lógica en vivo para Placa
    const placaInput = document.getElementById('placa');
    if (placaInput) {
        let placaDebounce = null;
        const msgPlaca = document.getElementById('msg-placa');
        
        placaInput.addEventListener('input', function(e) {
            let val = e.target.value.toUpperCase().replace(/\s+/g, '').replace(/[^A-Z0-9\-]/g, '');
            e.target.value = val;
            
            clearTimeout(placaDebounce);
            e.target.dataset.placaValida = '1'; 
            if (msgPlaca) msgPlaca.textContent = '';
            
            if (val.length >= 3 && val.length <= 15) {
                placaDebounce = setTimeout(function() {
                    const id = document.getElementById('id') ? document.getElementById('id').value : 0;
                    const url = base_url + 'Vehiculos/validarPlacaUnica';
                    if (msgPlaca) {
                        msgPlaca.textContent = 'Verificando...';
                        msgPlaca.style.display = 'block';
                        msgPlaca.classList.remove('invalid-feedback');
                        msgPlaca.style.color = '#0d6efd'; // Bootstrap primary color
                    }
                    
                    const formData = new FormData();
                    formData.append('placa', val);
                    formData.append('id', id);
                    
                    const http = new XMLHttpRequest();
                    http.open('POST', url, true);
                    http.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            try {
                                const res = JSON.parse(this.responseText);
                                if (!res.disponible) {
                                    e.target.dataset.placaValida = '0';
                                    e.target.classList.add('is-invalid');
                                    e.target.classList.remove('is-valid');
                                    if (msgPlaca) {
                                        msgPlaca.textContent = res.msg || 'Esta placa ya existe';
                                        msgPlaca.classList.add('invalid-feedback');
                                        msgPlaca.style.color = ''; 
                                        msgPlaca.style.display = 'block';
                                    }
                                } else {
                                    e.target.dataset.placaValida = '1';
                                    e.target.classList.remove('is-invalid');
                                    e.target.classList.add('is-valid');
                                    if (msgPlaca) {
                                        msgPlaca.textContent = '';
                                        msgPlaca.style.display = 'none';
                                    }
                                }
                                if (typeof SecuencialMantenimiento !== 'undefined') {
                                    SecuencialMantenimiento.refresh('vehiculos');
                                }
                            } catch(err) {}
                        }
                    };
                    http.send(formData);
                }, 500);
            }
        });
    }
    //Fin autocomple
    buttons = [
        {
            extend: 'collection',
            text: '<i class="fas fa-file-export me-1"></i> Reportes',
            className: 'btn btn-outline-secondary btn-sm fw-bold border-2 shadow-sm',
            buttons: [
                {
                    extend: 'excelHtml5',
                    footer: true,
                    title: 'Reporte',
                    filename: 'Reporte',
                    text: '<i class="fas fa-file-excel me-1 text-success"></i> Excel'
                },
                {
                    extend: 'pdfHtml5',
                    download: 'open',
                    footer: true,
                    title: 'Reporte',
                    filename: 'Reporte',
                    text: '<i class="fas fa-file-pdf me-1 text-danger"></i> PDF',
                    exportOptions: {
                        columns: [0, ':visible']
                    }
                },
                {
                    extend: 'copyHtml5',
                    footer: true,
                    title: 'Reporte',
                    filename: 'Reporte',
                    text: '<i class="fas fa-copy me-1 text-primary"></i> Copiar',
                    exportOptions: {
                        columns: [0, ':visible']
                    }
                },
                {
                    extend: 'print',
                    footer: true,
                    filename: 'Reporte',
                    text: '<i class="fas fa-print me-1 text-dark"></i> Imprimir'
                },
                {
                    extend: 'csvHtml5',
                    footer: true,
                    filename: 'Reporte',
                    text: '<i class="fas fa-file-csv me-1 text-info"></i> CSV'
                },
                {
                    extend: 'colvis',
                    text: '<i class="fas fa-columns me-1 text-secondary"></i> Columnas',
                    postfixButtons: ['colvisRestore']
                }
            ]
        }
    ];
    dom = "<'row mb-3 align-items-center'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-center'B><'col-sm-12 col-md-4 text-end'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row mt-3 align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>";
    
    tblUsuarios = $('#tblUsuarios').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + 'Usuarios/listar',
            dataSrc: ''
        },
        columns: [
            { 'data': 'usuario' },
            { 'data': 'nombre' },
            { 'data': 'rol' },
            { 'data': 'estado' },
            { "data": "editar" }
        ],
        columnDefs: DT_PRIORIDAD_ACCIONES,
        language: DT_LANG_ES,
        dom,
        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ]
    });//Fin de la tabla usuarios
    t_moneda = $('#t_moneda').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: "" + base_url + "Administracion/listarMonedas",
            dataSrc: ""
        },
        columns: [
            {
                "data": "simbolo"
            },
            {
                "data": "nombre"
            },
            {
                "data": "estado"
            },
            {
                "data": "editar"
            }
        ],
        columnDefs: DT_PRIORIDAD_ACCIONES,
        language: DT_LANG_ES,
        dom,
        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ]
    });
    tblClientes = $('#tblClientes').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + "Clientes/listar",
            dataSrc: ''
        },
        columns: [
            { 'data': 'dni' },
            { 'data': 'nombre' },
            { 'data': 'apellido' },
            { 'data': 'telefono' },
            { 'data': 'email' },
            { 'data': 'direccion' },
            { 'data': 'estado' },
            { 'data': 'editar' }
        ],
        columnDefs: DT_PRIORIDAD_ACCIONES,
        language: DT_LANG_ES,
        dom,
        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ]
    });//Fin de la tabla clientes
    tblMarcas = $('#tblMarcas').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + 'Marcas/listar',
            dataSrc: ''
        },
        columns: [
            { 'data': 'marca' },
            { 'data': 'estado' },
            { 'data': 'editar' }
        ],
        columnDefs: DT_PRIORIDAD_ACCIONES,
        language: DT_LANG_ES,
        dom,
        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ]
    });//Fin de la tabla marcas
    tblModelos = $('#tblModelos').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + 'Modelos/listar',
            dataSrc: ''
        },
        columns: [
            { 'data': 'marca' },
            { 'data': 'nombre' },
            { 'data': 'estado' },
            { 'data': 'editar' }
        ],
        columnDefs: DT_PRIORIDAD_ACCIONES,
        language: DT_LANG_ES,
        dom,
        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ]
    });//Fin de la tabla modelos
    tblGamas = $('#tblGamas').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + 'Gamas/listar',
            dataSrc: ''
        },
        columns: [
            { 'data': 'tipo' },
            { 'data': 'descripcion' },
            { 'data': 'estado' },
            { 'data': 'editar' }
        ],
        columnDefs: DT_PRIORIDAD_ACCIONES,
        language: DT_LANG_ES,
        dom,
        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ]
    });//Fin de la tabla Gamas
    tblTipos = $('#tblTipos').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + 'Tipos/listar',
            dataSrc: ''
        },
        columns: [
            { 'data': 'tipo' },
            { 'data': 'estado' },
            { 'data': 'editar' }
        ],
        columnDefs: DT_PRIORIDAD_ACCIONES,
        language: DT_LANG_ES,
        dom,
        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ]
    });//Fin de la tabla Tipos
    tblVehiculos = $('#tblVehiculos').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        pageLength: 25,
        ajax: {
            url: base_url + 'Vehiculos/listar',
            dataSrc: ''
        },
        columns: [
            { 'data': 'imagen' },
            { 'data': 'placa' },
            { 'data': 'marca' },
            { 'data': 'modelo' },
            { 'data': 'tipo' },
            { 'data': 'anio' },
            { 'data': 'estado' },
            { 'data': 'editar' }
        ],
        columnDefs: DT_PRIORIDAD_ACCIONES,
        language: DT_LANG_ES,
        dom,

        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ]
    });//Fin de vehiculos
    tblDoc = $('#tblDoc').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + 'Documentos/listar',
            dataSrc: ''
        },
        columns: [
            {
                'data': 'documento'
            },
            {
                'data': 'estado'
            },
            {
                'data': 'editar'
            }
        ],
        columnDefs: DT_PRIORIDAD_ACCIONES,
        language: DT_LANG_ES,
        dom,
        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ]
    }); //Fin de la tabla documentos
    tbl = $('#tbl').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        language: DT_LANG_ES,
        dom,
        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ]
    }); //Fin de la tabla usuarios
    /* tblReservas = $('#tblReservas').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + 'Reservas/listar',
            dataSrc: ''
        },
        columns: [
            {'data': 'nombre'},
            {'data': 'marca'},
            {'data': 'placa'},
            {'data': 'modelo'},
            {'data': 'f_prestamo'},
            {'data': 'f_devolucion'},
            {'data': 'num_dias'},
            {'data': 'precio_dia'},
            {'data': 'abono'},
            {'data': 'estatus'},
            {'data': 'recibir'},
            {'data': 'accion'},
        ],
        language: DT_LANG_ES,
        dom,
        buttons,
        "createdRow": function (row, data, index) {
            //pintar una celda
            if (data.estado == 1) {
                $('td', row).eq(9).html('<span class="badge bg-dark">Prestado</span>');
                $('td', row).css({
                'background-color': '#FEA4AE'
                });
            } else {
                $('td', row).eq(9).html('<span class="badge bg-success">Devuelto</span>');
            }
        },
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ]
    }); */ //Fin de la tabla reservas
    /* tblPagos moved to pagos.js */
    $("#select_cliente").autocomplete({
        minLength: 2,
        source: function (request, response) {
            $.ajax({
                url: base_url + 'Clientes/buscarCliente/',
                dataType: "json",
                data: {
                    cli: request.term
                },
                success: function (data) {
                    response(data);
                }
            });
        },
        select: function (event, ui) {
            document.getElementById('id_cli').value = ui.item.id;
            document.getElementById('select_cliente').value = ui.item.nombre;
        }
    })
    $("#select_vehiculo").autocomplete({
        minLength: 2,
        source: function (request, response) {
            $.ajax({
                url: base_url + 'Vehiculos/buscarVehiculo/',
                dataType: "json",
                data: {
                    veh: request.term
                },
                success: function (data) {
                    response(data);
                }
            });
        },
        select: function (event, ui) {
            document.getElementById('id_veh').value = ui.item.id;
            document.getElementById('select_vehiculo').value = ui.item.value;
        }
    });

    if (document.getElementById('tblPrecios')) {
        tblPrecios = $('#tblPrecios').DataTable({
            responsive: true,
            processing: true,
            serverSide: false,
            ajax: {
                url: base_url + 'Precios/listar',
                dataSrc: ''
            },
            columns: [
                { 'data': 'vehiculo' },
                { 'data': 'precio' },
                { 'data': 'estado_badge' },
                { 'data': 'acciones' }
            ],
            columnDefs: DT_PRIORIDAD_ACCIONES,
            language: DT_LANG_ES,
            dom,
            buttons,
            resonsieve: true,
            bDestroy: true,
            iDisplayLength: 10,
            order: [[0, "asc"]]
        });
    }

    if (document.getElementById('tblFeriados')) {
        tblFeriados = $('#tblFeriados').DataTable({
            responsive: true,
            processing: true,
            serverSide: false,
            ajax: {
                url: base_url + 'Feriados/listar',
                dataSrc: ''
            },
            columns: [
                { 'data': 'fecha' },
                { 'data': 'descripcion' },
                { 'data': 'acciones' }
            ],
            columnDefs: DT_PRIORIDAD_ACCIONES,
            language: DT_LANG_ES,
            dom,
            buttons,
            resonsieve: true,
            bDestroy: true,
            iDisplayLength: 10,
            order: [[0, "desc"]]
        });
    }

    if (document.getElementById('tblTiposDia')) {
        tblTiposDia = $('#tblTiposDia').DataTable({
            responsive: true,
            processing: true,
            serverSide: false,
            ajax: {
                url: base_url + 'TiposDia/listar',
                dataSrc: ''
            },
            columns: [
                { 'data': 'id' },
                { 'data': 'nombre' },
                { 'data': 'estado' },
                { 'data': 'acciones' }
            ],
            columnDefs: DT_PRIORIDAD_ACCIONES,
            language: DT_LANG_ES,
            dom,
            buttons,
            bDestroy: true,
            iDisplayLength: 10,
            order: [[0, "desc"]]
        });
    }
});
function actualizarToggleLabel() {
    const chk = document.getElementById('estado');
    const txt = document.querySelector('.estado-text');
    if (chk && txt) {
        if (chk.checked) {
            txt.textContent = 'Activo';
            txt.classList.remove('inactivo');
            txt.classList.add('activo');
        } else {
            txt.textContent = 'Inactivo';
            txt.classList.remove('activo');
            txt.classList.add('inactivo');
        }
    }
}
function frmCambiarPass(e) {
    e.preventDefault();
    const actual = document.getElementById('clave_actual').value;
    const nueva = document.getElementById('clave_nueva').value;
    const confirmar = document.getElementById('confirmar_clave').value;
    if (actual == '' || nueva == '' || confirmar == '') {
        alertas('Todo los campos son obligatorios', 'warning');
        return false;
    } else {
        if (nueva != confirmar) {
            alertas('Las contraseñas no coinciden', 'warning');
            return false;
        } else {
            const url = base_url + "Usuarios/cambiarPass";
            const frm = document.getElementById("frmCambiarPass");
            const http = new XMLHttpRequest();
            http.open("POST", url, true);
            http.send(new FormData(frm));
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {

                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    myModal.hide();
                    frm.reset();
                }
            }
        }
    }
}
function frmUsuario() {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    const claves = document.getElementById("claves");
    const formulario = document.getElementById("formulario");
    const id = document.getElementById("id");
    const clave_label = document.getElementById("clave_label");
    const confirmar_label = document.getElementById("confirmar_label");

    if (title) title.textContent = "Nuevo Usuario";
    if (btnAccion) btnAccion.textContent = "Registrar";
    if (claves) claves.classList.remove("d-none");
    if (formulario) formulario.reset();
    if (id) id.value = "";
    if (clave_label) clave_label.innerHTML = '<i class="fas fa-key"></i> Contraseña <span class="text-danger fw-bold">*</span>';
    if (confirmar_label) confirmar_label.innerHTML = '<i class="fas fa-lock"></i> Confirmar Contraseña <span class="text-danger fw-bold">*</span>';

    if (myModal) myModal.show();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindUsuario(false);
}
function registrarUser(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsUsuario);
    }
    const usuario = document.getElementById("usuario").value;
    const nombre = document.getElementById("nombre").value;
    const apellido = document.getElementById("apellido").value;
    const rol = document.getElementById("rol") ? document.getElementById("rol").value : '';
    if (usuario == "" || nombre == "" || apellido == "" || rol === "") {
        alertas('Todos los campos son obligatorios', 'warning');
        return false;
    }
    if (typeof validarUsuarioSistema === 'function' && !validarUsuarioSistema(usuario)) {
        alertas('El usuario debe tener 3-50 caracteres (letras, números, . _ -).', 'warning');
        return false;
    }
    if (typeof validarNombrePersona === 'function' && (!validarNombrePersona(nombre) || !validarNombrePersona(apellido))) {
        alertas('Nombre y apellido solo deben contener letras y espacios.', 'warning');
        return false;
    }
    const id = document.getElementById("id").value;
    const clave = document.getElementById("clave").value;
    const confirmar = document.getElementById("confirmar").value;

    if (id == "" && (clave == "" || confirmar == "")) {
        alertas('La contraseña es obligatoria para nuevos usuarios', 'warning');
        return false;
    }

    if (clave != confirmar) {
        alertas('Las contraseñas no coinciden', 'warning');
        return false;
    }

    const url = base_url + 'Usuarios/registrar';
    const frm = document.getElementById("formulario");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            myModal.hide();
            tblUsuarios.ajax.reload();
            alertas(res.msg, res.icono);
        }
    }
}
function btnEditarUser(id) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = "Actualizar usuario";
    if (btnAccion) btnAccion.textContent = "Modificar";
    const url = base_url + 'Usuarios/editar/' + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById("id");
            const usuario = document.getElementById("usuario");
            const nombre = document.getElementById("nombre");
            const rol = document.getElementById("rol");
            const apellido = document.getElementById("apellido");
            const claves = document.getElementById("claves");
            const clave_label = document.getElementById("clave_label");
            const confirmar_label = document.getElementById("confirmar_label");

            if (id_input) id_input.value = res.id;
            if (usuario) usuario.value = res.usuario;
            if (nombre) nombre.value = res.nombre;
            if (rol) rol.value = res.rol;
            if (apellido) apellido.value = res.apellido;
            if (claves) claves.classList.remove("d-none");
            if (clave_label) clave_label.innerHTML = '<i class="fas fa-key"></i> Nueva Contraseña (Opcional)';
            if (confirmar_label) confirmar_label.innerHTML = '<i class="fas fa-lock"></i> Confirmar Nueva Contraseña';

            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindUsuario(true);
        }
    }
}
function btnEliminarUser(id) {
    Swal.fire({
        title: 'Esta seguro de eliminar?',
        text: "El usuario no se eliminará de forma permanente, solo cambiará el estado a inactivo!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "Usuarios/eliminar/" + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblUsuarios.ajax.reload();
                }
            }

        }
    })
}
function btnReingresarUser(id) {
    Swal.fire({
        title: 'Esta seguro de reingresar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "Usuarios/reingresar/" + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    if (typeof tblUsuarios !== 'undefined') tblUsuarios.ajax.reload();
                }
            }
        }
    })
}

//Fin Usuarios
function frmCliente() {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    const formulario = document.getElementById("formulario");
    const id = document.getElementById("id");
    const estado = document.getElementById("estado");

    if (title) title.textContent = "Nuevo Cliente";
    if (btnAccion) btnAccion.textContent = "Registrar";
    if (formulario) formulario.reset();
    if (id) id.value = "";
    if (estado) estado.checked = true;
    actualizarToggleLabel();
    if (myModal) myModal.show();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindCliente(false);
}
function registrarCli(e) {
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
        alertas(errCli, 'warning');
        return false;
    }
    const url = base_url + 'Clientes/registrar';
    const frm = document.getElementById("formulario");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            alertas(res.msg, res.icono);
            frm.reset();
            myModal.hide();
            tblClientes.ajax.reload();
        }
    }
}
function btnEditarCli(id) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = "Actualizar cliente";
    if (btnAccion) btnAccion.textContent = "Modificar";
    const url = base_url + "Clientes/editar/" + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById("id");
            const dni = document.getElementById("dni");
            const nombre = document.getElementById("nombre");
            const apellido = document.getElementById("apellido");
            const telefono = document.getElementById("telefono");
            const email = document.getElementById("email");
            const direccion = document.getElementById("direccion");
            const estado = document.getElementById("estado");

            if (id_input) id_input.value = res.id;
            if (dni) dni.value = res.dni;
            if (nombre) nombre.value = res.nombre;
            if (apellido) apellido.value = res.apellido;
            if (telefono) telefono.value = res.telefono;
            if (email) email.value = res.email;
            if (direccion) direccion.value = res.direccion;
            if (estado) estado.checked = (res.estado == 'Activo');
            actualizarToggleLabel();
            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindCliente(true);
        }
    }
}
function btnEliminarCli(id) {
    Swal.fire({
        title: 'Esta seguro de eliminar?',
        text: "El cliente no se eliminará de forma permanente, solo cambiará el estado a inactivo!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "Clientes/eliminar/" + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    tblClientes.ajax.reload();
                    alertas(res.msg, res.icono);
                }
            }

        }
    })
}
function btnReingresarCli(id) {
    Swal.fire({
        title: 'Esta seguro de reingresar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "Clientes/reingresar/" + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblClientes.ajax.reload();
                }
            }
        }
    })
}//Fin Clientes

function frmMarca() {
    document.getElementById("title").textContent = "Nueva Marca";
    document.getElementById("btnAccion").textContent = "Registrar";
    document.getElementById("formulario").reset();
    document.getElementById("id").value = "";
    document.getElementById("estado").checked = true;
    actualizarToggleLabel();
    myModal.show();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindMarca(false);
}
function registrarMarca(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsMarca);
    }
    const nombre = document.getElementById("nombre").value;
    if (nombre == '') {
        alertas('El nombre es requerido', 'warning');
    } else if (typeof validarTextoCatalogo === 'function' && !validarTextoCatalogo(nombre, 2, 120)) {
        alertas('El nombre de la marca no es válido (2-120 caracteres permitidos).', 'warning');
    } else {
        const url = base_url + 'Marcas/registrar';
        const frm = document.getElementById("formulario");
        const http = new XMLHttpRequest();
        http.open("POST", url, true);
        http.send(new FormData(frm));
        http.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                const res = JSON.parse(this.responseText);
                alertas(res.msg, res.icono);
                frm.reset();
                myModal.hide();
                tblMarcas.ajax.reload();
            }
        }
    }
}
function btnEditarMarca(id) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = "Actualizar Marca";
    if (btnAccion) btnAccion.textContent = "Modificar";
    const url = base_url + 'Marcas/editar/' + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById("id");
            const nombre = document.getElementById("nombre");
            const estado = document.getElementById("estado");

            if (id_input) id_input.value = res.id;
            if (nombre) nombre.value = res.marca;
            if (estado) estado.checked = (res.estado == 'Activo');
            actualizarToggleLabel();

            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindMarca(true);
        }
    }
}
function btnEliminarMarca(id) {
    Swal.fire({
        title: 'Esta seguro de eliminar?',
        text: "La marca no se eliminará de forma permanente, solo cambiará el estado a inactivo!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Marcas/eliminar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblMarcas.ajax.reload();
                }
            }
        }
    })
}
function btnReingresarMarca(id) {
    Swal.fire({
        title: 'Esta seguro de reingresar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Marcas/reingresar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblMarcas.ajax.reload();
                }
            }

        }
    })
}//Fin Marcas
function frmModelo() {
    document.getElementById("title").textContent = "Nuevo Modelo";
    document.getElementById("btnAccion").textContent = "Registrar";
    document.getElementById("formulario").reset();
    document.getElementById("id").value = "";
    document.getElementById("estado").checked = true;
    actualizarToggleLabel();
    myModal.show();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindModelo(false);
}
function registrarModelo(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsModelo);
    }
    const nombre = document.getElementById("nombre").value;
    const marca_id = document.getElementById("marca_id").value;
    if (nombre == '' || marca_id == '') {
        alertas('El nombre y la marca son requeridos', 'warning');
    } else if (typeof validarTextoCatalogo === 'function' && !validarTextoCatalogo(nombre, 2, 120)) {
        alertas('El nombre del modelo no es válido.', 'warning');
    } else {
        const url = base_url + 'Modelos/registrar';
        const frm = document.getElementById("formulario");
        const http = new XMLHttpRequest();
        http.open("POST", url, true);
        http.send(new FormData(frm));
        http.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                const res = JSON.parse(this.responseText);
                alertas(res.msg, res.icono);
                frm.reset();
                myModal.hide();
                tblModelos.ajax.reload();
            }
        }
    }
}
function btnEditarModelo(id) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = "Actualizar Modelo";
    if (btnAccion) btnAccion.textContent = "Modificar";
    const url = base_url + 'Modelos/editar/' + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById("id");
            const nombre = document.getElementById("nombre");
            const marca_id = document.getElementById("marca_id");
            const estado = document.getElementById("estado");

            if (id_input) id_input.value = res.id;
            if (nombre) nombre.value = res.nombre;
            if (marca_id) marca_id.value = res.marca_id;
            if (estado) estado.checked = (res.estado == 'Activo');
            actualizarToggleLabel();

            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindModelo(true);
        }
    }
}
function btnEliminarModelo(id) {
    Swal.fire({
        title: 'Esta seguro de eliminar?',
        text: "El modelo no se eliminará de forma permanente, solo cambiará el estado a inactivo!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Modelos/eliminar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblModelos.ajax.reload();
                }
            }
        }
    })
}
function btnReingresarModelo(id) {
    Swal.fire({
        title: 'Esta seguro de reingresar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Modelos/reingresar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblModelos.ajax.reload();
                }
            }

        }
    })
}//Fin Modelos
function frmGama() {
    document.getElementById("title").textContent = "Nueva Gama";
    document.getElementById("btnAccion").textContent = "Registrar";
    document.getElementById("formulario").reset();
    document.getElementById("id").value = "";
    document.getElementById("estado").checked = true;
    actualizarToggleLabel();
    myModal.show();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindGama(false);
}
function registrarGama(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsGama);
    }
    const nombre = document.getElementById("nombre");
    const descripcion = document.getElementById("descripcion");
    if (nombre.value == "" || descripcion.value == "") {
        alertas('El nombre y la descripción son requeridos', 'warning');
    } else if (typeof validarTextoCatalogo === 'function' && (!validarTextoCatalogo(nombre.value, 2, 120) || !validarTextoCatalogo(descripcion.value, 2, 500))) {
        alertas('Revise el nombre (2-120 caracteres) y la descripción (2-500 caracteres).', 'warning');
    } else {
        const url = base_url + 'Gamas/registrar';
        const frm = document.getElementById("formulario");
        const http = new XMLHttpRequest();
        http.open("POST", url, true);
        http.send(new FormData(frm));
        http.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                const res = JSON.parse(this.responseText);
                alertas(res.msg, res.icono);
                frm.reset();
                myModal.hide();
                tblGamas.ajax.reload();
            }
        }
    }
}
function btnEditarGama(id) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = "Actualizar Gama";
    if (btnAccion) btnAccion.textContent = "Modificar";
    const url = base_url + 'Gamas/editar/' + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById("id");
            const nombre = document.getElementById("nombre");
            const descripcion = document.getElementById("descripcion");
            const estado = document.getElementById("estado");

            if (id_input) id_input.value = res.id;
            if (nombre) nombre.value = res.tipo;
            if (descripcion) descripcion.value = res.descripcion;
            if (estado) estado.checked = (res.estado == 'Activo');
            actualizarToggleLabel();

            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindGama(true);
        }
    }
}
function btnEliminarGama(id) {
    Swal.fire({
        title: 'Esta seguro de desactivar?',
        text: "La gama cambiará el estado a inactivo",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Gamas/eliminar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblGamas.ajax.reload();
                }
            }

        }
    })
}
function btnReingresarGama(id) {
    Swal.fire({
        title: 'Esta seguro de reactivar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Gamas/reingresar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblGamas.ajax.reload();
                }
            }

        }
    })
}//Fin Gamas
function frmTipo() {
    document.getElementById("title").textContent = "Nuevo Tipo";
    document.getElementById("btnAccion").textContent = "Registrar";
    document.getElementById("formulario").reset();
    document.getElementById("id").value = "";
    document.getElementById("estado").checked = true;
    actualizarToggleLabel();
    myModal.show();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindTipo(false);
}
function registrarTipo(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsTipo);
    }
    const nombre = document.getElementById("nombre").value;
    if (nombre == '') {
        alertas('El nombre es requerido', 'warning');
    } else if (typeof validarTextoCatalogo === 'function' && !validarTextoCatalogo(nombre, 2, 120)) {
        alertas('El nombre del tipo no es válido.', 'warning');
    } else {
        const url = base_url + 'Tipos/registrar';
        const frm = document.getElementById("formulario");
        const http = new XMLHttpRequest();
        http.open("POST", url, true);
        http.send(new FormData(frm));
        http.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                const res = JSON.parse(this.responseText);
                alertas(res.msg, res.icono);
                frm.reset();
                myModal.hide();
                tblTipos.ajax.reload();
            }
        }
    }
}
function btnEditarTipo(id) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = "Actualizar Tipo";
    if (btnAccion) btnAccion.textContent = "Modificar";
    const url = base_url + 'Tipos/editar/' + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById("id");
            const nombre = document.getElementById("nombre");
            const estado = document.getElementById("estado");

            if (id_input) id_input.value = res.id;
            if (nombre) nombre.value = res.tipo;
            if (estado) estado.checked = (res.estado == 1);
            actualizarToggleLabel();

            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindTipo(true);
        }
    }
}
function btnEliminarTipo(id) {
    Swal.fire({
        title: 'Esta seguro de desactivar?',
        text: "El tipo cambiará el estado a inactivo",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Tipos/eliminar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblTipos.ajax.reload();
                }
            }
        }
    })
}
function btnReingresarTipo(id) {
    Swal.fire({
        title: 'Esta seguro de reactivar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Tipos/reingresar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblTipos.ajax.reload();
                }
            }
        }
    })
}//Fin Tipos
function getMarcasAutocompleteSource() {
    return (window.marcasVehiculo || []).map(function (m) {
        return { label: m.nombre, value: String(m.id) };
    });
}

function nombreMarcaPorId(id) {
    if (id === '' || id == null) return '';
    const list = window.marcasVehiculo || [];
    for (let i = 0; i < list.length; i++) {
        if (String(list[i].id) === String(id)) return list[i].nombre;
    }
    return '';
}

function sincronizarMarcaDesdeTexto() {
    const textEl = document.getElementById('marca_text');
    const hidden = document.getElementById('marca');
    if (!textEl || !hidden) return;
    const t = textEl.value;
    if (!t || !t.trim()) {
        hidden.value = '';
        cargarModelos('');
        return;
    }
    const term = t.trim().toLowerCase();
    const src = getMarcasAutocompleteSource();
    const exact = src.filter(function (x) { return x.label.toLowerCase() === term; });
    if (exact.length === 1) {
        hidden.value = exact[0].value;
        cargarModelos(exact[0].value);
    } else {
        hidden.value = '';
        cargarModelos('');
    }
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.refresh('vehiculos');
}

function sincronizarModeloDesdeTexto() {
    const modeloHidden = document.getElementById('modelo');
    const modeloText = document.getElementById('modelo_text');
    if (!modeloText || modeloHidden == null) return;
    const t = modeloText.value;
    if (!t || !t.trim()) {
        modeloHidden.value = '';
        return;
    }
    const term = t.trim().toLowerCase();
    const list = window.modelosVehiculoCache || [];
    const exact = list.filter(function (x) { return x.label.toLowerCase() === term; });
    if (exact.length === 1) {
        modeloHidden.value = exact[0].value;
        actualizarPreviewImagen();
    } else {
        modeloHidden.value = '';
    }
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.refresh('vehiculos');
}

function initVehiculoMarcaModeloCombobox() {
    if (!window.jQuery || !$.fn.autocomplete) return;
    const $marca = $('#marca_text');
    const $modelo = $('#modelo_text');
    window.modelosVehiculoCache = [];

    $marca.autocomplete({
        minLength: 0,
        appendTo: 'body',
        source: getMarcasAutocompleteSource(),
        select: function (event, ui) {
            $marca.val(ui.item.label);
            document.getElementById('marca').value = ui.item.value;
            cargarModelos(ui.item.value);
            if (typeof SecuencialMantenimiento !== 'undefined') {
                setTimeout(function () { SecuencialMantenimiento.refresh('vehiculos'); }, 0);
            }
            return false;
        }
    }).on('focus', function () {
        $marca.autocomplete('search', $marca.val() || '');
    }).on('blur', function () {
        setTimeout(sincronizarMarcaDesdeTexto, 200);
    });

    $modelo.autocomplete({
        minLength: 0,
        appendTo: 'body',
        source: function (request, response) {
            const term = $.ui.autocomplete.escapeRegex((request.term || '').trim());
            const list = window.modelosVehiculoCache || [];
            if (!term) {
                response(list.slice());
                return;
            }
            const matcher = new RegExp(term, 'i');
            response($.grep(list, function (item) {
                return matcher.test(item.label);
            }));
        },
        select: function (event, ui) {
            $modelo.val(ui.item.label);
            document.getElementById('modelo').value = ui.item.value;
            actualizarPreviewImagen();
            if (typeof SecuencialMantenimiento !== 'undefined') {
                setTimeout(function () { SecuencialMantenimiento.refresh('vehiculos'); }, 0);
            }
            return false;
        }
    }).on('focus', function () {
        if ($modelo.prop('disabled')) return;
        $modelo.autocomplete('search', $modelo.val() || '');
    }).on('blur', function () {
        setTimeout(sincronizarModeloDesdeTexto, 200);
    });

    $modelo.prop('disabled', true);
}

function cargarModelos(marca_id, callback) {
    const modeloHidden = document.getElementById('modelo');
    const modeloText = document.getElementById('modelo_text');
    if (!modeloHidden || !modeloText) {
        if (typeof callback === 'function') callback();
        return;
    }
    if (marca_id === '' || marca_id == null) {
        window.modelosVehiculoCache = [];
        modeloText.value = '';
        modeloHidden.value = '';
        modeloText.disabled = true;
        modeloText.placeholder = 'Primero elija una marca';
        if (window.jQuery && $(modeloText).data('ui-autocomplete')) {
            $(modeloText).autocomplete('option', 'source', function (request, response) {
                response([]);
            });
        }
        if (typeof callback === 'function') callback();
        if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.refresh('vehiculos');
        return;
    }
    modeloText.disabled = false;
    modeloText.placeholder = 'Escriba para filtrar o haga clic para ver todos';
    modeloText.value = 'Cargando...';
    modeloHidden.value = '';
    const url = base_url + 'Vehiculos/getModelosMarca/' + marca_id;
    const http = new XMLHttpRequest();
    http.open('GET', url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4) {
            if (this.status == 200) {
                const modelos = JSON.parse(this.responseText);
                window.modelosVehiculoCache = modelos.map(function (m) {
                    return { label: m.nombre, value: String(m.id) };
                });
                modeloText.value = '';
                if (window.jQuery && $(modeloText).data('ui-autocomplete')) {
                    $(modeloText).autocomplete('option', 'source', function (request, response) {
                        const term = $.ui.autocomplete.escapeRegex((request.term || '').trim());
                        const list = window.modelosVehiculoCache || [];
                        if (!term) {
                            response(list.slice());
                            return;
                        }
                        const matcher = new RegExp(term, 'i');
                        response($.grep(list, function (item) {
                            return matcher.test(item.label);
                        }));
                    });
                }
                if (typeof callback === 'function') callback();
                if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.refresh('vehiculos');
            } else {
                window.modelosVehiculoCache = [];
                modeloText.value = '';
                modeloHidden.value = '';
                alertas('No se pudieron cargar los modelos', 'warning');
                if (typeof callback === 'function') callback();
                if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.refresh('vehiculos');
            }
        }
    };
}
function frmVehiculo() {
    document.getElementById("title").textContent = "Nuevo Vehículo";
    document.getElementById("btnAccion").textContent = "Guardar Vehículo";
    const frm = document.getElementById("formulario");
    if (frm) frm.reset();
    document.getElementById("id").value = "";
    document.getElementById("img-preview").src = base_url + 'uploads/vehiculos/default.png';
    document.getElementById("img-preview").style.display = 'none';
    document.getElementById("no-image-placeholder").style.setProperty('display', 'flex', 'important');
    const btnDeleteImg = document.getElementById("btn-delete-img");
    if (btnDeleteImg) btnDeleteImg.classList.add("d-none");
    
    // Reset Precio UI
    document.getElementById("precio").value = "";
    document.getElementById("tipo_dia_hidden").value = "1";
    document.getElementById("estado_precio_hidden").value = "Activo";
    document.getElementById("btnTextPrecio").textContent = "Configurar Precio";
    document.getElementById("precioBadge").classList.add("d-none");
    document.getElementById("valPrecio").textContent = "0.00";

    const mh = document.getElementById("marca");
    const mtx = document.getElementById("marca_text");
    const modh = document.getElementById("modelo");
    const modtx = document.getElementById("modelo_text");
    if (mh) mh.value = "";
    if (mtx) mtx.value = "";
    if (modh) modh.value = "";
    if (modtx) {
        modtx.value = "";
        modtx.disabled = true;
        modtx.placeholder = "Primero elija una marca";
    }
    window.modelosVehiculoCache = [];

    myModal.show();
    deleteImg();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindVehiculo(false);
}

function actualizarPreviewImagen() {
    const modelo = document.getElementById('modelo').value;
    const anio = document.getElementById('anio').value;
    const imgPreview = document.getElementById('img-preview');
    const fotoActual = document.getElementById('foto_actual');
    const imgLoading = document.getElementById('img-loading');

    console.log('actualizarPreviewImagen called - modelo:', modelo, 'anio:', anio);

    // Limpiar timer anterior si existe
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    if (modelo != "" && anio != "") {
        // Solo si no se ha subido un archivo manualmente
        if (document.getElementById('imagen').value != "") {
            console.log('Imagen ya subida manualmente, ignorando búsqueda automática');
            return;
        }

        // Si ya hay una búsqueda en progreso, espera
        if (isSearching) {
            console.log('Búsqueda en progreso, usando debounce...');
            debounceTimer = setTimeout(() => actualizarPreviewImagen(), 500);
            return;
        }

        // Establecer debounce de 800ms para evitar múltiples llamadas
        debounceTimer = setTimeout(() => {
            ejecutarBusquedaImagen(modelo, anio, imgPreview, fotoActual, imgLoading);
        }, 800);
    }
}

function ejecutarBusquedaImagen(modelo, anio, imgPreview, fotoActual, imgLoading) {
    if (isSearching) {
        console.log('Búsqueda ya en progreso, cancelando...');
        return;
    }

    isSearching = true;

    if (imgLoading) {
        imgLoading.style.setProperty('display', 'flex', 'important');
    }

    const url = base_url + 'Vehiculos/obtenerImagenApi';
    const frmData = new FormData();
    frmData.append('modelo', modelo);
    frmData.append('anio', anio);

    console.log('Llamando a:', url, 'con modelo:', modelo, 'anio:', anio);

    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.timeout = 120000; // Búsqueda + descarga de imagen puede tardar; el servidor ya acota cada HTTP
    
    http.onreadystatechange = function () {
        if (this.readyState == 4) {
            isSearching = false; // Marcar como completado

            if (imgLoading) {
                imgLoading.style.setProperty('display', 'none', 'important');
            }
            
            const placeholder = document.getElementById("no-image-placeholder");
            const iconCerrar = document.getElementById("icon-cerrar");

            console.log('Response status:', this.status, 'Body:', this.responseText);

            if (this.status == 200) {
                const rawText = (this.responseText || '').trim();
                if (rawText.startsWith('<')) {
                    isSearching = false;
                    console.warn('Respuesta HTML recibida (sesión expirada)');
                    if (typeof alertas === 'function') alertas('Su sesión ha expirado. Redirigiendo...', 'warning');
                    setTimeout(function () { window.location.href = base_url; }, 1500);
                    return;
                }
                try {
                    const res = JSON.parse(rawText);
                    console.log('Respuesta parseada:', res);

                    if (res.session_expired) {
                        if (typeof alertas === 'function') alertas(res.msg || 'Sesión expirada. Redirigiendo...', 'warning');
                        setTimeout(function () { window.location.href = base_url; }, 1500);
                        return;
                    }
                    
                    if (imgPreview) {
                        const srcBanner = res.url
                            || (res.img && res.img.indexOf('uploads/') === 0
                                ? base_url + res.img
                                : base_url + 'uploads/vehiculos/' + (res.img || 'default.png'));
                        imgPreview.src = srcBanner + (srcBanner.indexOf('?') === -1 ? '?' : '&') + 'v=' + Date.now();

                        if (fotoActual) fotoActual.value = res.img;
                        
                        // Ocultar placeholder si hay imagen
                        if (res.img != 'default.png') {
                            if (placeholder) placeholder.style.setProperty('display', 'none', 'important');
                            imgPreview.style.display = 'block';
                            // Mostrar botón de eliminar
                            if (iconCerrar) iconCerrar.innerHTML = `
                                <button class="btn btn-danger btn-sm shadow-sm" type="button" onclick="deleteImg(event)">
                                <i class="fas fa-times"></i></button>`;
                        } else {
                            if (placeholder) placeholder.style.setProperty('display', 'flex', 'important');
                            imgPreview.style.display = 'none';
                            if (iconCerrar) iconCerrar.innerHTML = '';
                        }
                    }
                } catch (err) {
                    isSearching = false;
                    console.error('Error parseando JSON:', err, 'Response:', this.responseText);
                    alertas('Error obteniendo imagen', 'warning');
                    if (placeholder) placeholder.style.setProperty('display', 'flex', 'important');
                    if (imgPreview) imgPreview.style.display = 'none';
                    if (iconCerrar) iconCerrar.innerHTML = '';
                }
            } else if (this.status == 0) {
                isSearching = false;
                console.error('Timeout o conexión perdida');
                alertas('Timeout - Intenta nuevamente', 'warning');
                if (placeholder) placeholder.style.setProperty('display', 'flex', 'important');
                if (imgPreview) imgPreview.style.display = 'none';
            } else {
                isSearching = false;
                console.error('Error HTTP:', this.status, 'Response:', this.responseText);
                alertas('Error en conexión con API (' + this.status + ')', 'error');
                if (placeholder) placeholder.style.setProperty('display', 'flex', 'important');
                if (imgPreview) imgPreview.style.display = 'none';
                if (iconCerrar) iconCerrar.innerHTML = '';
            }
        }
    };

    http.onerror = function() {
        isSearching = false;
        console.error('Error en XMLHttpRequest');
        alertas('Error de conexión', 'error');
        if (imgLoading) imgLoading.style.setProperty('display', 'none', 'important');
    };

    http.ontimeout = function() {
        isSearching = false;
        console.error('Request timeout');
        alertas('Timeout - La solicitud tardó demasiado', 'warning');
        if (imgLoading) imgLoading.style.setProperty('display', 'none', 'important');
        const placeholder = document.getElementById('no-image-placeholder');
        const imgPreview = document.getElementById('img-preview');
        const iconCerrar = document.getElementById('icon-cerrar');
        if (placeholder) placeholder.style.setProperty('display', 'flex', 'important');
        if (imgPreview) imgPreview.style.display = 'none';
        if (iconCerrar) iconCerrar.innerHTML = '';
    };

    http.send(frmData);
}
function registrarVeh(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsVehiculo);
    }
    const placa = document.getElementById("placa").value;
    const marcaId = document.getElementById("marca") ? document.getElementById("marca").value : '';
    const modelo = document.getElementById("modelo").value;
    const tipo = document.getElementById("tipo").value;
    const anio = document.getElementById("anio").value;
    const color = document.getElementById("color").value;
    const kilometraje = document.getElementById("kilometraje").value;
    const combustible = document.getElementById("combustible").value;
    if (placa == '' || marcaId == '' || modelo == '' || tipo == '' || anio == '' || color == '' || kilometraje == '' || combustible == '') {
        alertas('Todo los campos son requeridos (elija marca y modelo de la lista o escriba el nombre exacto)', 'warning');
        return false;
    }
    if (typeof validarPlacaVehiculo === 'function' && !validarPlacaVehiculo(placa)) {
        alertas('La placa no es válida (3-15 caracteres, letras y números).', 'warning');
        return false;
    }
    const placaEl = document.getElementById("placa");
    if (placaEl && placaEl.dataset.placaValida === '0') {
        alertas('La placa ya está registrada en el sistema.', 'warning');
        return false;
    }
    if (typeof validarAnioVehiculo === 'function' && !validarAnioVehiculo(anio)) {
        alertas('El año del vehículo no es válido.', 'warning');
        return false;
    }
    if (typeof validarTextoCatalogo === 'function' && !validarTextoCatalogo(color, 2, 40)) {
        alertas('El color no es válido.', 'warning');
        return false;
    }
    if (!/^\d+$/.test(String(kilometraje).trim()) || parseInt(kilometraje, 10) < 0) {
        alertas('El kilometraje debe ser un número entero mayor o igual a 0.', 'warning');
        return false;
    }
    if (!combustible || combustible.trim() === '') {
        alertas('Seleccione el nivel de combustible.', 'warning');
        return false;
    }
    const url = base_url + 'Vehiculos/registrar';
    const frm = document.getElementById("formulario");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.upload.addEventListener('progress', function () {
        document.getElementById('btnAccion').textContent = 'Procesando...';
    });
    http.send(new FormData(frm));
    http.addEventListener('load', function () {
        document.getElementById('btnAccion').textContent = 'Procesando...';
    });
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
            const res = JSON.parse(this.responseText);
            alertas(res.msg, res.icono);
            
            if (res.icono === 'success') {
                frm.reset();
                myModal.hide();
                tblVehiculos.ajax.reload();
            }
            
            const isEdit = document.getElementById("id").value !== "";
            document.getElementById('btnAccion').textContent = isEdit ? 'Modificar' : 'Guardar Vehículo';
        }
    }
}
function btnEditarVeh(id) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = "Actualizar Vehículo";
    if (btnAccion) btnAccion.textContent = "Modificar";
    const url = base_url + 'Vehiculos/editar/' + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById("id");
            const placa = document.getElementById("placa");
            const marca = document.getElementById("marca");
            const modelo = document.getElementById("modelo");
            const tipo = document.getElementById("tipo");
            const anio = document.getElementById("anio");
            const color = document.getElementById("color");
            const kilometraje = document.getElementById("kilometraje");
            const combustible = document.getElementById("combustible");
            const img_preview = document.getElementById("img-preview");
            const icon_cerrar = document.getElementById("icon-cerrar");
            const icon_image = document.getElementById("icon-image");
            const foto_actual = document.getElementById("foto_actual");
            const precio = document.getElementById("precio"); // Added for price handling

            if (id_input) id_input.value = res.id;
            if (placa) placa.value = res.placa;
            if (marca) marca.value = res.marca_id;
            const marcaTextEl = document.getElementById("marca_text");
            if (marcaTextEl) marcaTextEl.value = nombreMarcaPorId(res.marca_id);
            if (modelo) {
                cargarModelos(res.marca_id, function () {
                    modelo.value = res.modelo_id;
                    const modeloTextEl = document.getElementById("modelo_text");
                    if (modeloTextEl) {
                        const list = window.modelosVehiculoCache || [];
                        let label = '';
                        for (let i = 0; i < list.length; i++) {
                            if (String(list[i].value) === String(res.modelo_id)) {
                                label = list[i].label;
                                break;
                            }
                        }
                        modeloTextEl.value = label;
                    }
                    actualizarPreviewImagen();
                });
            }
            if (tipo) tipo.value = res.tipo;
            if (anio) anio.value = res.anio;
            if (color) color.value = res.color;
            if (kilometraje) kilometraje.value = res.kilometraje_actual;
            if (combustible) combustible.value = res.combustible_actual;
            if (precio) {
                precio.value = res.precio;
                document.getElementById("tipo_dia_hidden").value = res.tipo_dia_id || 1;
                document.getElementById("estado_precio_hidden").value = res.estado_precio || 'Activo';
                
                if (parseFloat(res.precio) > 0) {
                    document.getElementById("btnTextPrecio").textContent = "Cambiar Precio";
                    document.getElementById("precioBadge").classList.remove("d-none");
                    document.getElementById("valPrecio").textContent = parseFloat(res.precio).toFixed(2);
                }
            }
            if (img_preview) {
                if (res.foto && (res.foto.startsWith('http://') || res.foto.startsWith('https://'))) {
                    img_preview.src = res.foto;
                } else if (res.foto && res.foto.startsWith('uploads/')) {
                    img_preview.src = base_url + res.foto;
                } else if (res.foto == 'default.png' || !res.foto) {
                    img_preview.src = base_url + 'uploads/vehiculos/default.png';
                } else {
                    img_preview.src = base_url + 'uploads/vehiculos/' + res.foto;
                }
            }
            if (icon_cerrar) {
                icon_cerrar.innerHTML = `
                <button class="btn btn-outline-danger shadow-sm" type="button" onclick="deleteImg()">
                <i class="fas fa-times-circle"></i></button>`;
            }
            if (icon_image) icon_image.classList.add("d-none");
            if (foto_actual) foto_actual.value = res.foto;

            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindVehiculo(true);
        }
    }
}
function btnEliminarVeh(id) {
    Swal.fire({
        title: '¿Desactivar este vehículo?',
        text: 'No se borra de la base de datos: pasará a inactivo y dejará de mostrarse en nuevas reservas. Podrás reactivarlo desde Vehículos inactivos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Vehiculos/eliminar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblVehiculos.ajax.reload();
                }
            }

        }
    })
}
function btnReingresarVeh(id) {
    Swal.fire({
        title: '¿Reactivar este vehículo?',
        text: 'Volverá a estado Activo y podrá usarse en reservas.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, reactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Vehiculos/reingresar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    if (typeof tblVehiculos !== 'undefined') tblVehiculos.ajax.reload();
                }
            }

        }
    })
}
function preview(e) {
    var input = document.getElementById('imagen');
    var filePath = input.value;
    var extension = /(\.png|\.jpeg|\.jpg)$/i;
    if (!extension.exec(filePath)) {
        alertas('Seleccione un archivo valido', 'warning');
        deleteImg();
        return false;
    } else {
        const url = e.target.files[0];
        const urlTmp = URL.createObjectURL(url);
        const imgPreview = document.getElementById("img-preview");
        if (imgPreview) {
            imgPreview.src = urlTmp;
            imgPreview.style.display = 'block';
        }
        const placeholder = document.getElementById("no-image-placeholder");
        if (placeholder) placeholder.style.setProperty('display', 'none', 'important');
        
        const iconCerrar = document.getElementById("icon-cerrar");
        if (iconCerrar) {
            iconCerrar.innerHTML = `
            <button class="btn btn-outline-danger shadow-sm" type="button" onclick="deleteImg(event)"><i class="fas fa-times-circle"></i></button>
            `;
        }
    }
}
function previewLogo(e) {
    var input = document.getElementById('imagen');
    var filePath = input.value;
    var extension = /(\.png)$/i;
    if (!extension.exec(filePath)) {
        alertas('Seleccione un formato png', 'warning');
        deleteImg();
        return false;
    } else {
        const url = e.target.files[0];
        const urlTmp = URL.createObjectURL(url);
        document.getElementById("img-preview").src = urlTmp;
        document.getElementById("icon-image").classList.add("d-none");
        document.getElementById("icon-cerrar").innerHTML = `
        <button class="btn btn-outline-danger" onclick="deleteImg()"><i class="fas fa-times-circle"></i></button>
        `;
    }
}
function deleteImg(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const iconCerrar = document.getElementById("icon-cerrar");
    const imgPreview = document.getElementById("img-preview");
    const placeholder = document.getElementById("no-image-placeholder");
    const imgInput = document.getElementById("imagen");
    const fotoActual = document.getElementById("foto_actual");

    if (iconCerrar) iconCerrar.innerHTML = '';
    if (imgInput) imgInput.value = '';
    if (fotoActual) fotoActual.value = '';
    if (imgPreview) {
        imgPreview.src = base_url + 'uploads/vehiculos/default.png';
        imgPreview.style.display = 'none';
    }
    if (placeholder) placeholder.style.setProperty('display', 'flex', 'important');
}
function modificarEmpresa(e) {
    e.preventDefault();
    const id = document.getElementById("id").value;
    const ruc = document.getElementById("ruc").value;
    const nombre = document.getElementById("nombre").value;
    const telefono = document.getElementById("telefono").value;
    const correo = document.getElementById("correo").value;
    const direccion = document.getElementById("direccion").value;

    if (id == '' || ruc == '' || nombre == '' || telefono == '' || correo == '' || direccion == '') {
        alertas('Todo los campos son requerido', 'warning');
        return false;
    } else {
        const frm = document.getElementById('formulario');
        const url = base_url + 'Administracion/modificar';
        const http = new XMLHttpRequest();
        let frmData = new FormData(frm);
        http.open("POST", url, true);
        http.upload.addEventListener('progress', function () {
            document.getElementById('btnAccion').textContent = 'Procesando...';
        });
        http.send(frmData);
        http.addEventListener('load', function () {
            document.getElementById('btnAccion').textContent = 'Modificar';
        });
        http.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                const res = JSON.parse(this.responseText);
                alertas(res.msg, res.icono);
            }
        }
    }
}
function alertas(mensaje, icono) {
    Swal.fire({
        position: 'center',
        icon: icono,
        title: mensaje,
        showConfirmButton: false,
        timer: 3000
    })
}
function registrarPermisos(e) {
    e.preventDefault();
    const http = new XMLHttpRequest();
    const frm = document.getElementById("formulario");
    const url = base_url + 'Usuarios/registrarPermisos';
    http.open("POST", url);
    http.send(new FormData(frm));
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            alertas(res.msg, res.icono);
        }
    }
}
//Monedas
function frmMoneda() {
    document.getElementById('id').value = '';
    document.getElementById('title').textContent = 'Nuevo Moneda';
    document.getElementById('btnAccion').textContent = 'Registrar';
    document.getElementById('formulario').reset();
    myModal.show();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindMoneda(false);
}
function registrarMoneda(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsMoneda);
    }
    const nombre = document.getElementById('nombre');
    const simbolo = document.getElementById('simbolo');
    if (nombre.value == '' || simbolo.value == '') {
        alertas('Todo los campos son requeridos', 'warning');
        return false;
    }
    const s = (simbolo.value || '').trim();
    if (s.length < 1 || s.length > 12) {
        alertas('El símbolo debe tener entre 1 y 12 caracteres.', 'warning');
        return false;
    }
    if (typeof validarTextoCatalogo === 'function' && !validarTextoCatalogo(nombre.value, 2, 120)) {
        alertas('El nombre de la moneda no es válido.', 'warning');
        return false;
    }
    const url = base_url + 'Administracion/registrarMoneda';
    const frm = document.getElementById('formulario');
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            alertas(res.msg, res.icono);
            myModal.hide();
            t_moneda.ajax.reload();
        }
    }
}
function btnEditarMoneda(id) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = 'Modificar Moneda';
    if (btnAccion) btnAccion.textContent = 'Modificar';
    const url = base_url + 'Administracion/editarMoneda/' + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById('id');
            const nombre = document.getElementById('nombre');
            const simbolo = document.getElementById('simbolo');

            if (id_input) id_input.value = res.id;
            if (nombre) nombre.value = res.nombre;
            if (simbolo) simbolo.value = res.simbolo;

            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindMoneda(true);
        }
    }
}
function btnEliminarMoneda(id) {
    Swal.fire({
        title: 'Esta seguro de eliminar?',
        text: "La moneda no se eliminará de forma permanente, solo cambiará el estado a inactivo!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "Administracion/eliminarMoneda/" + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    t_moneda.ajax.reload();
                }
            }
        }
    })
}
function btnReingresarMoneda(id) {
    Swal.fire({
        title: 'Esta seguro de reingresar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "Administracion/reingresarMoneda/" + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            }

        }
    })
} //fin moneda
function frmDoc() {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    const formulario = document.getElementById("formulario");
    const id = document.getElementById("id");
    const claves = document.getElementById("claves");

    if (title) title.textContent = "Nuevo Documento";
    if (btnAccion) btnAccion.textContent = "Registrar";
    if (formulario) formulario.reset();
    if (id) id.value = "";
    if (claves) {
        claves.classList.remove("d-none");
    }
    if (myModal) myModal.show();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindDocumento(false);
}

function registrarDoc(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsDocumento);
    }
    const documento = document.getElementById("documento").value;
    if (documento == '') {
        alertas('El documento es requerido', 'warning');
    } else if (typeof validarTextoCatalogo === 'function' && !validarTextoCatalogo(documento, 2, 150)) {
        alertas('El nombre del documento no es válido (2-150 caracteres).', 'warning');
    } else {
        const url = base_url + 'Documentos/registrar';
        const frm = document.getElementById("formulario");
        const http = new XMLHttpRequest();
        http.open("POST", url, true);
        http.send(new FormData(frm));
        http.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                const res = JSON.parse(this.responseText);
                alertas(res.msg, res.icono);
                frm.reset();
                myModal.hide();
                tblDoc.ajax.reload();
            }
        }
    }
}

function btnEditarDoc(id) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = "Actualizar Documento";
    if (btnAccion) btnAccion.textContent = "Modificar";
    const url = base_url + 'Documentos/editar/' + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById("id");
            const documento = document.getElementById("documento");
            const estado = document.getElementById("estado");

            if (id_input) id_input.value = res.id;
            if (documento) documento.value = res.documento;
            if (estado) estado.checked = (res.estado == 1);

            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindDocumento(true);
        }
    }
}

function btnEliminarDoc(id) {
    Swal.fire({
        title: 'Esta seguro de eliminar?',
        text: "El documento no se eliminará de forma permanente, solo cambiará el estado a inactivo!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Documentos/eliminar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblDoc.ajax.reload();
                }
            }
        }
    })
}

function btnReingresarDoc(id) {
    Swal.fire({
        title: 'Esta seguro de reingresar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + 'Documentos/reingresar/' + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    if (typeof tblDoc !== 'undefined') tblDoc.ajax.reload();
                }
            }

        }
    })
} //Fin doc
function salir() {
    Swal.fire({
        title: 'Esta seguro de cerrar la sesión?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = base_url + 'Usuarios/salir';
        }
    })
}
function actualizarDatos(e) {
    e.preventDefault();
    const user = document.getElementById('usuario').value;
    const nombre = document.getElementById('nombre').value;
    const correo = document.getElementById('correo').value;
    const telefono = document.getElementById('telefono').value;
    const direccion = document.getElementById('direccion').value;
    const apellido = document.getElementById('apellido').value;
    if (user == '' || nombre == '' || apellido == '' || correo == '' || telefono == '' || direccion == '') {
        alertas('Todo los campos son requeridos', 'warning');
        return false;
    } else {
        const url = base_url + 'Usuarios/actualizarDato';
        const frm = document.getElementById("frmDatos");
        const http = new XMLHttpRequest();
        http.open("POST", url, true);
        http.send(new FormData(frm));
        http.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                console.log(this.responseText);
                const res = JSON.parse(this.responseText);
                alertas(res.msg, res.icono);
            }
        }
    }
}
function editarPerfil() {
    document.getElementById('editarPerfil').classList.remove('d-none');
}
function frmReserva() {
    document.getElementById("title").textContent = "Nueva Reserva";
    document.getElementById("btnAccion").textContent = "Registrar";
    document.getElementById("formulario").reset();
    myModal.show();
}
function registrarReserva(e) {
    e.preventDefault();
    const select_cliente = document.getElementById("select_cliente").value;
    const select_vehiculo = document.getElementById("select_vehiculo").value;
    const id_cli = document.getElementById("id_cli").value;
    const id_veh = document.getElementById("id_veh").value;
    const numero = document.getElementById("numero").value;
    const precio = document.getElementById("precio").value;
    const abono = document.getElementById("abono").value;
    const fecha = document.getElementById("fecha").value;
    if (select_cliente == '' || select_vehiculo == '' || id_cli == '' || id_veh == ''
        || numero == '' || precio == '' || abono == '' || fecha == '') {
        alertas('Todo los campos con * son requeridos', 'warning');
    } else {
        const url = base_url + 'Reservas/registrar';
        const frm = document.getElementById("formulario");
        const http = new XMLHttpRequest();
        http.open("POST", url, true);
        http.send(new FormData(frm));
        http.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                console.log(this.responseText);
                const res = JSON.parse(this.responseText);
                alertas(res.msg, res.icono);
                frm.reset();
                myModal.hide();
                if (res.id_alquiler > 0) {
                    setTimeout(() => {
                        window.open(base_url + 'Reservas/pdfPrestamo/' + res.id_alquiler);
                    }, 2000);
                }
                tblReservas.ajax.reload();
            }
        }
    }
}
function entrega(id) {
    const url = base_url + 'Reservas/ver/' + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
            const res = JSON.parse(this.responseText);
            document.getElementById('id_alquiler').value = res.id;
            document.getElementById('pendiente').value = res.abono;
            let total = parseFloat((res.precio_dia * res.num_dias) - res.abono);
            document.getElementById('monto_pagar').value = total.toFixed(2);
            m_entrega.show();
        }
    }

}
function procesarEntrega(e) {
    e.preventDefault();
    const id = document.getElementById('id_alquiler').value;
    const url = base_url + 'Reservas/procesar/' + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
            const res = JSON.parse(this.responseText);
            alertas(res.msg, res.icono);
            m_entrega.hide();
            tblReservas.ajax.reload();
        }
    }
}
function frmPrecio() {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    const formulario = document.getElementById("formulario");
    const id = document.getElementById("id");
    if (title) title.textContent = "Nuevo Precio";
    if (btnAccion) btnAccion.textContent = "Guardar Precio";
    if (formulario) formulario.reset();
    if (id) id.value = "";
    if (myModal) myModal.show();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindPrecio(false);
}

function registrarPrecio(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsPrecio);
    }
    const veh = document.getElementById("vehiculo").value;
    const tipo = document.getElementById("tipo_dia").value;
    const pre = document.getElementById("precio").value;
    if (veh == "" || tipo == "" || pre == "") {
        alertas('Todos los campos son obligatorios', 'warning');
        return false;
    }
    const precioNum = parseFloat(String(pre).replace(',', '.'));
    if (isNaN(precioNum) || precioNum <= 0 || precioNum > 999999.99) {
        alertas('El precio debe ser un número mayor que 0 y razonable.', 'warning');
        return false;
    }
    const url = base_url + 'Precios/registrar';
    const frm = document.getElementById("formulario");
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(new FormData(frm));
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            alertas(res.msg, res.icono);
            if (res.icono == "success") {
                if (myModal) myModal.hide();
                tblPrecios.ajax.reload();
            }
        }
    }
}

function btnEditarPrecio(id_p) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = "Actualizar Precio";
    if (btnAccion) btnAccion.textContent = "Modificar Precio";
    const url = base_url + "Precios/editar/" + id_p;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById("id");
            const vehiculo = document.getElementById("vehiculo");
            const tipo_dia = document.getElementById("tipo_dia");
            const precio = document.getElementById("precio");

            if (id_input) id_input.value = res.precio_id;
            if (vehiculo) vehiculo.value = res.vehiculo_id;
            if (tipo_dia) tipo_dia.value = res.tipo_dia_id;
            if (precio) precio.value = res.precio;

            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindPrecio(true);
        }
    }
}

function btnEliminarPrecio(id_p) {
    Swal.fire({
        title: '¿Está seguro de desactivar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ¡desactivar!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "Precios/eliminar/" + id_p;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblPrecios.ajax.reload();
                }
            }
        }
    })
}

function btnReingresarPrecio(id_p) {
    Swal.fire({
        title: '¿Está seguro de reactivar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ¡reactivar!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "Precios/reingresar/" + id_p;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblPrecios.ajax.reload();
                }
            }
        }
    })
}

// Funciones para manejo de precio 2-en-1 en Vehículos
function abrirPrecioVehiculo() {
    // Sincronizar el valor actual del formulario principal al modal secundario
    const p_input = document.getElementById("precio");
    const t_input = document.getElementById("tipo_dia_hidden");
    const e_input = document.getElementById("estado_precio_hidden");
    
    document.getElementById("temp_precio").value = p_input.value;
    document.getElementById("tipo_dia_modal").value = t_input.value;
    document.getElementById("estado_precio_modal").checked = (e_input.value === 'Activo');
    
    if (m_precio) m_precio.show();
}

function cerrarPrecio() {
    if (m_precio) m_precio.hide();
}

function aplicarPrecio() {
    const p_nuevo = document.getElementById("temp_precio").value;
    const t_nuevo = document.getElementById("tipo_dia_modal").value;
    const e_nuevo = document.getElementById("estado_precio_modal").checked ? 'Activo' : 'Inactivo';

    if (p_nuevo == "" || parseFloat(p_nuevo) <= 0) {
        alertas("Ingrese un precio válido", "warning");
        return;
    }
    
    // Sincronizar con el formulario principal
    document.getElementById("precio").value = p_nuevo;
    document.getElementById("tipo_dia_hidden").value = t_nuevo;
    document.getElementById("estado_precio_hidden").value = e_nuevo;
    
    document.getElementById("btnTextPrecio").textContent = "Cambiar Precio";
    const badge = document.getElementById("precioBadge");
    const valText = document.getElementById("valPrecio");
    
    if (badge) badge.classList.remove("d-none");
    if (valText) valText.textContent = parseFloat(p_nuevo).toFixed(2);
    
    cerrarPrecio();

    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.refresh('vehiculos');
    
    Swal.fire({
        position: 'center',
        icon: 'success',
        title: 'Configuración de precio capturada',
        showConfirmButton: false,
        timer: 1500
    });
}

// Feriados
function frmFeriado() {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    const formulario = document.getElementById("formulario");
    const id = document.getElementById("id");
    if (title) title.textContent = "Nuevo Feriado";
    if (btnAccion) btnAccion.textContent = "Guardar Feriado";
    if (formulario) formulario.reset();
    if (id) id.value = "";
    if (myModal) myModal.show();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindFeriado(false);
}

function registrarFeriado(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsFeriado);
    }
    const fecha = document.getElementById("fecha").value;
    const desc = document.getElementById("descripcion").value;
    if (fecha == "" || desc == "") {
        alertas('Todos los campos son obligatorios', 'warning');
    } else {
        const url = base_url + 'Feriados/registrar';
        const frm = document.getElementById("formulario");
        const http = new XMLHttpRequest();
        http.open("POST", url, true);
        http.send(new FormData(frm));
        http.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                const res = JSON.parse(this.responseText);
                alertas(res.msg, res.icono);
                if (res.icono == "success") {
                    if (myModal) myModal.hide();
                    tblFeriados.ajax.reload();
                }
            }
        }
    }
}

function btnEditarFeriado(id_f) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = "Actualizar Feriado";
    if (btnAccion) btnAccion.textContent = "Modificar Feriado";
    const url = base_url + "Feriados/editar/" + id_f;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById("id");
            const fecha = document.getElementById("fecha");
            const descripcion = document.getElementById("descripcion");

            if (id_input) id_input.value = res.feriado_id;
            if (fecha) fecha.value = res.fecha;
            if (descripcion) descripcion.value = res.descripcion;

            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindFeriado(true);
        }
    }
}

function btnEliminarFeriado(id_f) {
    Swal.fire({
        title: '¿Está seguro de eliminar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ¡eliminar!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "Feriados/eliminar/" + id_f;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    tblFeriados.ajax.reload();
                }
            }
        }
    })
}

// Tipos de Día
function frmTipoDia() {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    const formulario = document.getElementById("formulario");
    const id = document.getElementById("id");
    if (title) title.textContent = "Nuevo Tipo de Día";
    if (btnAccion) btnAccion.textContent = "Registrar";
    if (formulario) formulario.reset();
    if (id) id.value = "";
    if (myModal) myModal.show();
    if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindTipoDia(false);
}

function registrarTipoDia(e) {
    e.preventDefault();
    if (typeof SecuencialMantenimiento !== 'undefined') {
        SecuencialMantenimiento.unlockIds(SecuencialMantenimiento.idsTipoDia);
    }
    const nombre = document.getElementById("nombre").value;
    if (nombre == "") {
        alertas('Todos los campos son obligatorios', 'warning');
    } else if (typeof validarTextoCatalogo === 'function' && !validarTextoCatalogo(nombre, 2, 120)) {
        alertas('El nombre del tipo de día no es válido.', 'warning');
    } else {
        const url = base_url + 'TiposDia/registrar';
        const frm = document.getElementById("formulario");
        const http = new XMLHttpRequest();
        http.open("POST", url, true);
        http.send(new FormData(frm));
        http.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                const res = JSON.parse(this.responseText);
                alertas(res.msg, res.icono);
                if (res.icono == "success") {
                    if (myModal) myModal.hide();
                    if (typeof tblTiposDia !== 'undefined') tblTiposDia.ajax.reload();
                }
            }
        }
    }
}

function btnEditarTipoDia(id) {
    const title = document.getElementById("title");
    const btnAccion = document.getElementById("btnAccion");
    if (title) title.textContent = "Actualizar Tipo de Día";
    if (btnAccion) btnAccion.textContent = "Modificar";
    const url = base_url + "TiposDia/editar/" + id;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const id_input = document.getElementById("id");
            const nombre = document.getElementById("nombre");
            if (id_input) id_input.value = res.id;
            if (nombre) nombre.value = res.nombre;
            if (myModal) myModal.show();
            if (typeof SecuencialMantenimiento !== 'undefined') SecuencialMantenimiento.bindTipoDia(true);
        }
    }
}

function btnEliminarTipoDia(id) {
    Swal.fire({
        title: '¿Está seguro de inactivar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ¡inactivar!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "TiposDia/eliminar/" + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    if (typeof tblTiposDia !== 'undefined') tblTiposDia.ajax.reload();
                }
            }
        }
    })
}

function btnReingresarTipoDia(id) {
    Swal.fire({
        title: '¿Está seguro de reactivar?',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ¡reactivar!',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = base_url + "TiposDia/reingresar/" + id;
            const http = new XMLHttpRequest();
            http.open("GET", url, true);
            http.send();
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertas(res.msg, res.icono);
                    if (typeof tblTiposDia !== 'undefined') tblTiposDia.ajax.reload();
                }
            }
        }
    })
}
