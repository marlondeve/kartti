<?php
// Iniciar sesión con configuración segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_start();

require_once 'config/config.php';

// Verificar acceso directo a carta/ ANTES de cualquier otra lógica
// Solo si no hay route definido en GET
if (empty($_GET['route'])) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $parsedUri = parse_url($requestUri);
    $path = $parsedUri['path'] ?? '';

    // Normalizar el path
    $path = str_replace('\\', '/', $path);
    $baseDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($baseDir !== '/' && $baseDir !== '.' && $baseDir !== '\\' && strpos($path, $baseDir) === 0) {
        $path = substr($path, strlen($baseDir));
    }
    $path = ltrim($path, '/');

    // Si es una solicitud directa a carta/, manejarla inmediatamente
    if (strpos($path, 'carta') === 0) {
        // Si es solo 'carta' o 'carta/', establecer ruta como 'carta/'
        if ($path === 'carta' || $path === 'carta/' || preg_match('/^carta\/?$/', $path)) {
            $route = 'carta/';
        } else {
            $route = $path;
        }
        $_GET['route'] = $route;
    }
}

// Función para verificar autenticación
function estaAutenticado() {
    return isset($_SESSION['user_id']);
}

// Verificar si hay una cookie remember_me y no hay sesión activa
if (!estaAutenticado() && isset($_COOKIE['remember_me'])) {
    try {
        $token = $_COOKIE['remember_me'];
        
        // Buscar usuario con el token válido y no expirado
        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE remember_token = ? AND token_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Redirigir al dashboard
            redirect_to('index.php?route=dashboard');
        } else {
            // Token inválido o expirado, eliminar cookie
            setcookie('remember_me', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
    } catch (PDOException $e) {
        error_log("Error al verificar remember_me: " . $e->getMessage());
    }
}

// Definir rutas
$rutas = [
    'landing' => 'views/landing.php',
    'login' => 'views/login.php',
    'registro' => 'views/registro.php',
    'demo' => 'views/demo.php',
    'verificar' => 'views/verificar.php'
];

$rutasProtegidas = [
    'dashboard' => 'views/dashboard.php',
    'menu' => 'views/menu.php',
    'perfil' => 'views/perfil.php',
    'alertas' => 'views/alertas.php',
    'configuracion' => 'views/configuracion.php',
    'qr' => 'views/qr.php',
    'analitica' => 'views/analitica.php'
];

// Obtener la ruta de la URL
$route = $_GET['route'] ?? '';

// Si route viene de .htaccess como 'carta/' o 'carta', normalizarlo
if ($route === 'carta' || $route === 'carta/') {
    $route = 'carta/';
}

// Si no hay route pero la URL solicita un archivo de carta, extraerlo de REQUEST_URI
if (empty($route)) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // Remover query string de REQUEST_URI
    $requestUri = strtok($requestUri, '?');
    
    // Remover el directorio base si existe
    $basePath = dirname($scriptName);
    if ($basePath !== '/' && $basePath !== '.' && $basePath !== '\\') {
        // Normalizar separadores de directorio
        $basePath = str_replace('\\', '/', $basePath);
        $requestUri = str_replace('\\', '/', $requestUri);
        if (strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
    }
    $requestUri = ltrim($requestUri, '/');
    
    // Si la solicitud es para carta/, establecer la ruta
    if (strpos($requestUri, 'carta/') === 0 || $requestUri === 'carta') {
        $route = $requestUri;
    }
    
    // Debug: log para ver qué está pasando
    if (strpos($requestUri, 'carta') !== false) {
        error_log("Detección de carta - REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? ''));
        error_log("Detección de carta - Ruta extraída: " . $route);
    }
}

// Si la ruta es la raíz, redirigir a landing
if ($route === '' || $route === '/' || $route === 'index.php') {
    // Redirigir al index con parámetro para no depender de mod_rewrite
    redirect_to('index.php?route=landing');
}

// Si es una solicitud de logout
if ($route === 'logout') {
    // Iniciar sesión si aún no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Eliminar la cookie de remember_me si existe
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    // Limpiar todas las variables de sesión
    $_SESSION = array();
    
    // Destruir la sesión
    session_destroy();
    
    // Redirigir al login
    redirect_to('index.php?route=login');
}

// Si el usuario está autenticado y trata de acceder al login o registro, redirigir al dashboard
if (estaAutenticado() && in_array($route, ['login', 'registro'])) {
    redirect_to('index.php?route=dashboard');
}

// Permitir acceso directo a archivos de la API
if (strpos($route, 'api/') === 0) {
    $apiFile = __DIR__ . '/' . $route;
    if (file_exists($apiFile)) {
        require_once $apiFile;
        exit;
    }
}

// Permitir acceso directo a archivos de la carpeta carta
if (strpos($route, 'carta') === 0) {
    // Normalizar la ruta
    $route = rtrim($route, '/');
    
    // Si es solo 'carta' o 'carta/' (sin archivo), usar menu.html
    if ($route === 'carta') {
        $cartaFile = __DIR__ . '/carta/menu.html';
    } else {
        // Extraer el nombre del archivo de la ruta (después de 'carta/')
        $filePath = str_replace('carta/', '', $route);
        $filePath = trim($filePath, '/');
        
        // Si no hay archivo especificado, usar menu.html
        if (empty($filePath)) {
            $cartaFile = __DIR__ . '/carta/menu.html';
        } else {
            $cartaFile = __DIR__ . '/carta/' . $filePath;
            
            // Si el archivo no existe, usar menu.html por defecto
            if (!file_exists($cartaFile) || is_dir($cartaFile)) {
                $cartaFile = __DIR__ . '/carta/menu.html';
            }
        }
    }
    
    // Verificar que el archivo existe, si no, usar menu.html
    if (!file_exists($cartaFile)) {
        $cartaFile = __DIR__ . '/carta/menu.html';
    }
     try {
        $isMenu =
            (strpos($cartaFile, '/carta/menu.html') !== false) ||
            (strpos($cartaFile, DIRECTORY_SEPARATOR . 'carta' . DIRECTORY_SEPARATOR . 'menu.html') !== false);
    
        if ($isMenu) {
            // Establecer zona horaria de Colombia
            date_default_timezone_set('America/Bogota');
    
            $trackFile = __DIR__ . '/carta/qr-tracking.json';
    
            // Inicializar archivo si no existe
            if (!file_exists($trackFile)) {
                file_put_contents($trackFile, json_encode([
                    "resumen_general" => new stdClass(),
                    "historico_diario" => new stdClass()
                ], JSON_PRETTY_PRINT));
            }
    
            $id = $_GET['id'] ?? null;
    
            // Lectura + escritura segura
            $fp = fopen($trackFile, 'c+');
            if ($fp) {
                flock($fp, LOCK_EX);
    
                $contents = stream_get_contents($fp);
                $data = json_decode($contents ?: '{}', true);
    
                // Migrar estructura antigua si existe
                if (isset($data['locales']) && !isset($data['resumen_general'])) {
                    $data['resumen_general'] = [];
                    foreach ($data['locales'] as $localId => $localData) {
                        $data['resumen_general'][$localId] = $localData['visitas'] ?? 0;
                    }
                    unset($data['locales']);
                    unset($data['visitantes']);
                }
    
                // Inicializar estructura si no existe
                if (!isset($data['resumen_general'])) $data['resumen_general'] = [];
                if (!isset($data['historico_diario'])) $data['historico_diario'] = [];
    
                // Registrar visita si hay ID
                if ($id) {
                    $now = new DateTime('now', new DateTimeZone('America/Bogota'));
                    $fecha = $now->format('Y-m-d');
                    $hora = $now->format('H');
    
                    // Actualizar resumen general
                    if (!isset($data['resumen_general'][$id])) {
                        $data['resumen_general'][$id] = 0;
                    }
                    $data['resumen_general'][$id]++;
    
                    // Actualizar histórico diario
                    if (!isset($data['historico_diario'][$fecha])) {
                        $data['historico_diario'][$fecha] = [];
                    }
                    if (!isset($data['historico_diario'][$fecha][$id])) {
                        $data['historico_diario'][$fecha][$id] = [
                            "total" => 0,
                            "por_horas" => []
                        ];
                    }
    
                    // Incrementar total y hora específica
                    $data['historico_diario'][$fecha][$id]['total']++;
                    if (!isset($data['historico_diario'][$fecha][$id]['por_horas'][$hora])) {
                        $data['historico_diario'][$fecha][$id]['por_horas'][$hora] = 0;
                    }
                    $data['historico_diario'][$fecha][$id]['por_horas'][$hora]++;
                    
                    // Limpiar horas con valor 0 para ahorrar espacio
                    $data['historico_diario'][$fecha][$id]['por_horas'] = array_filter(
                        $data['historico_diario'][$fecha][$id]['por_horas'],
                        function($valor) { return $valor > 0; }
                    );
                }
    
                // Limpiar todas las horas con 0 en todo el histórico (por si hay datos antiguos)
                foreach ($data['historico_diario'] as $fechaKey => &$fechaData) {
                    foreach ($fechaData as $localKey => &$localData) {
                        if (isset($localData['por_horas']) && is_array($localData['por_horas'])) {
                            $localData['por_horas'] = array_filter(
                                $localData['por_horas'],
                                function($valor) { return $valor > 0; }
                            );
                        }
                    }
                }
                unset($fechaData, $localData);
    
                // Guardar
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                fflush($fp);
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }
    } catch (Throwable $e) {
        error_log('QR Tracking error: ' . $e->getMessage());
    }
    // ===== FIN TRACKING =====
    if (file_exists($cartaFile)) {
        // Determinar el tipo MIME basado en la extensión del archivo
        $extension = strtolower(pathinfo($cartaFile, PATHINFO_EXTENSION));
        $mimeTypes = [
            'html' => 'text/html',
            'js' => 'application/javascript',
            'css' => 'text/css',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'json' => 'application/json'
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'text/plain';
        header("Content-Type: $mimeType");
        
        // Para archivos HTML, no cachear para ver cambios en tiempo real
        if ($extension === 'html') {
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
        }
        
        readfile($cartaFile);
        exit;
    } else {
        // Si el archivo no existe, log para debug
        error_log("Archivo carta no encontrado: " . $cartaFile);
        error_log("Ruta recibida: " . $route);
    }
    
}

// Permitir acceso directo a archivos estáticos
if (strpos($route, 'public/') === 0) {
    $staticFile = __DIR__ . '/' . $route;
    if (file_exists($staticFile)) {
        // Determinar el tipo MIME basado en la extensión del archivo
        $extension = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'text/plain';
        header("Content-Type: $mimeType");
        header("Cache-Control: public, max-age=31536000");
        readfile($staticFile);
        exit;
    }
}

// Manejar las rutas
if (isset($rutas[$route])) {
    require_once $rutas[$route];
} elseif (isset($rutasProtegidas[$route])) {
    if (!estaAutenticado()) {
        redirect_to('index.php?route=login');
    }
    require_once $rutasProtegidas[$route];
} else {
    // Si la ruta no está definida, verificar si es un archivo físico que existe
    $physicalFile = __DIR__ . '/' . $route;
    if (file_exists($physicalFile) && is_file($physicalFile)) {
        // Determinar el tipo MIME
        $extension = strtolower(pathinfo($physicalFile, PATHINFO_EXTENSION));
        $mimeTypes = [
            'html' => 'text/html',
            'js' => 'application/javascript',
            'css' => 'text/css',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'json' => 'application/json',
            'ico' => 'image/x-icon'
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'text/plain';
        header("Content-Type: $mimeType");
        
        // Para archivos estáticos, agregar cache headers
        if (in_array($extension, ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2'])) {
            header("Cache-Control: public, max-age=31536000");
        }
        
        readfile($physicalFile);
        exit;
    }
    
    header("HTTP/1.0 404 Not Found");
    require_once 'views/404.php';
} 