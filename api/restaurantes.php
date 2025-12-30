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