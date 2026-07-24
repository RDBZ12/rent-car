<?php
require_once 'EnvLoader.php';
EnvLoader::load();

function strClean($cadena)
{
    $string = preg_replace(['/\s+/','/^\s|\s$/'],[' ',''], $cadena);
    $string = trim($string);
    $string = stripslashes($string);
    $string = str_ireplace('<script>', '', $string);
    $string = str_ireplace('</script>', '', $string);
    $string = str_ireplace('<script type=>', '', $string);
    $string = str_ireplace('<script src>', '', $string);
    $string = str_ireplace('SELECT * FROM', '', $string);
    $string = str_ireplace('DELETE FROM', '', $string);
    $string = str_ireplace('INSERT INTO', '', $string);
    $string = str_ireplace('SELECT COUNT(*) FROM', '', $string);
    $string = str_ireplace('DROP TABLE', '', $string);
    $string = str_ireplace("OR '1'='1", '', $string);
    $string = str_ireplace('OR ´1´=´1', '', $string);
    $string = str_ireplace('IS NULL', '', $string);
    $string = str_ireplace('LIKE "', '', $string);
    $string = str_ireplace("LIKE '", '', $string);
    $string = str_ireplace('LIKE ´', '', $string);
    $string = str_ireplace('OR "a"="a', '', $string);
    $string = str_ireplace("OR 'a'='a", '', $string);
    $string = str_ireplace('OR ´a´=´a', '', $string);
    $string = str_ireplace('--', '', $string);
    $string = str_ireplace('^', '', $string);
    $string = str_ireplace('[', '', $string);
    $string = str_ireplace(']', '', $string);
    $string = str_ireplace('==', '', $string);
    return $string;
}

function httpGetJsonCurl($url, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (PHP_VERSION_ID < 80000) {
        @curl_close($ch);
    }

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return false;
    }

    $data = json_decode($response, true);
    return is_array($data) ? $data : false;
}

function buscarEnSerpApi($query) {
    $API_KEY = EnvLoader::get('SERPAPI_API_KEY', '');

    if ($API_KEY === '' || $API_KEY === 'PON_AQUI_TU_KEY_SERPAPI') {
        return false;
    }

    $url = 'https://serpapi.com/search.json?q=' . urlencode($query) . '&tbm=isch&ijn=0&api_key=' . urlencode($API_KEY);
    $data = httpGetJsonCurl($url);

    if (!$data || empty($data['images_results'])) {
        return false;
    }

    foreach ($data['images_results'] as $img) {
        $urlImagen = $img['original'] ?? $img['thumbnail'] ?? '';
        if ($urlImagen !== '') {
            return [
                'preview' => $img['thumbnail'] ?? $urlImagen,
                'full' => $urlImagen,
                'source' => 'Google/SerpApi'
            ];
        }
    }

    return false;
}

function buscarEnPexels($query) {
    $API_KEY = EnvLoader::get('PEXELS_API_KEY', '');

    if ($API_KEY === '' || $API_KEY === 'PON_AQUI_TU_KEY_PEXELS') {
        return false;
    }

    $url = 'https://api.pexels.com/v1/search?query=' . urlencode($query) . '&per_page=1';
    $data = httpGetJsonCurl($url, [
        'Authorization: ' . $API_KEY
    ]);

    if (!$data || empty($data['photos'])) {
        return false;
    }

    $foto = $data['photos'][0];

    return [
        'preview' => $foto['src']['medium'] ?? '',
        'full' => $foto['src']['large'] ?? ($foto['src']['original'] ?? ''),
        'source' => 'Pexels'
    ];
}

function buscarEnPixabay($query) {
    $API_KEY = EnvLoader::get('PIXABAY_API_KEY', '');

    if ($API_KEY === '' || $API_KEY === 'PON_AQUI_TU_KEY_PIXABAY') {
        return false;
    }

    $url = 'https://pixabay.com/api/?key=' . urlencode($API_KEY)
        . '&q=' . urlencode($query)
        . '&image_type=photo&category=transportation&per_page=3&safesearch=true';

    $data = httpGetJsonCurl($url);

    if (!$data || empty($data['hits'])) {
        return false;
    }

    $foto = $data['hits'][0];

    return [
        'preview' => $foto['webformatURL'] ?? '',
        'full' => $foto['largeImageURL'] ?? ($foto['webformatURL'] ?? ''),
        'source' => 'Pixabay'
    ];
}

