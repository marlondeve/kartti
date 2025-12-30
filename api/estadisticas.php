<?php
require_once __DIR__ . '/../config/config.php';

// Verificar si el usuario está autenticado
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener el restaurante del usuario actual
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id FROM restaurantes WHERE user_id = ?");
$stmt->execute([$userId]);
$restaurante = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurante) {
    echo json_encode([
        'success' => false,
        'message' => 'No se encontró el restaurante'
    ]);
    exit;
}

$restauranteId = $restaurante['id'];
error_log("Restaurante ID: " . $restauranteId);

try {
    // Leer el archivo JSON del restaurante
    $jsonPath = $_SERVER['DOCUMENT_ROOT'] . '/public/json/restaurante' . $restauranteId . '.json';
    error_log("Ruta del JSON: " . $jsonPath);
    
    if (!file_exists($jsonPath)) {
        error_log("El archivo JSON no existe");
        echo json_encode([
            'success' => true,
            'data' => [
                'categorias_activas' => 0,
                'productos_activos' => 0,
                'qr_activos' => 0
            ]
        ]);
        exit;
    }

    $jsonContent = file_get_contents($jsonPath);
    $menuData = json_decode($jsonContent, true);
    error_log("Contenido del JSON: " . json_encode($menuData));

    // Contar categorías activas y productos activos
    $categoriasActivas = 0;
    $productosActivos = 0;

    foreach ($menuData as $categoria) {
        if ($categoria['status'] === 'activo') {
            $categoriasActivas++;
            $productosActivos += count($categoria['products']);
        }
    }

    error_log("Categorías activas: " . $categoriasActivas);
    error_log("Productos activos: " . $productosActivos);

    $response = [
        'success' => true,
        'data' => [
            'categorias_activas' => $categoriasActivas,
            'productos_activos' => $productosActivos,
            'qr_activos' => 0
        ]
    ];

    error_log("Respuesta final: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error en estadísticas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
    ]);
}