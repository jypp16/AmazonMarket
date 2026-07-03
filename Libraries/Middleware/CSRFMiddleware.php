<?php

namespace Libraries\Middleware;

class CSRFMiddleware extends Middleware {

    public function handle(): void {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function generateToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function getTokenField(): string {
        return '<input type="hidden" name="csrf_token" value="' . self::generateToken() . '">';
    }

    public static function getTokenMeta(): string {
        return '<meta name="csrf-token" content="' . self::generateToken() . '">';
    }

    public static function validate(): bool {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        $token = null;
        
        if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        } elseif (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        } elseif (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['csrf_token'] ?? null;
        }

        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            return false;
        }

        return true;
    }

    public static function verifyOrFail(): void {
        if (self::validate()) {
            return;
        }

        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['status' => false, 'message' => 'Token CSRF inválido. Recargue la página e intente de nuevo.']);
            exit;
        }

        $_SESSION['error'] = 'Token de seguridad inválido. Intente de nuevo.';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? (BASE_URL . "/Home")));
        exit;
    }
}
