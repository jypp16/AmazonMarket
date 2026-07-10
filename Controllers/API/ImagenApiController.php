<?php

namespace Controllers\API;

use Libraries\Core\ApiController;

class ImagenApiController extends ApiController {

    private string $storagePath;

    public function __construct() {
        $this->storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : 'C:/xampp/storage/amazon_market/productos';
    }

    public function get(?string $params = ""): void {
        if (empty($params)) {
            http_response_code(400);
            echo json_encode(['status' => false, 'message' => 'Código de barras requerido']);
            return;
        }

        $codigoBarra = urldecode($params);

        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $codigoBarra)) {
            http_response_code(400);
            echo json_encode(['status' => false, 'message' => 'Código de barras inválido']);
            return;
        }

        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $found = false;
        $filePath = '';

        foreach ($extensions as $ext) {
            $candidate = $this->storagePath . '/' . $codigoBarra . '.' . $ext;
            if (file_exists($candidate)) {
                $filePath = $candidate;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $fallback = __DIR__ . '/../../../Assets/img/productos/no-image.jpg';
            if (file_exists($fallback)) {
                $filePath = $fallback;
            } else {
                http_response_code(404);
                echo json_encode(['status' => false, 'message' => 'Imagen no encontrada']);
                return;
            }
        }

        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ];

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}