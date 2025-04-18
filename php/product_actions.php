<?php
session_start();
require_once 'db_config.php';

// Para obtener un producto
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $id = $_GET['id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Para editar o eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_POST['action'] ?? $input['action'] ?? '';

    try {
        switch ($action) {
            case 'edit':
                $id = $_POST['id'];
                $name = $_POST['name'];
                $price = $_POST['price'];
                $description = $_POST['description'];
                $stock = $_POST['stock'];

                $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, stock = ? WHERE id = ?");
                $stmt->execute([$name, $price, $description, $stock, $id]);
                
                echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente']);
                break;

            case 'delete':
                $id = $input['id'];
                $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
                
                echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente']);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
