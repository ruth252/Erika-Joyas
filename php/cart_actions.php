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
    
    switch($data['action']) {
        case 'add':
            try {
                // Verificar si ya existe en el carrito
                $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $data['product_id']]);
                if ($stmt->fetch()) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Este producto ya está en tu carrito. Ajusta la cantidad desde allí.'
                    ]);
                    exit;
                }

                // Verificar stock disponible
                $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
                $stmt->execute([$data['product_id']]);
                $product = $stmt->fetch();
                
                if (!$product || $product['stock'] <= 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Producto no disponible'
                    ]);
                    exit;
                }

                // Agregar al carrito
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                $stmt->execute([$userId, $data['product_id']]);
                
                // Obtener conteo actualizado
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
                $stmt->execute([$userId]);
                
                echo json_encode([
                    'success' => true,
                    'count' => $stmt->fetch()['count'],
                    'message' => '¡Producto agregado al carrito!'
                ]);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error al agregar al carrito']);
            }
            break;

        case 'update':
            try {
                $stmt = $conn->prepare("
                    UPDATE cart 
                    SET quantity = quantity + ? 
                    WHERE user_id = ? AND product_id = ?
                ");
                $stmt->execute([$data['change'], $userId, $data['product_id']]);
                
                // Eliminar si cantidad es 0
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND quantity <= 0");
                $stmt->execute([$userId]);
                
                // Recalcular totales
                $stmt = $conn->prepare("
                    SELECT 
                        SUM(c.quantity * p.price) as subtotal,
                        COUNT(*) as count
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ?
                ");
                $stmt->execute([$userId]);
                $result = $stmt->fetch();
                
                $subtotal = floatval($result['subtotal']);
                $itbis = $subtotal * 0.18;
                
                echo json_encode([
                    'success' => true,
                    'count' => (int)$result['count'],
                    'summary' => [
                        'subtotal' => number_format($subtotal, 2),
                        'itbis' => number_format($itbis, 2),
                        'total' => number_format($subtotal + $itbis, 2)
                    ]
                ]);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar cantidad']);
            }
            break;

        case 'remove':
            try {
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $data['product_id']]);
                
                // Recalcular totales después de eliminar
                $stmt = $conn->prepare("
                    SELECT SUM(c.quantity * p.price) as subtotal,
                           COUNT(*) as count
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ?
                ");
                $stmt->execute([$userId]);
                $result = $stmt->fetch();
                
                $subtotal = $result['subtotal'] ?? 0;
                $itbis = $subtotal * 0.18;
                
                echo json_encode([
                    'success' => true,
                    'count' => (int)$result['count'],
                    'summary' => [
                        'subtotal' => number_format($subtotal, 2),
                        'itbis' => number_format($itbis, 2),
                        'total' => number_format($subtotal + $itbis, 2)
                    ]
                ]);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar producto']);
            }
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("
            SELECT c.*, p.name, p.price, p.image 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'itbis' => number_format($itbis, 2, '.', ''),
                'total' => number_format($total, 2, '.', '')
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener carrito']);
    }
}
?>
