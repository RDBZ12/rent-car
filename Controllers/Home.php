<?php
class Home extends Controller
{
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        session_destroy();
        parent::__construct();
    }
    public function index()
    {
        $this->views->getView($this, "index");
    }
}
