<?php
class Home extends Controller
{
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        parent::__construct();
    }
    public function index()
    {
        if (!empty($_SESSION['activo'])) {
            header("location: " . base_url . "Administracion/home");
            exit();
        }
        $this->views->getView($this, "index");
    }
}
