<?php
require_once 'Config/Config.php';
require_once 'Config/App/Query.php';

class DBCheck extends Query {
    public function __construct() {
        parent::__construct();
    }
    public function check() {
        return $this->selectAll("DESCRIBE reservas");
    }
}

$db = new DBCheck();
$res = $db->check();
echo json_encode($res);
?>
