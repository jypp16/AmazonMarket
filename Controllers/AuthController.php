<?php

namespace Controllers;

use Libraries\Core\Controller;
use Services\AuthService;

class AuthController extends Controller {

    private $service;

    public function __construct() {
        parent::__construct();
        $this->service = new AuthService();
    }

    public function index() {
        \Libraries\Middleware\AuthMiddleware::guest();
        $this->views->render($this, "index");
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->service->login(
                $_POST['username'] ?? '',
                $_POST['password'] ?? ''
            );

            if ($resultado['status']) {
                header("Location: " . BASE_URL . "/Home");
            } else {
                $_SESSION['error'] = $resultado['message'];
                header("Location: " . BASE_URL . "/Auth");
            }
            exit;
        } else {
            header("Location: " . BASE_URL . "/Auth");
            exit;
        }
    }

    public function logout() {
        $this->service->logout();
        header("Location: " . BASE_URL . "/Auth");
        exit;
    }
}
