<?php

$controllerClass = "Controllers\\{$controller}Controller";

if (class_exists($controllerClass)) {
    $controllerObject = new $controllerClass();
    if (method_exists($controllerObject, $method)) {
        $controllerObject->$method($params);
    } else {
        $errorObject = new Controllers\ErrorController();
        $errorObject->index();
    }
} else {
    $errorObject = new Controllers\ErrorController();
    $errorObject->index();
}