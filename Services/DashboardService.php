<?php

namespace Services;

use Models\ProductoModel;
use Models\ClienteModel;
use Models\VentaModel;

class DashboardService {

    private $productoModel;
    private $clienteModel;
    private $ventaModel;

    public function __construct() {
        $this->productoModel = new ProductoModel();
        $this->clienteModel = new ClienteModel();
        $this->ventaModel = new VentaModel();
    }

    public function obtenerEstadisticas(): array {
        return [
            'productos' => $this->productoModel->where(['estado' => 1])->count(),
            'clientes' => $this->clienteModel->where(['estado' => 1])->count(),
            'ventas' => $this->ventaModel->where(['estado' => 1])->count(),
            'ingresos' => $this->ventaModel->where(['estado' => 1])->sum('total'),
        ];
    }
}
