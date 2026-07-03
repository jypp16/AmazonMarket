<?php

namespace Controllers;

use Libraries\Core\Controller;
use Services\DashboardService;
use Exception;

class HomeController extends Controller {

    private $service;

    public function __construct() {
        parent::__construct();
        $this->service = new DashboardService();
    }

    public function index() {
        try {
            $estadisticas = $this->service->obtenerEstadisticas();

            $data = array_merge($estadisticas, [
                'page_title' => 'Dashboard - Amazon Market'
            ]);

            $this->views->render($this, "index", $data);
        } catch (Exception $e) {
            die("Error en el Dashboard: " . $e->getMessage());
        }
    }
}
