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
    <title>Test Simple - Búsqueda de Imagen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-danger text-white">
                <h4>🔧 TEST SIMPLE - Búsqueda de Imagen</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Selecciona Modelo ID:</label>
                    <select id="modelo" class="form-select">
                        <option value="">-- Selecciona --</option>
                        <option value="1">1 - Corolla</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Ingresa Año:</label>
                    <input type="number" id="anio" class="form-control" value="2015" min="2000">
                </div>

                <button class="btn btn-danger w-100" onclick="buscarImagen()">
                    🔍 BUSCAR IMAGEN
                </button>

                <hr>

                <div id="resultado" style="display:none;">
                    <h5>✅ RESULTADO:</h5>
                    <div class="text-center mb-3">
                        <img id="previewImg" style="max-width: 100%; max-height: 400px; border-radius: 10px;">
                    </div>
                    <p><strong>Ruta:</strong> <code id="ruta"></code></p>
                    <p><strong>URL:</strong> <code id="url"></code></p>
                </div>

                <div id="error" style="display:none;" class="alert alert-danger"></div>

                <hr>

                <h5>📋 LOGS:</h5>
                <div id="logs" style="
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    font-family: monospace;
                    font-size: 12px;
                    max-height: 200px;
                    overflow-y: auto;
                    white-space: pre-wrap;
                "></div>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = "<?php echo base_url; ?>";
        
        function log(msg) {
            const logs = document.getElementById('logs');
            logs.innerHTML += '[' + new Date().toLocaleTimeString() + '] ' + msg + '\n';
            logs.scrollTop = logs.scrollHeight;
            console.log(msg);
        }

        function buscarImagen() {
            const modelo = document.getElementById('modelo').value;
            const anio = document.getElementById('anio').value;

            document.getElementById('resultado').style.display = 'none';
            document.getElementById('error').style.display = 'none';
            log('🔍 Iniciando búsqueda: modelo=' + modelo + ', anio=' + anio);

            if (!modelo || !anio) {
                log('❌ Faltan datos');
                return;
            }

            const formData = new FormData();
            formData.append('modelo', modelo);
            formData.append('anio', anio);

            const url = baseUrl + 'Vehiculos/obtenerImagenApi';
            log('📤 POST a: ' + url);

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(res => {
                log('📊 Status: ' + res.status);
                return res.text();
            })
            .then(text => {
                log('📨 Response: ' + text.substring(0, 100) + '...');
                const data = JSON.parse(text);
                log('✅ JSON parseado');

                document.getElementById('ruta').textContent = data.img;
                document.getElementById('url').textContent = data.url;
                document.getElementById('previewImg').src = data.url + '?t=' + Date.now();
                document.getElementById('resultado').style.display = 'block';
                log('🎉 Imagen cargada: ' + data.img);
            })
            .catch(err => {
                log('❌ Error: ' + err.message);
                document.getElementById('error').innerHTML = '<strong>Error:</strong> ' + err.message;
                document.getElementById('error').style.display = 'block';
            });
        }

        window.addEventListener('load', () => {
            log('✅ Página cargada');
            log('Base URL: ' + baseUrl);
        });
    </script>
</body>
</html>
