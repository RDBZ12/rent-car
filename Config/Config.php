<?php
// Autodetectar el protocolo y el host actual dinámicamente
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$protocol = $isHttps ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? '';

if (!empty($host) && $host !== 'localhost' && $host !== '127.0.0.1') {
    // En Vercel / Nube: usar SIEMPRE el dominio real desde el que accede el navegador
    $baseUrl = $protocol . $host . "/";
} else if (getenv('BASE_URL')) {
    $baseUrl = getenv('BASE_URL');
} else {
    $baseUrl = "http://localhost/alquiler/";
}

if (substr($baseUrl, -1) !== '/') {
    $baseUrl .= '/';
}

$dbHost  = getenv('DB_HOST')  ? getenv('DB_HOST')  : "localhost";
$dbUser  = getenv('DB_USER')  ? getenv('DB_USER')  : "root";
$dbPass  = getenv('DB_PASS')  !== false ? getenv('DB_PASS')  : "";
$dbName  = getenv('DB_NAME')  ? getenv('DB_NAME')  : "rect_car";
$dbPort  = getenv('DB_PORT')  ? getenv('DB_PORT')  : "3306";

define("base_url", $baseUrl);
define("host", $dbHost);
define("user", $dbUser);
define("pass", $dbPass);
define("db", $dbName);
define("db_port", $dbPort);
define("charset", "charset=utf8");
?>