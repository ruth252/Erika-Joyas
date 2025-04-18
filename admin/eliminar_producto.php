<?php
session_start();
require_once '../php/db_config.php';

if (isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        
        // Primero eliminar las referencias en el carrito
        $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ?");
        $stmt->execute([$id]);
        
        // Luego eliminar el producto
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: admin_panel.php");
        exit;
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
