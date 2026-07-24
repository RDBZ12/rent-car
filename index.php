<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    if (is_dir('/tmp') && is_writable('/tmp')) {
        @session_save_path('/tmp');
    }
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_path', '/');
    session_start();
}
require_once 'Config/Config.php';
if (!empty($_GET['url'])) {
    $ruta = $_GET['url'];
} else {
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $ruta = trim($requestUri, '/');
    if (empty($ruta)) {
        $ruta = "Home/index";
    }
}
$array = explode("/", $ruta);
$controller = $array[0];
$metodo = "index";
$parametro = "";
if (!empty($array[1])) {
    if (!empty($array[1] != "")) {
        $metodo = $array[1];
    }
}
if (!empty($array[2])) {
    if (!empty($array[2] != "")) {
        for ($i = 2; $i < count($array); $i++) {
            $parametro .= $array[$i] . ",";
        }
        $parametro = trim($parametro, ",");
    }
}
require_once 'Config/App/Autoload.php';
require_once 'Config/Helpers.php';
$dirControllers = "Controllers/" . $controller . ".php";
if (file_exists($dirControllers)) {
    require_once $dirControllers;
    $controller = new $controller();
    if (method_exists($controller, $metodo)) {
        $controller->$metodo($parametro);
    } else {
        header('Location: '.base_url.'Errors');
    }
} else {
    header('Location: ' . base_url . 'Errors');
}
?>