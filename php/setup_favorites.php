<?php
require_once 'db_config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS favorites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES products(id),
        UNIQUE KEY unique_favorite (user_id, product_id)
    )";
    
    $conn->exec($sql);
    echo "Tabla favorites creada correctamente";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
