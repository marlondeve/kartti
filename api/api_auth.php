<?php
// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Incluir archivo de configuración primero
require_once __DIR__ . '/../config/config.php';

// Configurar cookies de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_path', '/');

// Iniciar sesión
session_start();

// Verificar conexión a la base de datos
if (!isset($pdo)) {
    error_log("Error: La variable \$pdo no está definida");
    handleError('Error de conexión a la base de datos');
}

try {
    $pdo->query("SELECT 1");
    error_log("Conexión a la base de datos exitosa");
} catch (PDOException $e) {
    error_log("Error al verificar conexión: " . $e->getMessage());
    handleError('Error de conexión a la base de datos');
}

// Verificar cookie remember_me si no hay sesión activa
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
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
            error_log("Sesión restaurada para usuario: " . $user['email']);
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
            error_log("Token remember_me inválido o expirado");
        }
    } catch (PDOException $e) {
        error_log("Error al verificar remember_me: " . $e->getMessage());
    }
}

// Configurar headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Función para manejar errores PHP
function errorHandler($errno, $errstr, $errfile, $errline) {
    $error = [
        'success' => false,
        'message' => 'Error interno del servidor',
        'debug' => [
            'error' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]
    ];
    error_log("Error PHP: " . json_encode($error));
    echo json_encode($error);
    exit;
}
set_error_handler('errorHandler');

// Función para manejar excepciones
function exceptionHandler($e) {
    $error = [
        'success' => false,
        'message' => 'Error interno del servidor',
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ];
    error_log("Excepción: " . json_encode($error));
    echo json_encode($error);
    exit;
}
set_exception_handler('exceptionHandler');

