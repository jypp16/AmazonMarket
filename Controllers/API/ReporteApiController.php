<?php

namespace Controllers\API;

use Libraries\Core\ApiController;
use Services\ReporteService;

class ReporteApiController extends ApiController {

    private $service;

    public function __construct() {
        parent::__construct();
        $this->service = new ReporteService();
    }

    public function get(?string $params = ""): void {
        $this->requirePermission('reportes.ver');

        $tipo = $params ?? '';
        $input = $_GET;

        switch ($tipo) {
            case 'ventas':
                $this->sendJsonResponse(['status' => true, 'data' => $this->service->reporteVentas($input)]);
                break;
            case 'productos-mas-vendidos':
                $this->sendJsonResponse(['status' => true, 'data' => $this->service->reporteProductosMasVendidos($input)]);
                break;
            case 'productos-menos-vendidos':
                $this->sendJsonResponse(['status' => true, 'data' => $this->service->reporteProductosMenosVendidos($input)]);
                break;
            case 'inventario':
                $this->sendJsonResponse(['status' => true, 'data' => $this->service->reporteInventario()]);
                break;
            case 'clientes':
                $this->sendJsonResponse(['status' => true, 'data' => $this->service->reporteClientes($input)]);
                break;
            case 'vendedores':
                $this->sendJsonResponse(['status' => true, 'data' => $this->service->reporteVendedores($input)]);
                break;
            case 'categorias':
                $this->sendJsonResponse(['status' => true, 'data' => $this->service->reporteCategorias($input)]);
                break;
            case 'comprobantes':
                $this->sendJsonResponse(['status' => true, 'data' => $this->service->reporteComprobantes($input)]);
                break;
            case 'metodos-pago':
                $this->sendJsonResponse(['status' => true, 'data' => $this->service->reporteMetodosPago($input)]);
                break;
            case 'resumen':
                $this->sendJsonResponse(['status' => true, 'data' => $this->service->reporteResumen($input)]);
                break;
            case 'exportar-csv':
                $this->requirePermission('reportes.exportar');
                $this->exportarCsv($input);
                break;
            default:
                $this->sendJsonResponse(['status' => false, 'message' => 'Tipo de reporte no válido.'], 400);
        }
    }

    private function exportarCsv(array $input): void {
        $tipo = $input['tipo'] ?? '';
        $filename = 'reporte_' . $tipo . '_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        switch ($tipo) {
            case 'ventas':
                $data = $this->service->reporteVentas($input);
                fputcsv($output, ['Fecha', 'Serie', 'Número', 'Cliente', 'Comprobante', 'Total', 'Vendedor', 'Método Pago']);
                foreach ($data['ventas'] as $v) {
                    fputcsv($output, [$v['fecha_venta'], $v['serie'], $v['numero'], $v['cliente'], $v['comprobante'], $v['total'], $v['vendedor'], $v['metodo_pago']]);
                }
                break;
            case 'productos-mas-vendidos':
                $data = $this->service->reporteProductosMasVendidos($input);
                fputcsv($output, ['Producto', 'Categoría', 'Unidades Vendidas', 'Ingresos', '% Ingresos']);
                foreach ($data['productos'] as $p) {
                    fputcsv($output, [$p['nombre'], $p['categoria'], $p['unidades_vendidas'], $p['ingresos'], $p['porcentaje_ingresos'] . '%']);
                }
                break;
            case 'productos-menos-vendidos':
                $data = $this->service->reporteProductosMenosVendidos($input);
                fputcsv($output, ['Producto', 'Categoría', 'Stock Actual', 'Stock Mínimo', 'Precio', 'Unidades Vendidas', 'Ingresos']);
                foreach ($data['productos'] as $p) {
                    fputcsv($output, [$p['nombre'], $p['categoria'], $p['stock_actual'], $p['stock_minimo'], $p['precio_venta'], $p['unidades_vendidas'], $p['ingresos']]);
                }
                break;
            case 'inventario':
                $data = $this->service->reporteInventario();
                fputcsv($output, ['Código', 'Producto', 'Categoría', 'Stock Actual', 'Stock Mínimo', 'Precio Venta', 'Valor Stock', 'Stock Bajo']);
                foreach ($data['productos'] as $p) {
                    fputcsv($output, [$p['codigo_barra'], $p['nombre'], $p['categoria'], $p['stock_actual'], $p['stock_minimo'], $p['precio_venta'], $p['valor_stock'], $p['stock_bajo'] ? 'SÍ' : 'NO']);
                }
                break;
            case 'clientes':
                $data = $this->service->reporteClientes($input);
                fputcsv($output, ['Cliente', 'Documento', '# Compras', 'Total Gastado', 'Ticket Promedio', 'Última Compra']);
                foreach ($data['clientes'] as $c) {
                    fputcsv($output, [$c['nombre'], $c['tipo_doc'] . ' ' . $c['nro_documento'], $c['num_compras'], $c['total_gastado'], $c['ticket_promedio'], $c['ultima_compra']]);
                }
                break;
            case 'vendedores':
                $data = $this->service->reporteVendedores($input);
                fputcsv($output, ['Vendedor', '# Ventas', 'Ingreso Total', 'Ticket Promedio', '% del Total']);
                foreach ($data['vendedores'] as $v) {
                    fputcsv($output, [$v['vendedor'], $v['num_ventas'], $v['ingreso_total'], $v['ticket_promedio'], $v['porcentaje'] . '%']);
                }
                break;
            case 'categorias':
                $data = $this->service->reporteCategorias($input);
                fputcsv($output, ['Categoría', 'Unidades', 'Ingresos', '% del Total']);
                foreach ($data['categorias'] as $c) {
                    fputcsv($output, [$c['categoria'], $c['unidades'], $c['ingresos'], $c['porcentaje'] . '%']);
                }
                break;
            case 'comprobantes':
                $data = $this->service->reporteComprobantes($input);
                fputcsv($output, ['Comprobante', 'Serie', 'Cantidad', 'Total']);
                foreach ($data['comprobantes'] as $c) {
                    fputcsv($output, [$c['comprobante'], $c['serie'], $c['cantidad'], $c['total']]);
                }
                break;
            case 'metodos-pago':
                $data = $this->service->reporteMetodosPago($input);
                fputcsv($output, ['Método de Pago', '# Transacciones', 'Monto Total', '% del Total']);
                foreach ($data['metodos'] as $m) {
                    fputcsv($output, [$m['metodo_pago'], $m['num_transacciones'], $m['monto_total'], $m['porcentaje'] . '%']);
                }
                break;
            default:
                fputcsv($output, ['Tipo de reporte no válido']);
        }

        fclose($output);
        exit;
    }
}
