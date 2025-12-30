<?php
// Iniciar sesión con configuración segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_start();

require_once 'config/config.php';

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
            header('Location: /dashboard');
            exit;
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
    'qr' => 'views/qr.php'
];

// Obtener la ruta de la URL
$route = $_GET['route'] ?? '';

// Si la ruta es la raíz, redirigir a landing
if ($route === '' || $route === '/' || $route === 'index.php') {
    header('Location: /landing');
    exit;
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
    header('Location: /login');
    exit;
}

// Si el usuario está autenticado y trata de acceder al login o registro, redirigir al dashboard
if (estaAutenticado() && in_array($route, ['login', 'registro'])) {
    header('Location: /dashboard');
    exit;
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
if (strpos($route, 'carta/') === 0) {
    $cartaFile = __DIR__ . '/' . $route;
    
    // Si la ruta termina en carta/, servir menu.html por defecto
    if (is_dir($cartaFile)) {
        $cartaFile = __DIR__ . '/carta/menu.html';
    }
     try {
        $isMenu =
            (strpos($cartaFile, '/carta/menu.html') !== false) ||
            (strpos($cartaFile, DIRECTORY_SEPARATOR . 'carta' . DIRECTORY_SEPARATOR . 'menu.html') !== false);
    
        if ($isMenu) {
            date_default_timezone_set('UTC');
    
            $trackFile = __DIR__ . '/carta/qr-tracking.json';
    
            // Inicializar archivo si no existe
            if (!file_exists($trackFile)) {
                file_put_contents($trackFile, json_encode([
                    "locales" => new stdClass(),
                    "visitantes" => []
                ], JSON_PRETTY_PRINT));
            }
    
            // Obtener IP real
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $ip = trim($parts[0]);
            }
    
            $fullUrl = (isset($_SERVER['HTTPS']) ? "https" : "http")
                . "://" . ($_SERVER['HTTP_HOST'] ?? '')
                . ($_SERVER['REQUEST_URI'] ?? '');
    
            $id = $_GET['id'] ?? null;
    
            $entry = [
                "time" => gmdate('c'),
                "ip" => $ip,
                "userAgent" => $_SERVER['HTTP_USER_AGENT'] ?? '',
                "full_url" => $fullUrl,
                "query" => [
                    "id" => $_GET['id'] ?? null,
                    "tipo" => $_GET['tipo'] ?? null,
                    "nombre" => $_GET['nombre'] ?? null
                ]
            ];
    
            // Lectura + escritura segura
            $fp = fopen($trackFile, 'c+');
            if ($fp) {
                flock($fp, LOCK_EX);
    
                $contents = stream_get_contents($fp);
                $data = json_decode($contents ?: '{}', true);
    
                if (!isset($data['locales'])) $data['locales'] = [];
                if (!isset($data['visitantes'])) $data['visitantes'] = [];
    
                // Incrementar visitas por local
                if ($id) {
                    if (!isset($data['locales'][$id])) {
                        $data['locales'][$id] = ["visitas" => 0];
                    }
                    $data['locales'][$id]['visitas']++;
                }
    
                // Registrar visitante
                $data['visitantes'][] = $entry;
    
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
            'svg' => 'image/svg+xml'
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'text/plain';
        header("Content-Type: $mimeType");
        readfile($cartaFile);
        exit;
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
        header('Location: /login');
        exit;
    }
    require_once $rutasProtegidas[$route];
} else {
    header("HTTP/1.0 404 Not Found");
    require_once 'views/404.php';
} 