// Función para manejar errores
function handleError($message, $code = 400) {
    error_log("Error manejado: " . $message);
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

// Función para manejar éxito
function handleSuccess($data = [], $message = '') {
    error_log("Éxito: " . $message);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function sendVerificationEmail($email, $verification_code) {
    $apiKey = 're_Gmc4vnAp_BgzhHNZ8qCo89NPsfkGhaZr1';

    $data = [
        'from' => 'kartti <noreply@kartti.com>', // Asegúrate que este dominio esté VERIFICADO en Resend
        'to' => [$email],
        'subject' => 'Verifica tu cuenta de Kartti',
        'html' => " <div style='font-family: Arial, sans-serif; background-color: #f2f4f8; padding: 20px; color: #1a1a1a;'>
            <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; padding: 30px;'>
                <h2 style='color: #1f6feb;'>Bienvenido a Kartti</h2>
                <p style='margin-top: 10px;'>Hola,</p>
                <p style='margin: 10px 0 20px;'>Gracias por registrarte en <strong>Kartti</strong>. Para completar tu registro, por favor utiliza el siguiente código de verificación:</p>

                <div style='background-color: #f2f4f8; padding: 20px; border-radius: 6px; text-align: center;'>
                    <span style='font-size: 32px; font-weight: bold; color: #1f6feb;'>$verification_code</span>
                </div>

                <p style='margin: 30px 0 10px;'>Este código expirará en 10 minutos.</p>

                <p>Si no solicitaste esta verificación, puedes ignorar este mensaje.</p>

                <hr style='margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;'>

                <p style='font-size: 12px; color: #666;'>Este mensaje fue generado automáticamente. No respondas a este correo.</p>
            </div>
        </div>"
    ];

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($httpCode >= 400 || $error) {
        error_log("Error al enviar email: $response - $error");
    } else {
        error_log("Correo enviado correctamente: $response");
    }
}

try {
    error_log("Iniciando procesamiento de solicitud");
    
    // Manejar preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        handleError('Método no permitido');
    }

    // Obtener datos JSON
    $json = file_get_contents('php://input');
    error_log("Datos recibidos: " . $json);
    
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        handleError('Error al decodificar JSON: ' . json_last_error_msg());
    }

    // Verificar acción
    if (empty($data['action'])) {
        handleError('Acción no especificada');
    }

    error_log("Procesando acción: " . $data['action']);

    // Procesar la solicitud según el método
    switch ($data['action']) {
        case 'registro':
            // Validar datos requeridos
            if (empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
                handleError('Todos los campos son requeridos');
            }

            // Validar formato de email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                handleError('El formato del email no es válido');
            }

            // Validar longitud de contraseña
            if (strlen($data['password']) < 6) {
                handleError('La contraseña debe tener al menos 6 caracteres');
            }

            try {
                // Verificar si el email ya existe
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$data['email']]);
                if ($stmt->rowCount() > 0) {
                    handleError('El email ya está registrado');
                }

                // Generar código de verificación
                $verification_code = rand(100000, 999999);
                
                // Hash de la contraseña
                $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => HASH_COST]);
                
                // Insertar usuario
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, verification_code, verified) VALUES (?, ?, ?, ?, 0)");
                $stmt->execute([$data['nombre'], $data['email'], $hashed_password, $verification_code]);
                sendVerificationEmail($data['email'], $verification_code);
                handleSuccess([], 'Usuario registrado correctamente');
                
            } catch (PDOException $e) {
                error_log("Error en registro: " . $e->getMessage());
                handleError('Error al registrar usuario. Por favor, intenta nuevamente.');
            }
            break;
            
        case 'login':
            // Validar datos
            if (!isset($data['email']) || !isset($data['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Email y contraseña son requeridos']);
                exit;
            }

            $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
            $password = $data['password'];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Formato de email inválido']);
                exit;
            }

            // Buscar usuario
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Credenciales inválidas']);
                exit;
            }

            if (!$user['verified']) {
                http_response_code(400);
                echo json_encode(['error' => 'Por favor verifica tu email antes de iniciar sesión']);
                exit;
            }

            // Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            // Si se solicitó "remember me", generar token
            if (isset($data['remember_me']) && $data['remember_me']) {
                try {
                    // Generar token único
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    // Guardar token en la base de datos
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, token_expires = ? WHERE id = ?");
                    $stmt->execute([$token, $expires, $user['id']]);
                    
                    // Crear cookie remember_me con configuración segura
                    setcookie('remember_me', $token, [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'domain' => '',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                    
                    error_log("Cookie remember_me creada para usuario: " . $user['email']);
                } catch (PDOException $e) {
                    error_log("Error al crear remember_me: " . $e->getMessage());
                    // No interrumpimos el login si falla remember_me
                }
            }

            // Enviar respuesta exitosa
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email']
                ]
            ]);
            exit;
            
        case 'logout':
            // Destruir la sesión
            session_destroy();
            
            // Eliminar cookie de remember me si existe
            if (isset($_COOKIE['remember_me'])) {
                setcookie('remember_me', '', time() - 3600, '/', '', true, true);
            }
            
            // Enviar respuesta exitosa
            http_response_code(200);
            handleSuccess([], 'Sesión cerrada exitosamente');
            break;
            
        case 'verificar':
            // Validar datos requeridos
            if (empty($data['email']) || empty($data['codigo'])) {
                handleError('Email y código de verificación son requeridos');
            }

            try {
                // Verificar si el usuario existe y el código es correcto
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND verification_code = ? AND verified = 0");
                $stmt->execute([$data['email'], $data['codigo']]);
                
                if ($stmt->rowCount() === 0) {
                    handleError('Código de verificación inválido o ya verificado');
                }

                // Actualizar estado de verificación
                $stmt = $pdo->prepare("UPDATE users SET verified = 1, verification_code = NULL WHERE email = ?");
                $stmt->execute([$data['email']]);
                
                handleSuccess([], 'Email verificado correctamente');
            } catch (PDOException $e) {
                error_log("Error en verificación: " . $e->getMessage());
                handleError('Error al verificar email. Por favor, intenta nuevamente.');
            }
            break;
            
        case 'check_remember':
            try {
                // Verificar si existe la cookie remember_me
                if (!isset($_COOKIE['remember_me'])) {
                    handleError('No hay sesión recordada');
                }

                $token = $_COOKIE['remember_me'];
                
                // Buscar usuario con el token válido y no expirado
                $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE remember_token = ? AND token_expires > NOW()");
                $stmt->execute([$token]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    // Token inválido o expirado
                    setcookie('remember_me', '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'domain' => '',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                    handleError('Sesión expirada');
                }

                // Iniciar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                handleSuccess([
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email']
                    ]
                ], 'Sesión restaurada correctamente');
            } catch (PDOException $e) {
                error_log("Error en check_remember: " . $e->getMessage());
                handleError('Error al verificar sesión recordada');
            }
            break;
            
        case 'reenviar':
            // Validar email
            if (empty($data['email'])) {
                handleError('El email es requerido');
            }

            try {
                // Verificar si el usuario existe y no está verificado
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND verified = 0");
                $stmt->execute([$data['email']]);
                
                if ($stmt->rowCount() === 0) {
                    handleError('Usuario no encontrado o ya verificado');
                }

                // Generar nuevo código de verificación
                $verification_code = rand(100000, 999999);
                
                // Actualizar código de verificación
                $stmt = $pdo->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
                $stmt->execute([$verification_code, $data['email']]);
                
                sendVerificationEmail($data['email'], $verification_code);

                // TODO: Enviar email con el nuevo código
                // Por ahora solo devolvemos éxito
                handleSuccess([], 'Código de verificación reenviado correctamente');
            } catch (PDOException $e) {
                error_log("Error en reenvío: " . $e->getMessage());
                handleError('Error al reenviar el código. Por favor, intenta nuevamente.');
            }
            break;
            
        default:
            handleError('Acción no válida');
            break;
    }
} catch (Exception $e) {
    error_log("Error general: " . $e->getMessage());
    handleError('Error interno del servidor', 500);
} 