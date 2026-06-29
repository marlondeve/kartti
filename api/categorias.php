<?php
require_once __DIR__ . '/../config/config.php';

// Verificar si el usuario está autenticado
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Función para obtener el restaurante del usuario actual
function getUserRestaurant($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM restaurantes WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function actualizarJsonMenu($restaurante_id) {
    global $pdo;
    
    try {
        // Cambiar la consulta para incluir el estado
        $stmt = $pdo->prepare("SELECT id, nombre, estado FROM categorias WHERE restaurante_id = ? ORDER BY posicion");
        $stmt->execute([$restaurante_id]);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $menuData = [];
        
        // Para cada categoría, obtener sus productos
        foreach ($categorias as $categoria) {
            $stmt = $pdo->prepare("
                SELECT nombre, precio, descripcion, imagen, estado 
                FROM productos 
                WHERE categoria_id = ? 
                ORDER BY posicion
            ");
            $stmt->execute([$categoria['id']]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear los productos según la estructura requerida
            $productosFormateados = array_map(function($producto) {
                return [
                    "name" => $producto['nombre'] ?? '',
                    "price" => '€' . number_format((float)($producto['precio'] ?? 0), 0),
                    "description" => $producto['descripcion'] ?? '',
                    "image" => $producto['imagen'] ? 'https://kartti.com/' . $producto['imagen'] : '',
                    "status" => $producto['estado'] ?? 'activo'
                ];
            }, $productos);
            
            // Agregar la categoría con su estado y productos
            $menuData[$categoria['nombre']] = [
                'status' => $categoria['estado'] ?? 'activo',
                'products' => $productosFormateados
            ];
        }
        
        // Agregar logs para debug
        error_log("JSON a guardar: " . json_encode($menuData, JSON_PRETTY_PRINT));
        
        // Crear el directorio si no existe
        $dirPath = $_SERVER['DOCUMENT_ROOT'] . '/public/json';
        if (!file_exists($dirPath)) {
            if (!mkdir($dirPath, 0777, true)) {
                throw new Exception('No se pudo crear el directorio para el JSON');
            }
        }
        
        // Asegurarse de que el directorio tenga permisos de escritura
        if (!is_writable($dirPath)) {
            throw new Exception('El directorio no tiene permisos de escritura');
        }
        
        $jsonPath = $dirPath . '/restaurante' . $restaurante_id . '.json';
        // Preservar _settings y otras claves que empiecen por _ (ej. colores, WhatsApp) al reescribir el menú
        $dataToWrite = $menuData;
        if (file_exists($jsonPath)) {
            $existing = json_decode(file_get_contents($jsonPath), true);
            if (is_array($existing)) {
                foreach ($existing as $key => $value) {
                    if (strpos($key, '_') === 0) {
                        $dataToWrite[$key] = $value;
                    }
                }
            }
        }
        error_log("Guardando JSON en: " . $jsonPath);
        if (file_put_contents($jsonPath, json_encode($dataToWrite, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
            throw new Exception('No se pudo escribir el archivo JSON');
        }
        error_log("JSON actualizado exitosamente");
        
        return true;
    } catch (Exception $e) {
        // Mejorar el log de errores
        error_log("Error al actualizar JSON del menú: " . $e->getMessage());
        error_log("Trace: " . $e->getTraceAsString());
        return false;
    }
}
// Obtener la acción solicitada
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {
    // Verificar que el usuario tenga un restaurante
    $restaurant = getUserRestaurant($_SESSION['user_id']);
    if (empty($restaurant)) {
        throw new Exception('No tienes un restaurante configurado');
    }
    
    $restauranteId = $restaurant['id'];
    
    switch ($action) {
        case 'create':
            // Validar datos
            if (empty($_POST['nombre'])) {
                throw new Exception('El nombre de la categoría es requerido');
            }

            // Obtener la posición máxima actual
            $stmtMaxPos = $pdo->prepare("
                SELECT MAX(posicion) as max_posicion 
                FROM categorias 
                WHERE restaurante_id = ?
            ");
            $stmtMaxPos->execute([$restauranteId]);
            $maxPos = $stmtMaxPos->fetch(PDO::FETCH_ASSOC);
            
            // La nueva posición será la máxima + 1 (o 1 si no hay categorías previas)
            $nuevaPosicion = ($maxPos['max_posicion'] !== null) ? $maxPos['max_posicion'] + 1 : 1;

            // Insertar nueva categoría
            $stmt = $pdo->prepare("
                INSERT INTO categorias (nombre, restaurante_id, posicion, estado) 
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['nombre'],
                $restauranteId,
                $nuevaPosicion,
                'activo'
            ]);

            $categoriaId = $pdo->lastInsertId();
            if (!actualizarJsonMenu($restauranteId)) {
                error_log("Falló la actualización del JSON para el restaurante: " . $restauranteId);
                // Continuar con el listado aunque falle el JSON
            }
            echo json_encode([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'id' => $categoriaId,
                'nombre' => $_POST['nombre'],
                'posicion' => $nuevaPosicion
            ]);
            break;
            
        case 'list':
            // Listar categorías del restaurante
            $stmt = $pdo->prepare("
                SELECT id, nombre, posicion, estado 
                FROM categorias 
                WHERE restaurante_id = ?
                ORDER BY posicion ASC
            ");
            
            $stmt->execute([$restauranteId]);
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Contar productos por categoría
            foreach ($categorias as &$categoria) {
                $stmtCount = $pdo->prepare("
                    SELECT COUNT(*) as total 
                    FROM productos 
                    WHERE categoria_id = ?
                ");
                $stmtCount->execute([$categoria['id']]);
                $count = $stmtCount->fetch(PDO::FETCH_ASSOC);
                $categoria['productos_count'] = $count['total'];
            }
            
            echo json_encode([
                'success' => true,
                'categorias' => $categorias,
                'restaurante_id' => $restauranteId
            ]);
            break;
            
        case 'updatePositions':
            // Recibir el JSON con las nuevas posiciones
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !is_array($data)) {
                throw new Exception('Datos inválidos');
            }
            
            // Preparar la consulta de actualización
            $updateStmt = $pdo->prepare("
                UPDATE categorias 
                SET posicion = ? 
                WHERE id = ? AND restaurante_id = ?
            ");
            
            // Verificar primero que todas las categorías pertenecen al restaurante del usuario
            foreach ($data as $item) {
                if (empty($item['id']) || !isset($item['position'])) {
                    throw new Exception('Datos incompletos para una o más categorías');
                }
                
                $checkStmt = $pdo->prepare("
                    SELECT id FROM categorias 
                    WHERE id = ? AND restaurante_id = ?
                ");
                $checkStmt->execute([$item['id'], $restauranteId]);
                
                if (!$checkStmt->fetch()) {
                    throw new Exception('Una o más categorías no pertenecen a tu restaurante');
                }
            }
            
            // Actualizar las posiciones
            $pdo->beginTransaction();
            
            try {
                foreach ($data as $item) {
                    $updateStmt->execute([
                        $item['position'],
                        $item['id'],
                        $restauranteId
                    ]);
                }
                
                $pdo->commit();

                if (!actualizarJsonMenu($restauranteId)) {
                    error_log("Falló la actualización del JSON para el restaurante: " . $restauranteId);
                    // Continuar con el listado aunque falle el JSON
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Posiciones actualizadas correctamente'
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'update':
            // Recibir y validar los datos
            if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception('ID de categoría inválido');
            }
            
            if (empty($_POST['nombre'])) {
                throw new Exception('El nombre de la categoría es requerido');
            }
            
            // Verificar que la categoría pertenezca al restaurante del usuario
            $stmt = $pdo->prepare("
                SELECT id FROM categorias 
                WHERE id = ? AND restaurante_id = ?
            ");
            $stmt->execute([$_POST['id'], $restauranteId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('La categoría no pertenece a tu restaurante');
            }
            
            // Actualizar la categoría
            $updateStmt = $pdo->prepare("
                UPDATE categorias 
                SET nombre = ? 
                WHERE id = ? AND restaurante_id = ?
            ");
            
            $updateStmt->execute([
                $_POST['nombre'],
                $_POST['id'],
                $restauranteId
            ]);
            
            if (!actualizarJsonMenu($restauranteId)) {
                error_log("Falló la actualización del JSON para el restaurante: " . $restauranteId);
                // Continuar con el listado aunque falle el JSON
            }

            echo json_encode([
                'success' => true,
                'message' => 'Categoría actualizada correctamente',
                'id' => $_POST['id'],
                'nombre' => $_POST['nombre']
            ]);
            break;
            
        case 'delete':
            // Validar el ID
            if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception('ID de categoría inválido');
            }
            
            // Verificar que la categoría pertenezca al restaurante del usuario
            $stmt = $pdo->prepare("
                SELECT c.id, COUNT(p.id) as productos_count 
                FROM categorias c
                LEFT JOIN productos p ON c.id = p.categoria_id
                WHERE c.id = ? AND c.restaurante_id = ?
                GROUP BY c.id
            ");
            $stmt->execute([$_POST['id'], $restauranteId]);
            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$categoria) {
                throw new Exception('La categoría no pertenece a tu restaurante');
            }
            
            // Verificar si la categoría tiene productos
            if ($categoria['productos_count'] > 0) {
                throw new Exception('No puedes eliminar una categoría que contiene productos. Elimina los productos primero.');
            }
            
            // Eliminar la categoría
            $deleteStmt = $pdo->prepare("
                DELETE FROM categorias 
                WHERE id = ? AND restaurante_id = ?
            ");
            
            $deleteStmt->execute([
                $_POST['id'],
                $restauranteId
            ]);
            
            // Reordenar las posiciones después de eliminar
            $reorderStmt = $pdo->prepare("
                UPDATE categorias 
                SET posicion = (@pos := @pos + 1) - 1
                WHERE restaurante_id = ?
                ORDER BY posicion
            ");
            
            $pdo->query("SET @pos = 0");
            $reorderStmt->execute([$restauranteId]);
            
            if (!actualizarJsonMenu($restauranteId)) {
                error_log("Falló la actualización del JSON para el restaurante: " . $restauranteId);
                // Continuar con el listado aunque falle el JSON
            }

            echo json_encode([
                'success' => true,
                'message' => 'Categoría eliminada correctamente'
            ]);
            break;
            
        case 'updateStatus':
            // Obtener el cuerpo de la solicitud JSON
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (!isset($data['categoria_id']) || !isset($data['estado'])) {
                throw new Exception('Datos incompletos para actualizar el estado de la categoría');
            }
            
            // Verificar que la categoría pertenezca al restaurante
            $stmt = $pdo->prepare("
                SELECT id FROM categorias 
                WHERE id = ? AND restaurante_id = ?
            ");
            $stmt->execute([$data['categoria_id'], $restauranteId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('La categoría no pertenece a tu restaurante');
            }
            
            // Actualizar el estado de la categoría
            $stmt = $pdo->prepare("UPDATE categorias SET estado = ? WHERE id = ?");
            $stmt->execute([$data['estado'], $data['categoria_id']]);
            
            // Actualizar el JSON del menú
            if (!actualizarJsonMenu($restauranteId)) {
                error_log("Falló la actualización del JSON para el restaurante: " . $restauranteId);
                // Continuar con el listado aunque falle el JSON
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Estado de la categoría actualizado correctamente'
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 