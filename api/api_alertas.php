<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/config.php';

try {
    $db = getDBConnection();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            // Obtener datos del body
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['restaurante_id']) || !isset($data['tipo']) || !isset($data['nombre'])) {
                throw new Exception('Faltan datos requeridos');
            }
            
            // Validar que el QR existe y corresponde al restaurante
            $stmt = $db->prepare("
                SELECT q.id 
                FROM qr_codes q 
                INNER JOIN restaurantes r ON r.user_id = q.user_id 
                WHERE r.id = ? AND q.tipo = ? AND q.nombre = ?
            ");
            
            $stmt->execute([
                $data['restaurante_id'],
                $data['tipo'],
                $data['nombre']
            ]);
            
            $qr = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$qr) {
                throw new Exception('QR no encontrado o no pertenece a este restaurante');
            }
            
            // Establecer zona horaria de Colombia
            date_default_timezone_set('America/Bogota');
            $fechaColombia = date('Y-m-d H:i:s');
            // Insertar la alerta usando el ID del QR
            $stmt = $db->prepare("
                INSERT INTO alertas (
                    estado, 
                    qr_id,
                    fecha_creacion
                ) VALUES (
                    'pendiente',
                    ?,
                    ?
                )
            ");
            $stmt->execute([$qr['id'], $fechaColombia]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Alerta creada exitosamente',
                'data' => [
                    'id' => $db->lastInsertId(),
                    'qr_id' => $qr['id']
                ]
            ]);
            break;
            
        case 'GET':
            // Verificar sesión activa
            session_start();
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Sesión no válida');
            }
            
            // Permitir filtrar por estado (?estado=pendiente o ?estado=resuelta)
            $estado = isset($_GET['estado']) ? $_GET['estado'] : 'pendiente';
            $uid = (int)$_SESSION['user_id'];
            $stmt = $db->prepare("
                SELECT a.*, q.nombre, q.tipo
                FROM alertas a
                INNER JOIN qr_codes q ON a.qr_id = q.id
                WHERE a.estado = ? AND q.user_id = ?
                ORDER BY a.fecha_creacion DESC
            ");
            $stmt->execute([$estado, $uid]);
            $alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $alertas
            ]);
            break;
            
        case 'PUT':
            // Verificar sesión activa
            session_start();
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Sesión no válida');
            }
            
            // Actualizar estado de una alerta
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['alerta_id']) || !isset($data['estado'])) {
                throw new Exception('Faltan datos requeridos');
            }
            
            // Actualizar solo si la alerta pertenece al usuario logueado
            $stmt = $db->prepare("
                UPDATE alertas a
                INNER JOIN qr_codes q ON a.qr_id = q.id 
                SET a.estado = ? 
                WHERE a.id = ? AND q.user_id = ?
            ");
            $stmt->execute([$data['estado'], $data['alerta_id'], $_SESSION['user_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Estado de alerta actualizado'
            ]);
            break;
            
        case 'DELETE':
            // Verificar sesión activa
            session_start();
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Sesión no válida');
            }
            
            // Eliminar una alerta por id o todas por estado
            parse_str(file_get_contents('php://input'), $deleteVars);
            if (isset($deleteVars['alerta_id'])) {
                // Eliminar solo si la alerta pertenece al usuario logueado
                $stmt = $db->prepare("
                    DELETE a FROM alertas a 
                    INNER JOIN qr_codes q ON a.qr_id = q.id 
                    WHERE a.id = ? AND q.user_id = ?
                ");
                $stmt->execute([$deleteVars['alerta_id'], $_SESSION['user_id']]);
                echo json_encode([
                    'success' => true,
                    'message' => 'Alerta eliminada'
                ]);
            } elseif (isset($deleteVars['all']) && $deleteVars['all'] == 'true' && isset($deleteVars['estado'])) {
                // Eliminar todas las alertas del estado especificado solo del usuario logueado
                $stmt = $db->prepare("
                    DELETE a FROM alertas a 
                    INNER JOIN qr_codes q ON a.qr_id = q.id 
                    WHERE a.estado = ? AND q.user_id = ?
                ");
                $stmt->execute([$deleteVars['estado'], $_SESSION['user_id']]);
                echo json_encode([
                    'success' => true,
                    'message' => 'Todas las alertas eliminadas'
                ]);
            } else {
                throw new Exception('Parámetros insuficientes para eliminar');
            }
            break;
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
