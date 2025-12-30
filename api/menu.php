<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Incluir el archivo de configuración con la ruta correcta
require_once __DIR__ . '/../config/config.php';

try {
    // Verificar que se proporcionó el ID del restaurante
    if (!isset($_GET['id'])) {
        throw new Exception('ID de restaurante no proporcionado');
    }

    $restauranteId = $_GET['id'];
    
    // Obtener la conexión a la base de datos
    $db = getDBConnection();
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // Verificar que el restaurante existe
    $stmt = $db->prepare("SELECT id FROM restaurantes WHERE id = :id");
    $stmt->bindParam(':id', $restauranteId);
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        throw new Exception('Restaurante no encontrado');
    }
    
    // Obtener todas las categorías del restaurante
    $stmt = $db->prepare("
        SELECT id, nombre 
        FROM categorias 
        WHERE restaurante_id = :restaurante_id 
        ORDER BY posicion
    ");
    $stmt->bindParam(':restaurante_id', $restauranteId);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $menuData = [];
    
    // Para cada categoría, obtener sus productos
    foreach ($categories as $category) {
        $stmt = $db->prepare("
            SELECT 
                p.nombre,
                p.descripcion,
                p.precio,
                p.imagen
            FROM productos p
            WHERE p.categoria_id = :category_id 
            ORDER BY p.posicion
        ");
        $stmt->bindParam(':category_id', $category['id']);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Agregar la categoría y sus productos al menú
        $menuData[$category['nombre']] = array_map(function($product) {
            return [
                'name' => $product['nombre'],
                'price' => "$" . number_format($product['precio'], 0, ',', '.'),
                'image' => $product['imagen'] ?: 'https://via.placeholder.com/300x200',
                'description' => $product['descripcion'] ?: ''
            ];
        }, $products);
    }
    
    echo json_encode($menuData);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} 