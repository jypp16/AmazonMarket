<?php

namespace Services;

class StorageService {

    private string $storagePath;
    private string $publicPath;
    private array $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];
    private array $magicNumbers = [
        'image/jpeg' => "\xFF\xD8\xFF",
        'image/png'  => "\x89PNG\r\n\x1A\n",
        'image/gif'  => "GIF87a",
        'image/gif2'=> "GIF89a",
        'image/webp' => "RIFF",
    ];
    private int $maxSize = 5 * 1024 * 1024;

    public function __construct() {
        $configuredPath = defined('STORAGE_PATH') ? STORAGE_PATH : null;
        if ($configuredPath && is_dir($configuredPath)) {
            $this->storagePath = $configuredPath;
        } else {
            $path = __DIR__ . '/../../storage/productos';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $this->storagePath = realpath($path) ?: $path;
        }
        $this->publicPath = __DIR__ . '/../../Assets/img/productos';
    }

    public function guardarImagen(array $file, string $codigoBarra): array {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'message' => 'No se envió archivo.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'Error del servidor al subir el archivo (código ' . $file['error'] . ').'];
        }
        if ($file['size'] > $this->maxSize) {
            $maxMb = $this->maxSize / (1024 * 1024);
            return ['ok' => false, 'message' => "El archivo excede el tamaño máximo de {$maxMb} MB."];
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return ['ok' => false, 'message' => 'Tipo de archivo no permitido. Solo se aceptan JPG, PNG, GIF y WebP.'];
        }

        $content = file_get_contents($file['tmp_name']);
        if ($content === false) {
            return ['ok' => false, 'message' => 'No se pudo leer el archivo subido.'];
        }

        $mimeType = $this->detectarMimeType($content, $ext);
        if ($mimeType === null) {
            return ['ok' => false, 'message' => 'El contenido del archivo no coincide con su extensión. Archivo rechazado por seguridad.'];
        }
        if (!in_array($mimeType, $this->allowedMimes)) {
            return ['ok' => false, 'message' => 'Tipo MIME no permitido (' . $mimeType . ').'];
        }

        $hash = hash('sha256', $content);
        $finalName = $codigoBarra . '.' . $ext;
        $destino = $this->storagePath . '/' . $finalName;

        $this->eliminarImagen($codigoBarra);

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            return ['ok' => false, 'message' => 'No se pudo mover el archivo al almacenamiento.'];
        }

        $publicFile = $this->publicPath . '/' . $finalName;
        copy($destino, $publicFile);

        $this->registrarHash($codigoBarra, $hash, $ext);

        return ['ok' => true, 'message' => 'Imagen guardada correctamente.', 'filename' => $finalName, 'hash' => $hash];
    }

    public function renombrarImagen(string $oldCodigo, string $newCodigo): void {
        $oldFiles = glob($this->storagePath . '/' . $oldCodigo . '.*');
        if ($oldFiles) {
            foreach ($oldFiles as $oldFile) {
                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = $this->storagePath . '/' . $newCodigo . '.' . $ext;
                rename($oldFile, $newFile);
            }
        }
        $oldPublicFiles = glob($this->publicPath . '/' . $oldCodigo . '.*');
        if ($oldPublicFiles) {
            foreach ($oldPublicFiles as $oldFile) {
                $ext = pathinfo($oldFile, PATHINFO_EXTENSION);
                $newFile = $this->publicPath . '/' . $newCodigo . '.' . $ext;
                rename($oldFile, $newFile);
            }
        }
    }

    public function eliminarImagen(string $codigoBarra): bool {
        $files = glob($this->storagePath . '/' . $codigoBarra . '.*');
        $deleted = false;
        if ($files) {
            foreach ($files as $file) {
                if (unlink($file)) {
                    $deleted = true;
                }
            }
        }
        $publicFiles = glob($this->publicPath . '/' . $codigoBarra . '.*');
        if ($publicFiles) {
            foreach ($publicFiles as $file) {
                unlink($file);
            }
        }
        return $deleted;
    }

    public function existeImagen(string $codigoBarra): bool {
        return count(glob($this->storagePath . '/' . $codigoBarra . '.*')) > 0;
    }

    public function getRutaImagen(string $codigoBarra): ?string {
        $files = glob($this->storagePath . '/' . $codigoBarra . '.*');
        if ($files) {
            return $files[0];
        }
        return null;
    }

    public function verificarIntegridad(string $codigoBarra): array {
        $metaFile = $this->storagePath . '/.meta.json';
        if (!file_exists($metaFile)) {
            return ['ok' => false, 'message' => 'No hay metadatos registrados.'];
        }
        $meta = json_decode(file_get_contents($metaFile), true);
        if (!isset($meta[$codigoBarra])) {
            return ['ok' => false, 'message' => 'No se encontró registro de hash para este código.'];
        }
        $archivo = $this->storagePath . '/' . $codigoBarra . '.' . $meta[$codigoBarra]['ext'];
        if (!file_exists($archivo)) {
            return ['ok' => false, 'message' => 'El archivo de imagen no existe.'];
        }
        $hashActual = hash('sha256', file_get_contents($archivo));
        $integro = ($hashActual === $meta[$codigoBarra]['hash']);
        return [
            'ok' => true,
            'integro' => $integro,
            'hash_esperado' => $meta[$codigoBarra]['hash'],
            'hash_actual' => $hashActual,
        ];
    }

    private function registrarHash(string $codigoBarra, string $hash, string $ext): void {
        $metaFile = $this->storagePath . '/.meta.json';
        $meta = [];
        if (file_exists($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true) ?: [];
        }
        $meta[$codigoBarra] = ['hash' => $hash, 'ext' => $ext, 'fecha' => date('c')];
        file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));
    }

    private function detectarMimeType(string $content, string $ext): ?string {
        if (isset($this->magicNumbers['image/jpeg']) && str_starts_with($content, $this->magicNumbers['image/jpeg'])) {
            return 'image/jpeg';
        }
        if (str_starts_with($content, $this->magicNumbers['image/png'])) {
            return 'image/png';
        }
        if (str_starts_with($content, $this->magicNumbers['image/gif'])) {
            return 'image/gif';
        }
        if (str_starts_with($content, $this->magicNumbers['image/webp'])) {
            return 'image/webp';
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = $finfo->buffer($content);
        if ($detected && $detected !== 'application/octet-stream') {
            return $detected;
        }
        return null;
    }
}