<?php
session_start();
$_SESSION['activo'] = true;
$_SESSION['id_usuario'] = 1;

require_once 'Config/Config.php';
require_once 'Config/App/Autoload.php';
require_once 'Config/Helpers.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIAGNÓSTICO - Búsqueda de Imagen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light p-5">
    <div class="container">
        <div class="card shadow-lg">
            <div class="card-header bg-danger text-white">
                <h3>🔧 DIAGNÓSTICO COMPLETO - BÚSQUEDA DE IMAGEN</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Instrucciones:</strong> Esta página hace un test completo del sistema.
                    Abre la consola (F12) y observa todos los logs.
                </div>

                <hr>

                <h5>1. TEST DE ELEMENTOS HTML</h5>
                <div id="test-elementos"></div>

                <hr>

                <h5>2. TEST DE JAVASCRIPT</h5>
                <div id="test-js"></div>

                <hr>

                <h5>3. TEST DE FUNCIONES</h5>
                <div id="test-funciones"></div>

                <hr>

                <h5>4. BUSCAR IMAGEN MANUALMENTE</h5>
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" id="marca" class="form-control mb-2" placeholder="Marca" value="Toyota">
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="modelo" class="form-control mb-2" placeholder="Modelo" value="Corolla">
                    </div>
                    <div class="col-md-4">
                        <input type="number" id="anio" class="form-control mb-2" placeholder="Año" value="2005">
                    </div>
                </div>
                <button class="btn btn-danger w-100 mb-3" onclick="testBusqueda()">
                    🔍 TEST BÚSQUEDA
                </button>
                <div id="resultado-busqueda"></div>

                <hr>

                <h5>📋 LOGS COMPLETOS:</h5>
                <div id="logs" style="
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    font-family: monospace;
                    font-size: 12px;
                    max-height: 400px;
                    overflow-y: auto;
                    white-space: pre-wrap;
                    border: 2px solid #dee2e6;
                "></div>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = "<?php echo base_url; ?>";
        let testLog = [];

        function log(msg, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
            const line = `[${timestamp}] ${icon} ${msg}`;
            testLog.push(line);
            console.log(msg);
            document.getElementById('logs').innerHTML = testLog.join('\n');
            document.getElementById('logs').scrollTop = document.getElementById('logs').scrollHeight;
        }

        function addStatus(id, status, message) {
            const elem = document.getElementById(id);
            if (!elem) return;
            const icon = status ? '✅' : '❌';
            elem.innerHTML += `<div><span>${icon}</span> ${message}</div>`;
        }

        window.addEventListener('load', () => {
            log('=== INICIANDO DIAGNÓSTICO ===', 'info');

            // TEST 1: Elementos HTML
            log('TEST 1: Verificando elementos HTML');
            const elementos = {
                'modelo': document.getElementById('modelo'),
                'anio': document.getElementById('anio'),
                'img-preview': document.getElementById('img-preview'),
                'foto_actual': document.getElementById('foto_actual'),
                'img-loading': document.getElementById('img-loading'),
                'no-image-placeholder': document.getElementById('no-image-placeholder'),
                'icon-cerrar': document.getElementById('icon-cerrar')
            };

            Object.keys(elementos).forEach(key => {
                const existe = elementos[key] !== null;
                log(`  ${existe ? '✅' : '❌'} Elemento "${key}": ${existe ? 'Existe' : 'NO EXISTE'}`, existe ? 'success' : 'error');
                addStatus('test-elementos', existe, `${key}: ${existe ? 'OK' : 'FALTA'}`);
            });

            // TEST 2: JavaScript
            log('\nTEST 2: Verificando funciones JavaScript');
            const funciones = {
                'actualizarPreviewImagen': typeof actualizarPreviewImagen,
                'ejecutarBusquedaImagen': typeof ejecutarBusquedaImagen,
                'debounceTimer': typeof debounceTimer,
                'isSearching': typeof isSearching
            };

            Object.keys(funciones).forEach(key => {
                const existe = funciones[key] !== 'undefined';
                log(`  ${existe ? '✅' : '❌'} ${key}: ${funciones[key]}`, existe ? 'success' : 'error');
                addStatus('test-js', existe, `${key}: ${existe ? 'OK' : 'FALTA'}`);
            });

            // TEST 3: Variables globales
            log('\nTEST 3: Verificando variables globales');
            log(`  base_url: ${base_url}`, 'success');
            addStatus('test-funciones', true, `base_url: ${base_url}`);

            log('\n=== DIAGNÓSTICO COMPLETADO ===', 'success');
        });

        function testBusqueda() {
            const marca = document.getElementById('marca').value;
            const modelo = document.getElementById('modelo').value;
            const anio = document.getElementById('anio').value;

            log(`\n🔍 TEST BÚSQUEDA: ${marca} ${modelo} ${anio}`);

            const formData = new FormData();
            formData.append('modelo', '1'); // ID del modelo Corolla
            formData.append('anio', anio);

            const url = baseUrl + 'Vehiculos/obtenerImagenApi';
            log(`POST a: ${url}`);

            fetch(url, { method: 'POST', body: formData })
                .then(res => {
                    log(`Status: ${res.status}`);
                    return res.text();
                })
                .then(text => {
                    log(`Response: ${text.substring(0, 150)}...`);
                    const data = JSON.parse(text);
                    
                    if (data.img && data.img !== 'default.png') {
                        log(`✅ Imagen encontrada: ${data.img}`, 'success');
                        document.getElementById('resultado-busqueda').innerHTML = `
                            <div class="alert alert-success">
                                <strong>✅ Éxito!</strong><br>
                                Imagen: <code>${data.img}</code><br>
                                URL: <code>${data.url}</code>
                            </div>
                            <img src="${data.url}?t=${Date.now()}" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                        `;
                    } else {
                        log(`⚠️ Imagen default`, 'warning');
                    }
                })
                .catch(err => {
                    log(`❌ Error: ${err.message}`, 'error');
                    document.getElementById('resultado-busqueda').innerHTML = `
                        <div class="alert alert-danger">
                            <strong>❌ Error:</strong> ${err.message}
                        </div>
                    `;
                });
        }
    </script>
</body>
</html>
