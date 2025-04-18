<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    die(json_encode(['success' => false, 'message' => 'Usuario no autenticado']));
}

$userId = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data['action'] === 'add') {
        try {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$userId, $data['product_id']]);
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
            $count = $stmt->fetch()['count'];
            
            echo json_encode([
                'success' => true,
                'count' => (int)$count,
                'message' => 'Producto agregado al carrito'
            ]);
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al agregar al carrito'
            ]);
        }
    }
}
?>
