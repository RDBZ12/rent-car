<?php
require_once 'Config/Config.php';
require_once 'Config/App/Query.php';
require_once 'Models/RecepcionesModel.php';

$model = new RecepcionesModel();
$res = $model->buscarClientesConReservasActivas('r'); // Search for 'r'
print_r($res);
