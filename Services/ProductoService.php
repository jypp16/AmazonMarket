<?php

namespace Services;

use Models\ProductoModel;
use Libraries\Core\Validation;

class ProductoService {

    private $model;

    public function __construct() {
        $this->model = new ProductoModel();
    }

    public function crear(array $input): array {
        $validator = new Validation($input);
        $validator->required(['nombre', 'codigo_barra', 'precio_venta'])
            ->minLength('nombre', 3)
            ->maxLength('nombre', 100)
            ->minLength('codigo_barra', 3)
            ->maxLength('codigo_barra', 50)
            ->positiveNumber('precio_venta')
            ->nonNegativeNumber('stock_actual')
            ->nonNegativeNumber('stock_minimo')
            ->unique('codigo_barra', $this->model);

        if ($validator->fails()) {
            return ['status' => false, 'errors' => $validator->errors()];
        }

        $productoData = [
            'codigo_barra' => trim($input['codigo_barra']),
            'nombre' => trim($input['nombre']),
            'descripcion' => trim($input['descripcion'] ?? ''),
            'id_categoria' => intval($input['id_categoria'] ?? 1),
            'id_unidad' => intval($input['id_unidad'] ?? 1),
            'precio_venta' => floatval($input['precio_venta']),
            'stock_actual' => floatval($input['stock_actual'] ?? 0),
            'stock_minimo' => floatval($input['stock_minimo'] ?? 1),
            'estado' => 1
        ];

        if ($this->model->insert($productoData)) {
            return ['status' => true, 'message' => 'Producto creado con éxito.'];
        }
        return ['status' => false, 'message' => 'Error al guardar el producto.'];
    }

    public function actualizar(int $id, array $input): array {
        $validator = new Validation($input);
        $validator->required(['nombre', 'codigo_barra', 'precio_venta'])
            ->minLength('nombre', 3)
            ->maxLength('nombre', 100)
            ->minLength('codigo_barra', 3)
            ->maxLength('codigo_barra', 50)
            ->positiveNumber('precio_venta')
            ->nonNegativeNumber('stock_actual')
            ->nonNegativeNumber('stock_minimo')
            ->unique('codigo_barra', $this->model, $id);

        if ($validator->fails()) {
            return ['status' => false, 'errors' => $validator->errors()];
        }

        $productoData = [
            'codigo_barra' => trim($input['codigo_barra']),
            'nombre' => trim($input['nombre']),
            'descripcion' => trim($input['descripcion'] ?? ''),
            'id_categoria' => intval($input['id_categoria'] ?? 1),
            'id_unidad' => intval($input['id_unidad'] ?? 1),
            'precio_venta' => floatval($input['precio_venta'])
        ];

        if (isset($input['stock_actual'])) $productoData['stock_actual'] = floatval($input['stock_actual']);
        if (isset($input['stock_minimo'])) $productoData['stock_minimo'] = floatval($input['stock_minimo']);

        if ($this->model->update($id, $productoData)) {
            return ['status' => true, 'message' => 'Producto actualizado con éxito.'];
        }
        return ['status' => false, 'message' => 'Error al actualizar el producto.'];
    }

    public function eliminar(int $id): array {
        if ($this->model->update($id, ['estado' => 0])) {
            return ['status' => true, 'message' => 'Producto eliminado con éxito.'];
        }
        return ['status' => false, 'message' => 'Error al eliminar el producto.'];
    }

    public function obtenerTodos(): array {
        return $this->model->obtenerProductosConCategoria();
    }

    public function obtenerPorId(int $id) {
        return $this->model->find($id);
    }

    public function obtenerDetalle(int $id): ?array {
        $producto = $this->model->select(['producto.*', 'unidad_medida.abreviatura as unidad'])
            ->join('unidad_medida', 'producto.id_unidad = unidad_medida.id_unidad', 'INNER')
            ->where(['producto.id_producto' => $id])
            ->get();
        
        return !empty($producto) ? $producto[0] : null;
    }
}
