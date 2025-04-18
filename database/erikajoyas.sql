-- Eliminar la base de datos si existe
DROP DATABASE IF EXISTS erikajoyas_db;

-- Crear la nueva base de datos
CREATE DATABASE erikajoyas_db;
USE erikajoyas_db;

-- Tabla de usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    photo TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de productos
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT,
    stock INT NOT NULL DEFAULT 0,
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de carrito
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Tabla de favoritos
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insertar productos de ejemplo
INSERT INTO products (name, price, category, image, description, stock, available) VALUES 
('Collar de Oro 18k', 299.99, 'collares', 'collar1.jpg', 'Hermoso collar de oro de 18k con diseño elegante', 10, true),
('Anillo de Plata Sterling', 149.99, 'anillos', 'anillo1.jpg', 'Anillo de plata sterling con incrustaciones de zirconia', 15, true),
('Pulsera de Oro Rosa', 199.99, 'pulseras', 'pulsera1.jpg', 'Pulsera delicada de oro rosa de 14k', 8, true),
('Aretes de Perla', 89.99, 'aretes', 'aretes1.jpg', 'Aretes con perlas naturales y detalles en plata', 20, true);
