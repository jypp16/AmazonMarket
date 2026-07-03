<?php

namespace Services;

use Models\UsuarioModel;
use Libraries\Core\Validation;

class UsuarioService {

    private $model;

    public function __construct() {
        $this->model = new UsuarioModel();
    }

    public function crear(array $input): array {
        $validator = new Validation($input);
        $validator->required(['username', 'password', 'nombre', 'dni', 'id_rol'])
            ->minLength('username', 3)
            ->maxLength('username', 10)
            ->minLength('password', 6)
            ->minLength('nombre', 3)
            ->maxLength('nombre', 100)
            ->minLength('dni', 8)
            ->maxLength('dni', 8)
            ->phone('telefono')
            ->maxLength('direccion', 100)
            ->email('email')
            ->unique('username', $this->model)
            ->unique('dni', $this->model);

        if ($validator->fails()) {
            return ['status' => false, 'errors' => $validator->errors()];
        }

        $usuarioData = [
            'id_rol' => intval($input['id_rol']),
            'username' => trim($input['username']),
            'password_hash' => password_hash(trim($input['password']), PASSWORD_DEFAULT),
            'nombre' => trim($input['nombre']),
            'dni' => trim($input['dni']),
            'telefono' => trim($input['telefono'] ?? ''),
            'direccion' => trim($input['direccion'] ?? ''),
            'email' => trim($input['email'] ?? ''),
            'estado' => 1
        ];

        if ($this->model->insert($usuarioData)) {
            return ['status' => true, 'message' => 'Usuario creado con éxito.'];
        }
        return ['status' => false, 'message' => 'Error al guardar el usuario.'];
    }

    public function actualizar(int $id, array $input): array {
        $validator = new Validation($input);
        $validator->required(['username', 'nombre', 'dni', 'id_rol'])
            ->minLength('username', 3)
            ->maxLength('username', 10)
            ->minLength('nombre', 3)
            ->maxLength('nombre', 100)
            ->minLength('dni', 8)
            ->maxLength('dni', 8)
            ->phone('telefono')
            ->maxLength('direccion', 100)
            ->email('email')
            ->unique('username', $this->model, $id)
            ->unique('dni', $this->model, $id);

        if (!empty($input['password'])) {
            $validator->minLength('password', 6);
        }

        if ($validator->fails()) {
            return ['status' => false, 'errors' => $validator->errors()];
        }

        $usuarioData = [
            'id_rol' => intval($input['id_rol']),
            'username' => trim($input['username']),
            'nombre' => trim($input['nombre']),
            'dni' => trim($input['dni']),
            'telefono' => trim($input['telefono'] ?? ''),
            'direccion' => trim($input['direccion'] ?? ''),
            'email' => trim($input['email'] ?? '')
        ];

        if (!empty($input['password'])) {
            $usuarioData['password_hash'] = password_hash(trim($input['password']), PASSWORD_DEFAULT);
        }

        if ($this->model->update($id, $usuarioData)) {
            return ['status' => true, 'message' => 'Usuario actualizado con éxito.'];
        }
        return ['status' => false, 'message' => 'Error al actualizar el usuario.'];
    }

    public function eliminar(int $id): array {
        if ($this->model->update($id, ['estado' => 0])) {
            return ['status' => true, 'message' => 'Usuario eliminado con éxito.'];
        }
        return ['status' => false, 'message' => 'Error al eliminar el usuario.'];
    }

    public function obtenerTodos(): array {
        return $this->model->obtenerUsuarios();
    }

    public function obtenerPorId(int $id) {
        return $this->model->obtenerUsuarioPorId($id);
    }
}
