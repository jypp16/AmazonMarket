<?php

namespace Controllers\API;

use Libraries\Core\ApiController;
use Models\ClienteModel;

class ClienteApiController extends ApiController
{
    private ClienteModel $clienteModel;

    public function __construct()
    {
        parent::__construct();
        $this->clienteModel = new ClienteModel();
    }

    public function index(?string $params = ''): void
    {
        $this->requirePermission('clientes.listar');

        switch ($this->requestMethod) {
            case 'GET':
                $this->getClientes($params);
                break;
            case 'POST':
                $this->store();
                break;
            case 'PUT':
                $this->update($params);
                break;
            case 'DELETE':
                $this->destroy($params);
                break;
            default:
                header('Allow: GET, POST, PUT, DELETE');
                $this->sendJsonResponse(['status' => false, 'message' => 'Método no permitido'], 405);
                break;
        }
    }

    private function getClientes(?string $id): void
    {
        if (!empty($id)) {
            $cliente = $this->clienteModel
                ->where(['id_cliente' => (int)$id, 'estado = 1'])
                ->first();

            if (!$cliente) {
                $this->sendJsonResponse(['status' => false, 'message' => 'Cliente no encontrado'], 404);
            }

            $this->sendJsonResponse(['status' => true, 'data' => $cliente]);
        } else {
            $clientes = $this->clienteModel
                ->where(['estado = 1'])
                ->orderBy('nombre', 'ASC')
                ->get();

            $this->sendJsonResponse(['status' => true, 'data' => $clientes]);
        }
    }

    private function store(): void
    {
        $this->requirePermission('clientes.crear');

        $data = $this->getInput();

        $validator = new \Libraries\Core\Validation($data);
        $validator->required(['nombre', 'nro_documento', 'id_tipo_documento'])
            ->minLength('nombre', 3)
            ->maxLength('nombre', 100)
            ->unique('nro_documento', $this->clienteModel);

        if ($validator->fails()) {
            $this->sendJsonResponse(['status' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
        }

        $clienteData = [
            'id_tipo_documento' => intval($data['id_tipo_documento'] ?? 1),
            'nro_documento' => trim($data['nro_documento']),
            'nombre' => trim($data['nombre']),
            'direccion' => trim($data['direccion'] ?? ''),
            'telefono' => trim($data['telefono'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'estado' => 1
        ];

        if ($this->clienteModel->insert($clienteData)) {
            $this->sendJsonResponse(['status' => true, 'message' => 'Cliente registrado exitosamente'], 201);
        } else {
            $this->sendJsonResponse(['status' => false, 'message' => 'Error al registrar el cliente'], 500);
        }
    }

    private function update(?string $id): void
    {
        $this->requirePermission('clientes.editar');

        if (empty($id)) {
            $this->sendJsonResponse(['status' => false, 'message' => 'El identificador es requerido'], 400);
        }

        $idVal = (int)$id;
        $clienteExistente = $this->clienteModel->find($idVal);

        if (!$clienteExistente || $clienteExistente['estado'] != 1) {
            $this->sendJsonResponse(['status' => false, 'message' => 'Cliente no encontrado'], 404);
        }

        $data = $this->getInput();
        $datosActualizar = [];

        if (isset($data['nombre'])) $datosActualizar['nombre'] = trim($data['nombre']);
        if (isset($data['nro_documento'])) $datosActualizar['nro_documento'] = trim($data['nro_documento']);
        if (isset($data['id_tipo_documento'])) $datosActualizar['id_tipo_documento'] = intval($data['id_tipo_documento']);
        if (isset($data['direccion'])) $datosActualizar['direccion'] = trim($data['direccion']);
        if (isset($data['telefono'])) $datosActualizar['telefono'] = trim($data['telefono']);
        if (isset($data['email'])) $datosActualizar['email'] = trim($data['email']);

        if (empty($datosActualizar)) {
            $this->sendJsonResponse(['status' => false, 'message' => 'No se proporcionaron datos para actualizar'], 400);
        }

        if (isset($data['nro_documento'])) {
            $validator = new \Libraries\Core\Validation($data);
            $validator->unique('nro_documento', $this->clienteModel, $idVal);
            if ($validator->fails()) {
                $this->sendJsonResponse(['status' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
            }
        }

        if ($this->clienteModel->update($idVal, $datosActualizar)) {
            $this->sendJsonResponse(['status' => true, 'message' => 'Cliente actualizado exitosamente']);
        } else {
            $this->sendJsonResponse(['status' => false, 'message' => 'Error al actualizar el cliente'], 500);
        }
    }

    private function destroy(?string $id): void
    {
        $this->requirePermission('clientes.eliminar');

        if (empty($id)) {
            $this->sendJsonResponse(['status' => false, 'message' => 'El identificador es requerido'], 400);
        }

        $idVal = (int)$id;
        $clienteExistente = $this->clienteModel->find($idVal);

        if (!$clienteExistente || $clienteExistente['estado'] != 1) {
            $this->sendJsonResponse(['status' => false, 'message' => 'Cliente no encontrado'], 404);
        }

        if ($this->clienteModel->update($idVal, ['estado' => 0])) {
            $this->sendJsonResponse(['status' => true, 'message' => 'Cliente eliminado correctamente']);
        } else {
            $this->sendJsonResponse(['status' => false, 'message' => 'Error al eliminar el cliente'], 500);
        }
    }
}