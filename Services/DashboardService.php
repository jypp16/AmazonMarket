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
            'productos' => $this->productoModel->count('estado = 1'),
            'clientes' => $this->clienteModel->count('estado = 1'),
            'ventas' => $this->ventaModel->count('estado = 1'),
            'ingresos' => $this->ventaModel->sum('total', 'estado = 1'),
        ];
    }
}
