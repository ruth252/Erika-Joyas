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
    $action = $data['action'] ?? '';

    if ($action === 'toggle') {
        $productId = $data['product_id'];
        $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $isFavorite = false;

        if ($stmt->fetch()) {
            // Eliminar de favoritos
            $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
        } else {
            // Agregar a favoritos
            $stmt = $conn->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$userId, $productId]);
            $isFavorite = true;
        }

        // Obtener conteo actualizado
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
        $stmt->execute([$userId]);
        $count = $stmt->fetch()['count'];

        echo json_encode([
            'success' => true,
            'isFavorite' => $isFavorite,
            'count' => $count
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'list') {
        $stmt = $conn->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
        $stmt->execute([$userId]);
        $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'success' => true,
            'favorites' => $favorites,
            'count' => count($favorites)
        ]);
        exit;
    }
}
?>
