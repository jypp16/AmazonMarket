<?php

namespace Controllers\API;

use Libraries\Core\ApiController;
use Models\CategoriaModel;
use Models\UnidadMedidaModel;

class ProductoApiController extends ApiController {

    public function index(?string $params = ""): void {
        $this->requirePermission('productos.listar');

        switch ($this->requestMethod) {
            case 'GET':
                $this->getProductos($params);
                break;
            case 'POST':
                $this->createProducto();
                break;
            case 'PUT':
                $this->updateProducto($params);
                break;
            case 'DELETE':
                $this->deleteProducto($params);
                break;
            default:
                header("Allow: GET, POST, PUT, DELETE");
                $this->sendJsonResponse(['status' => false, 'message' => 'Método HTTP no permitido en este recurso'], 405);
                break;
        }
    }

    private function getProductos(?string $id): void {
        if (!empty($id)) {
            $idVal = intval($id);
            $producto = $this->model
                ->select(['producto.*', 'categoria.nombre as categoria', 'unidad_medida.abreviatura as unidad'])
                ->join('categoria', 'producto.id_categoria = categoria.id_categoria', 'INNER')
                ->join('unidad_medida', 'producto.id_unidad = unidad_medida.id_unidad', 'INNER')
                ->where(['producto.id_producto' => $idVal, 'producto.estado = 1'])
                ->first();

            if (!$producto) {
                $this->sendJsonResponse(['status' => false, 'message' => 'El producto solicitado no existe'], 404);
            }
            $this->sendJsonResponse(['status' => true, 'data' => $producto]);
        } else {
            $search = $this->getParam('q', '');
            $categoria = $this->getParam('categoria', '');
            $stock = $this->getParam('stock', '');

            $query = $this->model
                ->select(['producto.*', 'categoria.nombre as categoria', 'unidad_medida.abreviatura as unidad'])
                ->join('categoria', 'producto.id_categoria = categoria.id_categoria', 'INNER')
                ->join('unidad_medida', 'producto.id_unidad = unidad_medida.id_unidad', 'INNER')
                ->where(['producto.estado = 1']);

            if (!empty($search)) {
                $query->where(["(producto.nombre LIKE '%$search%' OR producto.codigo_barra LIKE '%$search%')"]);
            }
            if (!empty($categoria)) {
                $query->where(['producto.id_categoria' => intval($categoria)]);
            }
            if ($stock === 'bajo') {
                $query->where(['producto.stock_actual <= producto.stock_minimo']);
            }

            $productos = $query->orderBy('producto.nombre', 'ASC')->get();
            $this->sendJsonResponse(['status' => true, 'data' => $productos]);
        }
    }

    private function createProducto(): void {
        $this->requirePermission('productos.crear');

        $data = $this->getInput();

        if (empty($data['nombre']) || empty($data['codigo_barra']) || empty($data['precio_venta'])) {
            $this->sendJsonResponse(['status' => false, 'message' => 'Faltan campos obligatorios: nombre, codigo_barra, precio_venta'], 400);
        }

        $validator = new \Libraries\Core\Validation($data);
        $validator->required(['nombre', 'codigo_barra', 'precio_venta'])
            ->minLength('nombre', 3)
            ->maxLength('nombre', 100)
            ->minLength('codigo_barra', 3)
            ->maxLength('codigo_barra', 50)
            ->positiveNumber('precio_venta')
            ->unique('codigo_barra', $this->model);

        if ($validator->fails()) {
            $this->sendJsonResponse(['status' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
        }

        $productoData = [
            'codigo_barra' => htmlspecialchars(strip_tags(trim($data['codigo_barra']))),
            'nombre' => htmlspecialchars(strip_tags(trim($data['nombre']))),
            'descripcion' => trim($data['descripcion'] ?? ''),
            'id_categoria' => intval($data['id_categoria'] ?? 1),
            'id_unidad' => intval($data['id_unidad'] ?? 1),
            'precio_venta' => floatval($data['precio_venta']),
            'stock_actual' => floatval($data['stock_actual'] ?? 0),
            'stock_minimo' => floatval($data['stock_minimo'] ?? 1),
            'estado' => 1
        ];

        $exito = $this->model->insert($productoData);

        if ($exito) {
            $this->sendJsonResponse(['status' => true, 'message' => 'Producto registrado exitosamente'], 201);
        } else {
            $this->sendJsonResponse(['status' => false, 'message' => 'Ocurrió un error interno en el servidor'], 500);
        }
    }

    private function updateProducto(?string $id): void {
        $this->requirePermission('productos.editar');

        if (empty($id)) {
            $this->sendJsonResponse(['status' => false, 'message' => 'El identificador del producto es requerido'], 400);
        }

        $idVal = intval($id);
        $productoExistente = $this->model->find($idVal);

        if (!$productoExistente || $productoExistente['estado'] != 1) {
            $this->sendJsonResponse(['status' => false, 'message' => 'El recurso a modificar no existe'], 404);
        }

        $data = $this->getInput();
        $datosActualizar = [];

        if (isset($data['nombre'])) $datosActualizar['nombre'] = htmlspecialchars(strip_tags(trim($data['nombre'])));
        if (isset($data['codigo_barra'])) $datosActualizar['codigo_barra'] = htmlspecialchars(strip_tags(trim($data['codigo_barra'])));
        if (isset($data['descripcion'])) $datosActualizar['descripcion'] = trim($data['descripcion']);
        if (isset($data['id_categoria'])) $datosActualizar['id_categoria'] = intval($data['id_categoria']);
        if (isset($data['id_unidad'])) $datosActualizar['id_unidad'] = intval($data['id_unidad']);
        if (isset($data['precio_venta'])) $datosActualizar['precio_venta'] = floatval($data['precio_venta']);
        if (isset($data['stock_actual'])) $datosActualizar['stock_actual'] = floatval($data['stock_actual']);
        if (isset($data['stock_minimo'])) $datosActualizar['stock_minimo'] = floatval($data['stock_minimo']);

        if (empty($datosActualizar)) {
            $this->sendJsonResponse(['status' => false, 'message' => 'No se proporcionaron datos para actualizar'], 400);
        }

        if (isset($data['codigo_barra'])) {
            $validator = new \Libraries\Core\Validation($data);
            $validator->unique('codigo_barra', $this->model, $idVal);
            if ($validator->fails()) {
                $this->sendJsonResponse(['status' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
            }
        }

        $exito = $this->model->update($idVal, $datosActualizar);

        if ($exito) {
            $this->sendJsonResponse(['status' => true, 'message' => 'Producto actualizado de forma exitosa']);
        } else {
            $this->sendJsonResponse(['status' => false, 'message' => 'No se pudo actualizar el registro en el servidor'], 500);
        }
    }

    private function deleteProducto(?string $id): void {
        $this->requirePermission('productos.eliminar');

        if (empty($id)) {
            $this->sendJsonResponse(['status' => false, 'message' => 'El identificador es requerido'], 400);
        }

        $idVal = intval($id);
        $productoExistente = $this->model->find($idVal);

        if (!$productoExistente || $productoExistente['estado'] != 1) {
            $this->sendJsonResponse(['status' => false, 'message' => 'El recurso no fue encontrado'], 404);
        }

        $exito = $this->model->update($idVal, ['estado' => 0]);

        if ($exito) {
            $this->sendJsonResponse(['status' => true, 'message' => 'Producto eliminado correctamente']);
        } else {
            $this->sendJsonResponse(['status' => false, 'message' => 'Falló la eliminación interna del recurso'], 500);
        }
    }
}
