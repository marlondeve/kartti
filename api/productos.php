<?php
// Activar reporte de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivar la visualización de errores en la salida
ini_set('log_errors', 1); // Activar el registro de errores
ini_set('error_log', __DIR__ . '/../logs/php_errors.log'); // Ruta del archivo de log

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/image_config.php';

// Verificar si el usuario está autenticado
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');

// Función para obtener el restaurante del usuario actual
function getUserRestaurant($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM restaurantes WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Función para manejar errores
function handleError($e) {
    error_log("Error en productos.php: " . $e->getMessage());
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
    exit;
}

// Función para manejar errores de PHP
function handlePhpError($errno, $errstr, $errfile, $errline) {
    error_log("Error PHP en $errfile:$errline - $errstr");
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'debug' => [
            'file' => $errfile,
            'line' => $errline
        ]
    ]);
    exit;
}

// Registrar el manejador de errores de PHP
set_error_handler('handlePhpError');

function actualizarJsonMenu($restaurante_id) {
    global $pdo;
    
    try {
        // Incluir el estado en la consulta de categorías
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
                    "price" => '$' . number_format((float)($producto['precio'] ?? 0), 0),
                    "description" => $producto['descripcion'] ?? '',
                    "image" => $producto['imagen'] ? 'https://kartti.com/' . $producto['imagen'] : '',
                    "status" => $producto['estado'] ?? 'activo'
                ];
            }, $productos);
            
            // Agregar la categoría con su estado y sus productos al array final
            $menuData[$categoria['nombre']] = [
                "status" => $categoria['estado'] ?? 'activo',
                "products" => $productosFormateados
            ];
        }
        
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
        
        // Guardar el JSON
        $jsonPath = $dirPath . '/restaurante' . $restaurante_id . '.json';
        if (file_put_contents($jsonPath, json_encode($menuData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
            throw new Exception('No se pudo escribir el archivo JSON');
        }
        
        return true;
    } catch (Exception $e) {
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
            // Log de la solicitud para depuración
            error_log("Creando producto - POST data: " . print_r($_POST, true));
            error_log("Creando producto - FILES data: " . print_r($_FILES, true));
            
            // Validar datos
            if (empty($_POST['nombre'])) {
                throw new Exception('El nombre del producto es requerido');
            }
            
            if (!isset($_POST['precio']) || $_POST['precio'] === '' || !is_numeric($_POST['precio'])) {
                throw new Exception('El precio del producto es requerido y debe ser un número');
            }
            
            // Asegurar que el precio es un entero (permite 0)
            $_POST['precio'] = (int) $_POST['precio'];
            
            if (empty($_POST['categoria_id'])) {
                throw new Exception('La categoría del producto es requerida');
            }
            
            // Verificar que la categoría pertenezca al restaurante del usuario
            $stmt = $pdo->prepare("
                SELECT id FROM categorias 
                WHERE id = ? AND restaurante_id = ?
            ");
            $stmt->execute([$_POST['categoria_id'], $restauranteId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('La categoría seleccionada no pertenece a tu restaurante');
            }
            
            // Obtener la posición máxima actual de productos en esta categoría
            $stmtMaxPos = $pdo->prepare("
                SELECT MAX(posicion) as max_posicion 
                FROM productos 
                WHERE categoria_id = ?
            ");
            $stmtMaxPos->execute([$_POST['categoria_id']]);
            $maxPos = $stmtMaxPos->fetch(PDO::FETCH_ASSOC);
            
            // La nueva posición será la máxima + 1 (o 1 si no hay productos previos)
            $nuevaPosicion = ($maxPos['max_posicion'] !== null) ? $maxPos['max_posicion'] + 1 : 1;

            // Procesar la imagen si se ha subido una
            $imagenPath = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                try {
                    $imagenPath = processAndSaveImage($_FILES['imagen']);
                    error_log("Imagen procesada exitosamente: " . $imagenPath);
                } catch (Exception $e) {
                    error_log("Error al procesar la imagen: " . $e->getMessage());
                    throw new Exception('Error al procesar la imagen: ' . $e->getMessage());
                }
            } elseif (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Solo lanzar error si hay un error real (no cuando no se envió archivo)
                $errorMap = [
                    UPLOAD_ERR_INI_SIZE => 'La imagen excede el tamaño máximo permitido por el servidor',
                    UPLOAD_ERR_FORM_SIZE => 'La imagen excede el tamaño máximo permitido por el formulario',
                    UPLOAD_ERR_PARTIAL => 'La imagen se subió parcialmente',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal en el servidor',
                    UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
                    UPLOAD_ERR_EXTENSION => 'La subida fue detenida por una extensión del servidor'
                ];
                $fileErr = $_FILES['imagen']['error'];
                $msg = $errorMap[$fileErr] ?? 'Error al subir la imagen (código ' . $fileErr . ')';
                error_log('Error de subida de imagen: ' . $msg);
                throw new Exception($msg);
            }

            // Insertar el producto
            $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, imagen, categoria_id, posicion, estado) VALUES (?, ?, ?, ?, ?, ?, 'activo')");
            
            try {
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['descripcion'] ?? null,
                    $_POST['precio'],
                    $imagenPath,
                    $_POST['categoria_id'],
                    $nuevaPosicion
                ]);
                
                $producto_id = $pdo->lastInsertId();
                error_log("Producto creado exitosamente con ID: " . $producto_id);
                
                // Obtener el producto creado
                $stmt = $pdo->prepare("SELECT id, nombre, descripcion, precio, imagen, posicion, estado FROM productos WHERE id = ?");
                $stmt->execute([$producto_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$result) {
                    throw new Exception('Error al obtener el producto creado');
                }
                if (!actualizarJsonMenu($restauranteId)) {
                    error_log("Falló la actualización del JSON para el restaurante: " . $restauranteId);
                    // Continuar con el listado aunque falle el JSON
                }
                // Devolver respuesta exitosa con el producto creado
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto creado exitosamente',
                    'producto' => $result
                ]);
            } catch (PDOException $e) {
                error_log("Error al insertar producto: " . $e->getMessage());
                throw new Exception('Error al crear el producto en la base de datos: ' . $e->getMessage());
            }
            break;
            
        case 'list':
            // Validar categoría
            if (empty($_GET['categoria_id'])) {
                throw new Exception('Se requiere ID de categoría');
            }
            
            // Verificar que la categoría pertenezca al restaurante
            $stmt = $pdo->prepare("
                SELECT id FROM categorias 
                WHERE id = ? AND restaurante_id = ?
            ");
            $stmt->execute([$_GET['categoria_id'], $restauranteId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('La categoría seleccionada no pertenece a tu restaurante');
            }
            
            // Listar productos de la categoría ordenados por posición
            $stmt = $pdo->prepare("
                SELECT id, nombre, descripcion, precio, imagen, posicion, estado
                FROM productos 
                WHERE categoria_id = ?
                ORDER BY posicion ASC
            ");
            
            $stmt->execute([$_GET['categoria_id']]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'productos' => $productos
            ]);
            break;
            
        case 'updatePositions':
            // Obtener el cuerpo de la solicitud JSON
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (!is_array($data) || empty($data)) {
                throw new Exception('Datos de posiciones no válidos');
            }
            
            // Verificar que todos los productos pertenezcan a la categoría adecuada
            $categoriaId = $data[0]['categoria_id'] ?? null;
            
            if (!$categoriaId) {
                throw new Exception('ID de categoría no proporcionado');
            }
            
            // Verificar que la categoría pertenezca al restaurante
            $stmt = $pdo->prepare("
                SELECT id FROM categorias 
                WHERE id = ? AND restaurante_id = ?
            ");
            $stmt->execute([$categoriaId, $restauranteId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('La categoría seleccionada no pertenece a tu restaurante');
            }
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            try {
                // Preparar la consulta para actualizar la posición
                $stmt = $pdo->prepare("
                    UPDATE productos 
                    SET posicion = ? 
                    WHERE id = ? AND categoria_id = ?
                ");
                
                // Actualizar las posiciones
                foreach ($data as $index => $item) {
                    $stmt->execute([
                        $index + 1, // Posición basada en el índice (empezando desde 1)
                        $item['id'],
                        $categoriaId
                    ]);
                }
                
                // Confirmar transacción
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
                // Revertir transacción en caso de error
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'update':
            // Validar datos
            if (empty($_POST['id'])) {
                throw new Exception('El ID del producto es requerido');
            }
            
            if (empty($_POST['nombre'])) {
                throw new Exception('El nombre del producto es requerido');
            }
            
            if (!isset($_POST['precio']) || $_POST['precio'] === '' || !is_numeric($_POST['precio'])) {
                throw new Exception('El precio del producto es requerido y debe ser un número');
            }
            
            // Asegurar que el precio es un entero (permite 0)
            $_POST['precio'] = (int) $_POST['precio'];
            
            if (empty($_POST['categoria_id'])) {
                throw new Exception('La categoría del producto es requerida');
            }
            
            // Verificar que el producto pertenezca al restaurante del usuario
            $stmt = $pdo->prepare("
                SELECT p.id, p.imagen 
                FROM productos p
                JOIN categorias c ON p.categoria_id = c.id
                WHERE p.id = ? AND c.restaurante_id = ?
            ");
            $stmt->execute([$_POST['id'], $restauranteId]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$producto) {
                throw new Exception('El producto no pertenece a tu restaurante');
            }
            
            // Procesar la imagen si se ha subido una nueva
            $imagenPath = $producto['imagen']; // Mantener la imagen existente por defecto
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Eliminar la imagen anterior si existe
                    if ($producto['imagen']) {
                        $imagenAnterior = __DIR__ . '/../' . $producto['imagen'];
                        if (file_exists($imagenAnterior)) {
                            unlink($imagenAnterior);
                        }
                    }
                    
                    // Procesar y guardar la nueva imagen
                    $imagenPath = processAndSaveImage($_FILES['imagen']);
                } catch (Exception $e) {
                    throw new Exception('Error al procesar la imagen: ' . $e->getMessage());
                }
            }
            
            // Actualizar el producto
            $stmt = $pdo->prepare("
                UPDATE productos 
                SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, categoria_id = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['nombre'],
                $_POST['descripcion'] ?? null,
                $_POST['precio'],
                $imagenPath,
                $_POST['categoria_id'],
                $_POST['id']
            ]);
            if (!actualizarJsonMenu($restauranteId)) {
                error_log("Falló la actualización del JSON para el restaurante: " . $restauranteId);
                // Continuar con el listado aunque falle el JSON
            }
            echo json_encode([
                'success' => true,
                'message' => 'Producto actualizado correctamente',
                'imagen' => $imagenPath
            ]);
            break;
            
        case 'delete':
            // Validar datos
            if (empty($_POST['id'])) {
                throw new Exception('El ID del producto es requerido');
            }
            
            // Verificar que el producto pertenezca al restaurante del usuario
            $stmt = $pdo->prepare("
                SELECT p.id, p.imagen 
                FROM productos p
                JOIN categorias c ON p.categoria_id = c.id
                WHERE p.id = ? AND c.restaurante_id = ?
            ");
            $stmt->execute([$_POST['id'], $restauranteId]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$producto) {
                throw new Exception('El producto no pertenece a tu restaurante');
            }
            
            // Eliminar la imagen si existe
            if ($producto['imagen']) {
                $imagenPath = __DIR__ . '/../' . $producto['imagen'];
                if (file_exists($imagenPath)) {
                    unlink($imagenPath);
                }
            }
            
            // Eliminar el producto
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            // Agregar la actualización del JSON aquí
            if (!actualizarJsonMenu($restauranteId)) {
                error_log("Falló la actualización del JSON para el restaurante: " . $restauranteId);
                // Continuar con el listado aunque falle el JSON
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Producto eliminado correctamente'
            ]);
            break;
            
        case 'updateJson':
            // Obtener el cuerpo de la solicitud JSON
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (!isset($data['restaurante_id']) || !isset($data['producto_id']) || !isset($data['estado'])) {
                throw new Exception('Datos incompletos para actualizar el estado del producto');
            }
            
            // Verificar que el producto pertenezca al restaurante
            $stmt = $pdo->prepare("
                SELECT p.id 
                FROM productos p
                JOIN categorias c ON p.categoria_id = c.id
                WHERE p.id = ? AND c.restaurante_id = ?
            ");
            $stmt->execute([$data['producto_id'], $data['restaurante_id']]);
            
            if (!$stmt->fetch()) {
                throw new Exception('El producto no pertenece a tu restaurante');
            }
            
            // Actualizar el estado del producto
            $stmt = $pdo->prepare("UPDATE productos SET estado = ? WHERE id = ?");
            $stmt->execute([$data['estado'], $data['producto_id']]);
            
            // Actualizar el JSON del menú
            if (!actualizarJsonMenu($data['restaurante_id'])) {
                error_log("Falló la actualización del JSON para el restaurante: " . $data['restaurante_id']);
                // Continuar con el listado aunque falle el JSON
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Estado del producto actualizado correctamente'
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    handleError($e);
} 
