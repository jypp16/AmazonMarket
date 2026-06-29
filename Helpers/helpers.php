<?php

use Libraries\Middleware\CSRFMiddleware;

if (!function_exists('csrf_field')) {
    function csrf_field() {
        echo CSRFMiddleware::getTokenField();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() {
        echo CSRFMiddleware::generateToken();
    }
}

if (!function_exists('csrf_meta')) {
    function csrf_meta() {
        echo CSRFMiddleware::getTokenMeta();
    }
}

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('can')) {
    function can($slug) {
        if (empty($_SESSION['rol'])) return false;
        if ($_SESSION['rol'] == 1) return true;
        return \Libraries\Middleware\RBACMiddleware::tienePermiso($_SESSION['rol'], $slug);
    }
}

if (!function_exists('canAny')) {
    function canAny(array $slugs) {
        if (empty($_SESSION['rol'])) return false;
        if ($_SESSION['rol'] == 1) return true;
        return \Libraries\Middleware\RBACMiddleware::tieneAlgunPermiso($_SESSION['rol'], $slugs);
    }
}
