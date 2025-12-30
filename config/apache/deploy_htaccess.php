<?php
// Determinar el entorno basado en el host
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isProduction = !in_array($host, ['localhost', '127.0.0.1']);

// Ruta del archivo .htaccess en la raíz
$htaccessPath = __DIR__ . '/../../.htaccess';

// Seleccionar el archivo de configuración según el entorno
$sourceFile = $isProduction ? 
    __DIR__ . '/.htaccess.production' : 
    __DIR__ . '/../../.htaccess';

// Verificar que el archivo fuente existe
if (!file_exists($sourceFile)) {
    die("Error: No se encontró el archivo de configuración $sourceFile\n");
}

// Si estamos en desarrollo, no necesitamos copiar nada
if (!$isProduction) {
    echo "Entorno de desarrollo detectado. No se requiere acción.\n";
    exit;
}

// Copiar el archivo de configuración solo en producción
if (copy($sourceFile, $htaccessPath)) {
    echo "Configuración de Apache desplegada exitosamente para producción.\n";
} else {
    die("Error: No se pudo copiar el archivo de configuración\n");
} 