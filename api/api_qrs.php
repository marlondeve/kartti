<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log para depuración
error_log("API QR - Sesión ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'no definido'));
error_log("API QR - Método: " . $_SERVER['REQUEST_METHOD']);
error_log("API QR - POST Data: " . json_encode($_POST));
error_log("API QR - Headers: " . json_encode(getallheaders()));

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    error_log("API QR - Error: Usuario no autenticado");
    error_log("API QR - Session Data: " . json_encode($_SESSION));
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'No autorizado',
        'debug' => [
            'session_id' => session_id(),
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
            'session_data' => $_SESSION
        ]
    ]);
    exit;
}

// Obtener el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// Función para obtener el restaurante del usuario actual
function getUserRestaurant($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM restaurantes WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Función para actualizar el JSON de QRs (mostrar_nombre_en_carta se guarda solo en el JSON, no en la tabla)
function actualizarJsonQRs($restaurante_id) {
    global $pdo;
    
    try {
        error_log("Actualizando JSON de QRs para restaurante: " . $restaurante_id);
        
        // Obtener todos los QRs del restaurante desde la BD (sin mostrar_nombre_en_carta)
        $stmt = $pdo->prepare("
            SELECT q.id, q.nombre, q.tipo, q.estado, q.imagen, q.created_at, r.nombre as restaurante_nombre
            FROM qr_codes q
            INNER JOIN restaurantes r ON r.user_id = q.user_id
            WHERE r.id = ?
        ");
        $stmt->execute([$restaurante_id]);
        $qrs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $dirPath = __DIR__ . '/../public/json';
        $jsonPath = $dirPath . '/qr' . $restaurante_id . '.json';
        $mostrarNombreMap = [];
        if (file_exists($jsonPath)) {
            $existing = json_decode(file_get_contents($jsonPath), true);
            if (!empty($existing['qrs']) && is_array($existing['qrs'])) {
                foreach ($existing['qrs'] as $eq) {
                    $eid = isset($eq['id']) ? $eq['id'] : null;
                    if ($eid !== null) {
                        $mostrarNombreMap[$eid] = (int)(isset($eq['mostrar_nombre_en_carta']) ? $eq['mostrar_nombre_en_carta'] : 1);
                    }
                }
            }
        }
        
        // Formatear los QRs: datos de BD + mostrar_nombre_en_carta desde el JSON existente (o 1 por defecto)
        $qrsFormateados = array_map(function($qr) use ($mostrarNombreMap) {
            return [
                "id" => $qr['id'],
                "nombre" => $qr['nombre'],
                "tipo" => $qr['tipo'],
                "estado" => $qr['estado'] ?: 'activo',
                "imagen" => $qr['imagen'],
                "created_at" => $qr['created_at'],
                "restaurante_nombre" => $qr['restaurante_nombre'],
                "mostrar_nombre_en_carta" => isset($mostrarNombreMap[$qr['id']]) ? $mostrarNombreMap[$qr['id']] : 1
            ];
        }, $qrs);
        
        if (!file_exists($dirPath)) {
            if (!mkdir($dirPath, 0777, true)) {
                throw new Exception('No se pudo crear el directorio para el JSON');
            }
        }
        
        // Guardar el JSON (qr7.json, qr10.json, etc.) con mostrar_nombre_en_carta actualizado
        $jsonPath = $dirPath . '/qr' . $restaurante_id . '.json';
        $jsonContent = json_encode(['qrs' => $qrsFormateados], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        error_log("Guardando JSON en: " . $jsonPath);
        error_log("Contenido del JSON: " . $jsonContent);
        
        if (file_put_contents($jsonPath, $jsonContent) === false) {
            throw new Exception('No se pudo escribir el archivo JSON');
        }
        
        error_log("JSON de QRs actualizado exitosamente para restaurante " . $restaurante_id);
        return true;
    } catch (Exception $e) {
        error_log("Error al actualizar JSON de QRs: " . $e->getMessage());
        return false;
    }
}

// Función para actualizar el registro de QRs
function updateQRRegistry($action, $qrData) {
    try {
        // Obtener el ID del restaurante
        $restauranteId = $qrData['restaurante_id'];
        if (!$restauranteId) {
            throw new Exception('ID del restaurante no proporcionado');
        }

        // Actualizar el JSON de QRs
        if (!actualizarJsonQRs($restauranteId)) {
            error_log("Error al actualizar el JSON de QRs para el restaurante: " . $restauranteId);
            return false;
        }

        return true;
    } catch (Exception $e) {
        error_log("Error en updateQRRegistry: " . $e->getMessage());
        return false;
    }
}

try {
    // Verificar que el usuario tenga un restaurante
    $restaurant = getUserRestaurant($_SESSION['user_id']);
    if (empty($restaurant)) {
        throw new Exception('No tienes un restaurante configurado');
    }
    
    $restauranteId = $restaurant['id'];
    error_log("API QR - ID del restaurante: " . $restauranteId);

    switch ($method) {
        case 'GET':
            $stmt = $pdo->prepare("SELECT * FROM qr_codes WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $qr_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $mostrarNombreMap = [];
            $jsonPath = __DIR__ . '/../public/json/qr' . $restauranteId . '.json';
            if (file_exists($jsonPath)) {
                $data = json_decode(file_get_contents($jsonPath), true);
                if (!empty($data['qrs']) && is_array($data['qrs'])) {
                    foreach ($data['qrs'] as $eq) {
                        if (isset($eq['id'])) {
                            $mostrarNombreMap[(int)$eq['id']] = (int)(isset($eq['mostrar_nombre_en_carta']) ? $eq['mostrar_nombre_en_carta'] : 1);
                        }
                    }
                }
            }
            foreach ($qr_codes as &$q) {
                $q['mostrar_nombre_en_carta'] = isset($mostrarNombreMap[(int)$q['id']]) ? $mostrarNombreMap[(int)$q['id']] : 1;
            }
            unset($q);
            echo json_encode([
                'success' => true,
                'data' => $qr_codes ?: []
            ]);
            break;

        case 'POST':
            // Determinar la acción
            $action = $_POST['action'] ?? '';
            error_log("API QR - POST - Acción: " . $action);
            
            switch ($action) {
                case 'create':
                    // Crear nuevo QR
                    $nombre = $_POST['nombre'] ?? '';
                    $url = $_POST['url'] ?? '';
                    $tipo = $_POST['tipo'] ?? 'restaurante'; // Valor por defecto
                    $estado = 'activo'; // Por defecto será activo

                    error_log("API QR - CREATE - Datos recibidos: " . json_encode($_POST));
                    error_log("API QR - CREATE - Valores procesados: " . json_encode([
                        'nombre' => $nombre,
                        'url' => $url,
                        'tipo' => $tipo,
                        'estado' => $estado
                    ]));

                    if (empty($nombre)) {
                        throw new Exception('El nombre es requerido');
                    }
                    if (empty($url)) {
                        throw new Exception('La URL es requerida');
                    }
                    if (empty($tipo)) {
                        throw new Exception('El tipo es requerido');
                    }

                    // Verificar si se recibió la imagen del QR
                    if (!isset($_FILES['qr_image']) || $_FILES['qr_image']['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception('Error al recibir la imagen del QR');
                    }

                    // Validar tipo de archivo
                    $allowed_types = ['image/png', 'image/jpeg', 'image/gif'];
                    if (!in_array($_FILES['qr_image']['type'], $allowed_types)) {
                        throw new Exception('Tipo de archivo no permitido. Solo se aceptan imágenes PNG, JPEG o GIF');
                    }

                    // Validar tamaño máximo (5MB)
                    $max_size = 5 * 1024 * 1024; // 5MB
                    if ($_FILES['qr_image']['size'] > $max_size) {
                        throw new Exception('El archivo es demasiado grande. El tamaño máximo permitido es 5MB');
                    }

                    // Crear directorio si no existe
                    $upload_dir = __DIR__ . '/../uploads/qrs/';
                    if (!file_exists($upload_dir)) {
                        if (!mkdir($upload_dir, 0777, true)) {
                            throw new Exception('No se pudo crear el directorio de uploads');
                        }
                    }

                    // Verificar permisos de escritura
                    if (!is_writable($upload_dir)) {
                        throw new Exception('El directorio de uploads no tiene permisos de escritura');
                    }

                    // Sanitizar nombre del archivo
                    $safe_name = preg_replace('/[^a-zA-Z0-9-_]/', '', $nombre);
                    $file_extension = pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION);
                    $qr_filename = $safe_name . '_' . uniqid() . '.' . $file_extension;
                    $qr_path = $upload_dir . $qr_filename;
                    $relative_path = 'uploads/qrs/' . $qr_filename;

                    // Mover el archivo subido
                    if (!move_uploaded_file($_FILES['qr_image']['tmp_name'], $qr_path)) {
                        throw new Exception('Error al guardar la imagen del QR');
                    }

                    try {
                        $stmt = $pdo->prepare("INSERT INTO qr_codes (user_id, nombre, url, tipo, estado, imagen) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$_SESSION['user_id'], $nombre, $relative_path, $tipo, $estado, $relative_path]);

                        $newId = $pdo->lastInsertId();
                        error_log("API QR - CREATE - QR creado con ID: " . $newId);

                        // Actualizar el registro de QRs usando el ID del restaurante correcto
                        updateQRRegistry('create', [
                            'id' => $newId,
                            'nombre' => $nombre,
                            'tipo' => $tipo,
                            'restaurante_id' => $restauranteId
                        ]);

                        echo json_encode([
                            'success' => true,
                            'message' => 'QR creado exitosamente',
                            'data' => [
                                'id' => $newId,
                                'nombre' => $nombre,
                                'url' => $relative_path,
                                'tipo' => $tipo,
                                'estado' => $estado,
                                'imagen' => $relative_path,
                                'restaurante_id' => $restauranteId
                            ]
                        ]);
                    } catch (Exception $e) {
                        // Si hay error, eliminar la imagen subida
                        if (file_exists($qr_path)) {
                            unlink($qr_path);
                        }
                        error_log("API QR - CREATE - Error: " . $e->getMessage());
                        throw new Exception('Error al crear el QR en la base de datos: ' . $e->getMessage());
                    }
                    break;

                case 'update':
                    $id = $_POST['id'] ?? null;
                    $estado = isset($_POST['estado']) ? $_POST['estado'] : null;
                    $mostrarNombreEnCarta = isset($_POST['mostrar_nombre_en_carta']) ? $_POST['mostrar_nombre_en_carta'] : null;

                    error_log("API QR - UPDATE - Datos recibidos: " . json_encode($_POST));

                    if (!$id) {
                        throw new Exception('ID del QR no proporcionado');
                    }

                    $stmt = $pdo->prepare("SELECT id, nombre, url, tipo FROM qr_codes WHERE id = ? AND user_id = ?");
                    $stmt->execute([$id, $_SESSION['user_id']]);
                    $qr = $stmt->fetch();
                    if (!$qr) {
                        throw new Exception('QR no encontrado o no autorizado');
                    }

                    $valorMostrarNombre = ($mostrarNombreEnCarta === true || $mostrarNombreEnCarta === '1' || $mostrarNombreEnCarta === 1) ? 1 : 0;

                    // mostrar_nombre_en_carta se guarda solo en el JSON (no en la tabla)
                    if ($mostrarNombreEnCarta !== null) {
                        actualizarJsonQRs($restauranteId);
                        $dirPath = __DIR__ . '/../public/json';
                        $jsonPath = $dirPath . '/qr' . $restauranteId . '.json';
                        $data = json_decode(file_get_contents($jsonPath), true);
                        if (empty($data['qrs']) || !is_array($data['qrs'])) {
                            throw new Exception('El archivo JSON del restaurante no tiene datos de QRs');
                        }
                        foreach ($data['qrs'] as &$item) {
                            if (isset($item['id']) && (int)$item['id'] === (int)$id) {
                                $item['mostrar_nombre_en_carta'] = $valorMostrarNombre;
                                break;
                            }
                        }
                        if (file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
                            throw new Exception('No se pudo actualizar el archivo JSON del QR');
                        }
                        error_log("API QR - JSON actualizado mostrar_nombre_en_carta para QR " . $id);
                    }

                    // Estado sí se actualiza en la BD
                    if ($estado !== null) {
                        $updates = ["estado = ?"];
                        $params = [$estado === 'activo' ? 'activo' : 'inactivo', $id];
                        $stmt = $pdo->prepare("UPDATE qr_codes SET " . implode(", ", $updates) . " WHERE id = ?");
                        $stmt->execute($params);
                        updateQRRegistry('update', ['id' => $id, 'restaurante_id' => $restauranteId]);
                    } elseif ($mostrarNombreEnCarta === null) {
                        throw new Exception('No se proporcionaron campos para actualizar');
                    }

                    $stmt = $pdo->prepare("SELECT * FROM qr_codes WHERE id = ?");
                    $stmt->execute([$id]);
                    $updatedQR = $stmt->fetch();

                    echo json_encode([
                        'success' => true,
                        'message' => 'QR actualizado exitosamente',
                        'data' => $updatedQR
                    ]);
                    break;

                case 'delete':
                    // Eliminar QR
                    $id = $_POST['id'] ?? null;
                    
                    error_log("API QR - DELETE - ID recibido: " . $id);

                    if (!$id) {
                        throw new Exception('ID del QR no proporcionado');
                    }

                    // Verificar que el QR pertenece al usuario
                    $stmt = $pdo->prepare("SELECT id FROM qr_codes WHERE id = ? AND user_id = ?");
                    $stmt->execute([$id, $_SESSION['user_id']]);
                    
                    if (!$stmt->fetch()) {
                        throw new Exception('QR no encontrado o no autorizado');
                    }

                    $stmt = $pdo->prepare("DELETE FROM qr_codes WHERE id = ?");
                    $stmt->execute([$id]);

                    error_log("API QR - DELETE - QR eliminado: " . $id);

                    // Actualizar el registro de QRs usando el ID del restaurante correcto
                    updateQRRegistry('delete', [
                        'id' => $id,
                        'restaurante_id' => $restauranteId
                    ]);

                    echo json_encode([
                        'success' => true,
                        'message' => 'QR eliminado exitosamente'
                    ]);
                    break;

                default:
                    error_log("API QR - Error: Acción no válida: " . $action);
                    throw new Exception('Acción no válida');
            }
            break;

        default:
            error_log("API QR - Error: Método no permitido: " . $method);
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    error_log("API QR - Error: " . $e->getMessage());
    error_log("API QR - Stack Trace: " . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'post_data' => $_POST,
            'session_data' => $_SESSION
        ]
    ]);
} 