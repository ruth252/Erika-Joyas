<?php
session_start();
require_once '../php/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    try {
        // Validación de campos
        if (empty($_POST['name'])) $errors[] = "El nombre es requerido";
        if (empty($_POST['price']) || !is_numeric($_POST['price'])) $errors[] = "El precio debe ser un número válido";
        if (empty($_POST['stock']) || !is_numeric($_POST['stock'])) $errors[] = "El stock debe ser un número válido";
        if (empty($_POST['description'])) $errors[] = "La descripción es requerida";
        
        // Validación de imagen
        if ($_FILES['image']['error'] !== 0) {
            $errors[] = "Error al subir la imagen";
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                $errors[] = "Tipo de archivo no permitido. Use JPG o PNG";
            }
        }

        if (empty($errors)) {
            $name = trim($_POST['name']);
            $price = floatval($_POST['price']);
            $category = $_POST['category'];
            $description = trim($_POST['description']);
            $stock = intval($_POST['stock']);
            
            $image = $_FILES['image'];
            $imageName = time() . '_' . basename($image['name']);
            $target = "../Imagenes/" . $imageName;
            
            if (move_uploaded_file($image['tmp_name'], $target)) {
                $stmt = $conn->prepare("INSERT INTO products (name, price, category, image, description, stock) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $price, $category, $imageName, $description, $stock])) {
                    $_SESSION['success_message'] = "Producto agregado exitosamente";
                    header("Location: admin_panel.php");
                    exit;
                } else {
                    $errors[] = "Error al guardar en la base de datos";
                }
            } else {
                $errors[] = "Error al mover el archivo subido";
            }
        }
    } catch(PDOException $e) {
        $errors[] = "Error en la base de datos: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        
        @import url('https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap'); 
        
        body {
            font-family: 'Nunito', sans-serif;
            background: #f4f4f4;
            color: #333;
        }

        .admin-form {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .volver-btn {
            padding: 10px 20px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-guardar {
            width: 100%;
            padding: 15px;
            background: #d4af37;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .price-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .price-info {
            color: #666;
            font-size: 0.9em;
        }

        .price-with-tax {
            color: #d4af37;
            font-weight: bold;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-form">
        <div class="form-header">
            <h2>Agregar Nuevo Producto</h2>
            <a href="admin_panel.php" class="volver-btn">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages" style="color: red; margin-bottom: 15px; padding: 10px; background: #ffe6e6; border-radius: 5px;">
                <?php foreach ($errors as $error): ?>
                    <p style="margin: 5px 0;"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="productForm" onsubmit="return validateForm()">
            <div class="form-group">
                <label>Nombre del Producto:</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Precio:</label>
                <input type="number" name="price" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Categoría:</label>
                <select name="category" required>
                    <option value="collares">Collares</option>
                    <option value="anillos">Anillos</option>
                    <option value="pulseras">Pulseras</option>
                    <option value="aretes">Aretes</option>
                </select>
            </div>

            <div class="form-group">
                <label>Imagen:</label>
                <input type="file" name="image" accept="image/*" required>
            </div>

            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="description" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label>Stock disponible:</label>
                <input type="number" name="stock" min="0" required>
            </div>

            <button type="submit" class="btn-guardar">
                <i class="fas fa-save"></i> Guardar Producto
            </button>
        </form>
    </div>

    <script>
    function validateForm() {
        const name = document.querySelector('input[name="name"]').value.trim();
        const price = document.querySelector('input[name="price"]').value;
        const stock = document.querySelector('input[name="stock"]').value;
        const description = document.querySelector('textarea[name="description"]').value.trim();
        const image = document.querySelector('input[name="image"]').files[0];

        let errors = [];

        if (!name) errors.push("El nombre es requerido");
        if (!price || price <= 0) errors.push("El precio debe ser mayor a 0");
        if (!stock || stock < 0) errors.push("El stock no puede ser negativo");
        if (!description) errors.push("La descripción es requerida");
        if (!image) errors.push("La imagen es requerida");

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return false;
        }

        return confirm('¿Estás seguro de que deseas agregar este producto?');
    }
    </script>
</body>
</html>
