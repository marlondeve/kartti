<?php
// Configuración para el manejo de imágenes
define('UPLOAD_DIR', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'productos' . DIRECTORY_SEPARATOR);
define('TEMP_DIR', ini_get('upload_tmp_dir') ?: sys_get_temp_dir());
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', [
    'image/jpeg',
    'image/png',
    'image/webp'
]);
define('MAX_WIDTH', 1200);
define('MAX_HEIGHT', 1200);
define('QUALITY', 80);

// Función para validar el tipo de archivo
function validateFileType($file) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    return in_array($mimeType, ALLOWED_TYPES);
}

// Función para validar el tamaño del archivo
function validateFileSize($file) {
    return $file['size'] <= MAX_FILE_SIZE;
}

// Función para generar un nombre único para el archivo
function generateUniqueFileName($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

// Función para procesar y guardar la imagen
function processAndSaveImage($file) {
    try {
        if (!validateFileType($file) || !validateFileSize($file)) {
            throw new Exception('Archivo no válido');
        }

        $tempPath = $file['tmp_name'];

        // Verificar que la extensión GD esté disponible para evitar errores fatales
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled') || 
            !(function_exists('imagecreatefromjpeg') || function_exists('imagecreatefrompng') || function_exists('imagecreatefromwebp') || function_exists('imagecreatefromstring'))) {
            throw new Exception('La extensión GD no está disponible en PHP. Habilita ext-gd y reinicia Apache.');
        }

        // Crear directorio de destino si no existe
        if (!file_exists(UPLOAD_DIR)) {
            if (!mkdir(UPLOAD_DIR, 0777, true)) {
                throw new Exception('No se pudo crear el directorio de destino');
            }
        }

        // Verificar permisos de escritura
        if (!is_writable(UPLOAD_DIR)) {
            throw new Exception('El directorio de destino no tiene permisos de escritura');
        }

        // Intentar procesar la imagen y generar un cuadrado 500x500 (centrado)
        $imgInfo = getimagesize($tempPath);
        if ($imgInfo === false) {
            throw new Exception('No se pudo obtener información de la imagen');
        }

        $mime = $imgInfo['mime'];
        // Cargar la imagen fuente según el tipo
        switch ($mime) {
            case 'image/jpeg':
                $srcImg = imagecreatefromjpeg($tempPath);
                $ext = 'jpg';
                break;
            case 'image/png':
                $srcImg = imagecreatefrompng($tempPath);
                $ext = 'png';
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $srcImg = imagecreatefromwebp($tempPath);
                } else {
                    $srcImg = imagecreatefromstring(file_get_contents($tempPath));
                }
                $ext = 'webp';
                break;
            default:
                throw new Exception('Formato de imagen no soportado');
        }

        if (!$srcImg) {
            throw new Exception('No se pudo crear la imagen desde el archivo');
        }

        $origW = imagesx($srcImg);
        $origH = imagesy($srcImg);

        // Determinar rectángulo centrado para recortar un cuadrado
        $side = min($origW, $origH);
        $srcX = (int) floor(($origW - $side) / 2);
        $srcY = (int) floor(($origH - $side) / 2);

        // Crear lienzo de destino 500x500
        $dstW = 500;
        $dstH = 500;
        $dstImg = imagecreatetruecolor($dstW, $dstH);

        // Fondo blanco (por si la imagen no tiene alfa)
        $white = imagecolorallocate($dstImg, 255, 255, 255);
        imagefill($dstImg, 0, 0, $white);

        // Si la imagen original tiene transparencia (PNG), conservarla
        if ($mime === 'image/png') {
            imagealphablending($dstImg, false);
            imagesavealpha($dstImg, true);
            $transparent = imagecolorallocatealpha($dstImg, 0, 0, 0, 127);
            imagefilledrectangle($dstImg, 0, 0, $dstW, $dstH, $transparent);
        }

        // Copiar y redimensionar (recorte central -> 500x500)
        imagecopyresampled($dstImg, $srcImg, 0, 0, $srcX, $srcY, $dstW, $dstH, $side, $side);

        // Seleccionar el mejor formato de salida disponible (webp -> jpeg -> png)
        $saveFn = null;
        $ext = null;
        if (function_exists('imagewebp')) {
            $saveFn = 'imagewebp';
            $ext = 'webp';
        } elseif (function_exists('imagejpeg')) {
            $saveFn = 'imagejpeg';
            $ext = 'jpg';
        } elseif (function_exists('imagepng')) {
            $saveFn = 'imagepng';
            $ext = 'png';
        } else {
            imagedestroy($srcImg);
            imagedestroy($dstImg);
            throw new Exception('El servidor no permite guardar imágenes (faltan funciones GD para webp/jpeg/png)');
        }

        $fileName = uniqid() . '_' . time() . '.' . $ext;
        $targetPath = UPLOAD_DIR . $fileName;

        // Guardar con la función seleccionada
        $saved = false;
        if ($saveFn === 'imagewebp') {
            $saved = imagewebp($dstImg, $targetPath, QUALITY);
        } elseif ($saveFn === 'imagejpeg') {
            $saved = imagejpeg($dstImg, $targetPath, QUALITY);
        } elseif ($saveFn === 'imagepng') {
            // Para PNG la calidad va de 0 (sin compresión) a 9 (máxima). Mapear QUALITY(0-100) -> PNG compression
            $pngCompression = (int) round((100 - QUALITY) / 10);
            $saved = imagepng($dstImg, $targetPath, $pngCompression);
        }

        if (!$saved) {
            imagedestroy($srcImg);
            imagedestroy($dstImg);
            throw new Exception('Error al guardar la imagen procesada en disco');
        }

        // Liberar recursos
        imagedestroy($srcImg);
        imagedestroy($dstImg);

        return 'uploads/productos/' . $fileName;
    } catch (Exception $e) {
        error_log("Error en processAndSaveImage: " . $e->getMessage());
        throw $e;
    }
} 