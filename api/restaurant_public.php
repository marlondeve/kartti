<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

try {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) {
        throw new Exception('ID de restaurante no proporcionado');
    }

    $stmt = $pdo->prepare("SELECT id, nombre, call_waiter_enabled, whatsapp FROM restaurantes WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $rest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rest) {
        throw new Exception('Restaurante no encontrado');
    }

    echo json_encode(['success' => true, 'data' => [
        'id' => (int)$rest['id'],
        'nombre' => $rest['nombre'],
        'call_waiter_enabled' => isset($rest['call_waiter_enabled']) ? (int)$rest['call_waiter_enabled'] : 1,
        'whatsapp' => $rest['whatsapp']
    ]]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}