function buscarImagenVehiculo($marca, $modelo, $anio) {
    // Máximo ~3×3×8s en el peor caso (3 APIs × 3 consultas); prioriza términos más específicos
    $consultas = [
        "$marca $modelo $anio",
        "$marca $modelo $anio car",
        "$marca $modelo",
    ];

    foreach ($consultas as $consulta) {
        // SerpApi - Primera opción (Google Images)
        $resultado = buscarEnSerpApi($consulta);
        if ($resultado !== false && !empty($resultado['full'])) return $resultado;

        // Pexels - Segunda opción (Stock professional)
        $resultado = buscarEnPexels($consulta);
        if ($resultado !== false && !empty($resultado['full'])) return $resultado;

        // Pixabay - Tercera opción (Alternative stock)
        $resultado = buscarEnPixabay($consulta);
        if ($resultado !== false && !empty($resultado['full'])) return $resultado;
    }

    return false;
}

/** Descarga binaria con límite de tiempo (evita que file_get_contents cuelgue el request). */
function descargarBinarioCurl($url, $timeoutSeg = 18)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => $timeoutSeg,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; AlquilerVehiculo/1.0)',
    ]);
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (PHP_VERSION_ID < 80000) {
        @curl_close($ch);
    }
    if ($data === false || $code < 200 || $code >= 400) {
        return false;
    }
    return $data;
}

/**
 * Resuelve la imagen del vehículo (marca + modelo + año): usa archivo en uploads si ya existe; si no, busca en APIs y descarga.
 *
 * @return array{path: string, source: 'local'|'api'|'default'}
 */
function resolverImagenVehiculo($marca, $modelo, $anio)
{
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '_', trim($marca) . '_' . trim($modelo) . '_' . trim((string) $anio)));
    $slug = trim($slug, '_');
    if ($slug === '') {
        $slug = 'vehiculo_' . preg_replace('/\D/', '', (string) $anio);
    }
    $nombre = $slug . '.jpg';
    $carpeta = dirname(__DIR__) . '/uploads/vehiculos/';
    if (!@is_dir($carpeta)) {
        @mkdir($carpeta, 0777, true);
    }
    $rutaAbs = $carpeta . $nombre;
    $relPath = 'uploads/vehiculos/' . $nombre;

    if (@is_file($rutaAbs) && @filesize($rutaAbs) > 0) {
        return ['path' => $relPath, 'source' => 'local'];
    }

    $resultado = buscarImagenVehiculo($marca, $modelo, $anio);
    if (is_array($resultado) && !empty($resultado['full'])) {
        $urlRemota = $resultado['full'];
        $contenido = descargarBinarioCurl($urlRemota, 20);
        if ($contenido !== false && strlen($contenido) > 0) {
            if (@file_put_contents($rutaAbs, $contenido) !== false) {
                return ['path' => $relPath, 'source' => 'api'];
            } else {
                // En Vercel Serverless (sistema de archivos de solo lectura), retornar la URL externa directamente
                return ['path' => $urlRemota, 'source' => 'api'];
            }
        }
    }

    return ['path' => 'default.png', 'source' => 'default'];
}

function descargarImagenVehiculo($marca, $modelo, $anio)
{
    $r = resolverImagenVehiculo($marca, $modelo, $anio);
    return $r['path'];
}

/* ---------- Validaciones mantenimientos (servidor) ---------- */

/** Cédula dominicana: 11 dígitos con dígito verificador (algoritmo módulo 10 estándar). */
function validarCedulaDominicana(string $cedula): bool
{
    $c = preg_replace('/\D/', '', $cedula);
    if (strlen($c) !== 11 || !ctype_digit($c)) {
        return false;
    }
    $sum = 0;
    $weights = [1, 2, 1, 2, 1, 2, 1, 2, 1, 2];
    for ($i = 0; $i < 10; $i++) {
        $n = (int) $c[$i] * $weights[$i];
        $sum += intdiv($n, 10) + ($n % 10);
    }
    $verif = (10 - ($sum % 10)) % 10;
    return $verif === (int) $c[10];
}

/** Nombres y apellidos: solo letras (incl. tildes/ñ), espacios, apóstrofe y guion. */
function validarNombrePersona(string $s): bool
{
    $t = trim($s);
    if (strlen($t) < 2 || strlen($t) > 80) {
        return false;
    }
    return (bool) preg_match('/^[\p{L}\s\'\-]+$/u', $t);
}

