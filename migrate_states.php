<?php
require_once 'Config/Config.php';
require_once 'Config/App/Conexion.php';
require_once 'Config/App/Query.php';

class Migration extends Query {
    public function __construct() {
        parent::__construct();
    }
    public function run() {
        $queries = [
            "UPDATE reservas SET estado = 'Activa' WHERE estado = 'Pendiente' OR estado = 'Activa'",
            "UPDATE reservas SET estado = 'Devuelta' WHERE estado = 'Finalizada' OR estado = 'Devuelto con deuda' OR estado = 'Devuelto'",
            "UPDATE reservas SET estado = 'Cancelada' WHERE estado = 'Cancelada'"
        ];
        
        $results = [];
        foreach ($queries as $sql) {
            $results[] = $this->save($sql, []);
        }
        return $results;
    }
}

$m = new Migration();
$res = $m->run();
echo "Migración completada. Resultados: " . json_encode($res);
?>
