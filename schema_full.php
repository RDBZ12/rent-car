<?php
require_once "Config/Config.php";

$connectionString = "mysql:host=" . host . ";dbname=" . db . ";charset=utf8mb4";
try {
    $pdo = new PDO($connectionString, user, pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = ['pagos', 'pago_detalle', 'clientes', 'vehiculos'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("DESCRIBE $table");
        echo "=== $table ===\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
