<?php
session_start();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $category = $_GET['category'] ?? 'todos';
        
        if ($category === 'todos') {
            $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
        } else {
            $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
            $stmt->execute([$category]);
        }
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'products' => array_map(function($product) {
                return [
                    'id' => $product['id'],
                    'nombre' => $product['name'],
                    'precio' => floatval($product['price']),
                    'descripcion' => $product['description'],
                    'imagen' => $product['image'],
                    'categoria' => $product['category'],
                    'stock' => intval($product['stock']),
                    'disponible' => (bool)$product['available']
                ];
            }, $products)
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error al cargar productos']);
    }
}

// Manejo del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
        exit;
    }
    
    $user_id = $_SESSION['user']['id'];
    
    switch ($_POST['action']) {
        case 'add_to_cart':
            $product_id = $_POST['product_id'];
            $quantity = $_POST['quantity'] ?? 1;
            
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
            echo json_encode(['success' => true]);
            break;
            
        case 'get_cart':
            $stmt = $conn->prepare("
                SELECT p.*, c.quantity 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?
            ");
            $stmt->execute([$user_id]);
            $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'cart' => $cart]);
            break;
    }
}
?>
