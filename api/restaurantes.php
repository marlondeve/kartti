<?php
require_once __DIR__ . '/../config/config.php';

// Configurar headers para JSON
header('Content-Type: application/json');

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log de la solicitud
error_log("=== INICIO DE LA SOLICITUD ===");
error_log("Método HTTP: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));
error_log("Session data: " . print_r($_SESSION, true));
error_log("Raw POST data: " . file_get_contents('php://input'));

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    error_log("Usuario no autenticado");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Función para verificar si el usuario tiene restaurante
function checkUserRestaurant($userId) {
    global $pdo;
    error_log("Buscando restaurante para user_id: " . $userId);
    
    $stmt = $pdo->prepare("SELECT id, nombre FROM restaurantes WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Resultado de la consulta: " . print_r($result, true));
    return $result;
}

// Obtener la acción solicitada
$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Intentar obtener la acción de $_POST primero
    $action = $_POST['action'] ?? '';
    
    // Si no está en $_POST, intentar obtener del body raw
    if (empty($action)) {
        $rawData = file_get_contents('php://input');
        if (!empty($rawData)) {
            $jsonData = json_decode($rawData, true);
            if ($jsonData && isset($jsonData['action'])) {
                $action = $jsonData['action'];
                // Si los datos vienen como JSON, copiarlos a $_POST
                $_POST = array_merge($_POST, $jsonData);
            }
        }
    }
    
    error_log("POST action recibido: " . $action);
    error_log("POST nombre recibido: " . ($_POST['nombre'] ?? 'no definido'));
} else {
    $action = $_GET['action'] ?? '';
    error_log("GET action recibido: " . $action);
}

error_log("Action final: " . $action);

try {
    switch ($action) {
        case 'check':
            // Verificar si el usuario tiene restaurante
            $restaurant = checkUserRestaurant($_SESSION['user_id']);
            echo json_encode([
                'success' => true,
                'has_restaurant' => !empty($restaurant),
                'restaurant_name' => $restaurant['nombre'] ?? null,
                'id' => $restaurant['id'] ?? null
            ]);
            break;

        case 'create':
            error_log("Intentando crear restaurante");
            error_log("POST data recibida: " . print_r($_POST, true));
            
            // Validar datos
            if (empty($_POST['nombre'])) {
                error_log("Nombre del restaurante vacío");
                throw new Exception('El nombre del restaurante es requerido');
            }

            // Insertar nuevo restaurante
            $stmt = $pdo->prepare("
                INSERT INTO restaurantes (user_id, nombre, status) 
                VALUES (?, ?, 'active')
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $_POST['nombre']
            ]);

            error_log("Restaurante creado exitosamente");
            echo json_encode([
                'success' => true,
                'message' => 'Restaurante creado exitosamente',
                'id' => $pdo->lastInsertId()
            ]);
            break;

        case 'update_settings':
            error_log("Actualizando settings del restaurante (JSON)");
            // Obtener restaurante del usuario
            $restaurant = checkUserRestaurant($_SESSION['user_id']);
            if (empty($restaurant) || empty($restaurant['id'])) {
                throw new Exception('No se encontró restaurante para el usuario');
            }

            $restId = $restaurant['id'];

            // Obtener datos (aceptamos JSON o form-url)
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
            $callWaiter = isset($_POST['call_waiter_enabled']) ? (intval($_POST['call_waiter_enabled']) ? 1 : 0) : null;
            $whatsapp = isset($_POST['whatsapp']) ? trim($_POST['whatsapp']) : null;
            $colors = isset($_POST['colors']) && is_array($_POST['colors']) ? $_POST['colors'] : null;

            // Validaciones simples
            if ($nombre !== null && $nombre === '') {
                throw new Exception('El nombre no puede estar vacío');
            }

            // Normalizar número de whatsapp (solo dígitos y +)
            if ($whatsapp !== null && $whatsapp !== '') {
                $whatsapp = preg_replace('/[^0-9+]/', '', $whatsapp);
                if (strlen($whatsapp) > 25) {
                    throw new Exception('Número de WhatsApp demasiado largo');
                }
            } else {
                $whatsapp = null;
            }

            // Si se envió nombre, actualizar en BD
            if ($nombre !== null) {
                $stmt = $pdo->prepare("UPDATE restaurantes SET nombre = ? WHERE id = ?");
                $stmt->execute([$nombre, $restId]);
                
                // Actualizar el nombre en el archivo JSON del QR (qr{id}.json)
                $qrJsonFile = __DIR__ . '/../public/json/qr' . $restId . '.json';
                if (file_exists($qrJsonFile)) {
                    $qrJsonContent = file_get_contents($qrJsonFile);
                    $qrJsonData = json_decode($qrJsonContent, true);
                    if (is_array($qrJsonData) && isset($qrJsonData['qrs']) && is_array($qrJsonData['qrs'])) {
                        // Actualizar el restaurante_nombre en cada QR
                        foreach ($qrJsonData['qrs'] as &$qr) {
                            $qr['restaurante_nombre'] = $nombre;
                        }
                        unset($qr); // Liberar referencia
                        
                        if (file_put_contents($qrJsonFile, json_encode($qrJsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
                            error_log('Advertencia: No se pudo actualizar el archivo JSON del QR');
                        } else {
                            error_log('Archivo JSON del QR actualizado correctamente');
                        }
                    }
                }
            }

            // Guardar call_waiter_enabled y whatsapp en el JSON del restaurante (public/json/restaurante{id}.json)
            $jsonFile = __DIR__ . '/../public/json/restaurante' . $restId . '.json';
            $jsonData = [];
            if (file_exists($jsonFile)) {
                $raw = file_get_contents($jsonFile);
                $jsonData = json_decode($raw, true);
                if (!is_array($jsonData)) $jsonData = [];
            }

            // Asegurarse de estructura para settings
            if (!isset($jsonData['_settings']) || !is_array($jsonData['_settings'])) $jsonData['_settings'] = [];

            // Mantener un flag para determinar si se debe escribir el archivo
            $changed = false;

            // Eliminar restaurante_nombre del JSON del restaurante si existe (no debería estar aquí)
            if (isset($jsonData['restaurante_nombre'])) {
                unset($jsonData['restaurante_nombre']);
                $changed = true;
            }

            if ($callWaiter !== null) {
                $jsonData['_settings']['call_waiter_enabled'] = $callWaiter ? true : false;
                $changed = true;
            }
            if ($whatsapp !== null) {
                $jsonData['_settings']['whatsapp'] = $whatsapp !== '' ? $whatsapp : null;
                $changed = true;
            }
            
            if ($colors !== null) {
                // Validar y limpiar colores (solo permitir formato hexadecimal)
                $validColors = [];
                foreach ($colors as $key => $value) {
                    $value = trim($value);
                    if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                        $validColors[$key] = strtoupper($value);
                    }
                }
                if (!empty($validColors)) {
                    $jsonData['_settings']['colors'] = $validColors;
                    $changed = true;
                }
            }

            if ($changed) {
                if (file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
                    throw new Exception('No se pudo guardar el archivo JSON del restaurante');
                }
            }

            echo json_encode(['success' => true, 'message' => 'Configuración actualizada']);
            break;

        default:
            error_log("Acción no válida: " . $action);
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    error_log("Error en la operación: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

error_log("=== FIN DE LA SOLICITUD ==="); 