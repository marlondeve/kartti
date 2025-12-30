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
        
        $fileName = generateUniqueFileName($file['name']);
        $tempPath = $file['tmp_name'];
        $targetPath = UPLOAD_DIR . $fileName;
        
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
        
        // Copiar el archivo al directorio de destino
        if (!copy($tempPath, $targetPath)) {
            throw new Exception('Error al copiar la imagen al directorio de destino');
        }
        
        // Devolver la ruta relativa al archivo
        return 'uploads/productos/' . $fileName;
    } catch (Exception $e) {
        error_log("Error en processAndSaveImage: " . $e->getMessage());
        throw $e;
    }
} 