<?php
$baseUrl = getenv('BASE_URL') ? getenv('BASE_URL') : "http://localhost/alquiler/";
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