/** Teléfono RD: 10 dígitos; acepta opcional prefijo país 1. */
function validarTelefonoRepublicaDominicana(string $tel): bool
{
    $n = preg_replace('/\D/', '', $tel);
    if (strlen($n) === 11 && substr($n, 0, 1) === '1') {
        $n = substr($n, 1);
    }
    return strlen($n) === 10 && ctype_digit($n);
}

function validarEmailBasico(string $email): bool
{
    return (bool) filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}

function validarDireccionCliente(string $d): bool
{
    $t = trim($d);
    return strlen($t) >= 5 && strlen($t) <= 500;
}

/** Marca, modelo, gama, tipo, documento: letras, números, espacios y signos comunes. */
function validarTextoCatalogo(string $s, int $min = 2, int $max = 120): bool
{
    $t = trim($s);
    if (strlen($t) < $min || strlen($t) > $max) {
        return false;
    }
    return (bool) preg_match('/^[\p{L}0-9\s\'\.\,\-\_\#\/\(\)\:]+$/u', $t);
}

function validarUsuarioSistema(string $u): bool
{
    return (bool) preg_match('/^[a-zA-Z0-9._\-]{3,50}$/', $u);
}

function validarPlacaVehiculo(string $p): bool
{
    $t = strtoupper(preg_replace('/\s+/', '', trim($p)));
    return strlen($t) >= 3 && strlen($t) <= 15 && (bool) preg_match('/^[A-Z0-9\-]+$/', $t);
}

function validarAnioVehiculo(string $anio): bool
{
    if (!ctype_digit($anio)) {
        return false;
    }
    $y = (int) $anio;
    return $y >= 1950 && $y <= ((int) date('Y')) + 1;
}

function validarNumeroEnteroNoNegativo(string $v, int $max = 9999999): bool
{
    if (!ctype_digit($v)) {
        return false;
    }
    $n = (int) $v;
    return $n >= 0 && $n <= $max;
}

function loadSessionFromCookie()
{
    if (session_status() === PHP_SESSION_NONE) {
        if (is_dir('/tmp') && is_writable('/tmp')) {
            @session_save_path('/tmp');
        }
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_path', '/');
        session_start();
    }
    if (empty($_SESSION['activo']) && !empty($_COOKIE['RENT_CAR_SESS'])) {
        $parts = explode('.', $_COOKIE['RENT_CAR_SESS'], 2);
        if (count($parts) === 2) {
            $json = base64_decode($parts[0]);
            $sig  = $parts[1];
            $secret = defined('DB_PASS') && !empty(DB_PASS) ? DB_PASS : 'rent_car_secure_key_2026';
            $expectedSig = hash_hmac('sha256', $json, $secret);
            if (hash_equals($expectedSig, $sig)) {
                $data = json_decode($json, true);
                if (is_array($data) && !empty($data['activo']) && isset($data['exp']) && $data['exp'] > time()) {
                    $_SESSION['id_usuario'] = $data['id_usuario'];
                    $_SESSION['usuario']    = $data['usuario'];
                    $_SESSION['nombre']     = $data['nombre'];
                    $_SESSION['apellido']   = $data['apellido'];
                    $_SESSION['perfil']     = $data['perfil'] ?? 'default.png';
                    $_SESSION['rol']        = $data['rol'] ?? '';
                    $_SESSION['activo']     = true;
                }
            }
        }
    }
}

function saveSessionCookie()
{
    if (!empty($_SESSION['activo'])) {
        $data = [
            'id_usuario' => $_SESSION['id_usuario'] ?? null,
            'usuario'    => $_SESSION['usuario'] ?? '',
            'nombre'     => $_SESSION['nombre'] ?? '',
            'apellido'   => $_SESSION['apellido'] ?? '',
            'perfil'     => $_SESSION['perfil'] ?? 'default.png',
            'rol'        => $_SESSION['rol'] ?? '',
            'activo'     => true,
            'exp'        => time() + 86400 * 7
        ];
        $json = json_encode($data);
        $secret = defined('DB_PASS') && !empty(DB_PASS) ? DB_PASS : 'rent_car_secure_key_2026';
        $sig = hash_hmac('sha256', $json, $secret);
        $cookieVal = base64_encode($json) . '.' . $sig;
        if (!headers_sent()) {
            setcookie('RENT_CAR_SESS', $cookieVal, [
                'expires'  => time() + 86400 * 7,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }
}

?>