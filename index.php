<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("Config/Config.php");
session_start();

require_once("Libraries/Core/Autoload.php");
require_once("Helpers/helpers.php");

// =====================================================
// MIDDLEWARE PIPELINE
// =====================================================

// 1. Sanitización de entradas (siempre primero)
$sanitize = new Libraries\Middleware\SanitizeMiddleware();
$sanitize->handle();

// 2. Generación de token CSRF
$csrf = new Libraries\Middleware\CSRFMiddleware();
$csrf->handle();

// 3. Parseo de URL
$url = $_GET['url'] ?? 'Auth';
$arrUrl = explode("/", $url);

$controller = ucwords($arrUrl[0]) ?? 'Auth';
$method = $arrUrl[1] ?? 'index';
$params = "";

if (!empty($arrUrl[2])) {
    if ($arrUrl[2] != "") {
        for ($i = 2; $i < count($arrUrl); $i++) {
            $params .= $arrUrl[$i] . ",";
        }
        $params = trim($params, ",");
    }
}

// 4. Excluir rutas públicas del middleware de autenticación
$rutasPublicas = ['Auth'];
$esRutaPublica = in_array($controller, $rutasPublicas);

if (!$esRutaPublica) {
    // 5. Autenticación
    $auth = new Libraries\Middleware\AuthMiddleware();
    $auth->handle();

    // 6. RBAC - Control de acceso por roles
    $rbac = new Libraries\Middleware\RBACMiddleware();
    $rbac->handle();

    // 7. Validación CSRF para peticiones POST (excepto login)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        Libraries\Middleware\CSRFMiddleware::verifyOrFail();
    }
}

// 8. Dispatch al controlador
require_once("Libraries/Core/Load.php");
