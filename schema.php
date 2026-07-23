<?php
require_once "Config/Config.php";

$connectionString = "mysql:host=" . host . ";dbname=" . db . ";charset=utf8mb4";
try {
    $pdo = new PDO($connectionString, user, pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("DESCRIBE reserva_vehiculos");
    echo "=== reserva_vehiculos ===\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    $stmt = $pdo->query("DESCRIBE reservas");
    echo "\n=== reservas ===\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
