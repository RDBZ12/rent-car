/**
 * Validaciones en cliente (espejo de Config/Helpers.php).
 * Orden: validar de arriba a abajo y devolver el primer error.
 */
(function (w) {
    'use strict';

    function soloDigitos(s) {
        return String(s || '').replace(/\D/g, '');
    }

    w.validarCedulaDominicana = function (cedula) {
        var c = soloDigitos(cedula);
        if (c.length !== 11) return false;
        var sum = 0;
        var weights = [1, 2, 1, 2, 1, 2, 1, 2, 1, 2];
        for (var i = 0; i < 10; i++) {
            var n = parseInt(c.charAt(i), 10) * weights[i];
            sum += Math.floor(n / 10) + (n % 10);
        }
        var verif = (10 - (sum % 10)) % 10;
        return verif === parseInt(c.charAt(10), 10);
    };

    w.validarNombrePersona = function (s) {
        var t = (s || '').trim();
        if (t.length < 2 || t.length > 80) return false;
        return /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s'-]+$/.test(t);
    };

    w.validarTelefonoRD = function (tel) {
        var n = soloDigitos(tel);
        if (n.length === 11 && n.charAt(0) === '1') n = n.slice(1);
        return n.length === 10 && /^\d{10}$/.test(n);
    };

    w.validarEmailBasico = function (email) {
        var t = (email || '').trim();
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(t);
    };

    w.validarDireccionCliente = function (d) {
        var t = (d || '').trim();
        return t.length >= 5 && t.length <= 500;
    };

    w.validarTextoCatalogo = function (s, minLen, maxLen) {
        minLen = minLen || 2;
        maxLen = maxLen || 120;
        var t = (s || '').trim();
        if (t.length < minLen || t.length > maxLen) return false;
        return /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s'.,_#\/():-]+$/.test(t);
    };

    w.validarUsuarioSistema = function (u) {
        return /^[a-zA-Z0-9._-]{3,50}$/.test((u || '').trim());
    };

    w.validarPlacaVehiculo = function (p) {
        var t = (p || '').trim().replace(/\s+/g, '').toUpperCase();
        return t.length >= 3 && t.length <= 15 && /^[A-Z0-9\-]+$/.test(t);
    };

    w.validarAnioVehiculo = function (anio) {
        var y = parseInt(String(anio).trim(), 10);
        if (isNaN(y)) return false;
        var max = new Date().getFullYear() + 1;
        return y >= 1950 && y <= max;
    };

    /** Mensajes cliente en orden de llenado */
    w.validarFormularioClienteOrden = function (dni, telefono, nombre, apellido, email, direccion) {
        if (!dni || !String(dni).trim()) return 'Ingrese la cédula.';
        if (!validarCedulaDominicana(dni)) return 'La cédula no es válida (11 dígitos, República Dominicana).';
        if (!telefono || !String(telefono).trim()) return 'Ingrese el teléfono.';
        if (!validarTelefonoRD(telefono)) return 'El teléfono debe tener 10 dígitos (opcional +1 al inicio).';
        if (!nombre || !String(nombre).trim()) return 'Ingrese el nombre.';
        if (!validarNombrePersona(nombre)) return 'El nombre solo debe contener letras y espacios.';
        if (!apellido || !String(apellido).trim()) return 'Ingrese el apellido.';
        if (!validarNombrePersona(apellido)) return 'El apellido solo debe contener letras y espacios.';
        if (!email || !String(email).trim()) return 'Ingrese el correo electrónico.';
        if (!validarEmailBasico(email)) return 'El correo electrónico no es válido.';
        if (!direccion || !String(direccion).trim()) return 'Ingrese la dirección.';
        if (!validarDireccionCliente(direccion)) return 'La dirección debe tener entre 5 y 500 caracteres.';
        return null;
    };

    /**
     * Llenado secuencial + validación en vivo (solo altas; en edición todo habilitado).
     * Antes de enviar el formulario hay que llamar unlockIds con los mismos ids para que FormData incluya los campos.
     */
    w.SecuencialMantenimiento = {
        _abort: {},
        _configs: {},

        unlockIds: function (ids) {
            if (!ids || !ids.length) return;
            ids.forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.disabled = false;
            });
        },

        refresh: function (key) {
            var cfg = this._configs[key];
            if (cfg) this.applyChain(cfg.steps, cfg.isEdit, cfg);
        },

        _syncPeers: function (steps) {
            for (var k = 0; k < steps.length; k++) {
                var s = steps[k];
                if (!s.peerIds || !s.peerIds.length) continue;
                var rel = document.getElementById(s.id);
                var enable = rel && !rel.disabled;
                s.peerIds.forEach(function (pid) {
                    var p = document.getElementById(pid);
                    if (p) p.disabled = !enable;
                });
            }
        },

        applyChain: function (steps, isEdit, cfg) {
            var self = this;
            if (isEdit) {
                steps.forEach(function (s) {
                    var el = document.getElementById(s.id);
                    if (el) el.disabled = false;
                });
                this._syncPeers(steps);
                return;
            }

            var allPrevOk = true;
            for (var i = 0; i < steps.length; i++) {
                var s = steps[i];
                var el = document.getElementById(s.id);
                if (!el) continue;

                if (!allPrevOk) {
                    el.disabled = true;
                    if (el.tagName === 'SELECT') {
                        el.value = '';
                    } else if (el.type !== 'checkbox' && el.type !== 'file') {
                        el.value = '';
                    }
                    el.classList.remove('is-valid', 'is-invalid');
                    continue;
                }

                el.disabled = false;

                var v = el.value;
                var nonEmpty = String(v || '').trim() !== '';
                var ok = typeof s.validate === 'function' ? s.validate(v, el) : true;

                if (!nonEmpty) {
                    el.classList.remove('is-valid', 'is-invalid');
                    allPrevOk = false;
                } else if (!ok) {
                    el.classList.remove('is-valid');
                    el.classList.add('is-invalid');
                    allPrevOk = false;
                } else {
                    el.classList.remove('is-invalid');
                    el.classList.add('is-valid');
                    allPrevOk = true;
                }
            }

            if (cfg && typeof cfg.postApply === 'function') cfg.postApply();
            self._syncPeers(steps);
        },

        bind: function (key, steps, isEdit, postApply) {
            var prev = this._abort[key];
            if (prev) prev.abort();
            var ac = new AbortController();
            this._abort[key] = ac;
            var sig = { signal: ac.signal };
            var self = this;
            var cfg = { steps: steps, isEdit: !!isEdit, postApply: postApply };
            this._configs[key] = cfg;

            if (isEdit) {
                this.applyChain(steps, true, cfg);
                return;
            }

            var run = function () {
                self.applyChain(steps, false, cfg);
            };

            steps.forEach(function (s) {
                var el = document.getElementById(s.id);
                if (!el) return;
                el.addEventListener('input', run, sig);
                el.addEventListener('change', run, sig);
            });

            run();
        },

        bindCliente: function (isEdit) {
            var steps = [
                { id: 'dni', validate: function (v) { return w.validarCedulaDominicana(v); } },
                { id: 'telefono', validate: function (v) { return w.validarTelefonoRD(v); } },
                { id: 'nombre', validate: function (v) { return w.validarNombrePersona(v); } },
                { id: 'apellido', validate: function (v) { return w.validarNombrePersona(v); } },
                { id: 'email', validate: function (v) { return w.validarEmailBasico(v); } },
                { id: 'direccion', validate: function (v) { return w.validarDireccionCliente(v); } }
            ];
            this.bind('clientes', steps, isEdit);
        },

        idsCliente: ['dni', 'telefono', 'nombre', 'apellido', 'email', 'direccion'],

        bindMarca: function (isEdit) {
            var steps = [
                { id: 'nombre', validate: function (v) { return w.validarTextoCatalogo(v, 2, 120); } }
            ];
            this.bind('marcas', steps, isEdit);
        },
        idsMarca: ['nombre'],

        bindModelo: function (isEdit) {
            var steps = [
                { id: 'marca_id', validate: function (v) { return String(v || '').trim() !== ''; } },
                { id: 'nombre', validate: function (v) { return w.validarTextoCatalogo(v, 2, 120); } }
            ];
            this.bind('modelos', steps, isEdit);
        },
        idsModelo: ['marca_id', 'nombre'],

        bindGama: function (isEdit) {
            var steps = [
                { id: 'nombre', validate: function (v) { return w.validarTextoCatalogo(v, 2, 120); } },
                { id: 'descripcion', validate: function (v) { return w.validarTextoCatalogo(v, 2, 500); } }
            ];
            this.bind('gamas', steps, isEdit);
        },
        idsGama: ['nombre', 'descripcion'],

        bindTipo: function (isEdit) {
            var steps = [
                { id: 'nombre', validate: function (v) { return w.validarTextoCatalogo(v, 2, 120); } }
            ];
            this.bind('tipos', steps, isEdit);
        },
        idsTipo: ['nombre'],

        bindDocumento: function (isEdit) {
            var steps = [
                { id: 'documento', validate: function (v) { return w.validarTextoCatalogo(v, 2, 150); } }
            ];
            this.bind('documentos', steps, isEdit);
        },
        idsDocumento: ['documento'],

        bindUsuario: function (isEdit) {
            var steps = [
                { id: 'usuario', validate: function (v) { return w.validarUsuarioSistema(v); } },
                { id: 'rol', validate: function (v) { return String(v || '').trim() !== ''; } },
                { id: 'nombre', validate: function (v) { return w.validarNombrePersona(v); } },
                { id: 'apellido', validate: function (v) { return w.validarNombrePersona(v); } },
                { id: 'clave', validate: function (v) { return String(v || '').trim().length > 0; } },
                {
                    id: 'confirmar',
                    validate: function (v) {
                        var c = document.getElementById('clave');
                        return String(v || '').trim() !== '' && c && String(v) === String(c.value);
                    }
                }
            ];
            this.bind('usuarios', steps, isEdit);
        },
        idsUsuario: ['usuario', 'rol', 'nombre', 'apellido', 'clave', 'confirmar'],

        bindMoneda: function (isEdit) {
            var steps = [
                {
                    id: 'simbolo',
                    validate: function (v) {
                        var t = String(v || '').trim();
                        return t.length >= 1 && t.length <= 12;
                    }
                },
                { id: 'nombre', validate: function (v) { return w.validarTextoCatalogo(v, 2, 120); } }
            ];
            this.bind('monedas', steps, isEdit);
        },
        idsMoneda: ['simbolo', 'nombre'],

        bindPrecio: function (isEdit) {
            var steps = [
                { id: 'vehiculo', validate: function (v) { return String(v || '').trim() !== ''; } },
                {
                    id: 'precio',
                    validate: function (v) {
                        var n = parseFloat(String(v || '').replace(',', '.'));
                        return !isNaN(n) && n > 0 && n <= 999999.99;
                    }
                }
            ];
            this.bind('precios', steps, isEdit);
        },
        idsPrecio: ['vehiculo', 'precio'],

        bindTipoDia: function (isEdit) {
            var steps = [
                { id: 'nombre', validate: function (v) { return w.validarTextoCatalogo(v, 2, 120); } }
            ];
            this.bind('tiposdia', steps, isEdit);
        },
        idsTipoDia: ['nombre'],

        bindFeriado: function (isEdit) {
            var steps = [
                {
                    id: 'fecha',
                    validate: function (v) {
                        var t = String(v || '').trim();
                        if (!t) return false;
                        return !isNaN(Date.parse(t));
                    }
                },
                { id: 'descripcion', validate: function (v) { return w.validarTextoCatalogo(v, 2, 500); } }
            ];
            this.bind('feriados', steps, isEdit);
        },
        idsFeriado: ['fecha', 'descripcion'],

        bindVehiculo: function (isEdit) {
            var steps = [
                { 
                    id: 'placa', 
                    validate: function (v, el) { 
                        return w.validarPlacaVehiculo(v) && (!el || el.dataset.placaValida !== '0'); 
                    } 
                },
                {
                    id: 'marca_text',
                    validate: function () {
                        var h = document.getElementById('marca');
                        return !!(h && String(h.value).trim());
                    }
                },
                {
                    id: 'modelo_text',
                    validate: function () {
                        var h = document.getElementById('modelo');
                        return !!(h && String(h.value).trim());
                    }
                },
                { id: 'tipo', validate: function (v) { return String(v || '').trim() !== ''; } },
                { id: 'anio', validate: function (v) { return w.validarAnioVehiculo(v); } },
                { id: 'color', validate: function (v) { return w.validarTextoCatalogo(v, 2, 40); } },
                {
                    id: 'kilometraje',
                    validate: function (v) {
                        return /^\d+$/.test(String(v || '').trim()) && parseInt(v, 10) >= 0;
                    }
                },
                { id: 'combustible', validate: function (v) { return String(v || '').trim() !== ''; } },
                {
                    id: 'precio',
                    peerIds: ['btnAbrirPrecioVehiculo'],
                    validate: function (v) {
                        var n = parseFloat(String(v || '').replace(',', '.'));
                        return !isNaN(n) && n > 0;
                    }
                }
            ];
            var postApply = function () {
                var mtx = document.getElementById('marca_text');
                var mhid = document.getElementById('marca');
                if (mtx && mtx.disabled) {
                    if (mhid) mhid.value = '';
                    if (window.modelosVehiculoCache) window.modelosVehiculoCache = [];
                }
                var modt = document.getElementById('modelo_text');
                var modh = document.getElementById('modelo');
                if (modt && modt.disabled && modh) modh.value = '';
                var pr = document.getElementById('precio');
                if (pr && !String(pr.value || '').trim()) {
                    var badge = document.getElementById('precioBadge');
                    if (badge) badge.classList.add('d-none');
                    var bt = document.getElementById('btnTextPrecio');
                    if (bt) bt.textContent = 'Configurar Precio';
                    var vp = document.getElementById('valPrecio');
                    if (vp) vp.textContent = '0.00';
                }
            };
            this.bind('vehiculos', steps, isEdit, postApply);
        },
        idsVehiculo: ['placa', 'marca_text', 'modelo_text', 'tipo', 'anio', 'color', 'kilometraje', 'combustible', 'precio']
    };
})(window);
