<?php

// Para rutas API, buscar en Controllers/
$controllerClass = "Controllers\\{$controller}";

if (!class_exists($controllerClass)) {
    // Intentar con Controller al final
    $controllerClass = "Controllers\\{$controller}Controller";
}

if (class_exists($controllerClass)) {
    $controllerObject = new $controllerClass();
    if (method_exists($controllerObject, $method)) {
        $controllerObject->$method($params);
    } else {
        if ($isApiRoute ?? false) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(405);
            echo json_encode(['status' => false, 'message' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $errorObject = new Controllers\ErrorController();
        $errorObject->index();
    }
} else {
    if ($isApiRoute ?? false) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['status' => false, 'message' => 'Endpoint no encontrado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $errorObject = new Controllers\ErrorController();
    $errorObject->index();
}
