<?php
require_once "Config/Config.php";

$connectionString = "mysql:host=" . host . ";dbname=" . db . ";charset=utf8mb4";
try {
    $pdo = new PDO($connectionString, user, pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking for column 'observacion' in 'reservas'...\n";
    $cols = $pdo->query("DESCRIBE reservas")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('observacion', $cols)) {
        $pdo->exec("ALTER TABLE reservas ADD COLUMN observacion TEXT AFTER total");
        echo "Column 'observacion' added to 'reservas'.\n";
    } else {
        echo "Column 'observacion' already exists.\n";
    }

    echo "Checking for column 'estado' in 'reserva_vehiculos'...\n";
    $cols = $pdo->query("DESCRIBE reserva_vehiculos")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('estado', $cols)) {
        $pdo->exec("ALTER TABLE reserva_vehiculos ADD COLUMN estado VARCHAR(20) DEFAULT 'Pendiente'");
        echo "Column 'estado' added to 'reserva_vehiculos'.\n";
    } else {
        echo "Column 'estado' already exists.\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
