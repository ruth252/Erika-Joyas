<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die(json_encode(['success' => false, 'message' => 'Acceso denegado']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'edit_product') {
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            $price = floatval($_POST['price']);
            $category = trim($_POST['category']);
            $description = trim($_POST['description']);
            $stock = intval($_POST['stock']);

            // Validar datos
            if (empty($name) || $price <= 0 || empty($category)) {
                throw new Exception('Todos los campos son requeridos');
            }

            // Preparar la consulta base
            $sql = "UPDATE products SET name = ?, price = ?, category = ?, description = ?, stock = ?";
            $params = [$name, $price, $category, $description, $stock];

            // Procesar imagen si se subió una nueva
            if (!empty($_FILES['image']['name'])) {
                $image = time() . '_' . basename($_FILES['image']['name']);
                $target = "../Imagenes/" . $image;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $sql .= ", image = ?";
                    $params[] = $image;
                }
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto actualizado correctamente'
                ]);
            } else {
                throw new Exception('No se pudo actualizar el producto');
            }
        }
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>
