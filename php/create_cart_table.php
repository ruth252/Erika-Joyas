<?php
require_once 'db_config.php';

try {
    // Eliminar tabla si existe
    $conn->exec("DROP TABLE IF EXISTS cart");
    
    // Crear nueva tabla
    $sql = "CREATE TABLE cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    
    $conn->exec($sql);
    echo "Tabla cart creada exitosamente";
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
