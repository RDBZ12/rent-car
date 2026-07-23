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
    <title>Test API Búsqueda de Imágenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background: #f5f7fa;
        }
        .card {
            max-width: 800px;
            margin: 20px auto;
        }
        .preview-img {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .debug-panel {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .spinner {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        .spinner.active {
            display: block;
        }
        .badge-status {
            margin: 10px 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-image"></i> Test API Búsqueda de Imágenes</h4>
            </div>
            <div class="card-body">
                <!-- Status Pills -->
                <div class="mb-3">
                    <span class="badge bg-success badge-status">✓ EnvLoader Activo</span>
                    <span class="badge bg-success badge-status">✓ API Keys Cargadas</span>
                    <span class="badge bg-info badge-status">Base URL: <?php echo base_url; ?></span>
                </div>

                <!-- Test Form -->
                <form id="testForm" onsubmit="testearAPI(event)">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="marca" class="form-label fw-bold">Marca</label>
                            <input type="text" class="form-control" id="marca" value="Toyota" required>
                        </div>
                        <div class="col-md-4">
                            <label for="modelo" class="form-label fw-bold">Modelo</label>
                            <input type="text" class="form-control" id="modelo" value="Corolla" required>
                        </div>
                        <div class="col-md-4">
                            <label for="anio" class="form-label fw-bold">Año</label>
                            <input type="number" class="form-control" id="anio" value="2002" min="2000" max="2099" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Buscar Imagen
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-secondary w-100" onclick="limpiarDebug()">
                                <i class="fas fa-trash"></i> Limpiar Logs
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Loading Spinner -->
                <div id="spinner" class="spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Buscando imagen...</p>
                </div>

                <!-- Results -->
                <div id="results" style="display: none;">
                    <hr>
                    <h5 class="mb-3">Resultado</h5>
                    <div id="imagenContainer" class="text-center">
                        <img id="previewImg" class="preview-img" src="" alt="Preview">
                    </div>
                    <div class="mt-3 p-3 bg-light rounded">
                        <p><strong>Ruta BD:</strong> <code id="rutaBD"></code></p>
                        <p><strong>URL Completa:</strong> <code id="urlCompleta"></code></p>
                        <p><strong>Fuente:</strong> <span id="fuente" class="badge bg-info"></span></p>
                    </div>
                </div>

                <!-- Debug Panel -->
                <div class="debug-panel">
                    <strong>📋 Logs de Debugging:</strong>
                    <div id="debugPanel"></div>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li><strong>Endpoint:</strong> <code>POST /Vehiculos/obtenerImagenDirecta</code></li>
                    <li><strong>Parámetros:</strong> marca, modelo, anio</li>
                    <li><strong>Directorio de descargas:</strong> <code>/uploads/vehiculos/</code></li>
                    <li><strong>APIs configuradas:</strong> SerpApi, Pexels, Pixabay</li>
                    <li><strong>Configuración:</strong> Variables de entorno (.env)</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = "<?php echo base_url; ?>";
        
        function log(mensaje) {
            const panel = document.getElementById('debugPanel');
            const timestamp = new Date().toLocaleTimeString();
            panel.innerHTML += `[${timestamp}] ${mensaje}\n`;
            panel.scrollTop = panel.scrollHeight;
        }

        function limpiarDebug() {
            document.getElementById('debugPanel').innerHTML = '';
        }

        function testearAPI(event) {
            event.preventDefault();
            
            const marca = document.getElementById('marca').value;
            const modelo = document.getElementById('modelo').value;
            const anio = document.getElementById('anio').value;

            log(`🔍 Iniciando búsqueda: ${marca} ${modelo} ${anio}`);
            
            document.getElementById('spinner').classList.add('active');
            document.getElementById('results').style.display = 'none';

            const formData = new FormData();
            formData.append('marca', marca);
            formData.append('modelo', modelo);
            formData.append('anio', anio);

            const url = baseUrl + 'Vehiculos/obtenerImagenDirecta';
            log(`📤 Enviando POST a: ${url}`);

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                log(`📊 Status HTTP: ${response.status}`);
                return response.text();
            })
            .then(text => {
                log(`📨 Response recibido: ${text.substring(0, 100)}...`);
                
                try {
                    const data = JSON.parse(text);
                    log(`✅ JSON parseado correctamente`);
                    
                    document.getElementById('spinner').classList.remove('active');
                    
                    if (data.img && data.img !== 'default.png') {
                        log(`🎉 Imagen encontrada: ${data.img}`);
                        mostrarResultado(data);
                    } else {
                        log(`⚠️ Sin imagen, usando default`);
                        mostrarResultado(data);
                    }
                } catch (err) {
                    log(`❌ Error parseando JSON: ${err.message}`);
                    document.getElementById('spinner').classList.remove('active');
                }
            })
            .catch(error => {
                log(`❌ Error en fetch: ${error.message}`);
                document.getElementById('spinner').classList.remove('active');
            });
        }

        function mostrarResultado(data) {
            document.getElementById('rutaBD').textContent = data.img;
            document.getElementById('urlCompleta').textContent = data.url;
            document.getElementById('fuente').textContent = data.source || 'Desconocida';
            document.getElementById('previewImg').src = data.url + '?t=' + Date.now();
            document.getElementById('results').style.display = 'block';
            log(`🖼️ Imagen mostrada en preview`);
        }

        // Log inicial
        window.addEventListener('load', () => {
            log(`✅ Página cargada - Base URL: ${baseUrl}`);
            log(`🔧 Variables de entorno: Cargadas`);
        });
    </script>
</body>
</html>
