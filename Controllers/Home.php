<?php
class Home extends Controller
{
    public function __construct() {
        session_start();
        session_destroy();
        parent::__construct();
    }
    public function index()
    {
        $this->views->getView($this, "index");
    }
}
