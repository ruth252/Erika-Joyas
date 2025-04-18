<?php
require_once 'db_config.php';

try {
    $conn->exec("DROP TABLE IF EXISTS cart");
    $conn->exec("CREATE TABLE cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )");
    echo "Tabla cart creada correctamente";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
