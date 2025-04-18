<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

// Verificar si es una solicitud para obtener el contador
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => intval($result['count'])
    ]);
    exit;
}

if (!isset($_SESSION['user'])) {
    die(json_encode(['success' => false, 'message' => 'No has iniciado sesión']));
}

$userId = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    switch($data['action']) {
        case 'add':
            // Verificar si el producto ya está en el carrito
            $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $data['product_id']]);
            if ($stmt->fetch()) {
                // Si ya existe, incrementar la cantidad
                $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $data['product_id']]);
            } else {
                // Si no existe, insertar nuevo
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                $stmt->execute([$userId, $data['product_id']]);
            }
            break;

        case 'update':
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$data['change'], $userId, $data['product_id']]);

            // Eliminar si cantidad es 0 o menor
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND quantity <= 0");
            $stmt->execute([$userId]);
            break;

        case 'remove':
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $data['product_id']]);
            break;
    }

    // Retornar estado actualizado del carrito
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $count = $stmt->fetch()['count'];
    
    echo json_encode([
        'success' => true,
        'count' => intval($count)
    ]);
    exit;
}

// Para solicitudes GET normales, retornar los items del carrito
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totales
    $subtotal = 0;
    foreach ($items as &$item) {
        $item['subtotal'] = $item['price'] * $item['quantity'];
        $subtotal += $item['subtotal'];
    }

    $itbis = $subtotal * 0.18;
    $total = $subtotal + $itbis;

    echo json_encode([
        'success' => true,
        'items' => $items,
        'summary' => [
            'subtotal' => $subtotal,
            'itbis' => $itbis,
            'total' => $total
        ]
    ]);
}
?>
